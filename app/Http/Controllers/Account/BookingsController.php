<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\RefundAttempt;
use App\Models\SupportTicket;
use Illuminate\Http\Request;

final class BookingsController extends Controller
{
    public function index(Request $request)
    {
        $userId = (int) $request->user()->id;

        $orders = Order::query()
            ->where('user_id', $userId)
            ->with([
                'items',
                // hasManyThrough: Order -> PaymentAttempt -> RefundAttempt
                'refundAttempts' => function ($q) {
                    $q->select('refund_attempts.*')
                        ->where('refund_attempts.status', RefundAttempt::STATUS_SUCCESS)
                        ->orderByDesc('refund_attempts.id');
                },
            ])
            ->latest('id')
            ->get();

        // ---- Ticket map (1 sipariş = 1 ticket kuralı) ----
        $orderIds = $orders->pluck('id')->filter()->values();

        $ticketByOrderId = SupportTicket::query()
            ->where('user_id', $userId)
            ->whereNotNull('order_id')
            ->whereIn('order_id', $orderIds)
            ->pluck('id', 'order_id'); // [order_id => ticket_id]

        foreach ($orders as $order) {
            $ticketId = $ticketByOrderId->get($order->id);

            $order->setAttribute('ticket_id', $ticketId);
            $order->setAttribute('has_ticket', ! empty($ticketId));
        }

        return view('pages.account.bookings', compact('orders'));
    }
}
