<?php

namespace App\Services;

use App\Models\Currency as CurrencyModel;
use App\Models\Room;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class RoomRateResolver
{
    /**
     * currency_id -> exponent cache (avoid N+1 in range)
     *
     * @var array<int,int>
     */
    private array $currencyExponentCache = [];

    /**
     * UI'dan gelen "YYYY-MM-DD - YYYY-MM-DD" veya "YYYY-MM-DD" inputunu
     * Y-m-d string'e normalize eder.
     *
     * Dönüş: [checkinYmd, checkoutYmd, nights] veya null
     */
    public function parseUiDateRangeToYmd(?string $raw): ?array
    {
        if (! is_string($raw)) {
            return null;
        }

        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        // Tek tarih veya range: "Y-m-d" | "Y-m-d - Y-m-d" (toleranslı split)
        $parts = preg_split('/\s+-\s+/', $raw, 2);
        if (! is_array($parts) || empty($parts)) {
            return null;
        }

        $startRaw = trim((string) ($parts[0] ?? ''));
        $endRaw   = trim((string) ($parts[1] ?? ''));

        if ($startRaw === '' || ! $this->isValidYmd($startRaw)) {
            return null;
        }

        try {
            $start = Carbon::createFromFormat('Y-m-d', $startRaw)->startOfDay();
        } catch (\Throwable) {
            return null;
        }

        // Tek tarih verilirse: checkout = start + 1 day
        if ($endRaw === '') {
            $end = $start->copy()->addDay();
        } else {
            if (! $this->isValidYmd($endRaw)) {
                return null;
            }

            try {
                $end = Carbon::createFromFormat('Y-m-d', $endRaw)->startOfDay();
            } catch (\Throwable) {
                return null;
            }

            // 🔒 K1: Sessiz düzeltme yok (end <= start -> invalid)
            if ($end->lte($start)) {
                return null;
            }
        }

        $nights = (int) $start->diffInDays($end);
        if ($nights < 1) {
            return null;
        }

        return [
            $start->format('Y-m-d'),
            $end->format('Y-m-d'),
            $nights,
        ];
    }

    /**
     * checkin/checkout (Y-m-d) ile konaklama için fiyatlanan aralığı döndürür.
     * checkout gününü fiyatlamaz (checkout-1'e kadar).
     */
    public function resolveRangeForStay(
        Room $room,
        string $checkinYmd,
        string $checkoutYmd,
        int $currencyId,
        ?int $boardTypeId,
        int $adults,
        int $children = 0
    ): Collection {
        $checkinYmd  = trim($checkinYmd);
        $checkoutYmd = trim($checkoutYmd);

        if ($checkinYmd === '' || $checkoutYmd === '') {
            return collect();
        }

        if (! $this->isValidYmd($checkinYmd) || ! $this->isValidYmd($checkoutYmd)) {
            return collect();
        }

        try {
            $checkin  = Carbon::createFromFormat('Y-m-d', $checkinYmd)->startOfDay();
            $checkout = Carbon::createFromFormat('Y-m-d', $checkoutYmd)->startOfDay();
        } catch (\Throwable) {
            return collect();
        }

        // 🔒 K1: Sessiz düzeltme yok
        if ($checkout->lte($checkin)) {
            return collect();
        }

        $nights = (int) $checkin->diffInDays($checkout);
        if ($nights < 1) {
            return collect();
        }

        // checkout fiyatlanmaz → inclusive resolver kullandığımız için checkout-1
        $rangeEnd = $checkout->copy()->subDay();

        return $this->resolveRange(
            $room,
            $checkin->format('Y-m-d'),
            $rangeEnd->format('Y-m-d'),
            $currencyId,
            $boardTypeId,
            $adults,
            $children
        );
    }

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
        $date = trim($date);

        if ($date === '' || ! $this->isValidYmd($date)) {
            return ['ok' => false, 'closed' => false, 'reason' => 'invalid_date'];
        }

        try {
            $d = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        } catch (\Throwable) {
            return ['ok' => false, 'closed' => false, 'reason' => 'invalid_date'];
        }

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

        // 🔒 K2: Rounding otoritesi burada
        $exp = $this->getCurrencyExponent($currencyId);

        $amount = (float) $rule->amount;
        $amount = round($amount, $exp, PHP_ROUND_HALF_UP);

        $mode = $rule->price_type === 'room_per_night' ? 'room' : 'person';

        // Çocuk indirimi (oda > otel). Yalnız kişi-başı modda uygulanır.
        [$childPct, $childSource] = $this->resolveChildDiscount($room);

        if ($mode === 'room') {
            $adultTotal      = $amount; // oda/gece
            $childUnitsTotal = 0.0;
            $total           = $adultTotal;
        } else {
            $adultTotal = max(0, (int) $adults) * $amount;

            $children = max(0, (int) $children);
            if ($children > 0 && $childPct !== null) {
                $pct       = max(0, min(100, (float) $childPct));
                $childUnit = $amount * (1 - $pct / 100);
            } else {
                $childUnit = $amount; // indirim yoksa çocuk yetişkin gibi
            }

            $childUnit = round($childUnit, $exp, PHP_ROUND_HALF_UP);

            $childUnitsTotal = $children * $childUnit;
            $total = $adultTotal + $childUnitsTotal;
        }

        $adultTotal      = round((float) $adultTotal, $exp, PHP_ROUND_HALF_UP);
        $childUnitsTotal = round((float) $childUnitsTotal, $exp, PHP_ROUND_HALF_UP);
        $total           = round((float) $total, $exp, PHP_ROUND_HALF_UP);

        return [
            'ok'            => true,
            'label'         => $rule->label,
            'closed'        => false,
            'cta'           => (bool) $rule->cta,
            'ctd'           => (bool) $rule->ctd,
            'rule_id'       => $rule->id,
            'price_mode'    => $mode,
            'unit_amount'   => $amount,
            'total'         => $total,
            'currency_id'   => $rule->currency_id,
            'board_type_id' => $rule->board_type_id,
            'is_baseline'   => is_null($rule->date_start) && is_null($rule->date_end),

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
    ): Collection {
        $out = collect();

        $dateStart = trim($dateStart);
        $dateEnd   = trim($dateEnd);

        if ($dateStart === '' || $dateEnd === '' || ! $this->isValidYmd($dateStart) || ! $this->isValidYmd($dateEnd)) {
            return $out;
        }

        try {
            $start = Carbon::createFromFormat('Y-m-d', $dateStart)->startOfDay();
            $end   = Carbon::createFromFormat('Y-m-d', $dateEnd)->startOfDay();
        } catch (\Throwable) {
            return $out;
        }

        // 🔒 K1: Sessiz düzeltme yok
        if ($end->lt($start)) {
            return $out;
        }

        // Başlangıç ve bitiş dahil
        $nights = $start->diffInDays($end) + 1;
        $period = CarbonPeriod::create($start, $end); // [start .. end]

        foreach ($period as $d) {
            $ymd = $d->format('Y-m-d');

            $r = $this->resolveDay(
                $room,
                $ymd,
                $currencyId,
                $boardTypeId,
                $adults,
                $children,
                $nights
            );

            $out->push(['date' => $ymd] + $r);
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
                'ok'              => false,
                'state'           => 'no_rate',
                'reason'          => 'empty_range',
                'price_mode'      => null,
                'nights'          => 0,
                'total'           => 0.0,
                'per_night_total' => 0.0,
                'unit_amount'     => null,
                'currency_id'     => null,
                'board_type_id'   => $boardTypeId,
                'meta'            => [],
            ];
        }

        if ($days->contains(fn (array $d) => ($d['closed'] ?? false) === true)) {
            return [
                'ok'              => false,
                'state'           => 'closed',
                'reason'          => 'closed',
                'price_mode'      => null,
                'nights'          => $days->count(),
                'total'           => 0.0,
                'per_night_total' => 0.0,
                'unit_amount'     => null,
                'currency_id'     => null,
                'board_type_id'   => $boardTypeId,
                'meta'            => [],
            ];
        }

        if ($days->contains(fn (array $d) => ($d['ok'] ?? false) === false)) {
            $firstError = $days->first(fn (array $d) => ($d['ok'] ?? false) === false);

            return [
                'ok'              => false,
                'state'           => 'no_rate',
                'reason'          => $firstError['reason'] ?? 'rule_not_found',
                'price_mode'      => null,
                'nights'          => $days->count(),
                'total'           => 0.0,
                'per_night_total' => 0.0,
                'unit_amount'     => null,
                'currency_id'     => null,
                'board_type_id'   => $boardTypeId,
                'meta'            => [],
            ];
        }

        $nights   = $days->count();
        $total    = (float) $days->sum('total');
        $first    = $days->first();
        $mode     = $first['price_mode'] ?? null;
        $currency = $first['currency_id'] ?? $currencyId;
        $board    = $first['board_type_id'] ?? $boardTypeId;

        $exp = $this->getCurrencyExponent((int) $currency);
        $total = round($total, $exp, PHP_ROUND_HALF_UP);

        $perNight = $nights > 0 ? $total / $nights : 0.0;
        $perNight = round($perNight, $exp, PHP_ROUND_HALF_UP);

        return [
            'ok'              => true,
            'state'           => $mode === 'room' ? 'ok_room' : 'ok_person',
            'price_mode'      => $mode,
            'nights'          => $nights,
            'total'           => $total,
            'per_night_total' => $perNight,
            'unit_amount'     => $first['unit_amount'] ?? null,
            'currency_id'     => $currency,
            'board_type_id'   => $board,
            'meta'            => [
                'adults'   => $adults,
                'children' => $children,
            ],
        ];
    }

    private function getCurrencyExponent(int $currencyId): int
    {
        if ($currencyId <= 0) {
            return 2;
        }

        if (array_key_exists($currencyId, $this->currencyExponentCache)) {
            return $this->currencyExponentCache[$currencyId];
        }

        $exp = 2;

        try {
            $m = CurrencyModel::query()->find($currencyId);
            if ($m && $m->exponent !== null) {
                $exp = (int) $m->exponent;
            }
        } catch (\Throwable) {
            $exp = 2;
        }

        if ($exp < 0) {
            $exp = 0;
        }

        $this->currencyExponentCache[$currencyId] = $exp;

        return $exp;
    }

    private function isValidYmd(string $ymd): bool
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $ymd)) {
            return false;
        }

        [$y, $m, $d] = array_map('intval', explode('-', $ymd, 3));

        return checkdate($m, $d, $y);
    }
}
