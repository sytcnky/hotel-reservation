<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'route_id',
        'vehicle_id',
        'direction',
        'from_location_id',
        'to_location_id',
        'departure_date',
        'return_date',
        'pickup_time_outbound',
        'flight_number_outbound',
        'pickup_time_return',
        'flight_number_return',
        'price_total',
        'currency',
        'snapshot',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'return_date'    => 'date',
        'price_total'    => 'decimal:2',
        'snapshot'       => 'array',
    ];

    // İlişkiler
    public function item()
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    // İleride gerekirse açarız:
    // public function route() { return $this->belongsTo(TransferRoute::class, 'route_id'); }
    // public function vehicle() { return $this->belongsTo(TransferVehicle::class, 'vehicle_id'); }
}
