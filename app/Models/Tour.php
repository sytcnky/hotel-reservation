<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedColumns;
use App\Support\Helpers\ImageHelper;
use App\Support\Helpers\MediaConversions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Tour extends Model implements HasMedia
{
    use HasLocalizedColumns, SoftDeletes, InteractsWithMedia;

    protected $table = 'tours';

    protected $guarded = [];

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

    /*
    |--------------------------------------------------------------------------
    | Model Events
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Accessors (localized label)
    |--------------------------------------------------------------------------
    */

    /**
     * Admin accessor
     * Kontrat: fallback YOK, sadece UI locale.
     */
    public function getNameLAttribute(): ?string
    {
        return $this->getLocalized('name');
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function category(): BelongsTo
    {
        return $this->belongsTo(TourCategory::class, 'tour_category_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Media
    |--------------------------------------------------------------------------
    */

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('cover')
            ->singleFile()
            ->useDisk(config('media-library.disk_name'))
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this
            ->addMediaCollection('gallery')
            ->useDisk(config('media-library.disk_name'))
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        MediaConversions::apply($this, 'cover');
        MediaConversions::apply($this, 'gallery');
    }

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
