<?php

namespace App\Models;

use App\Support\Helpers\ImageHelper;
use App\Support\Helpers\MediaConversions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Villa extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'slug',
        'canonical_slug',
        'description',
        'highlights',
        'stay_info',
        'max_guests',
        'bedroom_count',
        'bathroom_count',
        'villa_category_id',
        'cancellation_policy_id',
        'location_id',
        'address_line',
        'latitude',
        'longitude',
        'nearby',
        'phone',
        'email',
        'promo_video_id',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'name'              => 'array',
        'slug'              => 'array',
        'description'       => 'array',
        'highlights'        => 'array',
        'stay_info'         => 'array',
        'nearby'            => 'array',
        'is_active'         => 'boolean',
        'latitude'          => 'decimal:7',
        'longitude'         => 'decimal:7',
        'max_guests'        => 'integer',
        'bedroom_count'     => 'integer',
        'bathroom_count'    => 'integer',
        'promo_video_id'    => 'string',
    ];

    /*
    |--------------------------------------------------------------------------
    | Accessors (localized name)
    |--------------------------------------------------------------------------
    */

    public function getNameLAttribute(): ?string
    {
        $raw = $this->getAttribute('name');

        if ($raw === null) {
            return null;
        }

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $raw = $decoded;
            } else {
                return $raw;
            }
        }

        if (! is_array($raw)) {
            return (string) $raw;
        }

        $loc  = app()->getLocale();
        $base = config('app.locale', 'tr');

        return $raw[$loc] ?? $raw[$base] ?? reset($raw) ?: null;
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function category(): BelongsTo
    {
        return $this->belongsTo(VillaCategory::class, 'villa_category_id');
    }

    public function cancellationPolicy(): BelongsTo
    {
        return $this->belongsTo(CancellationPolicy::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function featureGroups(): HasMany
    {
        return $this->hasMany(VillaFeatureGroup::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Slug Mutator
    |--------------------------------------------------------------------------
    */

    protected function setSlugAttribute($value): void
    {
        $this->attributes['slug'] = is_array($value) ? json_encode($value) : $value;

        $base = config('app.locale', 'tr');

        $arr = is_array($value)
            ? $value
            : json_decode($this->attributes['slug'] ?? '[]', true);

        $name    = $this->attributes['name'] ?? '{}';
        $nameArr = is_array($name) ? $name : json_decode($name, true);

        $this->attributes['canonical_slug'] = Str::slug(
            $arr[$base]
            ?? ($arr[array_key_first($arr) ?? ''] ?? ($nameArr[$base] ?? 'villa'))
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Media Collections
    |--------------------------------------------------------------------------
    */

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
            ->map(fn (Media $m) => ImageHelper::normalize($m))
            ->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | Model Events
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function (Villa $villa) {
            // Kod
            if (empty($villa->code)) {
                $nextId     = (int) static::max('id') + 1;
                $villa->code = 'VIL-' . str_pad((string) $nextId, 6, '0', STR_PAD_LEFT);
            }

            // canonical_slug
            $base = config('app.locale', 'tr');
            $name = (array) ($villa->name ?? []);
            $slug = (array) ($villa->slug ?? []);

            $villa->canonical_slug = Str::slug(
                $slug[$base]
                ?? ($slug[array_key_first($slug) ?? ''] ?? ($name[$base] ?? 'villa'))
            );
        });

        static::saving(function (Villa $villa) {
            $base = config('app.locale', 'tr');
            $name = (array) ($villa->name ?? []);
            $slug = (array) ($villa->slug ?? []);

            if (empty($villa->canonical_slug)) {
                $villa->canonical_slug = Str::slug(
                    $slug[$base]
                    ?? ($slug[array_key_first($slug) ?? ''] ?? ($name[$base] ?? 'villa'))
                );
            }
        });
    }
}
