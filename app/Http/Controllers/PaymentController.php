<?php

namespace App\Http\Controllers;

use App\Models\Order;

class PaymentController extends Controller
{
    /**
     * Ödeme sayfası
     *
     * Sipariş koduna göre Order'ı bulur ve ödeme sayfasını gösterir.
     */
    public function show(string $code)
    {
        $order = Order::query()
            ->where('code', $code)
            ->firstOrFail();

        // Eğer sipariş zaten ödenmişse, doğrudan teşekkür sayfasına yönlendirebiliriz.
        if ($order->payment_status === 'paid') {
            return redirect()
                ->to(localized_route('order.thankyou', ['code' => $order->code]));
        }

        // Şimdilik sadece order bilgisini ödeme sayfasına gönderiyoruz.
        // Blade yolunu kendi mevcut ödeme sayfana göre ayarla.
        return view('pages.payment.index', [
            'order' => $order,
        ]);
    }
}
