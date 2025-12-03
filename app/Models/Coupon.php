<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    // İstersen burada açıkça belirtmeyebiliriz; Laravel otomatik "coupons" kullanır.
    // protected $table = 'coupons';

    protected $fillable = [
        'is_active',
        'code',

        'title',
        'description',
        'badge_label',

        'valid_from',
        'valid_until',

        'is_exclusive',
        'max_uses_per_user',

        'discount_type',
        'percent_value',

        'scope_type',
        'product_types',
        'product_domain',
        'product_id',

        'min_nights',

        'currency_data',
    ];

    protected $casts = [
        'is_active'          => 'bool',
        'is_exclusive'       => 'bool',

        'title'              => 'array',
        'description'        => 'array',
        'badge_label'        => 'array',

        'valid_from'         => 'datetime',
        'valid_until'        => 'datetime',

        'max_uses_per_user'  => 'integer',
        'percent_value'      => 'float',

        'product_types'      => 'array',
        'product_id'         => 'integer',
        'min_nights'         => 'integer',

        'currency_data'      => 'array',
    ];

    /*
     |--------------------------------------------------------------------------
     | İlişkiler
     |--------------------------------------------------------------------------
     */

    public function userCoupons()
    {
        return $this->hasMany(UserCoupon::class);
    }

    public function users()
    {
        // Eğer ileride doğrudan BelongsToMany kullanmak istersen hazır olsun.
        return $this->belongsToMany(User::class, 'user_coupons')
            ->using(UserCoupon::class)
            ->withPivot([
                'assigned_at',
                'expires_at',
                'used_count',
                'last_used_at',
                'source',
            ])
            ->withTimestamps();
    }
}
