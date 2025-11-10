<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransferRoute extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'from_location_id','to_location_id',
        'duration_minutes','distance_km',
        'prices','is_active','sort_order',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'distance_km' => 'decimal:2',
        'prices' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function from() { return $this->belongsTo(\App\Models\Location::class, 'from_location_id'); }
    public function to()   { return $this->belongsTo(\App\Models\Location::class, 'to_location_id'); }
}
