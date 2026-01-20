<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedColumns;
use App\Support\Helpers\ImageHelper;
use App\Support\Helpers\MediaConversions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TransferVehicle extends Model implements HasMedia
{
    use HasLocalizedColumns, SoftDeletes, InteractsWithMedia;

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
        'name'                        => 'array',
        'description'                 => 'array',
        'capacity_total'              => 'integer',
        'capacity_adult_max'          => 'integer',
        'capacity_child_max'          => 'integer',
        'capacity_infant_max'         => 'integer',
        'infants_count_towards_total' => 'boolean',
        'is_active'                   => 'boolean',
        'sort_order'                  => 'integer',
    ];

    protected $appends = [
        'cover_image',
        'gallery_images',
    ];

    /*
    |--------------------------------------------------------------------------
    | Admin localized accessor (NO fallback)
    |--------------------------------------------------------------------------
    */
    public function getNameLAttribute(): ?string
    {
        return $this->getLocalized('name');
    }

    /*
    |--------------------------------------------------------------------------
    | Route bazlı fiyat kayıtları (yeni sistem)
    |--------------------------------------------------------------------------
    */
    public function routePrices(): HasMany
    {
        return $this->hasMany(TransferRouteVehiclePrice::class, 'transfer_vehicle_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Media Collections
    |--------------------------------------------------------------------------
    */
    public function registerMediaCollections(): void
    {
        $disk = config('media-library.disk_name');

        $this
            ->addMediaCollection('cover')
            ->singleFile()
            ->useDisk($disk)
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this
            ->addMediaCollection('gallery')
            ->useDisk($disk)
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        MediaConversions::apply($this, 'cover');
        MediaConversions::apply($this, 'gallery');
    }

    /*
    |--------------------------------------------------------------------------
    | Image accessors (policy-compliant)
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
