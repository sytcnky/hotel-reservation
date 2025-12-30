<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderCreatedOpsMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build(): self
    {
        return $this
            ->subject('Yeni sipariş: ' . ($this->order->code ?: ('#' . $this->order->id)))
            ->view('emails.orders.ops-created', [
                'order' => $this->order,
                'layoutVariant' => 'ops',
            ]);
    }
}
