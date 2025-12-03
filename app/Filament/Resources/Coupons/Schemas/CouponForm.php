<?php

namespace App\Filament\Resources\Coupons\Schemas;

use App\Models\Currency;
use App\Models\Hotel;
use App\Models\Tour;
use App\Models\TransferRoute;
use App\Models\Villa;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Session;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        $base    = config('app.locale', 'tr');
        $locales = config('app.supported_locales', [$base]);
        $ui      = app()->getLocale();

        $currencyCodes = Currency::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('code')
            ->pluck('code')
            ->all();

        if (empty($currencyCodes)) {
            $currencyCodes = config('app.supported_currencies', []);
        }

        return $schema->components([

            Group::make()->columnSpanFull()->schema([

                Grid::make()
                    ->columns(['default' => 1, 'lg' => 12])
                    ->gap(6)
                    ->schema([

                        // -----------------------------------------------------
                        // SOL (8 kolon) — İçerik + Para birimi
                        // -----------------------------------------------------
                        Group::make()
                            ->columnSpan(['default' => 12, 'lg' => 8])
                            ->schema([

                                // Çok dilli içerik tabları
                                Tabs::make('i18n')->tabs(
                                    collect($locales)->map(function (string $loc) use ($base) {
                                        $isBase = ($loc === $base);

                                        return Tab::make(strtoupper($loc))->schema([
                                            TextInput::make("title.$loc")
                                                ->label(__('admin.coupons.form.title'))
                                                ->required($isBase),

                                            Textarea::make("description.$loc")
                                                ->label(__('admin.coupons.form.description'))
                                                ->rows(3),

                                            TextInput::make("badge_label.$loc")
                                                ->label(__('admin.coupons.form.badge_label'))
                                                ->helperText(__('admin.coupons.form.badge_label_help')),
                                        ]);
                                    })->all()
                                ),

                                // Para Birimi Tabs (currency_data JSON)
                                Section::make(__('admin.coupons.sections.currencies'))->schema([
                                    Select::make('discount_type')
                                        ->label(__('admin.coupons.form.discount_type'))
                                        ->options([
                                            'percent' => __('admin.coupons.form.discount_type_percent'),
                                            'amount'  => __('admin.coupons.form.discount_type_amount'),
                                        ])
                                        ->native(false)
                                        ->required()
                                        ->default('percent')
                                        ->live(),

                                    TextInput::make('percent_value')
                                        ->label(__('admin.coupons.form.percent_value'))
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(100)
                                        ->step(0.01)
                                        ->visible(fn (Get $get) => $get('discount_type') === 'percent')
                                        ->required(fn (Get $get) => $get('discount_type') === 'percent'),
                                    Tabs::make('currency_tabs')
                                        ->tabs(
                                            collect($currencyCodes)->map(function (string $code) {
                                                $code = strtoupper($code);

                                                return Tab::make($code)->schema([
                                                    Grid::make()->columns(2)->schema([

                                                        // Tutar tipi kuponlar için indirim tutarı
                                                        TextInput::make("currency_data.$code.amount")
                                                            ->label(__('admin.coupons.form.amount'))
                                                            ->numeric()
                                                            ->minValue(0)
                                                            ->required()
                                                            ->visible(fn (Get $get) => $get('discount_type') === 'amount'),

                                                        // Alt limit (her iki tipte de)
                                                        TextInput::make("currency_data.$code.min_booking_amount")
                                                            ->label(__('admin.coupons.form.min_booking_amount'))
                                                            ->numeric()
                                                            ->minValue(0),

                                                        // Opsiyonel tavan indirim (yüzde tipinde anlamlı)
                                                        TextInput::make("currency_data.$code.max_discount_amount")
                                                            ->label(__('admin.coupons.form.max_discount_amount'))
                                                            ->numeric()
                                                            ->minValue(0)
                                                            ->visible(fn (Get $get) => $get('discount_type') === 'percent')
                                                            ->helperText(__('admin.coupons.form.max_discount_amount_help')),
                                                    ]),
                                                ]);
                                            })->all()
                                        )
                                        ->persistTabInQueryString(),
                                ]),

                            ]),

                        // -----------------------------------------------------
                        // SAĞ (4 kolon) — Durum, Kapsam, Kullanım
                        // -----------------------------------------------------
                        Group::make()
                            ->columnSpan(['default' => 12, 'lg' => 4])
                            ->schema([

                                // -----------------------------
                                // DURUM & İNDİRİM TİPİ
                                // -----------------------------
                                Section::make(__('admin.coupons.sections.status'))->schema([
                                    Toggle::make('is_active')
                                        ->label(__('admin.coupons.form.active'))
                                        ->default(true),

                                    TextInput::make('code')
                                        ->label(__('admin.coupons.form.code'))
                                        ->maxLength(100)
                                        ->helperText(__('admin.coupons.form.code_help')),

                                    DateTimePicker::make('valid_from')
                                        ->label(__('admin.coupons.form.valid_from'))
                                        ->seconds(false)
                                        ->required(),

                                    DateTimePicker::make('valid_until')
                                        ->label(__('admin.coupons.form.valid_until'))
                                        ->seconds(false)
                                        ->helperText(__('admin.coupons.form.valid_until_help')),
                                ]),

                                // -----------------------------
                                // KAPSAM (scope)
                                // -----------------------------
                                Section::make(__('admin.coupons.sections.scope'))->schema([

                                    Select::make('scope_type')
                                        ->label(__('admin.coupons.form.scope_type'))
                                        ->options([
                                            'order_total'  => __('admin.coupons.form.scope_type_order_total'),
                                            'product_type' => __('admin.coupons.form.scope_type_product_type'),
                                            'product'      => __('admin.coupons.form.scope_type_product'),
                                        ])
                                        ->native(false)
                                        ->required()
                                        ->live(),

                                    // product_type — çoklu seçim
                                    Select::make('product_types')
                                        ->label(__('admin.coupons.form.product_types'))
                                        ->multiple()
                                        ->native(false)
                                        ->options([
                                            'hotel'    => __('admin.coupons.form.product_type_hotel'),
                                            'villa'    => __('admin.coupons.form.product_type_villa'),
                                            'tour'     => __('admin.coupons.form.product_type_tour'),
                                            'transfer' => __('admin.coupons.form.product_type_transfer'),
                                        ])
                                        ->visible(fn (Get $get) => $get('scope_type') === 'product_type'),

                                    // product_domain + product_id — tekil ürün
                                    Select::make('product_domain')
                                        ->label(__('admin.coupons.form.product_domain'))
                                        ->options([
                                            'hotel'    => __('admin.coupons.form.product_type_hotel'),
                                            'villa'    => __('admin.coupons.form.product_type_villa'),
                                            'tour'     => __('admin.coupons.form.product_type_tour'),
                                            'transfer' => __('admin.coupons.form.product_type_transfer'),
                                        ])
                                        ->native(false)
                                        ->visible(fn (Get $get) => $get('scope_type') === 'product')
                                        ->live(),

                                    Select::make('product_id')
                                        ->label(__('admin.coupons.form.product_name'))
                                        ->native(false)
                                        ->searchable()
                                        ->options(function (Get $get) use ($ui, $base) {
                                            $domain = $get('product_domain');

                                            if (! $domain) {
                                                return [];
                                            }

                                            return match ($domain) {
                                                'hotel' => Hotel::query()
                                                    ->where('is_active', true)
                                                    ->orderBy('sort_order')
                                                    ->selectRaw("id, COALESCE(name->>'$ui', name->>'$base') AS label")
                                                    ->pluck('label', 'id')
                                                    ->all(),

                                                'villa' => Villa::query()
                                                    ->where('is_active', true)
                                                    ->orderBy('sort_order')
                                                    ->selectRaw("id, COALESCE(name->>'$ui', name->>'$base') AS label")
                                                    ->pluck('label', 'id')
                                                    ->all(),

                                                'tour' => Tour::query()
                                                    ->where('is_active', true)
                                                    ->orderBy('sort_order')
                                                    ->selectRaw("id, COALESCE(name->>'$ui', name->>'$base') AS label")
                                                    ->pluck('label', 'id')
                                                    ->all(),

                                                'transfer' => TransferRoute::query()
                                                    ->where('is_active', true)
                                                    ->orderBy('sort_order')
                                                    ->with(['from', 'to'])
                                                    ->get()
                                                    ->mapWithKeys(function ($route) {
                                                        $loc  = Session::get('display_locale') ?: app()->getLocale();
                                                        $base = config('app.locale', 'tr');

                                                        $fromName = $route->from?->name;
                                                        $toName   = $route->to?->name;

                                                        $fromLabel = null;
                                                        if (is_array($fromName)) {
                                                            $fromLabel = $fromName[$loc] ?? $fromName[$base] ?? (string) (array_values($fromName)[0] ?? null);
                                                        } elseif ($fromName) {
                                                            $fromLabel = (string) $fromName;
                                                        }

                                                        $toLabel = null;
                                                        if (is_array($toName)) {
                                                            $toLabel = $toName[$loc] ?? $toName[$base] ?? (string) (array_values($toName)[0] ?? null);
                                                        } elseif ($toName) {
                                                            $toLabel = (string) $toName;
                                                        }

                                                        $label = trim(($fromLabel ?? '') . ' → ' . ($toLabel ?? ''));

                                                        if ($label === '→' || $label === '') {
                                                            $label = 'Route #' . $route->id;
                                                        }

                                                        return [$route->id => $label];
                                                    })
                                                    ->all(),

                                                default => [],
                                            };
                                        })
                                        ->visible(fn (Get $get) => $get('scope_type') === 'product')
                                        ->required(fn (Get $get) => $get('scope_type') === 'product'),

                                    TextInput::make('min_nights')
                                        ->label(__('admin.coupons.form.min_nights'))
                                        ->numeric()
                                        ->minValue(1)
                                        ->helperText(__('admin.coupons.form.min_nights_help')),
                                ]),

                                // -----------------------------
                                // KULLANIM (stack + limit)
                                // -----------------------------
                                Section::make(__('admin.coupons.sections.usage'))->schema([

                                    Toggle::make('is_exclusive')
                                        ->label(__('admin.coupons.form.is_exclusive'))
                                        ->helperText(__('admin.coupons.form.is_exclusive_help')),

                                    TextInput::make('max_uses_per_user')
                                        ->label(__('admin.coupons.form.max_uses_per_user'))
                                        ->numeric()
                                        ->minValue(1)
                                        ->helperText(__('admin.coupons.form.max_uses_per_user_help')),
                                ]),

                            ]),

                    ]),

            ]),

        ]);
    }
}
