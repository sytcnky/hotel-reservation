<?php

namespace App\Filament\Resources\StaticPages\Schemas;

use App\Filament\Resources\StaticPages\Forms\ContactPageForm;
use App\Filament\Resources\StaticPages\Forms\HelpPageForm;
use App\Filament\Resources\StaticPages\Forms\HomePageForm;
use App\Filament\Resources\StaticPages\Forms\HotelPageForm;
use App\Filament\Resources\StaticPages\Forms\LegalPageForm;
use App\Filament\Resources\StaticPages\Forms\TourPageForm;
use App\Filament\Resources\StaticPages\Forms\TransferPageForm;
use App\Filament\Resources\StaticPages\Forms\TravelGuidePageForm;
use App\Filament\Resources\StaticPages\Forms\VillaPageForm;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class StaticPageForm
{
    public static function configure(Schema $schema): Schema
    {
        $pageKeyOptions = [
            'home_page' => __('admin.static_page_labels.page_keys.home_page'),
            'transfer_page' => __('admin.static_page_labels.page_keys.transfer_page'),
            'villa_page' => __('admin.static_page_labels.page_keys.villa_page'),
            'hotel_page' => __('admin.static_page_labels.page_keys.hotel_page'),
            'tour_page' => __('admin.static_page_labels.page_keys.tour_page'),
            'travel_guide_page' => __('admin.static_page_labels.page_keys.travel_guide_page'),

            'help_page' => __('admin.static_page_labels.page_keys.help_page'),
            'contact_page' => __('admin.static_page_labels.page_keys.contact_page'),

            'privacy_policy_page' => __('admin.static_page_labels.page_keys.privacy_policy_page'),
            'terms_of_use_page' => __('admin.static_page_labels.page_keys.terms_of_use_page'),
            'distance_sales_page' => __('admin.static_page_labels.page_keys.distance_sales_page'),
        ];

        return $schema->components([
            Group::make()
                ->columnSpanFull()
                ->schema([
                    Grid::make()
                        ->columns(['default' => 1, 'lg' => 12])
                        ->gap(6)
                        ->schema([
                            // SOL (8) - içerik
                            Group::make()
                                ->columnSpan(['default' => 12, 'lg' => 8])
                                ->schema([
                                    Group::make()
                                        ->visible(fn (Get $get) => $get('key') === 'home_page')
                                        ->schema(HomePageForm::schema()),

                                    Group::make()
                                        ->visible(fn (Get $get) => $get('key') === 'transfer_page')
                                        ->schema(TransferPageForm::schema()),

                                    Group::make()
                                        ->visible(fn (Get $get) => $get('key') === 'villa_page')
                                        ->schema(VillaPageForm::schema()),

                                    Group::make()
                                        ->visible(fn (Get $get) => $get('key') === 'hotel_page')
                                        ->schema(HotelPageForm::schema()),

                                    Group::make()
                                        ->visible(fn (Get $get) => $get('key') === 'tour_page')
                                        ->schema(TourPageForm::schema()),

                                    Group::make()
                                        ->visible(fn (Get $get) => $get('key') === 'travel_guide_page')
                                        ->schema(TravelGuidePageForm::schema()),

                                    Group::make()
                                        ->visible(fn (Get $get) => $get('key') === 'help_page')
                                        ->schema(HelpPageForm::schema()),

                                    Group::make()
                                        ->visible(fn (Get $get) => $get('key') === 'contact_page')
                                        ->schema(ContactPageForm::schema()),

                                    Group::make()
                                        ->visible(fn (Get $get) => $get('key') === 'privacy_policy_page')
                                        ->schema(LegalPageForm::schema()),

                                    Group::make()
                                        ->visible(fn (Get $get) => $get('key') === 'terms_of_use_page')
                                        ->schema(LegalPageForm::schema()),

                                    Group::make()
                                        ->visible(fn (Get $get) => $get('key') === 'distance_sales_page')
                                        ->schema(LegalPageForm::schema()),
                                ]),

                            // SAĞ (4) - ayarlar / durum
                            Group::make()
                                ->columnSpan(['default' => 12, 'lg' => 4])
                                ->schema([
                                    Section::make(__('admin.static_pages.sections.settings'))
                                        ->schema([
                                            Select::make('key')
                                                ->label(__('admin.static_pages.form.key'))
                                                ->options($pageKeyOptions)
                                                ->native(false)
                                                ->required()
                                                ->live(),

                                            Toggle::make('is_active')
                                                ->label(__('admin.field.is_active'))
                                                ->default(true)
                                                ->required(),

                                            TextInput::make('sort_order')
                                                ->label(__('admin.field.sort_order'))
                                                ->numeric()
                                                ->default(0)
                                                ->required(),
                                        ]),
                                ]),
                        ]),
                ]),
        ]);
    }
}
