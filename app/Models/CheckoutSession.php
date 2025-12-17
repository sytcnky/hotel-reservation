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

    protected $guarded = [];

    protected $casts = [
        'customer_snapshot' => 'array',
        'cart_total'        => 'float',
        'discount_amount'   => 'float',
        'started_at'        => 'datetime',
        'completed_at'      => 'datetime',
    ];

    /*
     |--------------------------------------------------------------------------
     | Relations
     |--------------------------------------------------------------------------
     */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentAttempts()
    {
        return $this->hasMany(PaymentAttempt::class);
    }
}
