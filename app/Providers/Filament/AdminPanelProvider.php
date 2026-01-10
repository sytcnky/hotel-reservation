<?php

namespace App\Providers\Filament;

use App\Http\Middleware\SetLocaleAdminPanel;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
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
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')

            // User menu: Panel locale switch (single item; always shows the other language)
            ->userMenuItems([
                'panel_locale_switch' => Action::make('panel_locale_switch')
                    ->label(function (): string {
                        $current = (string) session('panel_locale', 'en');
                        $current = in_array($current, ['tr', 'en'], true) ? $current : 'en';

                        $target = $current === 'tr' ? 'en' : 'tr';

                        return $target === 'tr'
                            ? 'Türkçe'
                            : 'English';
                    })
                    ->icon('heroicon-o-language')
                    ->url(function (): string {
                        $current = (string) session('panel_locale', 'en');
                        $current = in_array($current, ['tr', 'en'], true) ? $current : 'en';

                        $target = $current === 'tr' ? 'en' : 'tr';

                        // Relative current path + query (safe for redirect check)
                        $redirect = request()->getRequestUri();

                        return route('admin.panel-locale.set', ['locale' => $target]) . '?redirect=' . urlencode($redirect);
                    })
            ])

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

                SetLocaleAdminPanel::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                NavigationGroup::make()
                    ->label(fn (): string => ('Ürünler'))
                    ->icon('heroicon-o-rectangle-stack'),

                NavigationGroup::make()
                    ->label(fn (): string => __('admin.nav.order_group'))
                    ->icon('heroicon-o-rectangle-stack'),

                NavigationGroup::make()
                    ->label(fn (): string => __('admin.nav.sales_group'))
                    ->icon('heroicon-o-tag'),

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
                    ->label(fn (): string => __('admin.nav.content'))
                    ->icon('heroicon-o-rectangle-stack'),

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
