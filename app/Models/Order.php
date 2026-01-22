<?php

namespace App\Models;

use App\Support\Currency\CurrencyPresenter;
use App\Support\Date\DatePresenter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    public const STATUS_PENDING   = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';

    protected $table = 'orders';

    protected $fillable = [
        'code',

        'user_id',

        'status',
        'payment_status',

        'currency',
        'total_amount',
        'discount_amount',

        'coupon_code',
        'coupon_snapshot',

        'customer_name',
        'customer_email',
        'customer_phone',

        'billing_address',
        'metadata',

        'payment_expires_at',
        'locale',
    ];

    protected $casts = [
        'total_amount'       => 'float',
        'discount_amount'    => 'float',
        'billing_address'    => 'array',
        'metadata'           => 'array',
        'coupon_snapshot'    => 'array',
        'paid_at'            => 'datetime',
        'payment_expires_at' => 'datetime',
        'cancelled_at'       => 'datetime',
        'approved_at'        => 'datetime',
        'completed_at'       => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Status transitions (tek kaynak)
    |--------------------------------------------------------------------------
    */
    public function canApprove(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function canCancel(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED], true);
    }

    public function approve(?int $adminUserId, ?Carbon $now = null): void
    {
        if (! $this->canApprove()) {
            throw new \DomainException('Bu sipariş onaylanamaz.');
        }

        // Sprint D: instant date tek otorite (timezone app config’ten gelir)
        $now = $now ?: now();

        $this->status = self::STATUS_CONFIRMED;
        $this->approved_at = $now;
        $this->approved_by = $adminUserId;
    }

    public function cancel(?int $adminUserId, string $reason, ?Carbon $now = null): void
    {
        if (! $this->canCancel()) {
            throw new \DomainException('Bu sipariş iptal edilemez.');
        }

        $reason = trim($reason);
        if ($reason === '') {
            throw new \DomainException('İptal gerekçesi zorunludur.');
        }

        // Sprint D: instant date tek otorite (timezone app config’ten gelir)
        $now = $now ?: now();

        $this->status = self::STATUS_CANCELLED;
        $this->cancelled_at = $now;
        $this->cancelled_by = $adminUserId;
        $this->cancelled_reason = $reason;
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'cancelled_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(\App\Models\OrderItem::class);
    }

    public function paymentAttempts(): HasMany
    {
        return $this->hasMany(\App\Models\PaymentAttempt::class);
    }

    public function successfulPaymentAttempt(): ?\App\Models\PaymentAttempt
    {
        return $this->paymentAttempts()
            ->where('status', \App\Models\PaymentAttempt::STATUS_SUCCESS)
            ->orderByDesc('id')
            ->first();
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

    public function supportTickets(): HasMany
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
    | Infolist Accessors (mevcut)
    |--------------------------------------------------------------------------
    */
    public function getItemsForInfolistAttribute(): array
    {
        return $this->items
            ->map(function (\App\Models\OrderItem $item): array {
                $s        = (array) ($item->snapshot ?? []);
                $type     = $item->product_type;
                $currency = strtoupper($item->currency ?? $this->currency ?? '');

                $image = $this->resolveItemImageFromSnapshot($s);

                $formatMoney = function ($amount) use ($currency): ?string {
                    if ($amount === null) {
                        return null;
                    }

                    return CurrencyPresenter::formatAdmin($amount, $currency);
                };

                // Sprint D kapsamı: bu accessor snapshot tarihlerini "standardize etmez".
                // Sadece datetime parse gerekiyorsa Carbon::parse yerine DatePresenter tek otorite kullanılır.
                // Parse edilemezse ham değer korunur (UI kırılmaz).
                $formatDateTimeFromParts = function (?string $date, ?string $time = null): ?string {
                    if (blank($date)) {
                        return null;
                    }

                    $date = trim((string) $date);
                    if ($date === '') {
                        return null;
                    }

                    // 🔒 KRİTİK KURAL
                    // Saat YOKSA → sadece tarih göster (00:00 üretme)
                    if (blank($time)) {
                        $outDate = DatePresenter::human(
                            ymd: (string) $date,
                            pattern: 'd.m.Y'
                        );
                        return $outDate !== '' ? $outDate : $date;
                    }

                    $time  = trim((string) $time);
                    $value = trim($date . ' ' . $time);

                    if ($value === '') {
                        return null;
                    }

                    $out = DatePresenter::humanDateTimeFromString($value, null, 'd.m.Y H:i');

                    return $out !== '' ? $out : $value;
                };


                $formatDateTimeFromString = function (?string $value): ?string {
                    if (blank($value)) {
                        return null;
                    }

                    $value = trim((string) $value);
                    if ($value === '') {
                        return null;
                    }

                    $out = DatePresenter::humanDateTimeFromString($value, null, 'd.m.Y H:i');

                    return $out !== '' ? $out : $value;
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

                            // Sprint D: civil date alanları değiştirilmez (ham taşınır)
                            'checkin'    => $s['checkin']  ?? null,
                            'checkout'   => $s['checkout'] ?? null,

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

                            // Sprint D: civil date alanları değiştirilmez (ham taşınır)
                            'checkin'    => $s['checkin']  ?? null,
                            'checkout'   => $s['checkout'] ?? null,

                            'paid'       => $pre       !== null ? $formatMoney($pre)       . $rateText : null,
                            'remaining'  => $remaining !== null ? $formatMoney($remaining) : null,
                            'total'      => $total     !== null ? $formatMoney($total)     : null,
                        ];
                }

                if ($type === 'tour' || $type === 'excursion') {
                    $tourDate = $s['date'] ?? null;

                    $dateOut = null;

                    if (! blank($tourDate)) {
                        $dateOut = DatePresenter::human(
                            ymd: (string) $tourDate,
                            pattern: 'd.m.Y'
                        );

                        $dateOut = $dateOut !== '' ? $dateOut : (string) $tourDate;
                    }

                    return $base + [
                            'tour_name' => $s['tour_name'] ?? $s['title'] ?? null,
                            'date'      => $dateOut,
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

                    $departureDate = $formatDateTimeFromParts(
                        $s['departure_date']       ?? null,
                        $s['pickup_time_outbound'] ?? null,
                    );

                    $returnDate = $formatDateTimeFromParts(
                        $s['return_date']        ?? null,
                        $s['pickup_time_return'] ?? null,
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

    protected function resolveItemImageFromSnapshot(array $s): ?array
    {
        foreach (['hotel_image', 'villa_image', 'vehicle_cover', 'cover_image'] as $key) {
            if (! array_key_exists($key, $s)) {
                continue;
            }

            $value = $s[$key];

            // Yeni kontrat: snapshot zaten UI-ready image array
            if (is_array($value)) {
                return $value;
            }
        }

        return null;
    }

    public function getDiscountsForInfolistAttribute(): array
    {
        $rows = (array) ($this->coupon_snapshot ?? []);

        return collect($rows)
            ->map(function ($row) {
                $amount = (float) ($row['discount'] ?? 0);
                if ($amount <= 0) {
                    return null;
                }

                $amountFormatted = \App\Support\Currency\CurrencyPresenter::format(
                    $row['discount'] ?? null,
                    $this->currency ?? null
                );

                $title = trim((string) ($row['title'] ?? ''));
                $code  = trim((string) ($row['code'] ?? ''));

                // Alt başlık (snapshot’ta hangisi varsa)
                $subtitle =
                    trim((string) ($row['subtitle'] ?? '')) ?:
                        trim((string) ($row['campaign_subtitle'] ?? '')) ?:
                            trim((string) ($row['sub_title'] ?? '')) ?:
                                trim((string) ($row['context'] ?? ''));

                // Hiç başlık yoksa satırı tamamen düşür
                $labelBase = $title !== '' ? $title : $code;
                if ($labelBase === '') {
                    return null;
                }

                $label = $subtitle !== '' ? trim($labelBase . ' ' . $subtitle) : $labelBase;

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
            ->filter()   // null olanlar tamamen gider
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
                $name = trim((string) ($r->initiator_name ?? ''));
                $role = trim((string) ($r->initiator_role ?? ''));

                return [
                    'name'   => $name !== '' ? $name : '-',
                    'badge'  => $role !== '' ? $role : '-',
                    'reason' => $r->reason ?: null,

                    // Sprint D: instant date tek otorite (->format yerine presenter)
                    'time'   => $r->created_at
                        ? DatePresenter::humanDateTimeShort($r->created_at)
                        : null,

                    'amount' => CurrencyPresenter::formatAdmin($r->amount, $this->currency ?? null),
                ];
            })
            ->values()
            ->all();
    }

    public function getCustomerTypeAttribute(): string
    {
        return $this->user_id ? 'member' : 'guest';
    }

    /*
    |--------------------------------------------------------------------------
    | Status meta (tek kaynak)
    |--------------------------------------------------------------------------
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

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_CANCELLED,
        self::STATUS_COMPLETED,
    ];

    public static function statusList(): array
    {
        return self::STATUSES;
    }
}
