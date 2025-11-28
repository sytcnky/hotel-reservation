<?php

namespace App\Providers\Filament;

use App\Http\Middleware\SetLocaleFromUser;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\NavigationGroup;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('ICR Travel')
            ->login()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                SetLocaleFromUser::class, // kullanıcı bazlı dil
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                NavigationGroup::make()
                    ->label(fn (): string => __('admin.nav.order_group'))
                    ->icon('heroicon-o-rectangle-stack'),
                NavigationGroup::make()
                    ->label(fn (): string => __('admin.nav.hotel_group'))
                    ->icon('heroicon-o-building-office'),
                NavigationGroup::make()
                    ->label(fn (): string => __('admin.nav.villa_group'))
                    ->icon('heroicon-o-home-modern'),
                NavigationGroup::make()
                    ->label(fn (): string => __('admin.nav.transfer_group'))
                    ->icon('heroicon-o-map-pin'),

                NavigationGroup::make()
                    ->label(fn (): string => __('admin.nav.tour_group'))
                    ->icon('heroicon-o-map'),

                NavigationGroup::make()
                    ->label(fn (): string => __('admin.nav.taxonomies'))
                    ->icon('heroicon-o-rectangle-stack'),

                NavigationGroup::make()
                    ->label(fn (): string => __('admin.nav.settings_group'))
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsed(),
            ]);
    }
}
