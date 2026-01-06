<?php

namespace App\Filament\Resources\StaticPages\Forms;

use App\Forms\Components\IconPicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class TransferPageForm
{
    public static function schema(): array
    {
        $base = config('app.locale', 'tr');
        $locales = config('app.supported_locales', [$base]);
        $uiLocale = app()->getLocale();

        $pickLocale = function (?array $map) use ($uiLocale, $base): ?string {
            if (! is_array($map)) {
                return null;
            }

            return $map[$uiLocale] ?? $map[$base] ?? (array_values($map)[0] ?? null);
        };

        $tabs = function (string $name, array $fieldsByLocale) use ($locales): Tabs {
            return Tabs::make($name)->tabs(
                collect($locales)
                    ->map(fn (string $loc) => Tab::make(strtoupper($loc))->schema($fieldsByLocale[$loc] ?? []))
                    ->all()
            );
        };

        return [
            Section::make(__('admin.static_pages.pages.transfer.page_header'))
                ->schema([
                    $tabs('transfer_header_i18n', collect($locales)->mapWithKeys(function (string $loc) {
                        return [$loc => [
                            TextInput::make("content.page_header.title.$loc")
                                ->label(__('admin.static_pages.form.title')),

                            Textarea::make("content.page_header.description.$loc")
                                ->label(__('admin.static_pages.form.description'))
                                ->rows(4),
                        ]];
                    })->all()),
                ]),

            Section::make(__('admin.static_pages.pages.transfer.page_content'))
                ->schema([
                    $tabs('transfer_content_i18n', collect($locales)->mapWithKeys(function (string $loc) {
                        return [$loc => [
                            TextInput::make("content.page_content.title.$loc")
                                ->label(__('admin.static_pages.form.title')),

                            Textarea::make("content.page_content.description.$loc")
                                ->label(__('admin.static_pages.form.description'))
                                ->rows(4),
                        ]];
                    })->all()),

                    Grid::make()
                        ->columns(['default' => 1, 'lg' => 12])
                        ->schema([
                            SpatieMediaLibraryFileUpload::make('transfer_content_image')
                                ->label(__('admin.static_pages.pages.transfer.image'))
                                ->collection('transfer_content_image')
                                ->preserveFilenames()
                                ->image()
                                ->maxFiles(1)
                                ->columnSpan(['default' => 12, 'lg' => 12]),
                        ]),

                    Section::make(__('admin.static_pages.form.icons'))
                        ->schema([
                            Repeater::make('content.page_content.icons')
                                ->label(__('admin.static_pages.form.icons'))
                                ->columns(12)
                                ->collapsed()
                                ->addActionLabel(__('admin.static_pages.form.add_icon'))
                                ->itemLabel(function (array $state) use ($pickLocale): ?string {
                                    $icon = (string) ($state['icon'] ?? '');

                                    $text = $pickLocale(is_array($state['text'] ?? null) ? $state['text'] : null);
                                    $text = is_string($text) ? trim($text) : null;

                                    if ($text) {
                                        $label = trim($icon . ' — ' . $text);
                                        return $label !== '—' ? $label : ($icon ?: null);
                                    }

                                    return $icon ?: null;
                                })
                                ->schema([
                                    IconPicker::make('icon')
                                        ->label(__('admin.static_pages.form.icon'))
                                        ->variant('outline')
                                        ->columnSpan(2),

                                    $tabs('icon_text_i18n', collect($locales)->mapWithKeys(function (string $loc) {
                                        return [$loc => [
                                            TextInput::make("text.$loc")
                                                ->label(__('admin.static_pages.form.icon_text'))
                                                ->helperText(__('admin.static_pages.form.icon_text_optional')),
                                        ]];
                                    })->all())
                                        ->columnSpan(10),
                                ]),
                        ])
                        ->collapsed(),

                    $tabs('transfer_body_i18n', collect($locales)->mapWithKeys(function (string $loc) {
                        return [$loc => [
                            TextInput::make("content.page_content.content_title.$loc")
                                ->label(__('admin.static_pages.pages.transfer.content_title')),

                            Textarea::make("content.page_content.content_text.$loc")
                                ->label(__('admin.static_pages.pages.transfer.content_text'))
                                ->rows(6),
                        ]];
                    })->all()),

                    $tabs('transfer_features_i18n', collect($locales)->mapWithKeys(function (string $loc) {
                        return [$loc => [
                            Repeater::make("content.page_content.features.$loc")
                                ->label(__('admin.static_pages.pages.transfer.features'))
                                ->reorderable()
                                ->defaultItems(0)
                                ->schema([
                                    TextInput::make('text')
                                        ->label(__('admin.static_pages.pages.transfer.feature')),
                                ]),
                        ]];
                    })->all()),
                ]),
        ];
    }
}
