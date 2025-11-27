<?php

namespace App\Services;

use App\Models\Villa;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class VillaRateResolver
{
    /**
     * Tek gün için fiyatı çözer.
     */
    public function resolveDay(
        Villa $villa,
        string $date,
        int $currencyId,
    ): array {
        $d = Carbon::parse($date)->startOfDay();

        // 1 = Mon .. 7 = Sun, bit mask
        $weekdayBit = 1 << ($d->dayOfWeekIso - 1);

        $q = $villa->rateRules()
            ->where('currency_id', $currencyId)
            ->where(function ($q) use ($d) {
                $q->whereNull('date_start')->orWhere('date_start', '<=', $d);
            })
            ->where(function ($q) use ($d) {
                $q->whereNull('date_end')->orWhere('date_end', '>=', $d);
            })
            ->whereRaw('(weekday_mask & ?) <> 0', [$weekdayBit])
            ->where('is_active', true)
            ->orderByDesc('priority')
            ->orderByRaw('(COALESCE(date_end, date_start) - COALESCE(date_start, date_end)) asc')
            ->orderBy('id');

        $rule = $q->first();

        if (! $rule) {
            return [
                'ok'        => false,
                'closed'    => false,
                'reason'    => 'rule_not_found',
                'price_mode'=> null,
                'unit_amount' => null,
                'total'       => 0.0,
                'currency_id' => $currencyId,
                'meta'        => [],
            ];
        }

        if ($rule->closed) {
            return [
                'ok'         => false,
                'closed'     => true,
                'reason'     => 'closed',
                'price_mode' => null,
                'unit_amount'=> (float) $rule->amount,
                'total'      => 0.0,
                'currency_id'=> $rule->currency_id,
                'meta'       => [],
            ];
        }

        $amount = (float) $rule->amount;

        return [
            'ok'          => true,
            'label'       => $rule->label,
            'closed'      => false,
            'cta'         => (bool) $rule->cta,
            'ctd'         => (bool) $rule->ctd,
            'rule_id'     => $rule->id,
            'price_mode'  => 'room',      // villa = oda/gece bazlı
            'unit_amount' => $amount,
            'total'       => $amount,     // 1 villa, 1 gece
            'currency_id' => $rule->currency_id,
            'is_baseline' => is_null($rule->date_start) && is_null($rule->date_end),
            'meta'        => [],
        ];
    }

    /**
     * Tarih aralığı için günlük fiyatları döndürür.
     * Burada başlangıç ve bitiş dahil olarak gösteriyoruz (önizleme amaçlı).
     */
    public function resolveRange(
        Villa $villa,
        string $dateStart,
        string $dateEnd,
        int $currencyId,
    ): Collection {
        $out   = collect();

        $start = Carbon::parse($dateStart)->startOfDay();
        $end   = Carbon::parse($dateEnd)->startOfDay();

        if ($end->lt($start)) {
            $end = $start->copy();
        }

        $period = CarbonPeriod::create($start, $end); // [start .. end]

        foreach ($period as $d) {
            $r = $this->resolveDay(
                $villa,
                $d->format('Y-m-d'),
                $currencyId,
            );

            $out->push(['date' => $d->format('Y-m-d')] + $r);
        }

        return $out;
    }
}
