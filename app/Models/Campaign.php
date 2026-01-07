<?php

namespace App\Models;

use App\Support\Helpers\ImageHelper;
use App\Support\Helpers\MediaConversions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Campaign extends Model implements HasMedia
{
    use HasFactory;
    use SoftDeletes;
    use InteractsWithMedia;

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
        'content'            => 'array',
        'discount'           => 'array',
        'conditions'         => 'array',
        'placements'         => 'array',
    ];

    public function registerMediaCollections(): void
    {
        $disk = config('media-library.disk_name');

        $this
            ->addMediaCollection('background_image')
            ->singleFile()
            ->useDisk($disk)
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
            ]);

        $this
            ->addMediaCollection('transparent_image')
            ->singleFile()
            ->useDisk($disk)
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
            ]);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        MediaConversions::apply($this, 'background_image');
        MediaConversions::apply($this, 'transparent_image');
    }

    public function getBackgroundImageAttribute(): array
    {
        return ImageHelper::normalize($this->getFirstMedia('background_image'));
    }

    public function getTransparentImageAttribute(): array
    {
        return ImageHelper::normalize($this->getFirstMedia('transparent_image'));
    }
}
