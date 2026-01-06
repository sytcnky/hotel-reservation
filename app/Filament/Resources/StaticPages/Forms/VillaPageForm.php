<?php

namespace App\Filament\Resources\StaticPages\Forms;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class VillaPageForm
{
    public static function schema(): array
    {
        $base = config('app.locale', 'tr');
        $locales = config('app.supported_locales', [$base]);

        $tabs = function (string $name, array $fieldsByLocale) use ($locales): Tabs {
            return Tabs::make($name)->tabs(
                collect($locales)
                    ->map(fn (string $loc) => Tab::make(strtoupper($loc))->schema($fieldsByLocale[$loc] ?? []))
                    ->all()
            );
        };

        return [
            Section::make(__('admin.static_pages.pages.villa.page_header'))
                ->schema([
                    $tabs('villa_header_i18n', collect($locales)->mapWithKeys(function (string $loc) {
                        return [$loc => [
                            TextInput::make("content.page_header.title.$loc")
                                ->label(__('admin.static_pages.form.title')),

                            Textarea::make("content.page_header.description.$loc")
                                ->label(__('admin.static_pages.form.description'))
                                ->rows(4),
                        ]];
                    })->all()),
                ]),

            Section::make(__('admin.static_pages.pages.villa.page_content'))
                ->schema([
                    $tabs('villa_content_i18n', collect($locales)->mapWithKeys(function (string $loc) {
                        return [$loc => [
                            TextInput::make("content.page_content.title.$loc")
                                ->label(__('admin.static_pages.form.title')),

                            Textarea::make("content.page_content.description.$loc")
                                ->label(__('admin.static_pages.form.description'))
                                ->rows(6),
                        ]];
                    })->all()),

                    Section::make(__('admin.static_pages.pages.villa.images'))
                        ->schema([
                            SpatieMediaLibraryFileUpload::make('villa_content_images')
                                ->label(__('admin.static_pages.pages.villa.images'))
                                ->collection('villa_content_images')
                                ->preserveFilenames()
                                ->image()
                                ->multiple()
                                ->reorderable()
                                ->panelLayout('grid')
                                ->maxFiles(4),
                        ])
                        ->collapsed(),

                    Section::make(__('admin.static_pages.pages.villa.image_texts'))
                        ->schema([
                            $tabs('villa_image_texts_i18n', collect($locales)->mapWithKeys(function (string $loc) {
                                return [$loc => [
                                    TextInput::make("content.page_content.image_texts.0.$loc")
                                        ->label(__('admin.static_pages.pages.villa.image_text_1')),

                                    TextInput::make("content.page_content.image_texts.1.$loc")
                                        ->label(__('admin.static_pages.pages.villa.image_text_2')),

                                    TextInput::make("content.page_content.image_texts.2.$loc")
                                        ->label(__('admin.static_pages.pages.villa.image_text_3')),

                                    TextInput::make("content.page_content.image_texts.3.$loc")
                                        ->label(__('admin.static_pages.pages.villa.image_text_4')),
                                ]];
                            })->all()),
                        ])
                        ->collapsed(),
                ]),
        ];
    }
}
