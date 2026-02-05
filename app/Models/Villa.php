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

class Villa extends Model implements HasMedia
{
    use HasFactory, HasLocalizedColumns, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'slug',
        'description',
        'highlights',
        'stay_info',
        'max_guests',
        'bedroom_count',
        'bathroom_count',
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
        'prepayment_rate',
    ];

    protected $casts = [
        'name'            => 'array',
        'slug'            => 'array',
        'description'     => 'array',
        'highlights'      => 'array',
        'stay_info'       => 'array',
        'nearby'          => 'array',
        'is_active'       => 'boolean',
        'latitude'        => 'decimal:7',
        'longitude'       => 'decimal:7',
        'max_guests'      => 'integer',
        'bedroom_count'   => 'integer',
        'bathroom_count'  => 'integer',
        'promo_video_id'  => 'string',
        'prepayment_rate' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Accessors (localized)
    |--------------------------------------------------------------------------
    */

    public function getNameLAttribute(): ?string
    {
        return $this->getLocalized('name');
    }

    /**
     * Legacy uyumluluk: daha önce tek kategori adı bekleyen yerler için.
     * Artık "ilk kategori adı" döner.
     */
    public function getCategoryNameAttribute(): ?string
    {
        $first = $this->categories()->first();
        return $first?->name_l;
    }

    /**
     * Çoklu kullanım için: kategori adları listesi (UI locale).
     */
    public function getCategoryNamesAttribute(): array
    {
        return $this->categories
            ->map(fn (VillaCategory $c) => (string) ($c->name_l ?? ''))
            ->filter(fn (string $v) => trim($v) !== '')
            ->values()
            ->all();
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            VillaCategory::class,
            'villa_category_villa',
            'villa_id',
            'villa_category_id'
        )->withTimestamps();
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

    public function rateRules(): HasMany
    {
        return $this->hasMany(VillaRateRule::class);
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
            ->map(fn (Media $m) => ImageHelper::normalize($m))
            ->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | Basit fiyat çözücü (listeleme için en düşük aktif kural)
    |--------------------------------------------------------------------------
    */

    public function getBasePrice(string $currencyCode): ?float
    {
        $currencyCode = strtoupper($currencyCode);

        $rule = $this->rateRules()
            ->whereHas('currency', function ($q) use ($currencyCode) {
                $q->whereRaw('upper(code) = ?', [$currencyCode]);
            })
            ->where(function ($q) {
                $q->where('is_active', true)->orWhereNull('is_active');
            })
            ->where(function ($q) {
                $q->where('closed', false)->orWhereNull('closed');
            })
            ->orderBy('priority', 'asc')
            ->orderBy('date_start', 'asc')
            ->first();

        return $rule && $rule->amount !== null
            ? (float) $rule->amount
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Model Events
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function (Villa $villa) {
            if (empty($villa->code)) {
                $nextId = (int) static::max('id') + 1;
                $villa->code = 'VIL-' . str_pad((string) $nextId, 6, '0', STR_PAD_LEFT);
            }
        });
    }
}
