<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Hotel extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'slug',
        'canonical_slug',
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
        'child_discount_percent'
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

    ];

    /**
     * @var mixed|string
     */
    private mixed $canonical_slug;

    /**
     * @var mixed|string
     */
    private mixed $code;

    private mixed $name;

    private static function max(string $string) {}

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

    public function childPolicies()
    {
        return $this->hasMany(\App\Models\ChildPolicy::class);
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

    protected function setSlugAttribute($value): void
    {
        $this->attributes['slug'] = is_array($value) ? json_encode($value) : $value;
        $base = config('app.locale', 'tr');
        $arr = is_array($value) ? $value : json_decode($this->attributes['slug'] ?? '[]', true);
        $name = $this->attributes['name'] ?? '{}';
        $nameArr = is_array($name) ? $name : json_decode($name, true);

        $this->attributes['canonical_slug'] =
            Str::slug($arr[$base] ?? ($arr[array_key_first($arr) ?? ''] ?? ($nameArr[$base] ?? 'otel')));
    }

    /*
    |--------------------------------------------------------------------------
    | Media Collections
    |--------------------------------------------------------------------------
    */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('gallery')->useFallbackUrl('/images/default.jpg');
    }

    /*
    |--------------------------------------------------------------------------
    | Model Events
    |--------------------------------------------------------------------------
    */
    protected static function booted(): void
    {
        static::creating(function (Hotel $hotel) {
            // Kod
            if (empty($hotel->code)) {
                $nextId = (int) static::max('id') + 1;
                $hotel->code = 'HTL-' . str_pad((string) $nextId, 6, '0', STR_PAD_LEFT);
            }
            // canonical_slug
            $base = config('app.locale', 'tr');
            $name = (array) ($hotel->name ?? []);
            $slug = (array) ($hotel->slug ?? []);
            $hotel->canonical_slug = Str::slug(
                $slug[$base] ?? ($slug[array_key_first($slug) ?? ''] ?? ($name[$base] ?? 'otel'))
            );
        });

        static::saving(function (Hotel $hotel) {
            // Boş/tekrarsız garanti
            $base = config('app.locale', 'tr');
            $name = (array) ($hotel->name ?? []);
            $slug = (array) ($hotel->slug ?? []);
            if (empty($hotel->canonical_slug)) {
                $hotel->canonical_slug = Str::slug(
                    $slug[$base] ?? ($slug[array_key_first($slug) ?? ''] ?? ($name[$base] ?? 'otel'))
                );
            }
        });
    }
}
