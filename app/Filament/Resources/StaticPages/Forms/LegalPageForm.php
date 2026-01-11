<?php

namespace App\Filament\Resources\StaticPages\Forms;

use App\Support\Helpers\LocaleHelper;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class LegalPageForm
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
            Section::make(__('admin.static_pages.pages.legal.page_content'))
                ->schema([
                    $tabs(
                        collect($locales)->mapWithKeys(function (string $loc) {
                            return [$loc => [
                                TextInput::make("content.title.$loc")
                                    ->label(__('admin.static_pages.form.title')),

                                RichEditor::make("content.body.$loc")
                                    ->label(__('admin.static_pages.form.content'))
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'underline',
                                        'strike',
                                        'link',
                                        'blockquote',
                                        'h2',
                                        'h3',
                                        'bulletList',
                                        'orderedList',
                                        'undo',
                                        'redo',
                                    ])
                                    ->columnSpanFull(),
                            ]];
                        })->all()
                    ),
                ]),
        ];
    }
}
