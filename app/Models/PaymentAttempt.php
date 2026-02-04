<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentAttempt extends Model
{
    use SoftDeletes;

    public const STATUS_PENDING     = 'pending';
    public const STATUS_PENDING_3DS = 'pending_3ds';
    public const STATUS_SUCCESS     = 'success';
    public const STATUS_FAILED      = 'failed';
    public const STATUS_CANCELLED   = 'cancelled';
    public const STATUS_EXPIRED     = 'expired';

    protected $fillable = [
        'checkout_session_id',
        'order_id',

        'amount',
        'currency',

        'gateway',
        'gateway_reference',

        'status',

        'idempotency_key',

        'meta',
        'ip_address',
        'user_agent',

        'started_at',
        'completed_at',

        'raw_request',
        'raw_response',

        'error_code',
        'error_message',
    ];

    protected $casts = [
        'amount'       => 'float',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];


    /*
     |--------------------------------------------------------------------------
     | Raw payload policy (prod: kapalı)
     |--------------------------------------------------------------------------
     |
     | Prod ortamında raw_request/raw_response kesinlikle DB'ye yazılmaz.
     | Non-prod ortamında sanitize edilerek JSON (array) olarak yazılır.
     */

    public function setRawRequestAttribute($value): void
    {
        $this->attributes['raw_request'] = $this->normalizeRawPayload($value);
    }

    public function setRawResponseAttribute($value): void
    {
        $this->attributes['raw_response'] = $this->normalizeRawPayload($value);
    }

    /**
     * DB jsonb beklediği için array döndürür.
     * Eloquent JSON cast array'i otomatik serialize eder.
     */
    private function normalizeRawPayload($value): ?array
    {
        // PROD: kesin kapalı
        if (app()->isProduction()) {
            return null;
        }

        if ($value === null) {
            return null;
        }

        // Bazı yerlerde string gelebilir; non-prod'da normalize ediyoruz.
        if (is_string($value)) {
            $value = ['_raw' => $value];
        }

        if (! is_array($value)) {
            return null;
        }

        return $this->sanitizePayload($value);
    }

    private function sanitizePayload(array $data): array
    {
        $sensitiveKeys = [
            // card/PAN
            'pan', 'cardnumber', 'card_number', 'cardno', 'card_no', 'number',

            // cvv/cvc
            'cvv', 'cvc', 'cvv2',

            // expiry
            'exp', 'expiry', 'expires', 'exp_month', 'exp-year', 'exp_year', 'exp-month',

            // tokens/signatures that may be replayable
            'token', 'signature', 'auth', 'authorization',

            // PII (use specific keys, not generic "name")
            'email', 'phone', 'customer_email', 'customer_phone',
            'customer_name', 'full_name', 'cardholder', 'card_holder',
        ];

        $walk = function ($v) use (&$walk, $sensitiveKeys) {
            if (is_array($v)) {
                $out = [];
                foreach ($v as $k => $vv) {
                    $key = is_string($k) ? strtolower($k) : $k;

                    if (is_string($key) && in_array($key, $sensitiveKeys, true)) {
                        $out[$k] = '[redacted]';
                        continue;
                    }

                    $out[$k] = $walk($vv);
                }
                return $out;
            }

            if (is_string($v)) {
                // Sadece tamamı digit olan uzun stringleri maskele (PAN/log sızıntısı)
                $digitsOnly = preg_replace('/\D+/', '', $v);
                if ($digitsOnly !== '' && $digitsOnly === $v && strlen($digitsOnly) >= 12) {
                    $last4 = substr($digitsOnly, -4);
                    return '[masked:****' . $last4 . ']';
                }
            }

            return $v;
        };

        return $walk($data);
    }



    /*
     |--------------------------------------------------------------------------
     | Relations
     |--------------------------------------------------------------------------
     */

    public function checkoutSession()
    {
        return $this->belongsTo(\App\Models\CheckoutSession::class);
    }

    public function order()
    {
        return $this->belongsTo(\App\Models\Order::class);
    }

    public function refundAttempts()
    {
        return $this->hasMany(\App\Models\RefundAttempt::class);
    }

    /*
     |--------------------------------------------------------------------------
     | Status helpers (single source of truth for admin UI)
     |--------------------------------------------------------------------------
     */

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING     => __('admin.payment_attempts.status.pending'),
            self::STATUS_PENDING_3DS => __('admin.payment_attempts.status.pending_3ds'),
            self::STATUS_SUCCESS     => __('admin.payment_attempts.status.success'),
            self::STATUS_FAILED      => __('admin.payment_attempts.status.failed'),
            self::STATUS_CANCELLED   => __('admin.payment_attempts.status.cancelled'),
            self::STATUS_EXPIRED     => __('admin.payment_attempts.status.expired'),
        ];
    }

    public static function labelForStatus(?string $status): string
    {
        $status = (string) $status;

        return self::statusOptions()[$status] ?? $status;
    }

    public static function colorForStatus(?string $status): string
    {
        return match ((string) $status) {
            self::STATUS_SUCCESS     => 'success',
            self::STATUS_FAILED      => 'danger',
            self::STATUS_CANCELLED   => 'danger',
            self::STATUS_PENDING,
            self::STATUS_PENDING_3DS => 'warning',
            self::STATUS_EXPIRED     => 'gray',
            default                  => 'gray',
        };
    }
}
