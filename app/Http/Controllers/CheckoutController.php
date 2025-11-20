<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransferBookingRequest;
use Illuminate\Http\Request;
use App\Models\Hotel;

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

    public function bookHotel(Request $request)
    {
        $data = $request->validate([
            'hotel_id'    => ['required', 'integer'],
            'hotel_name'  => ['required', 'string'],
            'room_id'     => ['required', 'integer'],
            'room_name'   => ['required', 'string'],
            'checkin'     => ['required', 'date'],
            'checkout'    => ['required', 'date', 'after:checkin'],
            'nights'      => ['required', 'integer', 'min:1'],
            'adults'      => ['required', 'integer', 'min:1'],
            'children'    => ['nullable', 'integer', 'min:0'],
            'currency'    => ['required', 'string', 'size:3'],
            'price_total' => ['required', 'numeric', 'min:0'],
            'board_type_name' => ['required', 'string'],
        ]);

        // Null children yerine 0
        $data['children'] = $data['children'] ?? 0;

        // Opsiyonel metin alanları (pansiyon tipi, lokasyon)
        $snapshot = $data;
        foreach (['board_type_name', 'location_label'] as $extraKey) {
            if ($request->filled($extraKey)) {
                $snapshot[$extraKey] = $request->input($extraKey);
            }
        }

        // Otel cover görselini çek → yoksa galeriden al
        $hotel = Hotel::query()
            ->with('media')
            ->findOrFail($data['hotel_id']);

        $media = $hotel->getFirstMedia('cover')
            ?: $hotel->getFirstMedia('gallery');

        if ($media) {
            $snapshot['hotel_image'] = [
                'thumb'   => $media->getUrl('thumb'),
                'thumb2x' => $media->getUrl('thumb2x'),
                'alt'     => $data['hotel_name'],
            ];
        }

        $cart = session()->get('cart', [
            'items' => [],
        ]);

        $cart['items'][] = [
            'product_type' => 'hotel_room',
            'amount'       => (float) $data['price_total'],
            'currency'     => strtoupper($data['currency']),
            'snapshot'     => $snapshot,
        ];

        session()->put('cart', $cart);

        return redirect()->to(localized_route('cart'))
            ->with('ok', 'validated');
    }


}
