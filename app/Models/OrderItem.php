<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_type',
        'product_id',
        'title_snapshot',
        'date_snapshot',
        'guest_snapshot',
        'amount',
        'currency',
        'meta',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'date_snapshot' => 'array',
        'guest_snapshot'=> 'array',
        'meta'          => 'array',
    ];

    // İlişkiler
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function transferBooking()
    {
        return $this->hasOne(TransferBooking::class);
    }
}
