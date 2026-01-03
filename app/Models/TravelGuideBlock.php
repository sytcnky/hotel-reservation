<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Support\Helpers\MediaConversions;
use App\Support\Helpers\ImageHelper;

class TravelGuideBlock extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $table = 'travel_guide_blocks';

    protected $fillable = [
        'travel_guide_id',
        'type',
        'sort_order',
        'data',
    ];

    protected $casts = [
        'data'       => 'array',
        'sort_order' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function travelGuide(): BelongsTo
    {
        return $this->belongsTo(TravelGuide::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Media Collections
    |--------------------------------------------------------------------------
    */

    public function registerMediaCollections(): void
    {
        // Blok görseli (tek dosya)
        $this
            ->addMediaCollection('image')
            ->singleFile()
            ->useDisk(config('media-library.disk_name'))
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
            ]);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        // Standart ICR dönüşümleri
        MediaConversions::apply($this, 'image');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors (image helpers)
    |--------------------------------------------------------------------------
    */

    public function getImageAssetAttribute(): array
    {
        $media = $this->getFirstMedia('image');

        return ImageHelper::normalize($media);
    }
}
