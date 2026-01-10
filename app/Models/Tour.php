<?php

namespace App\Models;

use App\Support\Helpers\ImageHelper;
use App\Support\Helpers\MediaConversions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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
                    $n = ((int) $m[1]) + 1;
                }

                $tour->code = sprintf('TUR-%06d', $n);
            }
        });
    }

    protected $casts = [
        'name'                 => 'array',
        'slug'                 => 'array',
        'short_description'    => 'array',
        'long_description'     => 'array',
        'notes'                => 'array',
        'prices'               => 'array',
        'days_of_week'         => 'array',
        'included_service_ids' => 'array',
        'excluded_service_ids' => 'array',
        'is_active'            => 'boolean',
        'start_time'           => 'datetime:H:i',
    ];

    public function category()
    {
        return $this->belongsTo(\App\Models\TourCategory::class, 'tour_category_id');
    }

    public function registerMediaCollections(): void
    {
        // Kapak (tek dosya)
        $this
            ->addMediaCollection('cover')
            ->singleFile()
            ->useDisk(config('media-library.disk_name'))
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
            ]);

        // Galeri (çoklu)
        $this
            ->addMediaCollection('gallery')
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
        MediaConversions::apply($this, 'cover');
        MediaConversions::apply($this, 'gallery');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors (image helpers)
    |--------------------------------------------------------------------------
    */

    public function getCoverImageAttribute(): array
    {
        $media = $this->getFirstMedia('cover');

        return ImageHelper::normalize($media);
    }

    public function getGalleryImagesAttribute(): array
    {
        return $this->getMedia('gallery')
            ->map(fn (Media $media) => ImageHelper::normalize($media))
            ->toArray();
    }

}
