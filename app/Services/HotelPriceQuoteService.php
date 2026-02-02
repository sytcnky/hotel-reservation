<?php

namespace App\Services;

use App\Models\Currency as CurrencyModel;
use App\Models\Room;
use App\Support\Currency\CurrencyContext;
use App\Support\Helpers\I18nHelper;
use App\Support\Helpers\LocaleHelper;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class HotelPriceQuoteService
{
    /**
     * @return array{
     *   ok: bool,
     *   err: ?string,
     *   currency: ?string,
     *   amount: float,
     *   nights: int,
     *   snapshot: array
     * }
     */
    public function quote(
        int $roomId,
        string $checkinYmd,
        string $checkoutYmd,
        int $adults,
        int $children,
        ?int $boardTypeId,
        Request $request
    ): array {
        $currencyCode = CurrencyContext::code($request);
        $currencyCode = strtoupper(trim((string) $currencyCode));

        if ($currencyCode === '') {
            return [
                'ok'       => false,
                'err'      => 'msg.err.hotel.currency_missing',
                'currency' => null,
                'amount'   => 0.0,
                'nights'   => 0,
                'snapshot' => [],
            ];
        }

        try {
            $in  = CarbonImmutable::createFromFormat('Y-m-d', $checkinYmd)->startOfDay();
            $out = CarbonImmutable::createFromFormat('Y-m-d', $checkoutYmd)->startOfDay();
        } catch (\Throwable) {
            return [
                'ok'       => false,
                'err'      => 'msg.err.hotel.dates_invalid',
                'currency' => $currencyCode,
                'amount'   => 0.0,
                'nights'   => 0,
                'snapshot' => [],
            ];
        }

        $nights = (int) $in->diffInDays($out);
        if ($nights < 1) {
            return [
                'ok'       => false,
                'err'      => 'msg.err.hotel.dates_invalid',
                'currency' => $currencyCode,
                'amount'   => 0.0,
                'nights'   => 0,
                'snapshot' => [],
            ];
        }

        if ($adults < 1) {
            return [
                'ok'       => false,
                'err'      => 'msg.info.price_not_found',
                'currency' => $currencyCode,
                'amount'   => 0.0,
                'nights'   => $nights,
                'snapshot' => [],
            ];
        }

        $currency = CurrencyModel::query()
            ->where('code', $currencyCode)
            ->where('is_active', true)
            ->first();

        if (! $currency) {
            return [
                'ok'       => false,
                'err'      => 'msg.err.hotel.currency_missing',
                'currency' => $currencyCode,
                'amount'   => 0.0,
                'nights'   => $nights,
                'snapshot' => [],
            ];
        }

        $room = Room::query()
            ->with([
                'rateRules',
                'hotel.location.parent',
                'hotel.media',
            ])
            ->find($roomId);

        if (! $room || ! $room->hotel) {
            return [
                'ok'       => false,
                'err'      => 'msg.err.hotel.not_found',
                'currency' => $currencyCode,
                'amount'   => 0.0,
                'nights'   => $nights,
                'snapshot' => [],
            ];
        }

        /** @var RoomRateResolver $resolver */
        $resolver = app(RoomRateResolver::class);

        $days = $resolver->resolveRangeForStay(
            $room,
            $checkinYmd,
            $checkoutYmd,
            (int) $currency->id,
            $boardTypeId,
            $adults,
            $children
        );

        if ($days->isEmpty()) {
            return [
                'ok'       => false,
                'err'      => 'msg.info.price_not_found',
                'currency' => $currencyCode,
                'amount'   => 0.0,
                'nights'   => $nights,
                'snapshot' => [],
            ];
        }

        if ($days->contains(fn (array $d) => ($d['closed'] ?? false) === true)) {
            return [
                'ok'       => false,
                'err'      => 'msg.err.hotel.not_available',
                'currency' => $currencyCode,
                'amount'   => 0.0,
                'nights'   => $nights,
                'snapshot' => [],
            ];
        }

        if ($days->contains(fn (array $d) => ($d['ok'] ?? false) === false)) {
            return [
                'ok'       => false,
                'err'      => 'msg.info.price_not_found',
                'currency' => $currencyCode,
                'amount'   => 0.0,
                'nights'   => $nights,
                'snapshot' => [],
            ];
        }

        $totalAmount = (float) $days->sum('total');
        if ($totalAmount <= 0) {
            return [
                'ok'       => false,
                'err'      => 'msg.info.price_not_found',
                'currency' => $currencyCode,
                'amount'   => 0.0,
                'nights'   => $nights,
                'snapshot' => [],
            ];
        }

        $hotel = $room->hotel;

        $ui   = app()->getLocale();
        $base = LocaleHelper::defaultCode();

        $hotelName = I18nHelper::scalar($hotel->name ?? null, $ui, $base);
        $roomName  = I18nHelper::scalar($room->name ?? null, $ui, $base);

        $boardTypeName = null;
        if ($boardTypeId) {
            $bt = \App\Models\BoardType::query()->find($boardTypeId);
            if ($bt) {
                $boardTypeName = I18nHelper::scalar($bt->name ?? null, $ui, $base);
            }
        }

        $area     = $hotel->location?->name;
        $district = $hotel->location?->parent?->name;

        $parts = array_values(array_filter([
            is_string($area) && trim($area) !== '' ? trim($area) : null,
            is_string($district) && trim($district) !== '' ? trim($district) : null,
        ]));

        $locationLabel = ! empty($parts) ? implode(', ', $parts) : null;

        $snapshot = [
            'hotel_id'  => (int) $hotel->id,
            'room_id'   => (int) $room->id,

            'checkin'   => $checkinYmd,
            'checkout'  => $checkoutYmd,
            'nights'    => $nights,

            'adults'    => $adults,
            'children'  => $children,

            'board_type_id' => $boardTypeId,

            'currency'    => $currencyCode,
            'price_total' => $totalAmount,

            'hotel_name'      => $hotelName,
            'room_name'       => $roomName,
            'board_type_name' => $boardTypeName,

            'location_label'  => $locationLabel,
        ];

        $img = $hotel->cover_image ?? null;
        if (is_array($img) && ($img['exists'] ?? false)) {
            $snapshot['hotel_image'] = $img;
        }

        return [
            'ok'       => true,
            'err'      => null,
            'currency' => $currencyCode,
            'amount'   => $totalAmount,
            'nights'   => $nights,
            'snapshot' => $snapshot,
        ];
    }
}
