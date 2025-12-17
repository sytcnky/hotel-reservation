<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Order extends Model
{
    use SoftDeletes;

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
    | Order Items (Infolist için)
    |--------------------------------------------------------------------------
    */

    public function getItemsForInfolistAttribute(): array
    {
        return $this->items
            ->map(function (OrderItem $item): array {
                $s        = (array) ($item->snapshot ?? []);
                $type     = $item->product_type;
                $currency = strtoupper($item->currency ?? $this->currency ?? 'TRY');

                $image = $this->resolveItemImageFromSnapshot($s);

                // Para formatlayıcı
                $formatMoney = function ($amount) use ($currency): ?string {
                    if ($amount === null) {
                        return null;
                    }

                    $amount = (float) $amount;

                    return number_format($amount, 0, ',', '.') . ' ' . $currency;
                };

                // Tarih formatlayıcı
                $formatDateTime = function (?string $date, ?string $time = null): ?string {
                    if (blank($date)) {
                        return null;
                    }

                    $value = trim($date . ' ' . (string) $time);

                    try {
                        return Carbon::parse($value)->format('d.m.Y H:i');
                    } catch (\Throwable $e) {
                        return $value;
                    }
                };

                $formatDateOnly = function (?string $value): ?string {
                    if (blank($value)) {
                        return null;
                    }

                    try {
                        return Carbon::parse($value)->format('d.m.Y');
                    } catch (\Throwable $e) {
                        return $value;
                    }
                };

                // Kişi sayısı — sadece sayısal ham veri
                $pax = null;

                $a = (int) ($s['adults']   ?? 0);
                $c = (int) ($s['children'] ?? 0);
                $i = (int) ($s['infants']  ?? 0);

                $paxParts = [];

                if ($a) {
                    $paxParts[] = trans_choice('admin.orders.pax.adult', $a, ['count' => $a]);
                }

                if ($c) {
                    $paxParts[] = trans_choice('admin.orders.pax.child', $c, ['count' => $c]);
                }

                if ($i) {
                    $paxParts[] = trans_choice('admin.orders.pax.infant', $i, ['count' => $i]);
                }

                if ($paxParts) {
                    $pax = implode(', ', $paxParts);
                }

                // Ortak base
                $base = [
                    'type'  => $type,
                    'image' => $image,
                    'pax'   => $pax,
                ];

                // Otel
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

                // Villa
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

                // Tur
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

                // Transfer
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

                // Fallback
                return $base + [
                        'paid' => $formatMoney($item->total_price ?? $item->unit_price),
                    ];
            })
            ->values()
            ->all();
    }

    /**
     * Snapshot içinden kart görselini çözer.
     */
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

    /**
     * Bu siparişe ait tüm ödeme girişimleri.
     */
    public function paymentAttempts()
    {
        return $this->hasMany(\App\Models\PaymentAttempt::class);
    }

    /**
     * En son oluşturulmuş ödeme girişimi (ör. yeniden deneme senaryoları).
     */
    public function latestPaymentAttempt()
    {
        return $this->hasOne(\App\Models\PaymentAttempt::class)->latestOfMany();
    }

    /**
     * Bu siparişe ait tüm geri ödeme girişimleri.
     *
     * Order -> PaymentAttempt (order_id) -> RefundAttempt (payment_attempt_id)
     */
    public function refundAttempts(): HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Models\RefundAttempt::class,
            \App\Models\PaymentAttempt::class,
            'order_id',            // payment_attempts.order_id
            'payment_attempt_id',  // refund_attempts.payment_attempt_id
            'id',                  // orders.id
            'id'                   // payment_attempts.id
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

    /*
    |--------------------------------------------------------------------------
    | Refunds (Infolist için)
    |--------------------------------------------------------------------------
    */

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
                    'badge'  => $r->initiator_role ?? null,
                    'name'   => $r->initiator_name ?? null,
                    'reason' => $r->reason ?: null,
                    'amount' => number_format((float) $r->amount, 2, ',', '.') . ' ' . $currency,
                    'time'   => $r->created_at?->format('d.m.Y H:i') ?? null,
                ];
            })
            ->values()
            ->all();
    }

    /*
    |--------------------------------------------------------------------------
    | CUSTOMER ACCESSORS (Üye + Misafir için birleşik API)
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

    public function getIsGuestAttribute(): bool
    {
        return ! empty($this->metadata['guest']);
    }
}
