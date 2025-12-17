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

    protected $guarded = [];

    protected $casts = [
        'amount'       => 'float',
        'raw_request'  => 'array',
        'raw_response' => 'array',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

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
