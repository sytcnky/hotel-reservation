<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportTicketCategory extends Model
{
    use HasFactory, HasLocalizedColumns, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'requires_order',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'name' => 'array',
        'slug' => 'array',
        'description' => 'array',
        'requires_order' => 'boolean',
        'is_active' => 'boolean',
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

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(\Spatie\Permission\Models\Role::class, 'support_ticket_category_role');
    }
}
