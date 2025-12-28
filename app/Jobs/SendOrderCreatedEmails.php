<?php

namespace App\Jobs;

use App\Mail\OrderCreatedCustomerMail;
use App\Mail\OrderCreatedOpsMail;
use App\Models\Order;
use App\Support\Mail\MailDefaults;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class SendOrderCreatedEmails implements ShouldQueue
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

        // Idempotency
        $key = 'mail:order-created:' . $order->id;
        if (! Cache::add($key, 1, now()->addDays(MailDefaults::idempotencyDays()))) {
            return;
        }

        $locale = MailDefaults::mailLocale($order->locale);

        // Customer mail
        $toCustomer = $order->customer_email;
        if (is_string($toCustomer) && trim($toCustomer) !== '') {
            $mailable = new OrderCreatedCustomerMail($order);
            MailDefaults::applyFrom($mailable);

            Mail::to($toCustomer)
                ->locale($locale)
                ->send($mailable);
        }

        // Ops mail
        $opsTo = (array) config('icr.mail.ops_to', []);
        if (! empty($opsTo)) {
            $mailable = new OrderCreatedOpsMail($order);
            MailDefaults::applyFrom($mailable);

            Mail::to($opsTo)
                ->locale($locale)
                ->send($mailable);
        }
    }
}
