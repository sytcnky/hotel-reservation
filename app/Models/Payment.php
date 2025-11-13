<?php

namespace App\Models;

use App\Support\Services\SequenceService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_no',
        'user_id',
        'order_id',
        'method_code',
        'provider',
        'provider_txn_id',
        'amount',
        'currency',
        'status',
        'response',
        'note',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'response'=> 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->payment_no)) {
                $gen = SequenceService::next('payment');
                $model->payment_no = $gen['number'];
            }
        });
    }

    // İlişkiler
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
