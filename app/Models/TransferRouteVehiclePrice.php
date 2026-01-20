<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransferRouteVehiclePrice extends Model
{
    use SoftDeletes;

    protected $table = 'transfer_route_vehicle_prices';

    protected $fillable = [
        'transfer_route_id',
        'transfer_vehicle_id',
        'prices',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'prices'     => 'array',
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function route(): BelongsTo
    {
        return $this->belongsTo(TransferRoute::class, 'transfer_route_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(TransferVehicle::class, 'transfer_vehicle_id');
    }
}
