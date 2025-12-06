<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Campaign extends Model implements HasMedia
{
    use HasFactory;
    use SoftDeletes;
    use InteractsWithMedia;

    /**
     * Kitleme izin verilen alanlar.
     */
    protected $fillable = [
        'is_active',
        'start_date',
        'end_date',
        'priority',
        'global_usage_limit',
        'user_usage_limit',
        'usage_count',
        'visible_on_web',
        'visible_on_mobile',

        'content',
        'discount',
        'conditions',
        'placements',
    ];

    /**
     * Cast'ler.
     */
    protected $casts = [
        'is_active'          => 'bool',
        'start_date'         => 'date',
        'end_date'           => 'date',
        'priority'           => 'integer',
        'global_usage_limit' => 'integer',
        'user_usage_limit'   => 'integer',
        'usage_count'        => 'integer',

        'visible_on_web'     => 'bool',
        'visible_on_mobile'  => 'bool',

        'content'            => 'array', // locale => [title, subtitle, description, cta_text, cta_link]
        'discount'           => 'array', // ['type' => 'percent|fixed_amount', 'value' => ..., 'max_discount_amount' => ...]
        'conditions'         => 'array', // sepet/ürün/kullanıcı/tarih/cihaz koşulları
        'placements'         => 'array', // ['homepage_hero', 'listing_top', ...]
    ];

    /**
     * Medya koleksiyonları (Background / Transparent image).
     */
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('background_image')
            ->singleFile();

        $this
            ->addMediaCollection('transparent_image')
            ->singleFile();
    }
}
