<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Tour extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $table = 'tours';
    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(function (Tour $tour) {
            if (blank($tour->code)) {
                $last = static::withTrashed()
                    ->where('code', 'LIKE', 'TUR-%')
                    ->orderByDesc('id')
                    ->value('code');

                $n = 1;
                if ($last && preg_match('/TUR-(\d+)/', $last, $m)) {
                    $n = (int)$m[1] + 1;
                }

                $tour->code = sprintf('TUR-%06d', $n);
            }
        });
    }

    protected $casts = [
        'name'                  => 'array',
        'slug'                  => 'array',
        'short_description'     => 'array',
        'long_description'      => 'array',
        'notes'                 => 'array',
        'prices'                => 'array',
        'days_of_week'          => 'array',
        'included_service_ids'  => 'array',
        'excluded_service_ids'  => 'array',
        'is_active'             => 'boolean',
        'start_time'            => 'datetime:H:i',
    ];

    public function category()
    {
        return $this->belongsTo(\App\Models\TourCategory::class, 'tour_category_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('gallery');
    }
}
