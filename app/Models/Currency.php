<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model
{
    use HasFactory, HasLocalizedColumns, SoftDeletes;

    protected $fillable = ['name', 'slug', 'description', 'code', 'symbol', 'exponent', 'is_active', 'sort_order'];

    protected $casts = [
        'name' => 'array',
        'slug' => 'array',
        'description' => 'array',
        'is_active' => 'boolean',
        'exponent' => 'integer',
    ];

    protected $appends = ['name_l', 'slug_l'];

    public function getNameLAttribute(): string
    {
        return $this->getLocalized('name');
    }

    public function getSlugLAttribute(): string
    {
        return $this->getLocalized('slug');
    }
}
