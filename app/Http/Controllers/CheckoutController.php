<?php

namespace App\Http\Controllers;

use App\Http\Requests\HotelBookingRequest;
use App\Http\Requests\TourBookingRequest;
use App\Http\Requests\TransferBookingRequest;
use App\Http\Requests\VillaBookingRequest;
use App\Models\Hotel;
use App\Models\Tour;
use App\Models\TransferRouteVehiclePrice;
use App\Models\TransferVehicle;
use App\Models\Villa;
use App\Services\CartInvariant;
use App\Support\Currency\CurrencyContext;
use Carbon\CarbonImmutable;

class CheckoutController extends Controller
{
    /**
     * Tüm sepet ekleme işlemleri için ortak helper.
     *
     * @return bool true: eklendi, false: currency mismatch → cart reset
     */
    private function addToCart(
        string $productType,
        int $productId,
        float $amount,
        string $currency,
        array $snapshot,
        CartInvariant $cartInvariant
    ): bool {
        $cart = session()->get('cart', [
            'items' => [],
        ]);

        $items = (array) ($cart['items'] ?? []);

        $incomingCurrency = strtoupper((string) $currency);

        // Mixed currency guard (fail-fast)
        if ($cartInvariant->resetIfCurrencyMismatch($items)) {
            return false;
        }

        // Sepette currency varsa incoming ile aynı olmalı
        if ($cartInvariant->resetIfCannotAcceptIncomingCurrency($items, $incomingCurrency)) {
            return false;
        }

        $items[] = [
            'product_type' => $productType,
            'product_id'   => $productId,

            // Tahsil edilecek satır tutarı (payable)
            'amount'       => $amount,
            'currency'     => $incomingCurrency,

            // Cart item sözleşmesi (kilitli)
            'quantity'     => 1,
            'unit_price'   => $amount,
            'total_price'  => $amount,

            // Ürüne özel donmuş veri
            'snapshot'     => $snapshot,
        ];

        $cart['items'] = $items;

        session()->put('cart', $cart);

        return true;
    }

    /**
     * Transfer booking -> sepete ekleme
     */
    public function bookTransfer(TransferBookingRequest $request, CartInvariant $cartInvariant)
    {
        $data = $request->validated();

        foreach (['from_label', 'to_label', 'vehicle_name'] as $extraKey) {
            if ($request->filled($extraKey)) {
                $data[$extraKey] = $request->input($extraKey);
            }
        }

        // -----------------------------
        // PRICE + CURRENCY (server authoritative)
        // -----------------------------
        $routeId   = (int) ($data['route_id'] ?? 0);
        $vehicleId = (int) ($data['vehicle_id'] ?? 0);

        $currency = strtoupper((string) CurrencyContext::code($request));
        if ($currency === '') {
            return redirect()
                ->to(localized_route('transfers'))
                ->with('err', 'err_transfer_currency_missing');
        }

        $row = TransferRouteVehiclePrice::query()
            ->where('transfer_route_id', $routeId)
            ->where('transfer_vehicle_id', $vehicleId)
            ->where('is_active', true)
            ->first();

        if (! $row) {
            return redirect()
                ->to(localized_route('transfers'))
                ->with('err', 'err_transfer_price_not_found');
        }

        $prices = $row->prices;
        if (! is_array($prices) || ! array_key_exists($currency, $prices)) {
            return redirect()
                ->to(localized_route('transfers'))
                ->with('err', 'err_transfer_price_not_found');
        }

        $oneWay = (float) ($prices[$currency] ?? 0);
        if ($oneWay <= 0) {
            return redirect()
                ->to(localized_route('transfers'))
                ->with('err', 'err_transfer_price_not_found');
        }

        $direction = (string) ($data['direction'] ?? 'oneway');
        $total = $direction === 'roundtrip' ? $oneWay * 2 : $oneWay;

        // Snapshot: server authoritative
        $data['price_total'] = $total;
        $data['currency']    = $currency;

        $vehicle = TransferVehicle::query()
            ->with('media')
            ->findOrFail($vehicleId);

        $img = $vehicle->cover_image ?? null;
        if (is_array($img) && ($img['exists'] ?? false)) {
            $data['vehicle_cover'] = $img;
        }

        $amount = (float) $data['price_total'];

        $ok = $this->addToCart(
            'transfer',
            $routeId,
            $amount,
            $currency,
            $data,
            $cartInvariant
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
    public function bookTour(TourBookingRequest $request, CartInvariant $cartInvariant)
    {
        $data = $request->validated();

        // Opsiyonel alanları snapshot'a ekle (görsel + kategori)
        foreach (['cover_image', 'category_name'] as $extraKey) {
            if ($request->filled($extraKey)) {
                $data[$extraKey] = $request->input($extraKey);
            }
        }

        // Null children/infants yerine 0
        $adults   = (int) ($data['adults'] ?? 0);
        $children = (int) ($data['children'] ?? 0);
        $infants  = (int) ($data['infants'] ?? 0);

        $data['children'] = $children;
        $data['infants']  = $infants;

        // Currency: server authoritative
        $currency = strtoupper((string) CurrencyContext::code($request));
        if ($currency === '') {
            return redirect()
                ->to(localized_route('cart'))
                ->with('err', 'err_tour_currency_missing');
        }

        $tourId = (int) ($data['tour_id'] ?? 0);

        $tour = Tour::query()
            ->where('is_active', true)
            ->findOrFail($tourId);

        $prices = $tour->prices;
        if (! is_array($prices) || ! isset($prices[$currency]) || ! is_array($prices[$currency])) {
            return redirect()
                ->to(localized_route('cart'))
                ->with('err', 'err_tour_price_not_found');
        }

        $cfg = $prices[$currency];

        $adultUnit  = isset($cfg['adult'])  && is_numeric($cfg['adult'])  ? (float) $cfg['adult']  : null;
        $childUnit  = isset($cfg['child'])  && is_numeric($cfg['child'])  ? (float) $cfg['child']  : 0.0;
        $infantUnit = isset($cfg['infant']) && is_numeric($cfg['infant']) ? (float) $cfg['infant'] : 0.0;

        // Adult zorunlu (min:1) → unit price yoksa fail
        if ($adultUnit === null || $adultUnit < 0) {
            return redirect()
                ->to(localized_route('cart'))
                ->with('err', 'err_tour_price_not_found');
        }

        $total =
            ($adults * $adultUnit) +
            ($children * $childUnit) +
            ($infants * $infantUnit);

        if ($total <= 0) {
            return redirect()
                ->to(localized_route('cart'))
                ->with('err', 'err_tour_price_not_found');
        }

        // Snapshot authoritative override
        $data['currency']    = $currency;
        $data['price_total'] = $total;

        $ok = $this->addToCart(
            'tour',
            $tourId,
            $total,
            $currency,
            $data,
            $cartInvariant
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
    public function bookHotel(HotelBookingRequest $request, CartInvariant $cartInvariant)
    {
        $data = $request->validated();

        $data['children'] = $data['children'] ?? 0;

        // Currency: server authoritative
        $currencyCode = strtoupper((string) CurrencyContext::code($request));
        if ($currencyCode === '') {
            return redirect()
                ->to(localized_route('cart'))
                ->with('err', 'err_hotel_currency_missing');
        }

        // Dates + counts (server-side guard)
        $checkin  = (string) ($data['checkin'] ?? '');
        $checkout = (string) ($data['checkout'] ?? '');
        $nights   = (int) ($data['nights'] ?? 0);

        if ($checkin === '' || $checkout === '' || $nights < 1) {
            return redirect()
                ->to(localized_route('cart'))
                ->with('err', 'err_hotel_dates_invalid');
        }

        $adults   = (int) ($data['adults'] ?? 0);
        $children = (int) ($data['children'] ?? 0);

        // CurrencyId resolve (authoritative)
        $currency = \App\Models\Currency::query()
            ->where('code', $currencyCode)
            ->where('is_active', true)
            ->first();

        if (! $currency) {
            return redirect()
                ->to(localized_route('cart'))
                ->with('err', 'err_hotel_currency_missing');
        }

        // Load room (for rateRules relationship)
        $room = \App\Models\Room::query()
            ->with(['rateRules', 'hotel'])
            ->findOrFail((int) $data['room_id']);

        $boardTypeId = isset($data['board_type_id']) ? (int) $data['board_type_id'] : null;

        /** @var \App\Services\RoomRateResolver $resolver */
        $resolver = app(\App\Services\RoomRateResolver::class);

        // IMPORTANT:
        // summarizeStay() bu sınıfta "dateEnd" için inclusive çalışıyor.
        // Konaklamada checkout fiyatlanmamalı → checkout-1'e kadar fiyatlamalıyız.
        // Bu yüzden resolveRangeForStay + sum kullanıyoruz.
        $days = $resolver->resolveRangeForStay(
            $room,
            $checkin,
            $checkout,
            (int) $currency->id,
            $boardTypeId,
            $adults,
            $children
        );

        if ($days->isEmpty()) {
            return redirect()
                ->to(localized_route('cart'))
                ->with('err', 'err_hotel_price_not_found');
        }

        if ($days->contains(fn (array $d) => ($d['closed'] ?? false) === true)) {
            return redirect()
                ->to(localized_route('cart'))
                ->with('err', 'err_hotel_not_available');
        }

        if ($days->contains(fn (array $d) => ($d['ok'] ?? false) === false)) {
            return redirect()
                ->to(localized_route('cart'))
                ->with('err', 'err_hotel_price_not_found');
        }

        $totalAmount = (float) $days->sum('total');
        if ($totalAmount <= 0) {
            return redirect()
                ->to(localized_route('cart'))
                ->with('err', 'err_hotel_price_not_found');
        }

        // Snapshot: authoritative override
        $snapshot = $data;
        $snapshot['currency']    = $currencyCode;
        $snapshot['price_total'] = $totalAmount;

        if ($request->filled('location_label')) {
            $snapshot['location_label'] = $request->input('location_label');
        }

        // Image policy (hotel)
        $hotel = Hotel::query()
            ->with('media')
            ->findOrFail((int) $data['hotel_id']);

        $img = $hotel->cover_image ?? null;
        if (is_array($img) && ($img['exists'] ?? false)) {
            $snapshot['hotel_image'] = $img;
        }

        $ok = $this->addToCart(
            'hotel_room',
            (int) $data['room_id'],
            $totalAmount,
            $currencyCode,
            $snapshot,
            $cartInvariant
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
    public function bookVilla(VillaBookingRequest $request, CartInvariant $cartInvariant)
    {
        $data = $request->validated();

        $data['children'] = $data['children'] ?? 0;

        if ($request->filled('location_label')) {
            $data['location_label'] = $request->input('location_label');
        }

        // -----------------------------
        // Currency: server authoritative
        // -----------------------------
        $currency = CurrencyContext::code($request);
        $currency = strtoupper(trim((string) $currency));

        if ($currency === '') {
            return redirect()
                ->to(localized_route('cart'))
                ->with('err', 'err_villa_currency_missing');
        }

        // -----------------------------
        // Dates: server authoritative nights
        // -----------------------------
        $checkinYmd  = (string) ($data['checkin'] ?? '');
        $checkoutYmd = (string) ($data['checkout'] ?? '');

        try {
            $in  = CarbonImmutable::createFromFormat('Y-m-d', $checkinYmd)->startOfDay();
            $out = CarbonImmutable::createFromFormat('Y-m-d', $checkoutYmd)->startOfDay();
        } catch (\Throwable) {
            return redirect()
                ->to(localized_route('cart'))
                ->with('err', 'err_villa_dates_invalid');
        }

        $nights = (int) $in->diffInDays($out);
        if ($nights < 1) {
            return redirect()
                ->to(localized_route('cart'))
                ->with('err', 'err_villa_dates_invalid');
        }

        // -----------------------------
        // Villa + rule: server authoritative nightly
        // -----------------------------
        $villaId = (int) ($data['villa_id'] ?? 0);

        $villa = Villa::query()
            ->with(['media', 'rateRules.currency'])
            ->findOrFail($villaId);

        $rule = $villa->rateRules()
            ->whereHas('currency', function ($q) use ($currency) {
                $q->whereRaw('upper(code) = ?', [$currency]);
            })
            ->where(function ($q) {
                $q->where('is_active', true)->orWhereNull('is_active');
            })
            ->where(function ($q) {
                $q->where('closed', false)->orWhereNull('closed');
            })
            ->orderBy('priority', 'asc')
            ->orderBy('date_start', 'asc')
            ->first();

        $nightly = ($rule && $rule->amount !== null) ? (float) $rule->amount : 0.0;
        if ($nightly <= 0) {
            return redirect()
                ->to(localized_route('cart'))
                ->with('err', 'err_villa_price_not_found');
        }

        // -----------------------------
        // Currency exponent (authoritative rounding)
        // -----------------------------
        $currencyModel = CurrencyContext::model($request);
        $exp = $currencyModel ? (int) $currencyModel->exponent : 2;
        if ($exp < 0) {
            $exp = 0;
        }

        // totals
        $totalRaw = $nightly * $nights;
        $total = round($totalRaw, $exp, PHP_ROUND_HALF_UP);

        $rate = (float) ($villa->prepayment_rate ?? 0);
        $prepaymentRaw = $rate > 0 ? ($total * ($rate / 100)) : 0.0;
        $prepayment = round($prepaymentRaw, $exp, PHP_ROUND_HALF_UP);

        $nightly = round($nightly, $exp, PHP_ROUND_HALF_UP);

        if ($total <= 0 || $prepayment <= 0) {
            return redirect()
                ->to(localized_route('cart'))
                ->with('err', 'err_villa_price_not_found');
        }

        // -----------------------------
        // Snapshot override (server authoritative)
        // -----------------------------
        $data['currency']         = $currency;
        $data['nights']           = $nights;
        $data['price_nightly']    = $nightly;
        $data['price_total']      = $total;
        $data['price_prepayment'] = $prepayment;

        // Image (policy: accessor already normalized)
        $img = $villa->cover_image ?? null;
        if (is_array($img) && ($img['exists'] ?? false)) {
            $data['villa_image'] = $img;
        }

        // Sepette “şimdi ödenecek” tutar: ön ödeme (authoritative)
        $amount = $prepayment;

        $ok = $this->addToCart(
            'villa',
            $villaId,
            $amount,
            $currency,
            $data,
            $cartInvariant
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
