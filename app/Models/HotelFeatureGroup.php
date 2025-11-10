<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelFeatureGroup extends Model
{
    use HasFactory;

    protected $table = 'hotel_feature_groups';

    protected $fillable = [
        'hotel_id',
        'title',
        'sort_order',
    ];

    protected $casts = [
        'title' => 'array',   // jsonb: { tr: "...", en: "..." }
        'sort_order' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function facilities()
    {
        return $this->belongsToMany(
            \App\Models\Facility::class,
            'feature_group_facility',   // pivot tablo
            'feature_group_id',         // bu modelin FK'si (tablodaki ad)
            'facility_id'               // karşı FK
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
