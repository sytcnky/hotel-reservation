<?php

namespace App\Http\Controllers;

use App\Http\Requests\HotelBookingRequest;
use App\Http\Requests\TourBookingRequest;
use App\Http\Requests\TransferBookingRequest;
use App\Http\Requests\VillaBookingRequest;
use App\Models\Hotel;
use App\Models\TransferVehicle;
use App\Models\Villa;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    /**
     * Tüm sepet ekleme işlemleri için ortak helper.
     *
     * @return bool true: eklendi, false: currency mismatch → cart reset
     */
    private function addToCart(string $productType, int $productId, float $amount, string $currency, array $snapshot): bool
    {
        $cart = session()->get('cart', [
            'items' => [],
        ]);

        $items = (array) ($cart['items'] ?? []);

        $incomingCurrency = strtoupper((string) $currency);

        // Mixed currency guard (fail-fast): cart invariant bozulursa resetle
        $cartCurrency = null;
        foreach ($items as $ci) {
            $c = $ci['currency'] ?? null;
            if (! $c) {
                continue;
            }

            $c = strtoupper((string) $c);

            if ($cartCurrency === null) {
                $cartCurrency = $c;
                continue;
            }

            // extra safety: mevcut items içinde mismatch varsa da resetle
            if ($c !== $cartCurrency) {
                session()->forget('cart');
                session()->forget('cart.applied_coupons');
                return false;
            }
        }

        if ($cartCurrency !== null && $incomingCurrency !== $cartCurrency) {
            session()->forget('cart');
            session()->forget('cart.applied_coupons');
            return false;
        }

        $items[] = [
            'product_type' => $productType,
            'product_id'   => $productId,
            'amount'       => $amount,
            'currency'     => $incomingCurrency,
            'snapshot'     => $snapshot,
        ];

        $cart['items'] = $items;

        session()->put('cart', $cart);

        return true;
    }

    /**
     * Transfer booking -> sepete ekleme
     */
    public function bookTransfer(TransferBookingRequest $request)
    {
        // Validasyon
        $data = $request->validated();

        // Formdan gelen (validation dışında kalan) ek alanları snapshot'a ekle
        // NOT: vehicle_image artık client’tan taşınmaz (image policy). Görsel DB’den cover_image ile gelir.
        foreach (['from_label', 'to_label', 'vehicle_name'] as $extraKey) {
            if ($request->filled($extraKey)) {
                $data[$extraKey] = $request->input($extraKey);
            }
        }

        // Controller image policy üretmez: model accessor’dan normalize edilmiş image taşınır
        $vehicle = TransferVehicle::query()
            ->with('media')
            ->findOrFail((int) $data['vehicle_id']);

        $img = $vehicle->cover_image ?? null;
        if (is_array($img) && ($img['exists'] ?? false)) {
            // Snapshot standardına uygun şekilde (mevcut key’i koruyarak)
            $data['vehicle_cover'] = $img;
        }

        $snapshot = $data;

        $amount   = (float) ($snapshot['price_total'] ?? 0);
        $currency = $snapshot['currency'];
        $routeId  = (int) ($snapshot['route_id'] ?? 0);

        $ok = $this->addToCart(
            'transfer',
            $routeId,
            $amount,
            $currency,
            $snapshot,
        );

        if (! $ok) {
            return redirect()
                ->to(localized_route('cart'))
                ->with('err', 'err_cart_currency_mismatch');
        }

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

        $ok = $this->addToCart(
            'tour',
            (int) $data['tour_id'],
            (float) $data['price_total'],
            $data['currency'],
            $data,
        );

        if (! $ok) {
            return redirect()
                ->to(localized_route('cart'))
                ->with('err', 'err_cart_currency_mismatch');
        }

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

        // Controller image policy üretmez: model accessor’dan normalize edilmiş image taşınır
        $hotel = Hotel::query()
            ->with('media')
            ->findOrFail($data['hotel_id']);

        $img = $hotel->cover_image ?? null;
        if (is_array($img) && ($img['exists'] ?? false)) {
            $snapshot['hotel_image'] = $img;
        }

        $ok = $this->addToCart(
            'hotel_room',
            (int) $data['room_id'],
            (float) $data['price_total'],
            $data['currency'],
            $snapshot,
        );

        if (! $ok) {
            return redirect()
                ->to(localized_route('cart'))
                ->with('err', 'err_cart_currency_mismatch');
        }

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

        // Controller image policy üretmez: model accessor’dan normalize edilmiş image taşınır
        $villa = Villa::query()
            ->with('media')
            ->findOrFail($data['villa_id']);

        $img = $villa->cover_image ?? null;
        if (is_array($img) && ($img['exists'] ?? false)) {
            $data['villa_image'] = $img;
        }

        // Sepette “şimdi ödenecek” tutar olarak ön ödemeyi kullanıyoruz
        $ok = $this->addToCart(
            'villa',
            (int) $data['villa_id'],
            (float) $data['price_prepayment'],
            $data['currency'],
            $data,
        );

        if (! $ok) {
            return redirect()
                ->to(localized_route('cart'))
                ->with('err', 'err_cart_currency_mismatch');
        }

        return redirect()
            ->to(localized_route('cart'))
            ->with('ok', 'validated');
    }
}
