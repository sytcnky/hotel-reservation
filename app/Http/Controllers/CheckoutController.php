<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransferBookingRequest;

class CheckoutController extends Controller
{
    public function bookTransfer(TransferBookingRequest $request)
    {
        // Önce validasyon
        $data = $request->validated();

        // Formdan gelen (validation dışında kalan) ek alanları da snapshot'a ekle
        foreach (['from_label', 'to_label', 'vehicle_image'] as $extraKey) {
            if ($request->filled($extraKey)) {
                $data[$extraKey] = $request->input($extraKey);
            }
        }

        // Mevcut sepeti al
        $cart = session()->get('cart', [
            'items' => [],
        ]);

        // Yeni transfer öğesini ekle
        $cart['items'][] = [
            'product_type' => 'transfer',
            'amount'       => (float) ($data['price_total'] ?? 0),
            'currency'     => $data['currency'] ?? 'TRY',
            'snapshot'     => $data,
        ];

        session()->put('cart', $cart);

        // Sepete yönlendir + başarı mesajı
        return redirect()->to(localized_route('cart'))
            ->with('ok', 'validated');
    }
}
