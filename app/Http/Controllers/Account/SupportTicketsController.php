<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Jobs\SendSupportTicketCreatedOpsEmail;
use App\Jobs\SendSupportTicketCustomerMessageOpsEmail;
use App\Models\Order;
use App\Models\RefundAttempt;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\SupportTicketCategory;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SupportTicketsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $tickets = SupportTicket::query()
            ->with(['category', 'order']) // order badge için gerekli
            ->where('user_id', $user->id)
            ->orderByDesc('last_message_at')
            ->get();

        return view('pages.account.tickets', [
            'tickets' => $tickets,
        ]);
    }

    public function show(Request $request, SupportTicket $ticket)
    {
        $user = $request->user();

        abort_unless($ticket->user_id === $user->id, 403);

        $ticket->load([
            'category',
            'order',
            'messages.author',
            'messages.media',
        ]);

        return view('pages.account.ticket-detail', [
            'ticket' => $ticket,
        ]);
    }

    public function create(Request $request)
    {
        $user = $request->user();

        $categories = SupportTicketCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $orders = Order::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(100)
            ->select(['id', 'code'])
            ->selectSub(function ($q) use ($user) {
                $q->from('support_tickets')
                    ->selectRaw('1')
                    ->whereColumn('support_tickets.order_id', 'orders.id')
                    ->where('support_tickets.user_id', $user->id)
                    ->whereNull('support_tickets.deleted_at')
                    ->limit(1);
            }, 'has_ticket')
            ->get()
            ->each(function ($o) {
                $o->has_ticket = (bool) $o->has_ticket;
            });

        // ---- Prefill: category ----
        $prefillCategoryId = $request->integer('category_id') ?: null;
        $lockCategory = false;

        if ($prefillCategoryId) {
            // aktif kategori mi?
            SupportTicketCategory::query()
                ->where('id', $prefillCategoryId)
                ->where('is_active', true)
                ->firstOrFail();

            $lockCategory = true;
        }

        // ---- Prefill: order ----
        $prefillOrderId = $request->integer('order_id') ?: null;
        $lockOrder = false;

        if ($prefillOrderId) {
            // ownership
            Order::query()
                ->where('id', $prefillOrderId)
                ->where('user_id', $user->id)
                ->firstOrFail();

            // already has ticket? -> redirect to detail
            $existing = SupportTicket::query()
                ->where('user_id', $user->id)
                ->where('order_id', $prefillOrderId)
                ->first();

            if ($existing) {
                return redirect()->to(localized_route('account.tickets.show', ['ticket' => $existing->id]));
            }

            $lockOrder = true;
        }

        return view('pages.account.ticket-create', [
            'categories' => $categories,
            'orders' => $orders,

            'prefillCategoryId' => $prefillCategoryId,
            'lockCategory' => $lockCategory,

            'prefillOrderId' => $prefillOrderId,
            'lockOrder' => $lockOrder,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        // ---- Lock enforcement (server otoritesi) ----
        // Create sayfasına query ile gelen order_id / category_id, store anında da korunur.
        // Client manipülasyonu (disabled kaldırma, hidden değiştirme, direct POST) etkisiz kalır.
        $lockedOrderId = $request->integer('order_id') ?: null;
        $lockedCategoryId = $request->integer('category_id') ?: null;

        if ($lockedCategoryId) {
            SupportTicketCategory::query()
                ->where('id', $lockedCategoryId)
                ->where('is_active', true)
                ->firstOrFail();
        }

        if ($lockedOrderId) {
            Order::query()
                ->where('id', $lockedOrderId)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $existing = SupportTicket::query()
                ->where('order_id', $lockedOrderId)
                ->where('user_id', $user->id)
                ->first();

            if ($existing) {
                throw ValidationException::withMessages([
                    'order_id' => 'msg.err.account.tickets.order_already_has_ticket',
                ]);
            }
        }

        $data = $request->validate(
            [
                'support_ticket_category_id' => ['required', 'integer', 'exists:support_ticket_categories,id'],
                'order_id' => ['nullable', 'integer', 'exists:orders,id'],
                'subject' => ['required', 'string', 'max:255'],
                'body' => ['required', 'string'],
                'attachments' => ['nullable', 'array', 'max:5'],
                'attachments.*' => ['file', 'max:2048', 'mimes:jpg,jpeg,png,webp'],
            ],
            [
                // array max:5
                'attachments.max' => 'msg.err.account.tickets.attachments.errors.too_many',

                // file validations
                'attachments.*.mimes' => 'msg.err.account.tickets.attachments.errors.type_unsupported',
                'attachments.*.max' => 'msg.err.account.tickets.attachments.errors.too_large',
                'attachments.uploaded' => 'msg.err.account.tickets.attachments.errors.invalid_generic',
                'attachments.*.uploaded' => 'msg.err.account.tickets.attachments.errors.invalid_generic',
            ]
        );

        // Locked değerler varsa, request’ten gelenleri override et (tek otorite: server)
        if ($lockedCategoryId) {
            $data['support_ticket_category_id'] = $lockedCategoryId;
        }
        if ($lockedOrderId) {
            $data['order_id'] = $lockedOrderId;
        }

        /** @var SupportTicketCategory $category */
        $category = SupportTicketCategory::query()->findOrFail($data['support_ticket_category_id']);

        // order zorunlu ise
        if ($category->requires_order && empty($data['order_id'])) {
            throw ValidationException::withMessages([
                'order_id' => 'msg.err.account.tickets.order_required',
            ]);
        }

        if (! empty($data['order_id'])) {
            Order::query()
                ->where('id', $data['order_id'])
                ->where('user_id', $user->id)
                ->firstOrFail();

            $existing = SupportTicket::query()
                ->where('order_id', $data['order_id'])
                ->where('user_id', $user->id)
                ->first();

            if ($existing) {
                throw ValidationException::withMessages([
                    'order_id' => 'msg.err.account.tickets.order_already_has_ticket',
                ]);
            }
        }

        $ticketId = null;
        $messageId = null;

        DB::transaction(function () use ($user, $data, &$ticketId, &$messageId) {
            $ticket = SupportTicket::query()->create([
                'user_id' => $user->id,
                'support_ticket_category_id' => $data['support_ticket_category_id'],
                'order_id' => $data['order_id'] ?? null,
                'subject' => $data['subject'],
                'locale' => app()->getLocale(),
                'status' => SupportTicket::STATUS_WAITING_AGENT,
                'last_message_at' => now(),
            ]);

            $ticketId = $ticket->id;

            $message = $ticket->messages()->create([
                'author_user_id' => $user->id,
                'author_type' => SupportMessage::AUTHOR_CUSTOMER,
                'body' => $data['body'],
            ]);

            $messageId = $message->id;

            foreach (($data['attachments'] ?? []) as $file) {
                if (! $file instanceof UploadedFile) {
                    continue;
                }

                $message->addMedia($file->getRealPath())
                    ->usingFileName($file->getClientOriginalName())
                    ->toMediaCollection('attachments');
            }
        });

        // Ops mail: müşteri yeni destek talebi oluşturdu
        if ($ticketId && $messageId) {
            dispatch(new SendSupportTicketCreatedOpsEmail($ticketId, $messageId));
        }

        return redirect()
            ->to(localized_route('account.tickets.show', ['ticket' => $ticketId]))
            ->with('success', 'msg.ok.account.support_tickets.created');
    }

    public function storeMessage(Request $request, SupportTicket $ticket)
    {
        $user = $request->user();

        abort_unless($ticket->user_id === $user->id, 403);

        $data = $request->validate(
            [
                'body' => ['required', 'string'],
                'attachments' => ['nullable', 'array', 'max:5'],
                'attachments.*' => ['file', 'max:2048', 'mimes:jpg,jpeg,png,webp'],
            ],
            [
                // array max:5
                'attachments.max' => 'msg.err.account.tickets.attachments.errors.too_many',

                // file validations
                'attachments.*.mimes' => 'msg.err.account.tickets.attachments.errors.type_unsupported',
                'attachments.*.max' => 'msg.err.account.tickets.attachments.errors.too_large',
                'attachments.uploaded' => 'msg.err.account.tickets.attachments.errors.invalid_generic',
                'attachments.*.uploaded' => 'msg.err.account.tickets.attachments.errors.invalid_generic',
            ]
        );

        $messageId = null;

        DB::transaction(function () use ($ticket, $user, $data, &$messageId) {
            $message = $ticket->messages()->create([
                'author_user_id' => $user->id,
                'author_type' => SupportMessage::AUTHOR_CUSTOMER,
                'body' => $data['body'],
            ]);

            $messageId = $message->id;

            foreach (($data['attachments'] ?? []) as $file) {
                if (! $file instanceof UploadedFile) {
                    continue;
                }

                $message->addMedia($file->getRealPath())
                    ->usingFileName($file->getClientOriginalName())
                    ->toMediaCollection('attachments');
            }

            $ticket->forceFill([
                'status' => SupportTicket::STATUS_WAITING_AGENT,
                'closed_at' => null,
                'last_message_at' => now(),
            ])->save();
        });

        // Ops mail: müşteri yeni mesaj ekledi
        if ($messageId) {
            dispatch(new SendSupportTicketCustomerMessageOpsEmail($messageId));
        }

        return redirect()->back()->with('success', 'msg.ok.account.support_tickets.message_sent');
    }
}
