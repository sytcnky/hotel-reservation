<?php

namespace App\Jobs;

use App\Mail\SupportTicketCreatedOpsMail;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Support\Mail\MailDefaults;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class SendSupportTicketCreatedOpsEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $ticketId;
    public int $messageId;

    public function __construct(int $ticketId, int $messageId)
    {
        $this->ticketId = $ticketId;
        $this->messageId = $messageId;
        // $this->onQueue('mail');
    }

    public function handle(): void
    {
        $ticket = SupportTicket::query()
            ->with(['user', 'category', 'order'])
            ->find($this->ticketId);

        if (! $ticket) {
            return;
        }

        $message = SupportMessage::query()
            ->with(['author', 'media', 'ticket'])
            ->where('support_ticket_id', $ticket->id)
            ->find($this->messageId);

        if (! $message) {
            return;
        }

        // Idempotency: ticket created mail only once per ticket
        $key = 'mail:support-ticket-created:' . $ticket->id;
        if (! Cache::add($key, 1, now()->addDays(MailDefaults::idempotencyDays()))) {
            return;
        }

        $opsTo = (array) config('icr.mail.ops_to', []);
        if (empty($opsTo)) {
            return;
        }

        $mailable = new SupportTicketCreatedOpsMail($ticket, $message);
        MailDefaults::applyFrom($mailable);

        $locale = MailDefaults::mailLocale($ticket->locale);

        Mail::to($opsTo)
            ->locale($locale)
            ->send($mailable);
    }
}
