<?php

namespace App\Jobs;

use App\Mail\OrderCancelledCustomerMail;
use App\Models\Order;
use App\Support\Mail\MailDefaults;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class SendOrderCancelledEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $orderId;

    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
        // $this->onQueue('mail');
    }

    public function handle(): void
    {
        $order = Order::query()->find($this->orderId);
        if (! $order) {
            return;
        }

        // Güvenlik: yanlış statüdeyse mail atma
        if ($order->status !== Order::STATUS_CANCELLED) {
            return;
        }

        // Idempotency
        $key = 'mail:order-cancelled:' . $order->id;
        if (! Cache::add($key, 1, now()->addDays(MailDefaults::idempotencyDays()))) {
            return;
        }

        $toCustomer = $order->customer_email;
        if (! is_string($toCustomer) || trim($toCustomer) === '') {
            return;
        }

        $mailable = new OrderCancelledCustomerMail($order);
        MailDefaults::applyFrom($mailable);

        $locale = MailDefaults::mailLocale($order->locale);

        Mail::to($toCustomer)
            ->locale($locale)
            ->send($mailable);
    }
}
