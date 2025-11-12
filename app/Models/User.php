<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasRoles;
    use Notifiable;
    use LogsActivity;
    use SoftDeletes;

    protected $guard_name = 'web';

    /**
     * Doldurulabilir alanlar.
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'locale',
        'currency',
        'phone',
    ];

    /**
     * Gizli alanlar.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Castler.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'locale'            => 'string',
        ];
    }

    /**
     * name accessor:
     * - Önce first_name + last_name
     * - Yoksa email
     */
    public function getNameAttribute(): string
    {
        $parts = array_filter([
            $this->first_name,
            $this->last_name,
        ]);

        if (! empty($parts)) {
            return implode(' ', $parts);
        }

        return (string) $this->email;
    }

    /**
     * Filament erişim kuralı.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(['admin', 'editor', 'ops']);
    }

    /**
     * Activitylog ayarları.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('user')
            ->logOnly([
                'first_name',
                'last_name',
                'email',
                'phone',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
