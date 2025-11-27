<?php

namespace App\Filament\Resources\Villas\Pages;

use App\Filament\Resources\Villas\VillaResource;
use App\Models\Currency;
use App\Services\VillaRateResolver;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;

class EditVilla extends EditRecord
{
    protected static string $resource = VillaResource::class;

    public function getTitle(): string
    {
        return __('admin.villas.singular');
    }

    protected function getHeaderActions(): array
    {
        $currencyOptions = Currency::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('code')
            ->pluck('code', 'id')
            ->toArray();

        $previewAction = Action::make('previewRates')
            ->label('Fiyat Önizleme')
            ->icon('heroicon-o-eye')
            ->schema([
                Grid::make()->columns(12)->schema([
                    Select::make('currency_id')
                        ->label('Para Birimi')
                        ->options($currencyOptions)
                        ->required()
                        ->default(array_key_first($currencyOptions))
                        ->native(false)
                        ->columnSpan(3),

                    DatePicker::make('date_start')
                        ->label('Başlangıç')
                        ->native(false)
                        ->displayFormat('Y-m-d')
                        ->required()
                        ->default(now()->toDateString())
                        ->columnSpan(3),

                    DatePicker::make('date_end')
                        ->label('Bitiş')
                        ->native(false)
                        ->displayFormat('Y-m-d')
                        ->required()
                        ->default(now()->toDateString())
                        ->minDate(fn (Get $get) => $get('date_start'))
                        ->columnSpan(3),

                    Html::make('preview_submit')
                        ->columnSpan(3)
                        ->content(
                            '<div class="flex" style="height:100%">
                                <button type="submit" class="fi-btn fi-btn-primary w-full py-2 px-3 text-sm" style="align-self:end">
                                    Göster
                                </button>
                            </div>'
                        ),
                ]),

                View::make('filament/villas/preview-rates')
                    ->reactive()
                    ->columnSpanFull()
                    ->viewData(function (Get $get) {
                        $villa = $this->getRecord();

                        $currencyId = $get('currency_id') ?: Currency::query()
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->orderBy('code')
                            ->value('id');

                        $ds = $get('date_start') ?: now()->toDateString();
                        $de = $get('date_end') ?: now()->toDateString();

                        $rows = app(VillaRateResolver::class)->resolveRange(
                            $villa,
                            $ds,
                            $de,
                            (int) $currencyId,
                        );

                        return [
                            'rows'     => $rows,
                            'currency' => Currency::find($currencyId)?->symbol,
                        ];
                    }),
            ])
            ->modalWidth('7xl')
            ->modalHeading('Fiyat Önizleme')
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->modalFooterActions([])
            ->action(fn (Action $action) => $action->halt());

        return [
            $previewAction,
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
