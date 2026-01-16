<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckoutSession extends Model
{
    public const TYPE_GUEST = 'guest';
    public const TYPE_USER  = 'user';

    public const STATUS_ACTIVE    = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_EXPIRED   = 'expired';
    public const STATUS_ABANDONED = 'abandoned';

    protected $fillable = [
        'code',
        'type',
        'user_id',
        'order_id',

        'customer_snapshot',

        'cart_total',
        'discount_amount',
        'currency',

        'status',

        'ip_address',
        'user_agent',

        'started_at',
        'completed_at',
        'expires_at',
    ];

    protected $casts = [
        'customer_snapshot' => 'array',

        'cart_total'        => 'float',
        'discount_amount'   => 'float',

        'started_at'        => 'datetime',
        'completed_at'      => 'datetime',
        'expires_at'        => 'datetime',
    ];

    /*
     |--------------------------------------------------------------------------
     | Relations
     |--------------------------------------------------------------------------
     */

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function order()
    {
        return $this->belongsTo(\App\Models\Order::class);
    }

    public function paymentAttempts()
    {
        return $this->hasMany(\App\Models\PaymentAttempt::class);
    }
}
