<?php

namespace App\Models;

use App\Support\Services\SequenceService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'order_no',
        'user_id',
        'status',
        'currency',
        'fx_rate',
        'subtotal',
        'discount_total',
        'fee_total',
        'tax_total',
        'total',
        'payment_id',
        'payment_method_code',
        'channel',
        'note',
        'meta',
    ];

    protected $casts = [
        'fx_rate'        => 'decimal:6',
        'subtotal'       => 'decimal:2',
        'discount_total' => 'decimal:2',
        'fee_total'      => 'decimal:2',
        'tax_total'      => 'decimal:2',
        'total'          => 'decimal:2',
        'meta'           => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->order_no)) {
                $gen = SequenceService::next('order');
                $model->order_no = $gen['number'];
            }
        });
    }

    // İlişkiler
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
