<?php

namespace App\Filament\Pages;

use App\Models\Language;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;

/**
 * @property-read Schema $form
 */
class GeneralSettings extends Page
{
    protected string $view = 'filament.pages.general-settings';

    public ?array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.settings_group');
    }

    protected static ?string $navigationLabel = 'Genel Ayarlar';
    protected static ?string $title           = 'Genel Ayarlar';

    public function mount(): void
    {
        $this->form->fill([
            'default_locale' => Setting::get(
                'default_locale',
                config('app.locale', 'tr')
            ),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $languages = Language::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('native_name', 'code') // ['tr' => 'Türkçe', ...]
            ->all();

        return $schema
            ->components([
                Form::make([
                    Select::make('default_locale')
                        ->label('Varsayılan Dil')
                        ->options($languages)
                        ->required(),
                ])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Kaydet')
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $locale = $data['default_locale'] ?? config('app.locale', 'tr');

        Setting::set('default_locale', $locale);

        cache()->forget('active_locales');

        Notification::make()
            ->title('Ayarlar güncellendi')
            ->success()
            ->send();
    }
}
