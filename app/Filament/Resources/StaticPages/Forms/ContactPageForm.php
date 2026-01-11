<?php

namespace App\Filament\Resources\StaticPages\Forms;

use App\Support\Helpers\LocaleHelper;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class ContactPageForm
{
    public static function schema(): array
    {
        $locales  = LocaleHelper::active();
        $uiLocale = app()->getLocale();

        /**
         * Admin kontratı: fallback yok.
         * Map içinde UI locale yoksa null döner.
         */
        $pickLocale = function (?array $map) use ($uiLocale): ?string {
            if (! is_array($map)) {
                return null;
            }

            $val = $map[$uiLocale] ?? null;

            return is_string($val) ? $val : null;
        };

        $tabs = function (array $fieldsByLocale) use ($locales): Tabs {
            return Tabs::make('i18n')->tabs(
                collect($locales)
                    ->map(fn (string $loc) => Tab::make(strtoupper($loc))->schema($fieldsByLocale[$loc] ?? []))
                    ->all()
            );
        };

        return [
            // =========================================================
            // PAGE HEADER (i18n)
            // =========================================================
            Section::make(__('admin.static_pages.pages.contact.page_header'))
                ->schema([
                    $tabs(
                        collect($locales)->mapWithKeys(function (string $loc) {
                            return [$loc => [
                                TextInput::make("content.page_header.title.$loc")
                                    ->label(__('admin.static_pages.form.title')),

                                Textarea::make("content.page_header.description.$loc")
                                    ->label(__('admin.static_pages.form.description'))
                                    ->rows(4),
                            ]];
                        })->all()
                    ),
                ]),

            // =========================================================
            // OFFICES (Repeater: mixed i18n + non-i18n)
            // =========================================================
            Section::make(__('admin.static_pages.pages.contact.offices'))
                ->schema([
                    Repeater::make('content.offices')
                        ->label(__('admin.static_pages.pages.contact.offices'))
                        ->reorderable()
                        ->defaultItems(0)
                        ->addActionLabel(__('admin.static_pages.pages.contact.add_office'))
                        ->itemLabel(function (array $state) use ($pickLocale): ?string {
                            $name = $pickLocale(is_array($state['name'] ?? null) ? $state['name'] : null);
                            $name = is_string($name) ? trim($name) : null;

                            return $name !== '' ? $name : null;
                        })
                        ->schema([
                            // i18n alanlar
                            $tabs(
                                collect($locales)->mapWithKeys(function (string $loc) {
                                    return [$loc => [
                                        TextInput::make("name.$loc")
                                            ->label(__('admin.static_pages.pages.contact.office_name')),

                                        Textarea::make("address.$loc")
                                            ->label(__('admin.static_pages.pages.contact.address'))
                                            ->rows(3),

                                        TextInput::make("working_hours.$loc")
                                            ->label(__('admin.static_pages.pages.contact.working_hours')),
                                    ]];
                                })->all()
                            )
                                ->columnSpanFull(),

                            // non-i18n alanlar
                            Grid::make()
                                ->columns(['default' => 1, 'lg' => 12])
                                ->schema([
                                    TextInput::make('map_embed_url')
                                        ->label(__('admin.static_pages.pages.contact.map_embed_url'))
                                        ->helperText(__('admin.static_pages.pages.contact.map_embed_url_help'))
                                        ->columnSpan(['default' => 12, 'lg' => 12]),

                                    TextInput::make('phone')
                                        ->label(__('admin.static_pages.pages.contact.phone'))
                                        ->columnSpan(['default' => 12, 'lg' => 6]),

                                    TextInput::make('email')
                                        ->label(__('admin.static_pages.pages.contact.email'))
                                        ->columnSpan(['default' => 12, 'lg' => 6]),
                                ]),
                        ])
                        ->collapsed(),
                ])
                ->collapsed(),
        ];
    }
}
