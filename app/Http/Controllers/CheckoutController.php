<?php

namespace App\Http\Controllers;

use App\Http\Requests\HotelBookingRequest;
use App\Http\Requests\TourBookingRequest;
use App\Http\Requests\TransferBookingRequest;
use App\Http\Requests\VillaBookingRequest;
use App\Services\CartInvariant;
use App\Services\HotelPriceQuoteService;
use App\Services\TourPriceQuoteService;
use App\Services\TransferPriceQuoteService;
use App\Services\VillaPriceQuoteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    /**
     * @param 'err'|'warn'|'notice'|'ok' $level
     */
    private function normalizeLevel(string $level): string
    {
        return in_array($level, ['err', 'warn', 'notice', 'ok'], true) ? $level : 'notice';
    }

    /**
     * @param 'err'|'warn'|'notice'|'ok' $level
     */
    private function backNotice(string $fallbackRoute, string $level, string $code, array $params = []): RedirectResponse
    {
        $level = $this->normalizeLevel($level);

        $code = is_string($code) ? trim($code) : '';
        if ($code === '') {
            return redirect()->to(localized_route($fallbackRoute));
        }

        $payload = [[
            'level'  => $level,
            'code'   => $code,
            'params' => is_array($params) ? $params : [],
        ]];

        // Form submit'ten gelindiyse aynı sayfaya dön (ürün detay UX)
        return redirect()->back()->with('notices', $payload);
    }

    /**
     * @param 'err'|'warn'|'notice'|'ok' $level
     */
    private function routeNotice(string $route, string $level, string $code, array $params = []): RedirectResponse
    {
        $level = $this->normalizeLevel($level);

        $code = is_string($code) ? trim($code) : '';
        if ($code === '') {
            return redirect()->to(localized_route($route));
        }

        return redirect()
            ->to(localized_route($route))
            ->with('notices', [[
                'level'  => $level,
                'code'   => $code,
                'params' => is_array($params) ? $params : [],
            ]]);
    }

    /**
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

        if ($cartInvariant->resetIfCurrencyMismatch($items)) {
            return false;
        }

        if ($cartInvariant->resetIfCannotAcceptIncomingCurrency($items, $incomingCurrency)) {
            return false;
        }

        $itemKey = (string) Str::uuid();

        $items[$itemKey] = [
            'key'          => $itemKey,

            'product_type' => $productType,
            'product_id'   => $productId,

            'amount'       => $amount,
            'currency'     => $incomingCurrency,

            'quantity'     => 1,
            'unit_price'   => $amount,
            'total_price'  => $amount,

            'snapshot'     => $snapshot,
        ];

        $cart['items'] = $items;

        session()->put('cart', $cart);

        return true;
    }

    public function bookTransfer(
        TransferBookingRequest $request,
        CartInvariant $cartInvariant,
        TransferPriceQuoteService $quoteService
    ) {
        $data = $request->validated();

        $routeId   = (int) ($data['route_id'] ?? 0);
        $vehicleId = (int) ($data['vehicle_id'] ?? 0);
        $direction = (string) ($data['direction'] ?? 'oneway');

        $q = $quoteService->quote($routeId, $vehicleId, $direction, $request);

        if (! ($q['ok'] ?? false)) {
            // Ürün sayfasına dön (back), ortak bilgi mesajı
            return $this->backNotice('transfers', 'err', (string) ($q['err'] ?? 'msg.info.price_not_found'));
        }

        $currency = (string) ($q['currency'] ?? '');
        $amount   = (float) ($q['amount'] ?? 0);

        if ($currency === '' || $amount <= 0) {
            return $this->backNotice('transfers', 'err', 'msg.info.price_not_found');
        }

        $snapshot = $data;

        $quoteSnapshot = (array) ($q['snapshot'] ?? []);
        foreach ($quoteSnapshot as $k => $v) {
            $snapshot[$k] = $v;
        }

        $ok = $this->addToCart(
            'transfer',
            $routeId,
            $amount,
            $currency,
            $snapshot,
            $cartInvariant
        );

        if (! $ok) {
            return $this->routeNotice('cart', 'err', 'msg.err.cart.currency_mismatch');
        }

        return $this->routeNotice('cart', 'ok', 'msg.ok.cart.item_added');
    }

    public function bookTour(
        TourBookingRequest $request,
        CartInvariant $cartInvariant,
        TourPriceQuoteService $quoteService
    ) {
        $data = $request->validated();

        $tourId  = (int) ($data['tour_id'] ?? 0);
        $dateYmd = (string) ($data['date'] ?? '');

        $adults   = (int) ($data['adults'] ?? 0);
        $children = (int) ($data['children'] ?? 0);
        $infants  = (int) ($data['infants'] ?? 0);

        $children = $children < 0 ? 0 : $children;
        $infants  = $infants < 0 ? 0 : $infants;

        $q = $quoteService->quote(
            $tourId,
            $dateYmd,
            $adults,
            $children,
            $infants,
            $request
        );

        if (! ($q['ok'] ?? false)) {
            return $this->backNotice('tours', 'err', (string) ($q['err'] ?? 'msg.info.price_not_found'));
        }

        $currency = (string) ($q['currency'] ?? '');
        $amount   = (float) ($q['amount'] ?? 0);

        if ($currency === '' || $amount <= 0) {
            return $this->backNotice('tours', 'err', 'msg.info.price_not_found');
        }

        $snapshot = $data;

        $quoteSnapshot = (array) ($q['snapshot'] ?? []);
        foreach ($quoteSnapshot as $k => $v) {
            $snapshot[$k] = $v;
        }

        $ok = $this->addToCart(
            'tour',
            $tourId,
            $amount,
            $currency,
            $snapshot,
            $cartInvariant
        );

        if (! $ok) {
            return $this->routeNotice('cart', 'err', 'msg.err.cart.currency_mismatch');
        }

        return $this->routeNotice('cart', 'ok', 'msg.ok.cart.item_added');
    }

    public function bookHotel(
        HotelBookingRequest $request,
        CartInvariant $cartInvariant,
        HotelPriceQuoteService $quoteService
    ) {
        $data = $request->validated();

        $roomId      = (int) ($data['room_id'] ?? 0);
        $checkinYmd  = (string) ($data['checkin'] ?? '');
        $checkoutYmd = (string) ($data['checkout'] ?? '');

        $adults   = (int) ($data['adults'] ?? 0);
        $children = (int) ($data['children'] ?? 0);
        $children = $children < 0 ? 0 : $children;

        $boardTypeId = isset($data['board_type_id']) ? (int) $data['board_type_id'] : null;

        $q = $quoteService->quote(
            $roomId,
            $checkinYmd,
            $checkoutYmd,
            $adults,
            $children,
            $boardTypeId,
            $request
        );

        if (! ($q['ok'] ?? false)) {
            return $this->backNotice('hotels', 'err', (string) ($q['err'] ?? 'msg.info.price_not_found'));
        }

        $currency = (string) ($q['currency'] ?? '');
        $amount   = (float) ($q['amount'] ?? 0);

        if ($currency === '' || $amount <= 0) {
            return $this->backNotice('hotels', 'err', 'msg.info.price_not_found');
        }

        $snapshot = $data;

        $quoteSnapshot = (array) ($q['snapshot'] ?? []);
        foreach ($quoteSnapshot as $k => $v) {
            $snapshot[$k] = $v;
        }

        $ok = $this->addToCart(
            'hotel_room',
            $roomId,
            $amount,
            $currency,
            $snapshot,
            $cartInvariant
        );

        if (! $ok) {
            return $this->routeNotice('cart', 'err', 'msg.err.cart.currency_mismatch');
        }

        return $this->routeNotice('cart', 'ok', 'msg.ok.cart.item_added');
    }

    public function bookVilla(
        VillaBookingRequest $request,
        CartInvariant $cartInvariant,
        VillaPriceQuoteService $quoteService
    ) {
        $data = $request->validated();

        $villaId = (int) ($data['villa_id'] ?? 0);

        /** @var ?\App\Models\Villa $villa */
        $villa = \App\Models\Villa::query()
            ->with(['location.parent', 'media', 'rateRules.currency'])
            ->find($villaId);

        if (! $villa) {
            return $this->backNotice('villas', 'err', 'msg.err.villa.not_found');
        }

        $q = $quoteService->quote(
            $villa,
            (string) ($data['checkin'] ?? ''),
            (string) ($data['checkout'] ?? ''),
            $request
        );

        if (! ($q['ok'] ?? false)) {
            // Burada villa quote, min/max nights gibi msg.err.* dönebilir; price_not_found ise msg.info.*
            return $this->backNotice('villas', 'err', (string) ($q['err'] ?? 'msg.info.price_not_found'));
        }

        $currency = (string) ($q['currency'] ?? '');
        $amount   = (float) ($q['amount'] ?? 0);

        if ($currency === '' || $amount <= 0) {
            return $this->backNotice('villas', 'err', 'msg.info.price_not_found');
        }

        $snapshot = $data;

        $quoteSnapshot = (array) ($q['snapshot'] ?? []);
        foreach ($quoteSnapshot as $k => $v) {
            $snapshot[$k] = $v;
        }

        $ok = $this->addToCart(
            'villa',
            $villaId,
            $amount,
            $currency,
            $snapshot,
            $cartInvariant
        );

        if (! $ok) {
            return $this->routeNotice('cart', 'err', 'msg.err.cart.currency_mismatch');
        }

        return $this->routeNotice('cart', 'ok', 'msg.ok.cart.item_added');
    }
}
