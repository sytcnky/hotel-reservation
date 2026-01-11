<?php

namespace App\Filament\Resources\StaticPages\Forms;

use App\Support\Helpers\LocaleHelper;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class HelpPageForm
{
    public static function schema(): array
    {
        $locales = LocaleHelper::active();

        $tabs = function (array $fieldsByLocale) use ($locales): Tabs {
            return Tabs::make('i18n')->tabs(
                collect($locales)
                    ->map(fn (string $loc) => Tab::make(strtoupper($loc))->schema($fieldsByLocale[$loc] ?? []))
                    ->all()
            );
        };

        return [
            Section::make(__('admin.static_pages.pages.help.page_header'))
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

            Section::make(__('admin.static_pages.pages.help.faq'))
                ->schema([
                    Repeater::make('content.faq_items')
                        ->label(__('admin.static_pages.form.faq_items'))
                        ->minItems(1)
                        ->reorderable()
                        ->schema([
                            $tabs(
                                collect($locales)->mapWithKeys(function (string $loc) {
                                    return [$loc => [
                                        TextInput::make("question.$loc")
                                            ->label(__('admin.static_pages.form.question')),

                                        Textarea::make("answer.$loc")
                                            ->label(__('admin.static_pages.form.answer'))
                                            ->rows(5),
                                    ]];
                                })->all()
                            ),
                        ]),
                ]),
        ];
    }
}
