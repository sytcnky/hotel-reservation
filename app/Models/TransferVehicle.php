<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class TransferVehicle extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'name', 'description',
        'capacity_total', 'capacity_adult_max', 'capacity_child_max', 'capacity_infant_max',
        'infants_count_towards_total',
        'is_active', 'sort_order',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'capacity_total' => 'integer',
        'capacity_adult_max' => 'integer',
        'capacity_child_max' => 'integer',
        'capacity_infant_max' => 'integer',
        'infants_count_towards_total' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
