<?php

namespace App\Services;

use App\Models\Room;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class RoomRateResolver
{
    /**
     * Tek gün için fiyatı çözer.
     * price_mode: 'room' | 'person'
     */
    public function resolveDay(
        Room $room,
        string $date,
        int $currencyId,
        ?int $boardTypeId,
        int $adults,
        int $children = 0,
        int $stayLength = 1
    ): array {
        $d = Carbon::parse($date)->startOfDay();
        $weekdayBit = 1 << ($d->dayOfWeekIso - 1); // 1=Mon..7=Sun

        // Kurala giriş için doluluk: yetişkin + çocuk
        $occupancy = max(1, (int) $adults + (int) $children);

        $q = $room->rateRules()
            ->where('currency_id', $currencyId)
            ->where(function ($q) use ($d) {
                $q->whereNull('date_start')->orWhere('date_start', '<=', $d);
            })
            ->where(function ($q) use ($d) {
                $q->whereNull('date_end')->orWhere('date_end', '>=', $d);
            })
            ->whereRaw('(weekday_mask & ?) <> 0', [$weekdayBit])
            ->where('occupancy_min', '<=', $occupancy)
            ->where('occupancy_max', '>=', $occupancy)
            ->where(function ($q) use ($boardTypeId) {
                $q->whereNull('board_type_id');
                if ($boardTypeId) {
                    $q->orWhere('board_type_id', $boardTypeId);
                }
            })
            ->where(function ($q) use ($stayLength) {
                $q->whereNull('los_min')->orWhere('los_min', '<=', $stayLength);
            })
            ->where(function ($q) use ($stayLength) {
                $q->whereNull('los_max')->orWhere('los_max', '>=', $stayLength);
            })
            ->where('is_active', true)
            ->orderByDesc('priority')
            ->orderByRaw('(COALESCE(date_end, date_start) - COALESCE(date_start, date_end)) asc')
            ->orderBy('id');

        $rule = $q->first();

        if (! $rule) {
            return ['ok' => false, 'closed' => false, 'reason' => 'rule_not_found'];
        }

        if ($rule->closed) {
            return ['ok' => false, 'closed' => true, 'reason' => 'closed'];
        }

        $amount = (float) $rule->amount;
        $mode   = $rule->price_type === 'room_per_night' ? 'room' : 'person';

        // Çocuk indirimi (oda > otel). Yalnız kişi-başı modda uygulanır.
        [$childPct, $childSource] = $this->resolveChildDiscount($room);

        if ($mode === 'room') {
            $adultTotal       = $amount;        // oda/gece
            $childUnitsTotal  = 0.0;
            $total            = $adultTotal;
        } else {
            $adultTotal = max(0, (int) $adults) * $amount;

            $children = max(0, (int) $children);
            if ($children > 0 && $childPct !== null) {
                $pct        = max(0, min(100, $childPct));
                $childUnit  = $amount * (1 - $pct / 100);
            } else {
                $childUnit  = $amount; // indirim yoksa çocuk yetişkin gibi
            }
            $childUnitsTotal = $children * $childUnit;

            $total = $adultTotal + $childUnitsTotal;
        }

        return [
            'ok'          => true,
            'label'       => $rule->label,
            'closed'      => false,
            'cta'         => (bool) $rule->cta,
            'ctd'         => (bool) $rule->ctd,
            'rule_id'     => $rule->id,
            'price_mode'  => $mode,
            'unit_amount' => $amount,
            'total'       => $total,
            'currency_id' => $rule->currency_id,
            'board_type_id' => $rule->board_type_id,
            'is_baseline' => is_null($rule->date_start) && is_null($rule->date_end),

            'meta' => [
                'adults'                 => (int) $adults,
                'children'               => $mode === 'person' ? (int) $children : 0,
                'children_units_total'   => $mode === 'person' ? $childUnitsTotal : 0.0,
                'child_discount_percent' => $mode === 'person' ? $childPct : null,
                'child_discount_source'  => $mode === 'person' ? $childSource : null, // 'room' | 'hotel' | null
            ],
        ];
    }

    /**
     * Tarih aralığı için günlük fiyatları döndürür.
     * Bitiş tarihi çıkıştır, fiyatlanmaz. LOS = (bitiş - başlangıç) geceler.
     */
    public function resolveRange(
        Room $room,
        string $dateStart,
        string $dateEnd,
        int $currencyId,
        ?int $boardTypeId,
        int $adults,
        int $children = 0
    ): \Illuminate\Support\Collection {
        $out   = collect();

        $start = \Carbon\Carbon::parse($dateStart)->startOfDay();
        $end   = \Carbon\Carbon::parse($dateEnd)->startOfDay();

        // Ters verilirse düzelt
        if ($end->lt($start)) {
            $end = $start->copy();
        }

        // Başlangıç ve bitiş dahil
        $nights = $start->diffInDays($end) + 1;
        $period = \Carbon\CarbonPeriod::create($start, $end); // [start .. end]

        foreach ($period as $d) {
            $r = $this->resolveDay(
                $room,
                $d->format('Y-m-d'),
                $currencyId,
                $boardTypeId,
                $adults,
                $children,
                $nights
            );

            $out->push(['date' => $d->format('Y-m-d')] + $r);
        }

        return $out;
    }


    /**
     * Oda > Otel sırası ile çocuk indirim yüzdesini getirir.
     * Dönüş: [percent|null, 'room'|'hotel'|null]
     */
    private function resolveChildDiscount(Room $room): array
    {
        if ($room->child_discount_active && $room->child_discount_percent !== null) {
            return [(float) $room->child_discount_percent, 'room'];
        }

        $hotel = $room->hotel;
        if ($hotel && $hotel->child_discount_active && $hotel->child_discount_percent !== null) {
            return [(float) $hotel->child_discount_percent, 'hotel'];
        }

        return [null, null];
    }


    /**
     * Bir konaklamayı (check-in / check-out + kişi sayıları) özetler.
     *
     * Dönen yapı:
     *  - ok: bool
     *  - state: 'no_rate' | 'closed' | 'ok_room' | 'ok_person'
     *  - price_mode: 'room' | 'person' | null
     *  - nights: int
     *  - total: float               // toplam konaklama tutarı
     *  - per_night_total: float     // gecelik ortalama toplam
     *  - unit_amount: float|null    // kuraldaki baz fiyat (gece / kişi)
     *  - currency_id: int|null
     *  - board_type_id: int|null
     *  - meta: []
     */
    public function summarizeStay(
        Room $room,
        string $dateStart,
        string $dateEnd,
        int $currencyId,
        ?int $boardTypeId,
        int $adults,
        int $children = 0
    ): array {
        // Günlük detayları al
        $days = $this->resolveRange(
            $room,
            $dateStart,
            $dateEnd,
            $currencyId,
            $boardTypeId,
            $adults,
            $children
        );

        if ($days->isEmpty()) {
            return [
                'ok'            => false,
                'state'         => 'no_rate',
                'reason'        => 'empty_range',
                'price_mode'    => null,
                'nights'        => 0,
                'total'         => 0.0,
                'per_night_total' => 0.0,
                'unit_amount'   => null,
                'currency_id'   => null,
                'board_type_id' => $boardTypeId,
                'meta'          => [],
            ];
        }

        // Herhangi bir gün kapalı mı?
        if ($days->contains(fn (array $d) => ($d['closed'] ?? false) === true)) {
            return [
                'ok'            => false,
                'state'         => 'closed',
                'reason'        => 'closed',
                'price_mode'    => null,
                'nights'        => $days->count(),
                'total'         => 0.0,
                'per_night_total' => 0.0,
                'unit_amount'   => null,
                'currency_id'   => null,
                'board_type_id' => $boardTypeId,
                'meta'          => [],
            ];
        }

        // Herhangi bir gün için kural bulunamamış mı?
        if ($days->contains(fn (array $d) => ($d['ok'] ?? false) === false)) {
            $firstError = $days->first(fn (array $d) => ($d['ok'] ?? false) === false);

            return [
                'ok'            => false,
                'state'         => 'no_rate',
                'reason'        => $firstError['reason'] ?? 'rule_not_found',
                'price_mode'    => null,
                'nights'        => $days->count(),
                'total'         => 0.0,
                'per_night_total' => 0.0,
                'unit_amount'   => null,
                'currency_id'   => null,
                'board_type_id' => $boardTypeId,
                'meta'          => [],
            ];
        }

        // Buraya geldiysek tüm günler için fiyat var
        $nights   = $days->count();
        $total    = $days->sum('total');
        $first    = $days->first();
        $mode     = $first['price_mode'] ?? null;
        $currency = $first['currency_id'] ?? $currencyId;
        $board    = $first['board_type_id'] ?? $boardTypeId;

        return [
            'ok'             => true,
            'state'          => $mode === 'room' ? 'ok_room' : 'ok_person',
            'price_mode'     => $mode,
            'nights'         => $nights,
            'total'          => $total,
            'per_night_total'=> $nights > 0 ? $total / $nights : 0.0,
            'unit_amount'    => $first['unit_amount'] ?? null,
            'currency_id'    => $currency,
            'board_type_id'  => $board,
            'meta'           => [
                'adults'   => $adults,
                'children' => $children,
                // İstersen ileride günlük bazda detayları da ekleyebiliriz
                // 'days'  => $days->all(),
            ],
        ];
    }

}
