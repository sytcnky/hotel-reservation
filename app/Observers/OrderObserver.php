<?php

namespace App\Observers;

use App\Jobs\SendOrderApprovedEmails;
use App\Jobs\SendOrderCancelledEmails;
use App\Models\Order;

class OrderObserver
{
    /**
     * Transaction içindeysek commit’ten sonra çalışsın.
     * (Transaction yoksa yine çalışır.)
     */
    public bool $afterCommit = true;

    public function updated(Order $order): void
    {
        // Status değişmediyse işimiz yok
        if (! $order->wasChanged('status')) {
            return;
        }

        $from = (string) $order->getOriginal('status');
        $to   = (string) $order->status;

        // Pending -> Confirmed (Onaylandı)
        if ($from === Order::STATUS_PENDING && $to === Order::STATUS_CONFIRMED) {
            SendOrderApprovedEmails::dispatch((int) $order->id);
            return;
        }

        // Pending/Confirmed -> Cancelled (İptal)
        if (
            in_array($from, [Order::STATUS_PENDING, Order::STATUS_CONFIRMED], true)
            && $to === Order::STATUS_CANCELLED
        ) {
            SendOrderCancelledEmails::dispatch((int) $order->id);
            return;
        }
    }
}
