<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransferBookingRequest;
use App\Http\Requests\TourBookingRequest;
use App\Http\Requests\HotelBookingRequest;
use App\Http\Requests\VillaBookingRequest;
use Illuminate\Http\Request;
use App\Models\Hotel;
use App\Models\Villa;

class CheckoutController extends Controller
{
    /**
     * Tüm sepet ekleme işlemleri için ortak helper.
     */
    private function addToCart(string $productType, int $productId, float $amount, string $currency, array $snapshot): void
    {
        $cart = session()->get('cart', [
            'items' => [],
        ]);

        $cart['items'][] = [
            'product_type' => $productType,
            'product_id'   => $productId,
            'amount'       => $amount,
            'currency'     => strtoupper($currency),
            'snapshot'     => $snapshot,
        ];

        session()->put('cart', $cart);
    }

    /**
     * Transfer booking -> sepete ekleme
     */
    public function bookTransfer(TransferBookingRequest $request)
    {
        // Validasyon
        $data = $request->validated();

        // Formdan gelen (validation dışında kalan) ek alanları da snapshot'a ekle
        foreach (['from_label', 'to_label', 'vehicle_image'] as $extraKey) {
            if ($request->filled($extraKey)) {
                $data[$extraKey] = $request->input($extraKey);
            }
        }

        $snapshot = $data;

        $amount   = (float) ($snapshot['price_total'] ?? 0);
        $currency = $snapshot['currency'];
        $routeId  = (int) ($snapshot['route_id'] ?? 0);

        $this->addToCart(
            'transfer',
            $routeId,
            $amount,
            $currency,
            $snapshot,
        );

        // Sepete yönlendir + başarı mesajı
        return redirect()
            ->to(localized_route('cart'))
            ->with('ok', 'validated');
    }

    /**
     * Excursion (tour) booking -> sepete ekleme
     */
    public function bookTour(TourBookingRequest $request)
    {
        $data = $request->validated();

        // Opsiyonel alanları snapshot'a ekle (görsel + kategori)
        foreach (['cover_image', 'category_name'] as $extraKey) {
            if ($request->filled($extraKey)) {
                $data[$extraKey] = $request->input($extraKey);
            }
        }

        // Null children/infants yerine 0 yazarak snapshot'ı normalize et
        $data['children'] = $data['children'] ?? 0;
        $data['infants']  = $data['infants'] ?? 0;

        $this->addToCart(
            'tour',
            (int) $data['tour_id'],
            (float) $data['price_total'],
            $data['currency'],
            $data,
        );

        return redirect()
            ->to(localized_route('cart'))
            ->with('ok', 'validated');
    }

    /**
     * Hotel room booking -> sepete ekleme
     */
    public function bookHotel(HotelBookingRequest $request)
    {
        $data = $request->validated();

        // Null children yerine 0
        $data['children'] = $data['children'] ?? 0;

        // Snapshot temel olarak valid alanlar
        $snapshot = $data;

        // Opsiyonel metin alanı (ör: lokasyon etiketi)
        if ($request->filled('location_label')) {
            $snapshot['location_label'] = $request->input('location_label');
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

        $this->addToCart(
            'hotel_room',
            (int) $data['room_id'],
            (float) $data['price_total'],
            $data['currency'],
            $snapshot,
        );

        return redirect()
            ->to(localized_route('cart'))
            ->with('ok', 'validated');
    }

    /**
     * Villa booking -> sepete ekleme
     */
    public function bookVilla(VillaBookingRequest $request)
    {
        $data = $request->validated();

        // Null children yerine 0
        $data['children'] = $data['children'] ?? 0;

        // Opsiyonel lokasyon etiketi
        if ($request->filled('location_label')) {
            $data['location_label'] = $request->input('location_label');
        }

        // Villa cover / gallery görseli → snapshot.villa_image
        $villa = Villa::query()
            ->with('media')
            ->findOrFail($data['villa_id']);

        $media = $villa->getFirstMedia('cover')
            ?: $villa->getFirstMedia('gallery');

        if ($media) {
            $data['villa_image'] = [
                'thumb'   => $media->getUrl('thumb'),
                'thumb2x' => $media->getUrl('thumb2x'),
                'alt'     => $data['villa_name'],
            ];
        }

        // Sepette “şimdi ödenecek” tutar olarak ön ödemeyi kullanıyoruz
        $this->addToCart(
            'villa',
            (int) $data['villa_id'],
            (float) $data['price_prepayment'],
            $data['currency'],
            $data,
        );

        return redirect()
            ->to(localized_route('cart'))
            ->with('ok', 'validated');
    }
}
