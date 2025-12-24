<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Order extends Model
{
    use SoftDeletes;

    public const STATUS_PENDING   = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';

    protected $table = 'orders';

    protected $guarded = [];

    protected $casts = [
        'total_amount'       => 'float',
        'discount_amount'    => 'float',
        'billing_address'    => 'array',
        'metadata'           => 'array',
        'coupon_snapshot'    => 'array',
        'paid_at'            => 'datetime',
        'payment_expires_at' => 'datetime',
        'cancelled_at'       => 'datetime',
        'completed_at'       => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (blank($order->code)) {
                $last = static::withTrashed()
                    ->where('code', 'LIKE', 'ORD-%')
                    ->orderByDesc('id')
                    ->value('code');

                $n = 1;
                if ($last && preg_match('/ORD-(\d+)/', $last, $m)) {
                    $n = ((int) $m[1]) + 1;
                }

                $order->code = sprintf('ORD-%06d', $n);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Status meta (tek kaynak)
    |--------------------------------------------------------------------------
    |
    | Filament: label_key + filament_color
    | FE (Bootstrap): label + bootstrap_class
    */
    public static function statusMeta(?string $status): array
    {
        $status = (string) ($status ?? '');

        return match ($status) {
            self::STATUS_PENDING => [
                'status'          => self::STATUS_PENDING,
                'label_key'       => 'admin.orders.status.pending',
                'label'           => 'Onay Bekliyor',
                'filament_color'  => 'warning',
                'bootstrap_class' => 'bg-warning',
            ],

            self::STATUS_CONFIRMED => [
                'status'          => self::STATUS_CONFIRMED,
                'label_key'       => 'admin.orders.status.confirmed',
                'label'           => 'Onaylandı',
                'filament_color'  => 'success',
                'bootstrap_class' => 'bg-success',
            ],

            self::STATUS_CANCELLED => [
                'status'          => self::STATUS_CANCELLED,
                'label_key'       => 'admin.orders.status.cancelled',
                'label'           => 'İptal Edildi',
                'filament_color'  => 'danger',
                'bootstrap_class' => 'bg-danger',
            ],

            self::STATUS_COMPLETED => [
                'status'          => self::STATUS_COMPLETED,
                'label_key'       => 'admin.orders.status.completed',
                'label'           => 'Tamamlandı',
                'filament_color'  => 'gray',
                'bootstrap_class' => 'bg-dark',
            ],

            default => [
                'status'          => $status,
                'label_key'       => null,
                'label'           => $status !== '' ? $status : '-',
                'filament_color'  => 'gray',
                'bootstrap_class' => 'bg-secondary',
            ],
        };
    }

    public static function statusList(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_CANCELLED,
            self::STATUS_COMPLETED,
        ];
    }

    public static function filamentStatusOptions(): array
    {
        $out = [];

        foreach (self::statusList() as $st) {
            $meta = self::statusMeta($st);
            $out[$st] = $meta['label_key'] ? __($meta['label_key']) : $st;
        }

        return $out;
    }

    /*
    |--------------------------------------------------------------------------
    | Completed hesabı (runtime)
    |--------------------------------------------------------------------------
    |
    | Kural:
    | - Sadece: hotel_room, villa, transfer, tour|excursion
    | - hotel item'ları yok sayılır.
    | - Her item için "bitiş tarihi" hesaplanır; siparişin service_end_at değeri en geç olanıdır.
    */
    public function computeServiceEndAt(): ?Carbon
    {
        $items = $this->items;

        if (! $items || $items->isEmpty()) {
            return null;
        }

        $tz = config('app.timezone', 'Europe/Istanbul');

        $endCandidates = [];

        foreach ($items as $item) {
            $type = (string) ($item->product_type ?? '');
            $s    = (array) ($item->snapshot ?? []);

            if ($type === 'hotel') {
                continue;
            }

            if ($type === 'hotel_room') {
                $dt = $this->parseYmdDateToEndOfDay($s['checkout'] ?? null, $tz);
                if ($dt) $endCandidates[] = $dt;
                continue;
            }

            if ($type === 'villa') {
                $dt = $this->parseYmdDateToEndOfDay($s['checkout'] ?? null, $tz);
                if ($dt) $endCandidates[] = $dt;
                continue;
            }

            if ($type === 'tour' || $type === 'excursion') {
                $dt = $this->parseYmdDateToEndOfDay($s['date'] ?? null, $tz);
                if ($dt) $endCandidates[] = $dt;
                continue;
            }

            if ($type === 'transfer') {
                $direction = (string) ($s['direction'] ?? 'oneway');

                $date = $direction === 'roundtrip'
                    ? ($s['return_date'] ?? null)
                    : ($s['departure_date'] ?? null);

                $dt = $this->parseYmdDateToEndOfDay($date, $tz);
                if ($dt) $endCandidates[] = $dt;
                continue;
            }
        }

        if (empty($endCandidates)) {
            return null;
        }

        /** @var Carbon $max */
        $max = collect($endCandidates)->sort()->last();

        return $max;
    }

    private function parseYmdDateToEndOfDay($value, string $tz): ?Carbon
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value, $tz)->endOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Order Items (Infolist için)
    |--------------------------------------------------------------------------
    */

    public function getItemsForInfolistAttribute(): array
    {
        return $this->items
            ->map(function (OrderItem $item): array {
                $s        = (array) ($item->snapshot ?? []);
                $type     = $item->product_type;
                $currency = strtoupper($item->currency ?? $this->currency ?? '');

                $image = $this->resolveItemImageFromSnapshot($s);

                $formatMoney = function ($amount) use ($currency): ?string {
                    if ($amount === null) {
                        return null;
                    }

                    $amount = (float) $amount;

                    return number_format($amount, 0, ',', '.') . ' ' . $currency;
                };

                $formatDateTime = function (?string $date, ?string $time = null): ?string {
                    if (blank($date)) {
                        return null;
                    }

                    $value = trim($date . ' ' . (string) $time);

                    try {
                        return Carbon::parse($value)->format('d.m.Y H:i');
                    } catch (\Throwable) {
                        return $value;
                    }
                };

                $formatDateOnly = function (?string $value): ?string {
                    if (blank($value)) {
                        return null;
                    }

                    try {
                        return Carbon::parse($value)->format('d.m.Y');
                    } catch (\Throwable) {
                        return $value;
                    }
                };

                $a = (int) ($s['adults']   ?? 0);
                $c = (int) ($s['children'] ?? 0);
                $i = (int) ($s['infants']  ?? 0);

                $paxParts = [];

                if ($a) $paxParts[] = trans_choice('admin.orders.pax.adult', $a, ['count' => $a]);
                if ($c) $paxParts[] = trans_choice('admin.orders.pax.child', $c, ['count' => $c]);
                if ($i) $paxParts[] = trans_choice('admin.orders.pax.infant', $i, ['count' => $i]);

                $pax = $paxParts ? implode(', ', $paxParts) : null;

                $base = [
                    'type'  => $type,
                    'image' => $image,
                    'pax'   => $pax,
                ];

                if ($type === 'hotel' || $type === 'hotel_room') {
                    return $base + [
                            'hotel_name' => $s['hotel_name']      ?? $s['title'] ?? null,
                            'room_name'  => $s['room_name']       ?? null,
                            'board_type' => $s['board_type_name'] ?? null,
                            'checkin'    => $formatDateOnly($s['checkin']  ?? null),
                            'checkout'   => $formatDateOnly($s['checkout'] ?? null),
                            'paid'       => $formatMoney($item->total_price ?? $item->unit_price),
                        ];
                }

                if ($type === 'villa') {
                    $total = isset($s['price_total'])      ? (float) $s['price_total']      : null;
                    $pre   = isset($s['price_prepayment']) ? (float) $s['price_prepayment'] : null;

                    $remaining = null;
                    $rateText  = null;

                    if ($total !== null && $pre !== null) {
                        $remaining = max($total - $pre, 0);

                        if ($total > 0) {
                            $rate     = round($pre / $total * 100);
                            $rateText = " ({$rate}%)";
                        }
                    }

                    return $base + [
                            'villa_name' => $s['villa_name'] ?? $s['title'] ?? null,
                            'checkin'    => $formatDateOnly($s['checkin']  ?? null),
                            'checkout'   => $formatDateOnly($s['checkout'] ?? null),
                            'paid'       => $pre       !== null ? $formatMoney($pre)       . $rateText : null,
                            'remaining'  => $remaining !== null ? $formatMoney($remaining) : null,
                            'total'      => $total     !== null ? $formatMoney($total)     : null,
                        ];
                }

                if ($type === 'tour' || $type === 'excursion') {
                    $tourDateTime = null;

                    if (! empty($s['date'])) {
                        $tourDateTime = $s['date'];

                        if (! empty($s['pickup_time'])) {
                            $tourDateTime .= ' ' . $s['pickup_time'];
                        } elseif (! empty($s['time'])) {
                            $tourDateTime .= ' ' . $s['time'];
                        }
                    }

                    return $base + [
                            'tour_name' => $s['tour_name'] ?? $s['title'] ?? null,
                            'date'      => $tourDateTime
                                ? $formatDateTime($tourDateTime)
                                : $formatDateOnly($s['date'] ?? null),
                            'paid'      => $formatMoney($item->total_price ?? $item->unit_price),
                        ];
                }

                if ($type === 'transfer') {
                    $route = null;

                    if (! empty($s['from_label']) || ! empty($s['to_label'])) {
                        $route = trim(
                            (string) ($s['from_label'] ?? '') .
                            ' → ' .
                            (string) ($s['to_label']   ?? '')
                        );
                    }

                    $departureDate = $formatDateTime(
                        $s['departure_date']       ?? null,
                        $s['pickup_time_outbound'] ?? null,
                    );

                    $returnDate = $formatDateTime(
                        $s['return_date']          ?? null,
                        $s['pickup_time_return']   ?? null,
                    );

                    return $base + [
                            'route'            => $route,
                            'vehicle'          => $s['vehicle_name'] ?? null,
                            'departure_date'   => $departureDate,
                            'return_date'      => $returnDate,
                            'departure_flight' => $s['flight_number_outbound'] ?? null,
                            'return_flight'    => $s['flight_number_return']   ?? null,
                            'paid'             => $formatMoney($item->total_price ?? $item->unit_price),
                        ];
                }

                return $base + [
                        'paid' => $formatMoney($item->total_price ?? $item->unit_price),
                    ];
            })
            ->values()
            ->all();
    }

    protected function resolveItemImageFromSnapshot(array $s): ?string
    {
        foreach (['hotel_image', 'villa_image', 'vehicle_image', 'cover_image'] as $key) {
            if (! array_key_exists($key, $s) || blank($s[$key])) {
                continue;
            }

            $value = $s[$key];

            if (is_array($value) && ! empty($value['thumb'])) {
                return $value['thumb'];
            }

            if (is_string($value)) {
                return $value;
            }
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function items()
    {
        return $this->hasMany(\App\Models\OrderItem::class);
    }

    public function successfulPaymentAttempt(): ?\App\Models\PaymentAttempt
    {
        return $this->paymentAttempts()
            ->where('status', \App\Models\PaymentAttempt::STATUS_SUCCESS)
            ->orderByDesc('id')
            ->first();
    }

    public function paymentAttempts()
    {
        return $this->hasMany(\App\Models\PaymentAttempt::class);
    }

    public function latestPaymentAttempt()
    {
        return $this->hasOne(\App\Models\PaymentAttempt::class)->latestOfMany();
    }

    public function refundAttempts(): HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Models\RefundAttempt::class,
            \App\Models\PaymentAttempt::class,
            'order_id',
            'payment_attempt_id',
            'id',
            'id'
        );
    }

    public function getDiscountsForInfolistAttribute(): array
    {
        $rows     = (array) ($this->coupon_snapshot ?? []);
        $currency = strtoupper($this->currency ?? '');

        return collect($rows)
            ->map(function ($row) use ($currency) {
                $amount = (float) ($row['discount'] ?? 0);
                if ($amount <= 0) {
                    return null;
                }

                $amountFormatted = number_format($amount, 2, ',', '.') . ' ' . $currency;

                $label = $row['title']
                    ?? $row['code']
                    ?? '-';

                $type = $row['type'] ?? null;

                if ($type === 'coupon') {
                    $badge = __('admin.orders.form.badge_coupon');
                } elseif ($type === 'campaign') {
                    $badge = __('admin.orders.form.badge_campaign');
                } else {
                    $badge = null;
                }

                return [
                    'amount' => $amountFormatted,
                    'label'  => $label,
                    'badge'  => $badge,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public function getRefundsForInfolistAttribute(): array
    {
        $currency = strtoupper((string) ($this->currency ?? ''));

        return $this->refundAttempts()
            ->select('refund_attempts.*')
            ->where('refund_attempts.status', \App\Models\RefundAttempt::STATUS_SUCCESS)
            ->orderByDesc('refund_attempts.id')
            ->get()
            ->map(function (\App\Models\RefundAttempt $r) use ($currency) {
                return [
                    'reason' => $r->reason ?: null,
                    'amount' => number_format((float) $r->amount, 2, ',', '.') . ' ' . $currency,
                    'time'   => $r->created_at?->format('d.m.Y H:i') ?? null,
                ];
            })
            ->values()
            ->all();
    }

    public function supportTickets()
    {
        return $this->hasMany(\App\Models\SupportTicket::class);
    }

    public function activeSupportTicket()
    {
        return $this->hasOne(\App\Models\SupportTicket::class)
            ->whereNull('deleted_at');
    }

    /*
    |--------------------------------------------------------------------------
    | CUSTOMER ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getCustomerNameAttribute(): ?string
    {
        $guest = $this->metadata['guest'] ?? null;
        if (is_array($guest)) {
            $first = $guest['first_name'] ?? null;
            $last  = $guest['last_name'] ?? null;

            return trim($first . ' ' . $last) ?: null;
        }

        if ($this->user) {
            return $this->user->name ?? null;
        }

        return null;
    }

    public function getCustomerEmailAttribute(): ?string
    {
        if (! empty($this->metadata['guest']['email'])) {
            return $this->metadata['guest']['email'];
        }

        return $this->user->email ?? null;
    }

    public function getCustomerPhoneAttribute(): ?string
    {
        if (! empty($this->metadata['guest']['phone'])) {
            return $this->metadata['guest']['phone'];
        }

        return $this->user->phone ?? null;
    }

    public function getCustomerTypeAttribute(): string
    {
        return $this->user_id ? 'member' : 'guest';
    }
}
