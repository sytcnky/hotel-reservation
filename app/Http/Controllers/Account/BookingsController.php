<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\RefundAttempt;
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

        return view('pages.account.bookings', compact('orders'));
    }
}
