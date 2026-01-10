<?php

namespace App\Models;

use App\Services\RoomRateResolver;
use App\Support\Helpers\ImageHelper;
use App\Support\Helpers\MediaConversions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Room extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'hotel_id',
        'name',
        'slug',
        'capacity_adults',
        'capacity_children',
        'size_m2',
        'smoking',
        'view_type_id',
        'description',
        'is_active',
        'sort_order',
        'child_discount_active',
        'child_discount_percent'
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'is_active' => 'boolean',
        'smoking' => 'boolean',
        'capacity_adults' => 'integer',
        'capacity_children' => 'integer',
        'size_m2' => 'integer',
        'child_discount_active' => 'boolean',
        'child_discount_percent' => 'float',

    ];

    protected $appends = ['name_l'];

    public function getNameLAttribute(): ?string
    {
        $v = $this->name;
        $loc = app()->getLocale();
        $base = config('app.locale', 'tr');

        if (is_array($v)) {
            return $v[$loc] ?? ($v[$base] ?? (array_values($v)[0] ?? null));
        }

        return is_string($v) ? $v : null;
    }

    public function resolveRate(string $date, int $currencyId, ?int $boardTypeId, int $occupancy, int $stayLength = 1): array
    {
        return app(RoomRateResolver::class)
            ->resolveDay($this, $date, $currencyId, $boardTypeId, $occupancy, $stayLength);
    }

    // --- Relationships ---

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function viewType(): BelongsTo
    {
        return $this->belongsTo(ViewType::class);
    }

    public function childPolicies()
    {
        return $this->hasMany(\App\Models\ChildPolicy::class);
    }

    public function facilities()
    {
        return $this->belongsToMany(RoomFacility::class, 'room_room_facility', 'room_id', 'room_facility_id')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function beds()
    {
        return $this->belongsToMany(BedType::class, 'room_bed', 'room_id', 'bed_type_id')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    // --- Fiyatlar ---
    public function rateRules()
    {
        return $this->hasMany(RoomRateRule::class);
    }

    public function registerMediaCollections(): void
    {
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
        MediaConversions::apply($this, 'gallery');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors (image helpers)
    |--------------------------------------------------------------------------
    */

    public function getGalleryImagesAttribute(): array
    {
        return $this->getMedia('gallery')
            ->map(fn($m) => ImageHelper::normalize($m))
            ->toArray();
    }
}
