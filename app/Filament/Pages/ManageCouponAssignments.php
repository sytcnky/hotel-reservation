<?php

namespace App\Filament\Pages;

use App\Models\Coupon;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form as SchemaForm;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Toplu Kupon İşlemleri
 *
 * @property-read Schema $form
 */
class ManageCouponAssignments extends Page
{
    /**
     * Bu sayfa kendi Blade view’ini kullanıyor.
     * View yalnızca: {{ $this->form }} içeriyor.
     */
    protected string $view = 'filament.pages.manage-coupon-assignments';

    /**
     * Form state’i (Schemas → statePath('data')).
     */
    public ?array $data = [];

    /**
     * Dry-run / sonuç state’i (form dışı).
     */
    public bool $hasRunDryRun   = false;
    public bool $hasApplyResult = false;

    public int $dryRunCount     = 0;

    public int $resultTotal    = 0;
    public int $resultInserted = 0;
    public int $resultUpdated  = 0;
    public int $resultSkipped  = 0;

    /*
     |--------------------------------------------------------------------------
     | Navigasyon / başlık
     |--------------------------------------------------------------------------
     */

    public static function getNavigationGroup(): ?string
    {
        return __('admin.coupons.navigation.group');
    }

    protected static ?string $navigationLabel = 'Toplu Kupon İşlemleri';

    public function getTitle(): string
    {
        return __('admin.coupons.bulk.page_title');
    }

    /*
     |--------------------------------------------------------------------------
     | Mount
     |--------------------------------------------------------------------------
     */

    public function mount(): void
    {
        $couponId = request()->integer('coupon_id');

        // Form state’ini backend default’larıyla dolduruyoruz
        $this->form->fill([
            'coupon_id'         => $couponId,
            'segment_mode'      => 'all',
            'existing_strategy' => 'skip',
            'new_expires_at'    => null,
            'registered_from'   => null,
            'registered_to'     => null,
        ]);
    }

    /*
     |--------------------------------------------------------------------------
     | Form şeması (Filament v4 Schemas API)
     |--------------------------------------------------------------------------
     */

    public function form(Schema $schema): Schema
    {
        $base = config('app.locale', 'tr');
        $ui   = app()->getLocale();

        // Kupon select’i için seçenekler
        $couponOptions = Coupon::query()
            ->where('is_active', true)
            ->orderByDesc('id')
            ->get()
            ->mapWithKeys(function (Coupon $coupon) use ($ui, $base) {
                $titleData = (array) ($coupon->title ?? []);

                $title = $titleData[$ui]
                    ?? $titleData[$base]
                    ?? (string) (array_values($titleData)[0] ?? '');

                $code  = $coupon->code ?: ('#' . $coupon->id);
                $label = $title ? sprintf('%s — %s', $code, $title) : $code;

                return [$coupon->id => $label];
            })
            ->all();

        return $schema
            ->components([
                SchemaForm::make([
                    Group::make()
                        ->columnSpanFull()
                        ->schema([

                            // 1) Kupon seçimi
                            Section::make(__('admin.coupons.bulk.coupon_section_title'))
                                ->description(__('admin.coupons.bulk.coupon_section_help'))
                                ->schema([
                                    Select::make('coupon_id')
                                        ->label(__('admin.coupons.bulk.coupon_field_label'))
                                        ->options($couponOptions)
                                        ->searchable()
                                        ->native(false)
                                        ->required(),
                                ]),

                            // 2) Segment + mevcut kayıt stratejisi
                            Section::make(__('admin.coupons.bulk.segment_section_title'))
                                ->description(__('admin.coupons.bulk.segment_section_help'))
                                ->schema([
                                    // Kullanıcı segmenti
                                    Radio::make('segment_mode')
                                        ->label(__('admin.coupons.bulk.segment_mode_label'))
                                        ->options([
                                            'all'     => __('admin.coupons.bulk.segment_mode_all'),
                                            'filters' => __('admin.coupons.bulk.segment_mode_filters'),
                                        ])
                                        ->default('all')        // Tüm müşteriler seçili gelsin
                                        ->inline()
                                        ->live(),               // Seçim değişince filtre alanlarını aç/kapat

                                    // Filtre formu — radio grubunun hemen altında
                                    Grid::make()
                                        ->columns(2)
                                        ->visible(fn (callable $get) => $get('segment_mode') === 'filters')
                                        ->schema([
                                            DateTimePicker::make('registered_from')
                                                ->label(__('admin.coupons.bulk.registered_from'))
                                                ->seconds(false)
                                                ->native(false),

                                            DateTimePicker::make('registered_to')
                                                ->label(__('admin.coupons.bulk.registered_to'))
                                                ->seconds(false)
                                                ->native(false),
                                        ]),

                                    // Mevcut kayıt davranışı
                                    Radio::make('existing_strategy')
                                        ->label(__('admin.coupons.bulk.existing_strategy'))
                                        ->options([
                                            'skip'   => __('admin.coupons.bulk.strategy_skip'),
                                            'update' => __('admin.coupons.bulk.strategy_update'),
                                        ])
                                        ->default('skip')       // Var ise atla seçili gelsin
                                        ->inline(),

                                    // Yeni geçerlilik tarihi
                                    DateTimePicker::make('new_expires_at')
                                        ->label(__('admin.coupons.bulk.new_expires_at'))
                                        ->seconds(false)
                                        ->native(false),
                                ]),

                            // 3) Önizleme / Sonuç
                            Section::make(__('admin.coupons.bulk.preview_title'))
                                // İlk açılışta hiç görünmemesi için:
                                ->visible(fn (self $livewire) =>
                                    $livewire->hasRunDryRun || $livewire->hasApplyResult
                                )
                                ->schema([
                                    TextEntry::make('preview_summary')
                                        ->hiddenLabel()
                                        ->state(function (self $livewire): string {
                                            // Apply sonrası özet
                                            if ($livewire->hasApplyResult) {
                                                $text = __(
                                                    'admin.coupons.bulk.apply_summary',
                                                    [
                                                        'total'    => $livewire->resultTotal,
                                                        'inserted' => $livewire->resultInserted,
                                                        'updated'  => $livewire->resultUpdated,
                                                        'skipped'  => $livewire->resultSkipped,
                                                    ]
                                                );

                                                if ($text === 'admin.coupons.bulk.apply_summary') {
                                                    return sprintf(
                                                        '%d kullanıcı değerlendirildi. Yeni: %d, güncellendi: %d, atlandı: %d.',
                                                        $livewire->resultTotal,
                                                        $livewire->resultInserted,
                                                        $livewire->resultUpdated,
                                                        $livewire->resultSkipped,
                                                    );
                                                }

                                                return $text;
                                            }

                                            // Dry-run sonrası özet
                                            if ($livewire->hasRunDryRun) {
                                                $count = $livewire->dryRunCount;

                                                $text = __(
                                                    'admin.coupons.bulk.dry_run_summary',
                                                    ['count' => $count]
                                                );

                                                if ($text === 'admin.coupons.bulk.dry_run_summary') {
                                                    return $count === 0
                                                        ? 'Hiç kullanıcı etkilenmeyecek.'
                                                        : $count . ' kullanıcı etkilenecek.';
                                                }

                                                return $text;
                                            }

                                            return '';
                                        }),
                                ])
                                ->contained(false),
                        ]),
                ])
                    ->statePath('data')
                    ->footer([
                        Actions::make([
                            Action::make('dryRun')
                                ->label(__('admin.coupons.bulk.button_dry_run'))
                                ->icon('heroicon-m-play')
                                ->action('runDryRun'),

                            Action::make('apply')
                                ->label(__('admin.coupons.bulk.button_apply'))
                                ->icon('heroicon-m-check-circle')
                                ->color('success')
                                ->disabled(fn (self $livewire): bool => ! $livewire->hasRunDryRun)
                                ->hidden(fn (self $livewire): bool => $livewire->hasApplyResult)
                                ->action('apply'),
                        ]),
                    ]),
            ]);
    }

    /*
     |--------------------------------------------------------------------------
     | Kullanıcı segment query’si (sadece müşteriler)
     |--------------------------------------------------------------------------
     */

    protected function getUsersQuery(): Builder
    {
        return User::query()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->whereHas('roles', function (Builder $q) {
                $q->where('name', 'customer');
            });
    }

    /*
     |--------------------------------------------------------------------------
     | Yardımcı: tarih filtresini sorguya uygula
     |--------------------------------------------------------------------------
     */
    protected function applySegmentFilters(Builder $query, array $data): Builder
    {
        // Segment "tüm müşteriler" ise filtre yok
        if (($data['segment_mode'] ?? 'all') !== 'filters') {
            return $query;
        }

        $from = ! empty($data['registered_from'] ?? null)
            ? Carbon::parse($data['registered_from'])->startOfDay()
            : null;

        $to = ! empty($data['registered_to'] ?? null)
            ? Carbon::parse($data['registered_to'])->endOfDay()
            : null;

        return $query
            ->when($from, fn (Builder $q) => $q->where('created_at', '>=', $from))
            ->when($to,   fn (Builder $q) => $q->where('created_at', '<=', $to));
    }

    /*
     |--------------------------------------------------------------------------
     | Aksiyonlar
     |--------------------------------------------------------------------------
     */

    public function runDryRun(): void
    {
        $rawState = $this->form->getState();
        $data     = $rawState['data'] ?? [];

        // segment_mode / existing_strategy nullable; backend’de default vereceğiz
        $validated = validator($data, [
            'coupon_id'         => ['required', 'integer', 'exists:coupons,id'],
            'segment_mode'      => ['nullable', 'in:all,filters'],
            'existing_strategy' => ['nullable', 'in:skip,update'],
            'new_expires_at'    => ['nullable', 'date'],
            'registered_from'   => ['nullable', 'date'],
            'registered_to'     => ['nullable', 'date', 'after_or_equal:registered_from'],
        ])->validate();

        // Backend default’ları
        $validated['segment_mode']      = $validated['segment_mode']      ?? 'all';
        $validated['existing_strategy'] = $validated['existing_strategy'] ?? 'skip';

        // Önceki sonuçları sıfırla
        $this->resetPreview();

        // Temel müşteri sorgusu + segment filtreleri
        $query = $this->applySegmentFilters(
            $this->getUsersQuery(),
            $validated
        );

        $this->dryRunCount    = $query->count();
        $this->hasRunDryRun   = true;
        $this->hasApplyResult = false;
    }

    public function apply(): void
    {
        // Dry-run yapılmamışsa önce dry-run çalıştır
        if (! $this->hasRunDryRun) {
            $this->runDryRun();

            if (! $this->hasRunDryRun) {
                return;
            }
        }

        $rawState = $this->form->getState();
        $data     = $rawState['data'] ?? [];

        $validated = validator($data, [
            'coupon_id'         => ['required', 'integer', 'exists:coupons,id'],
            'segment_mode'      => ['nullable', 'in:all,filters'],
            'existing_strategy' => ['nullable', 'in:skip,update'],
            'new_expires_at'    => ['nullable', 'date'],
            'registered_from'   => ['nullable', 'date'],
            'registered_to'     => ['nullable', 'date', 'after_or_equal:registered_from'],
        ])->validate();

        // Backend default’ları
        $validated['segment_mode']      = $validated['segment_mode']      ?? 'all';
        $validated['existing_strategy'] = $validated['existing_strategy'] ?? 'skip';

        /** @var Coupon $coupon */
        $coupon = Coupon::findOrFail($validated['coupon_id']);

        $newExpiresAt = $validated['new_expires_at']
            ? Carbon::parse($validated['new_expires_at'])
            : null;

        // Temel müşteri sorgusu + segment filtreleri
        $query = $this->applySegmentFilters(
            $this->getUsersQuery(),
            $validated
        );

        $total    = 0;
        $inserted = 0;
        $updated  = 0;
        $skipped  = 0;

        $now = now();

        DB::transaction(function () use (
            $query,
            $coupon,
            $newExpiresAt,
            &$total,
            &$inserted,
            &$updated,
            &$skipped,
            $now,
            $validated
        ) {
            $users = $query->get(['id']);

            foreach ($users as $user) {
                $total++;
                $userId = $user->id;

                $existing = DB::table('user_coupons')
                    ->where('user_id', $userId)
                    ->where('coupon_id', $coupon->id)
                    ->first();

                // 1) Kayıt var ve strateji skip ise
                if ($existing && $validated['existing_strategy'] === 'skip') {
                    $skipped++;
                    continue;
                }

                // 2) Kayıt var ve strateji update ise
                if ($existing && $validated['existing_strategy'] === 'update') {
                    DB::table('user_coupons')
                        ->where('id', $existing->id)
                        ->update([
                            'expires_at' => $newExpiresAt ?: $existing->expires_at,
                            'updated_at' => $now,
                        ]);

                    $updated++;
                    continue;
                }

                // 3) Yeni kayıt
                DB::table('user_coupons')->insert([
                    'user_id'     => $userId,
                    'coupon_id'   => $coupon->id,
                    'assigned_at' => $now,
                    'expires_at'  => $newExpiresAt,
                    'used_count'  => 0,
                    'last_used_at'=> null,
                    'source'      => 'bulk',
                    'created_by'  => auth()->id(),
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);

                $inserted++;
            }
        });

        $this->resultTotal    = $total;
        $this->resultInserted = $inserted;
        $this->resultUpdated  = $updated;
        $this->resultSkipped  = $skipped;

        $this->hasApplyResult = true;

        Notification::make()
            ->title(__('admin.coupons.bulk.notification_done'))
            ->success()
            ->send();
    }

    protected function resetPreview(): void
    {
        $this->hasRunDryRun   = false;
        $this->hasApplyResult = false;

        $this->dryRunCount    = 0;

        $this->resultTotal    = 0;
        $this->resultInserted = 0;
        $this->resultUpdated  = 0;
        $this->resultSkipped  = 0;
    }
}
