<?php

namespace App\Models;

use App\Support\Helpers\ImageHelper;
use App\Support\Helpers\MediaConversions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TransferVehicle extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'name',
        'description',
        'capacity_total',
        'capacity_adult_max',
        'capacity_child_max',
        'capacity_infant_max',
        'infants_count_towards_total',
        'is_active',
        'sort_order',
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

    protected $appends = [
        'cover_image',
        'gallery_images',
    ];

    public function registerMediaCollections(): void
    {
        $disk = config('media-library.disk_name'); // = public

        $this
            ->addMediaCollection('cover')
            ->singleFile()
            ->useDisk($disk)
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
            ]);

        $this
            ->addMediaCollection('gallery')
            ->useDisk($disk)
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
            ]);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        MediaConversions::apply($this, 'cover');
        MediaConversions::apply($this, 'gallery');
    }

    /**
     * Normalize edilmiş kapak görseli (tek otorite).
     * Cover yoksa placeholder normalize(null) döner (controller karar vermez).
     */
    public function getCoverImageAttribute(): array
    {
        $media = $this->getFirstMedia('cover');

        return ImageHelper::normalize($media);
    }

    /**
     * Normalize edilmiş galeri görselleri.
     * Gallery boşsa boş array döner (fallback yok).
     */
    public function getGalleryImagesAttribute(): array
    {
        return $this->getMedia('gallery')
            ->map(fn (Media $media) => ImageHelper::normalize($media))
            ->toArray();
    }
}
