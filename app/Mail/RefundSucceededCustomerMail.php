<?php

namespace App\Mail;

use App\Models\RefundAttempt;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RefundSucceededCustomerMail extends Mailable
{
    use Queueable, SerializesModels;

    public RefundAttempt $refund;

    public function __construct(RefundAttempt $refund)
    {
        $this->refund = $refund;
    }

    public function build(): self
    {
        $orderCode = $this->refund->order?->code ?? '-';

        return $this
            ->subject('Geri ödeme başarılı - Sipariş ' . $orderCode)
            ->view('emails.orders.customer-refunded', [
                'refund' => $this->refund,
                'order'  => $this->refund->order,
            ]);
    }
}
