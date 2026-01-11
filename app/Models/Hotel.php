<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedColumns;
use App\Support\Helpers\ImageHelper;
use App\Support\Helpers\MediaConversions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Hotel extends Model implements HasMedia
{
    use HasFactory, HasLocalizedColumns, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'slug',
        'description',
        'star_rating_id',
        'hotel_category_id',
        'board_type_id',
        'beach_type_id',
        'is_active',
        'sort_order',
        'location_id',
        'address_line',
        'latitude',
        'longitude',
        'nearby',
        'phone',
        'email',
        'policies',
        'notes',
        'cancellation_policy_id',
        'child_discount_active',
        'child_discount_percent',
        'promo_video_id',
    ];

    protected $casts = [
        'name' => 'array',
        'slug' => 'array',
        'description' => 'array',
        'policies' => 'array',
        'notes' => 'array',
        'is_active' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'nearby' => 'array',
        'child_discount_active' => 'boolean',
        'child_discount_percent' => 'float',
        'promo_video_id' => 'string',
    ];

    /**
     * Admin tablolarında kullanılan lokalize isim.
     * Fallback yok: sadece UI locale key'i okunur.
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
    public function starRating(): BelongsTo
    {
        return $this->belongsTo(StarRating::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(HotelCategory::class, 'hotel_category_id');
    }

    public function boardType(): BelongsTo
    {
        return $this->belongsTo(BoardType::class);
    }

    public function beachType(): BelongsTo
    {
        return $this->belongsTo(BeachType::class);
    }

    public function themes(): BelongsToMany
    {
        return $this->belongsToMany(HotelTheme::class, 'hotel_hotel_theme', 'hotel_id', 'hotel_theme_id');
    }

    public function paymentOptions(): BelongsToMany
    {
        return $this->belongsToMany(PaymentOption::class, 'hotel_payment_option', 'hotel_id', 'payment_option_id');
    }

    public function featureGroups(): HasMany
    {
        return $this->hasMany(HotelFeatureGroup::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function cancellationPolicy(): BelongsTo
    {
        return $this->belongsTo(CancellationPolicy::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Media Collections
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
            ->map(fn ($m) => ImageHelper::normalize($m))
            ->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | Model Events
    |--------------------------------------------------------------------------
    */
    protected static function booted(): void
    {
        static::creating(function (Hotel $hotel) {
            if (empty($hotel->code)) {
                $nextId = (int) static::max('id') + 1;
                $hotel->code = 'HTL-' . str_pad((string) $nextId, 6, '0', STR_PAD_LEFT);
            }
        });
    }
}
