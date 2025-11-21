<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VillaFeatureGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'villa_id',
        'title',
        'sort_order',
    ];

    protected $casts = [
        'title' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function villa(): BelongsTo
    {
        return $this->belongsTo(Villa::class);
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(
            VillaAmenity::class,
            'villa_amenity_villa_feature_group',
            'villa_feature_group_id',
            'villa_amenity_id'
        );
    }
}
