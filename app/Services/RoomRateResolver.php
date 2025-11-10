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
}
