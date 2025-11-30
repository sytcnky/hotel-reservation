<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCoupon extends Model
{
    use HasFactory;

    // Laravel varsayılan olarak "user_coupons" tablosunu kullanır.
    // protected $table = 'user_coupons';

    protected $fillable = [
        'user_id',
        'coupon_id',

        'assigned_at',
        'expires_at',

        'used_count',
        'last_used_at',

        'source',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'expires_at'  => 'datetime',
        'last_used_at'=> 'datetime',

        'used_count'  => 'integer',
    ];

    /*
     |--------------------------------------------------------------------------
     | İlişkiler
     |--------------------------------------------------------------------------
     */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }
}
