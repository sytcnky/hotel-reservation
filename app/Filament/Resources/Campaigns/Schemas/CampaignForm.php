<?php

namespace App\Filament\Resources\Campaigns\Schemas;

use App\Models\Currency;
use App\Models\Hotel;
use App\Models\Location;
use App\Models\Tour;
use App\Models\TransferRoute;
use App\Models\Villa;
use App\Support\Helpers\CampaignColorHelper;
use App\Support\Helpers\LocaleHelper;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\ColorEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class CampaignForm
{
    public static function configure(Schema $schema): Schema
    {
        $uiLocale = app()->getLocale();
        $locales  = LocaleHelper::active();

        $currencyCodes = Currency::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('code')
            ->pluck('code')
            ->all();

        if (empty($currencyCodes)) {
            $currencyCodes = (array) config('app.supported_currencies', []);
        }

        $placementOptions = [
            'homepage_banner' => __('admin.campaigns.placements.homepage_banner'),
            'hotel_detail'    => __('admin.campaigns.placements.hotel_detail'),
            'villa_detail'    => __('admin.campaigns.placements.villa_detail'),
            'tour_detail'     => __('admin.campaigns.placements.tour_detail'),
            'guide_detail'    => __('admin.campaigns.placements.guide_detail'),
            'basket'          => __('admin.campaigns.placements.basket'),
        ];

        $backgroundClasses = [
            'bg-primary',
            'bg-primary-subtle',
            'bg-secondary',
            'bg-secondary-subtle',
            'bg-success',
            'bg-success-subtle',
            'bg-danger',
            'bg-danger-subtle',
            'bg-warning',
            'bg-warning-subtle',
            'bg-info',
            'bg-info-subtle',
            'bg-light',
            'bg-light-subtle',
            'bg-dark',
            'bg-dark-subtle',
        ];

        $backgroundClassOptions = [];
        foreach ($backgroundClasses as $class) {
            $backgroundClassOptions[$class] = __('admin.campaigns.form.' . $class);
        }

        $productTypeOptions = [
            'hotel'    => __('admin.coupons.form.product_type_hotel'),
            'villa'    => __('admin.coupons.form.product_type_villa'),
            'tour'     => __('admin.coupons.form.product_type_tour'),
            'transfer' => __('admin.coupons.form.product_type_transfer'),
        ];

        $ruleTypeOptions = [
            'scope_product_types'           => __('admin.campaigns.rules.scope_product_types'),
            'scope_products'                => __('admin.campaigns.rules.scope_products'),
            'basket_required_product_types' => __('admin.campaigns.rules.basket_required_product_types'),
            'product_locations'             => __('admin.campaigns.rules.product_locations'),
            'product_min_nights'            => __('admin.campaigns.rules.product_min_nights'),
            'product_min_guests'            => __('admin.campaigns.rules.product_min_guests'),
            'dates_booking_between'         => __('admin.campaigns.rules.dates_booking_between'),
            'dates_stay_between'            => __('admin.campaigns.rules.dates_stay_between'),
            'user_registered_date'          => __('admin.campaigns.rules.user_registered_date'),
            'user_order_count'              => __('admin.campaigns.rules.user_order_count'),
            'device_allowed'                => __('admin.campaigns.rules.device_allowed'),
            'discount_target_product_types' => __('admin.campaigns.rules.discount_target_product_types'),
        ];

        return $schema->components([
            Group::make()->columnSpanFull()->schema([
                Grid::make()
                    ->columns(['default' => 1, 'lg' => 12])
                    ->gap(6)
                    ->schema([
                        // SOL (8)
                        Group::make()
                            ->columnSpan(['default' => 12, 'lg' => 8])
                            ->schema([
                                Tabs::make('i18n')->tabs(
                                    collect($locales)->map(function (string $loc) {
                                        return Tab::make(strtoupper($loc))->schema([
                                            TextInput::make("content.$loc.title")
                                                ->label(__('admin.campaigns.form.title'))
                                                ->required(),

                                            TextInput::make("content.$loc.subtitle")
                                                ->label(__('admin.campaigns.form.subtitle')),

                                            Textarea::make("content.$loc.description")
                                                ->label(__('admin.campaigns.form.description'))
                                                ->rows(3),

                                            TextInput::make("content.$loc.cta_text")
                                                ->label(__('admin.campaigns.form.cta_text')),

                                            TextInput::make("content.$loc.cta_link")
                                                ->label(__('admin.campaigns.form.cta_link'))
                                                ->helperText(__('admin.campaigns.form.cta_link_help')),
                                        ]);
                                    })->all()
                                ),

                                Section::make(__('admin.campaigns.sections.images'))
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('background_image')
                                            ->collection('background_image')
                                            ->label(__('admin.campaigns.form.background_image'))
                                            ->image()
                                            ->imageEditor()
                                            ->responsiveImages()
                                            ->preserveFilenames()
                                            ->hint(__('admin.campaigns.form.background_image_help')),

                                        SpatieMediaLibraryFileUpload::make('transparent_image')
                                            ->collection('transparent_image')
                                            ->label(__('admin.campaigns.form.transparent_image'))
                                            ->image()
                                            ->imageEditor()
                                            ->responsiveImages()
                                            ->preserveFilenames()
                                            ->hint(__('admin.campaigns.form.transparent_image_help')),
                                    ]),

                                Section::make(__('admin.campaigns.sections.background_class'))
                                    ->schema([
                                        Grid::make(['default' => 1, 'lg' => 2])->schema([
                                            Select::make('discount.background_class')
                                                ->hiddenLabel()
                                                ->options($backgroundClassOptions)
                                                ->default('bg-primary')
                                                ->live()
                                                ->native(false),

                                            ColorEntry::make('discount_background_preview')
                                                ->hiddenLabel()
                                                ->state(function (Get $get): string {
                                                    $class = $get('discount.background_class') ?: 'bg-primary';
                                                    return CampaignColorHelper::backgroundHexFromClass($class);
                                                })
                                                ->columnSpan(1),
                                        ]),
                                    ]),

                                Section::make(__('admin.campaigns.sections.discount'))->schema([
                                    Select::make('discount.type')
                                        ->label(__('admin.campaigns.form.discount_type'))
                                        ->options([
                                            'percent' => __('admin.campaigns.form.discount_type_percent'),
                                            'amount'  => __('admin.campaigns.form.discount_type_fixed'),
                                        ])
                                        ->native(false)
                                        ->required()
                                        ->default('percent')
                                        ->live(),

                                    TextInput::make('discount.percent_value')
                                        ->label(__('admin.campaigns.form.discount_value'))
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(100)
                                        ->step(0.01)
                                        ->visible(fn (Get $get) => $get('discount.type') === 'percent')
                                        ->required(fn (Get $get) => $get('discount.type') === 'percent'),

                                    Tabs::make('discount_currency_tabs')
                                        ->tabs(
                                            collect($currencyCodes)->map(function (string $code) {
                                                $code = strtoupper($code);

                                                return Tab::make($code)->schema([
                                                    Grid::make()->columns(2)->schema([
                                                        TextInput::make("discount.currency_data.$code.amount")
                                                            ->label(__('admin.coupons.form.amount'))
                                                            ->numeric()
                                                            ->minValue(0)
                                                            ->visible(fn (Get $get) => $get('discount.type') === 'amount')
                                                            ->required(fn (Get $get) => $get('discount.type') === 'amount'),

                                                        TextInput::make("discount.currency_data.$code.min_booking_amount")
                                                            ->label(__('admin.coupons.form.min_booking_amount'))
                                                            ->numeric()
                                                            ->minValue(0),

                                                        TextInput::make("discount.currency_data.$code.max_discount_amount")
                                                            ->label(__('admin.campaigns.form.max_discount_amount'))
                                                            ->numeric()
                                                            ->minValue(0)
                                                            ->visible(fn (Get $get) => $get('discount.type') === 'percent')
                                                            ->helperText(__('admin.campaigns.form.max_discount_amount_help')),
                                                    ]),
                                                ]);
                                            })->all()
                                        )
                                        ->persistTabInQueryString(),
                                ]),

                                Section::make(__('admin.campaigns.sections.conditions'))->schema([
                                    Repeater::make('conditions.rules')
                                        ->label(__('admin.campaigns.form.conditions_rules'))
                                        ->hiddenLabel()
                                        ->itemLabel(function (array $state) use ($ruleTypeOptions): ?string {
                                            $type = $state['type'] ?? null;

                                            return $type && isset($ruleTypeOptions[$type])
                                                ? $ruleTypeOptions[$type]
                                                : null;
                                        })
                                        ->schema([
                                            Select::make('type')
                                                ->label(__('admin.campaigns.rules.rule_type'))
                                                ->options($ruleTypeOptions)
                                                ->native(false)
                                                ->required()
                                                ->disableOptionsWhenSelectedInSiblingRepeaterItems(),

                                            Group::make()
                                                ->schema([
                                                    CheckboxList::make('product_types')
                                                        ->label(__('admin.campaigns.rules.scope_product_types_product_types'))
                                                        ->options($productTypeOptions)
                                                        ->columns(1)
                                                        ->bulkToggleable(),
                                                ])
                                                ->visible(fn (Get $get) => $get('type') === 'scope_product_types'),

                                            Group::make()
                                                ->schema([
                                                    Select::make('domain')
                                                        ->label(__('admin.coupons.form.product_domain'))
                                                        ->options($productTypeOptions)
                                                        ->native(false)
                                                        ->live(),

                                                    Select::make('id')
                                                        ->label(__('admin.coupons.form.product_name'))
                                                        ->native(false)
                                                        ->searchable()
                                                        ->options(function (Get $get) use ($uiLocale) {
                                                            $domain = $get('domain');

                                                            if (! $domain) {
                                                                return [];
                                                            }

                                                            return match ($domain) {
                                                                'hotel' => Hotel::query()
                                                                    ->where('is_active', true)
                                                                    ->orderBy('sort_order')
                                                                    ->selectRaw("id, NULLIF(name->>'{$uiLocale}', '') AS label")
                                                                    ->orderBy('label')
                                                                    ->pluck('label', 'id')
                                                                    ->all(),

                                                                'villa' => Villa::query()
                                                                    ->where('is_active', true)
                                                                    ->orderBy('sort_order')
                                                                    ->selectRaw("id, NULLIF(name->>'{$uiLocale}', '') AS label")
                                                                    ->orderBy('label')
                                                                    ->pluck('label', 'id')
                                                                    ->all(),

                                                                'tour' => Tour::query()
                                                                    ->where('is_active', true)
                                                                    ->orderBy('sort_order')
                                                                    ->selectRaw("id, NULLIF(name->>'{$uiLocale}', '') AS label")
                                                                    ->orderBy('label')
                                                                    ->pluck('label', 'id')
                                                                    ->all(),

                                                                'transfer' => TransferRoute::query()
                                                                    ->where('is_active', true)
                                                                    ->orderBy('sort_order')
                                                                    ->with(['from', 'to'])
                                                                    ->get()
                                                                    ->mapWithKeys(function ($route) use ($uiLocale) {
                                                                        $fromName = $route->from?->name;
                                                                        $toName   = $route->to?->name;

                                                                        $fromLabel = null;
                                                                        if (is_array($fromName)) {
                                                                            $fromLabel = $fromName[$uiLocale] ?? null;
                                                                        } elseif ($fromName) {
                                                                            $fromLabel = (string) $fromName;
                                                                        }

                                                                        $toLabel = null;
                                                                        if (is_array($toName)) {
                                                                            $toLabel = $toName[$uiLocale] ?? null;
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
                                                        }),
                                                ])
                                                ->visible(fn (Get $get) => $get('type') === 'scope_products'),

                                            Group::make()
                                                ->schema([
                                                    CheckboxList::make('required_types')
                                                        ->label(__('admin.campaigns.rules.basket_required_product_types_required'))
                                                        ->options($productTypeOptions)
                                                        ->columns(1)
                                                        ->bulkToggleable(),
                                                ])
                                                ->visible(fn (Get $get) => $get('type') === 'basket_required_product_types'),

                                            Group::make()
                                                ->schema([
                                                    CheckboxList::make('product_types')
                                                        ->label(__('admin.campaigns.rules.product_locations_product_types'))
                                                        ->options($productTypeOptions)
                                                        ->columns(1)
                                                        ->bulkToggleable(),

                                                    Select::make('location_ids')
                                                        ->label(__('admin.campaigns.rules.product_locations_locations'))
                                                        ->multiple()
                                                        ->searchable()
                                                        ->preload()
                                                        ->options(function () use ($uiLocale) {
                                                            return Location::query()
                                                                ->where('is_active', true)
                                                                ->orderBy('type')
                                                                ->orderBy('sort_order')
                                                                ->get()
                                                                ->mapWithKeys(function (Location $location) use ($uiLocale) {
                                                                    return [$location->id => $location->displayLabel($uiLocale)];
                                                                })
                                                                ->all();
                                                        }),
                                                ])
                                                ->visible(fn (Get $get) => $get('type') === 'product_locations'),

                                            Group::make()
                                                ->schema([
                                                    CheckboxList::make('product_types')
                                                        ->label(__('admin.campaigns.rules.product_min_nights_product_types'))
                                                        ->options($productTypeOptions)
                                                        ->columns(1)
                                                        ->bulkToggleable(),

                                                    TextInput::make('value')
                                                        ->label(__('admin.campaigns.rules.product_min_nights_value'))
                                                        ->numeric()
                                                        ->minValue(1),
                                                ])
                                                ->visible(fn (Get $get) => $get('type') === 'product_min_nights'),

                                            Group::make()
                                                ->schema([
                                                    TextInput::make('value')
                                                        ->label(__('admin.campaigns.rules.product_min_guests_value'))
                                                        ->numeric()
                                                        ->minValue(1),
                                                ])
                                                ->visible(fn (Get $get) => $get('type') === 'product_min_guests'),

                                            Group::make()
                                                ->schema([
                                                    DatePicker::make('from')
                                                        ->label(__('admin.campaigns.rules.dates_booking_from')),
                                                    DatePicker::make('to')
                                                        ->label(__('admin.campaigns.rules.dates_booking_to')),
                                                ])
                                                ->visible(fn (Get $get) => $get('type') === 'dates_booking_between'),

                                            Group::make()
                                                ->schema([
                                                    DatePicker::make('from')
                                                        ->label(__('admin.campaigns.rules.dates_stay_from')),
                                                    DatePicker::make('to')
                                                        ->label(__('admin.campaigns.rules.dates_stay_to')),
                                                ])
                                                ->visible(fn (Get $get) => $get('type') === 'dates_stay_between'),

                                            Group::make()
                                                ->schema([
                                                    Select::make('operator')
                                                        ->label(__('admin.campaigns.rules.user_registered_operator'))
                                                        ->options([
                                                            'before' => __('admin.campaigns.rules.user_registered_before'),
                                                            'after'  => __('admin.campaigns.rules.user_registered_after'),
                                                        ])
                                                        ->native(false),

                                                    DatePicker::make('date')
                                                        ->label(__('admin.campaigns.rules.user_registered_date')),
                                                ])
                                                ->visible(fn (Get $get) => $get('type') === 'user_registered_date'),

                                            Group::make()
                                                ->schema([
                                                    Select::make('operator')
                                                        ->label(__('admin.campaigns.rules.user_order_operator'))
                                                        ->options([
                                                            'eq'  => __('admin.campaigns.rules.user_order_eq'),
                                                            'gte' => __('admin.campaigns.rules.user_order_gte'),
                                                            'lte' => __('admin.campaigns.rules.user_order_lte'),
                                                        ])
                                                        ->native(false),

                                                    TextInput::make('value')
                                                        ->label(__('admin.campaigns.rules.user_order_value'))
                                                        ->numeric()
                                                        ->minValue(0),
                                                ])
                                                ->visible(fn (Get $get) => $get('type') === 'user_order_count'),

                                            Group::make()
                                                ->schema([
                                                    CheckboxList::make('product_types')
                                                        ->label(__('admin.campaigns.rules.discount_target_product_types_product_types'))
                                                        ->options($productTypeOptions)
                                                        ->columns(1)
                                                        ->bulkToggleable(),
                                                ])
                                                ->visible(fn (Get $get) => $get('type') === 'discount_target_product_types'),
                                        ])
                                        ->defaultItems(0)
                                        ->collapsed()
                                        ->addActionLabel(__('admin.campaigns.form.conditions_add_rule'))
                                        ->orderColumn(),
                                ]),
                            ]),

                        // SAĞ (4)
                        Group::make()
                            ->columnSpan(['default' => 12, 'lg' => 4])
                            ->schema([
                                Section::make(__('admin.campaigns.sections.status'))->schema([
                                    Toggle::make('is_active')
                                        ->label(__('admin.campaigns.form.active'))
                                        ->default(true),

                                    DatePicker::make('start_date')
                                        ->label(__('admin.campaigns.form.start_date')),

                                    DatePicker::make('end_date')
                                        ->label(__('admin.campaigns.form.end_date')),

                                    TextInput::make('priority')
                                        ->label(__('admin.campaigns.form.priority'))
                                        ->numeric()
                                        ->default(0),
                                ]),

                                Section::make(__('admin.campaigns.sections.placement'))->schema([
                                    CheckboxList::make('placements')
                                        ->hiddenLabel()
                                        ->options($placementOptions)
                                        ->columns(1)
                                        ->default([])
                                        ->bulkToggleable(),
                                ]),

                                Section::make(__('admin.campaigns.sections.usage'))->schema([
                                    TextInput::make('global_usage_limit')
                                        ->label(__('admin.campaigns.form.global_usage_limit'))
                                        ->numeric()
                                        ->minValue(0)
                                        ->helperText(__('admin.campaigns.form.global_usage_limit_help')),

                                    TextInput::make('user_usage_limit')
                                        ->label(__('admin.campaigns.form.user_usage_limit'))
                                        ->numeric()
                                        ->minValue(0)
                                        ->helperText(__('admin.campaigns.form.user_usage_limit_help')),
                                ]),
                            ]),
                    ]),
            ]),
        ]);
    }
}
