<?php

namespace App\Providers;

use Filament\Support\Facades\FilamentTimezone;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Gerekirse container bind/singleton kayıtları
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Filament bileşenleri (DateTimePicker, TextColumn vs.) için varsayılan timezone
        FilamentTimezone::set('Europe/Istanbul');
    }
}
