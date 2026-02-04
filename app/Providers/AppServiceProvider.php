<?php

namespace App\Providers;

use App\Models\Order;
use App\Observers\OrderObserver;
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
        /**
         * PROD SAFETY: Demo payment driver prod ortamında kesinlikle çalışmamalı.
         * - Yanlış env ile "demo ödeme"ye düşmeyi engeller.
         */
        if (app()->isProduction()) {
            $driver = (string) config('icr.payments.driver', '');

            if ($driver === '' || $driver === 'demo') {
                throw new \RuntimeException(
                    'PAYMENT_DRIVER misconfigured: production ortamında demo driver yasaktır. ' .
                    'Lütfen .env içinde PAYMENT_DRIVER değerini gerçek sağlayıcıya ayarlayın.'
                );
            }
        }

        // Filament bileşenleri (DateTimePicker, TextColumn vs.) için varsayılan timezone
        FilamentTimezone::set('Europe/Istanbul');

        // Order status mail tetikleri (approve/cancel)
        Order::observe(OrderObserver::class);
    }
}
