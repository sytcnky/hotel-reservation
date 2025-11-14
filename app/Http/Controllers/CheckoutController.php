<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransferBookingRequest;
use Illuminate\Http\Request;

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

    public function bookTour(Request $request)
    {
        $data = $request->validate([
            'tour_id'     => ['required'],
            'tour_name'   => ['required', 'string'],
            'date'        => ['required', 'string'],
            'adults'      => ['required', 'integer', 'min:1'],
            'children'    => ['nullable', 'integer', 'min:0'],
            'infants'     => ['nullable', 'integer', 'min:0'],
            'currency'    => ['required', 'string', 'size:3'],
            'price_total' => ['required', 'numeric', 'min:0'],
        ]);

        // Opsiyonel alanları snapshot'a ekle (görsel + kategori)
        foreach (['cover_image', 'category_name'] as $extraKey) {
            if ($request->filled($extraKey)) {
                $data[$extraKey] = $request->input($extraKey);
            }
        }

        // Null children/infants yerine 0
        $data['children'] = $data['children'] ?? 0;
        $data['infants']  = $data['infants'] ?? 0;

        $cart = session()->get('cart', [
            'items' => [],
        ]);

        $cart['items'][] = [
            'product_type' => 'tour',
            'amount'       => (float) $data['price_total'],
            'currency'     => strtoupper($data['currency']),
            'snapshot'     => $data,
        ];

        session()->put('cart', $cart);

        return redirect()->to(localized_route('cart'))
            ->with('ok', 'validated');
    }


}
