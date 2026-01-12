<?php

namespace App\Filament\Pages;

use App\Models\Currency;
use App\Models\Language as SiteLanguage;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

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

    protected static ?string $navigationLabel = 'Ayarlar';
    protected static ?string $title           = 'Ayarlar';

    public function mount(): void
    {
        $this->form->fill([
            'default_locale'        => Setting::get('default_locale'),
            'default_currency'      => Setting::get('default_currency', ''),
            'currency_display' => Setting::get('currency_display', 'symbol'),

            'google_analytics_code' => Setting::get('google_analytics_code', ''),

            // sosyal medya (url)
            'instagram'             => Setting::get('instagram', ''),
            'facebook'              => Setting::get('facebook', ''),
            'youtube'               => Setting::get('youtube', ''),

            // i18n maps (code => text)
            'header_info'              => Setting::get('header_info', []),
            'footer_copyright'         => Setting::get('footer_copyright', []),
            'footer_short_description' => Setting::get('footer_short_description', []),

            // iletişim bilgileri (i18n maps)
            'contact_whatsapp_label' => Setting::get('contact_whatsapp_label', []),
            'contact_whatsapp_phone' => Setting::get('contact_whatsapp_phone', []),

            'contact_office1_label'  => Setting::get('contact_office1_label', []),
            'contact_office1_phone'  => Setting::get('contact_office1_phone', []),

            'contact_office2_label'  => Setting::get('contact_office2_label', []),
            'contact_office2_phone'  => Setting::get('contact_office2_phone', []),

            'contact_support_email'  => Setting::get('contact_support_email', []),

            // logo
            'logo_text'              => Setting::get('logo_text', ''),
            'logo_subtitle'          => Setting::get('logo_subtitle', []),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $languages = SiteLanguage::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('native_name', 'code')
            ->all();

        $langCodes = array_keys($languages);

        $currencyOptions = Currency::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['code', 'name'])
            ->mapWithKeys(function (Currency $c) {
                $label = $c->name_l ?: $c->code;
                return [
                    $c->code => ($c->symbol ? ($c->symbol . ' ') : '') . $c->code . ' — ' . $label,
                ];
            })
            ->all();

        // Aynı tab altındaki tüm i18n alanları tek "Dil" Tabs içinde göster
        $contactLocaleTabs = array_map(function (string $code) {
            return Tab::make(strtoupper($code))
                ->schema([
                    TextInput::make("contact_whatsapp_label.$code")
                        ->label('WhatsApp Destek Hattı (Label)')
                        ->suffixIcon(Heroicon::Tag)
                        ->maxLength(255),

                    TextInput::make("contact_whatsapp_phone.$code")
                        ->label('WhatsApp Destek Hattı (Telefon)')
                        ->tel()
                        ->suffixIcon(Heroicon::Phone)
                        ->maxLength(50),

                    TextInput::make("contact_office1_label.$code")
                        ->label('Ofis 1 (Label)')
                        ->suffixIcon(Heroicon::Tag)
                        ->maxLength(255),

                    TextInput::make("contact_office1_phone.$code")
                        ->label('Ofis 1 (Telefon)')
                        ->tel()
                        ->suffixIcon(Heroicon::Phone)
                        ->maxLength(50),

                    TextInput::make("contact_office2_label.$code")
                        ->label('Ofis 2 (Label)')
                        ->suffixIcon(Heroicon::Tag)
                        ->maxLength(255),

                    TextInput::make("contact_office2_phone.$code")
                        ->label('Ofis 2 (Telefon)')
                        ->tel()
                        ->suffixIcon(Heroicon::Phone)
                        ->maxLength(50),

                    TextInput::make("contact_support_email.$code")
                        ->label('Destek E-postası')
                        ->email()
                        ->suffixIcon(Heroicon::Envelope)
                        ->maxLength(255),
                ])
                ->columns('2');
        }, $langCodes);

        $headerInfoLocaleTabs = array_map(function (string $code) {
            return Tab::make(strtoupper($code))
                ->schema([
                    TextInput::make("header_info.$code")
                        ->label('Üst Bilgi')
                        ->maxLength(255),
                ]);
        }, $langCodes);

        $footerLocaleTabs = array_map(function (string $code) {
            return Tab::make(strtoupper($code))
                ->schema([
                    TextInput::make("footer_copyright.$code")
                        ->label('Telif Hakkı')
                        ->maxLength(255),

                    TextInput::make("footer_short_description.$code")
                        ->label('Kısa Açıklama')
                        ->maxLength(255),
                ]);
        }, $langCodes);

        $logoLocaleTabs = array_map(function (string $code) {
            return Tab::make(strtoupper($code))
                ->schema([
                    TextInput::make("logo_subtitle.$code")
                        ->label('Logo Subtitle')
                        ->maxLength(255),
                ]);
        }, $langCodes);

        return $schema
            ->components([
                Form::make([
                    Tabs::make('settings')
                        ->vertical()
                        ->tabs([
                            Tab::make('Genel Ayarlar')
                                ->schema([
                                    Select::make('default_locale')
                                        ->label('Varsayılan Dil')
                                        ->native(false)
                                        ->options($languages)
                                        ->required(),

                                    Select::make('default_currency')
                                        ->label('Varsayılan Para Birimi')
                                        ->native(false)
                                        ->options($currencyOptions)
                                        ->searchable()
                                        ->required(fn () => count($currencyOptions) > 0),

                                    Select::make('currency_display')
                                        ->label('Para Birimi Gösterimi')
                                        ->native(false)
                                        ->options([
                                            'symbol' => 'Sembol (₺, €, £)',
                                            'code'   => 'Kısaltma (TRY, EUR, GBP)',
                                        ])
                                        ->required(),
                                ]),

                            Tab::make('Logo')
                                ->schema([
                                    TextInput::make('logo_text')
                                        ->label('Logo (şimdilik text)')
                                        ->maxLength(255),

                                    Tabs::make('logo_subtitle_i18n')
                                        ->tabs($logoLocaleTabs),
                                ]),

                            Tab::make('Üst Bilgi')
                                ->schema([
                                    Tabs::make('header_i18n')
                                        ->tabs($headerInfoLocaleTabs),
                                ]),

                            Tab::make('Alt Bilgi')
                                ->schema([
                                    Tabs::make('footer_i18n')
                                        ->tabs($footerLocaleTabs),
                                ]),

                            Tab::make('İletişim Bilgileri')
                                ->schema([
                                    Tabs::make('contact_i18n')
                                        ->tabs($contactLocaleTabs),
                                ]),

                            Tab::make('Sosyal Medya')
                                ->schema([
                                    TextInput::make('instagram')->label('Instagram')->url()->maxLength(255)->suffixIcon(Heroicon::GlobeAlt)->prefix('https://'),
                                    TextInput::make('facebook')->label('Facebook')->url()->maxLength(255)->suffixIcon(Heroicon::GlobeAlt)->prefix('https://'),
                                    TextInput::make('youtube')->label('YouTube')->url()->maxLength(255)->suffixIcon(Heroicon::GlobeAlt)->prefix('https://'),
                                ]),

                            Tab::make('Google Analytics')
                                ->schema([
                                    CodeEditor::make('google_analytics_code')
                                        ->label('Kod')
                                        ->language(Language::Html),
                                ]),
                        ]),
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

        $locale = $data['default_locale'] ?? null;
        if ($locale !== null) {
            Setting::set('default_locale', $locale);
        }

        Setting::set('default_currency', (string) ($data['default_currency'] ?? ''));
        Setting::set('currency_display', (string) $data['currency_display']);

        Setting::set('google_analytics_code', (string) ($data['google_analytics_code'] ?? ''));

        // sosyal medya
        Setting::set('instagram', (string) ($data['instagram'] ?? ''));
        Setting::set('facebook', (string) ($data['facebook'] ?? ''));
        Setting::set('youtube', (string) ($data['youtube'] ?? ''));

        // üst / alt bilgi
        Setting::set('header_info', (array) ($data['header_info'] ?? []));
        Setting::set('footer_copyright', (array) ($data['footer_copyright'] ?? []));
        Setting::set('footer_short_description', (array) ($data['footer_short_description'] ?? []));

        // iletişim
        Setting::set('contact_whatsapp_label', (array) ($data['contact_whatsapp_label'] ?? []));
        Setting::set('contact_whatsapp_phone', (array) ($data['contact_whatsapp_phone'] ?? []));

        Setting::set('contact_office1_label', (array) ($data['contact_office1_label'] ?? []));
        Setting::set('contact_office1_phone', (array) ($data['contact_office1_phone'] ?? []));

        Setting::set('contact_office2_label', (array) ($data['contact_office2_label'] ?? []));
        Setting::set('contact_office2_phone', (array) ($data['contact_office2_phone'] ?? []));

        Setting::set('contact_support_email', (array) ($data['contact_support_email'] ?? []));

        // logo
        Setting::set('logo_text', (string) ($data['logo_text'] ?? ''));
        Setting::set('logo_subtitle', (array) ($data['logo_subtitle'] ?? []));

        cache()->forget('active_locales');

        Notification::make()
            ->title('Ayarlar güncellendi')
            ->success()
            ->send();
    }
}
