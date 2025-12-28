<?php

namespace App\Jobs;

use App\Mail\RefundSucceededCustomerMail;
use App\Models\RefundAttempt;
use App\Support\Mail\MailDefaults;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class SendRefundSucceededEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $refundAttemptId;

    public function __construct(int $refundAttemptId)
    {
        $this->refundAttemptId = $refundAttemptId;
    }

    public function handle(): void
    {
        $refund = RefundAttempt::query()
            ->with(['order'])
            ->find($this->refundAttemptId);

        if (! $refund) {
            return;
        }

        if ($refund->status !== RefundAttempt::STATUS_SUCCESS) {
            return;
        }

        if (! $refund->order) {
            return;
        }

        $toCustomer = $refund->order->customer_email;
        if (! is_string($toCustomer) || trim($toCustomer) === '') {
            return;
        }

        // Idempotency
        $key = 'mail:refund-success:' . $refund->id;
        if (! Cache::add($key, 1, now()->addDays(MailDefaults::idempotencyDays()))) {
            return;
        }

        $mailable = new RefundSucceededCustomerMail($refund);
        MailDefaults::applyFrom($mailable);

        $locale = MailDefaults::mailLocale($refund->order->locale);

        Mail::to($toCustomer)
            ->locale($locale)
            ->send($mailable);
    }
}
