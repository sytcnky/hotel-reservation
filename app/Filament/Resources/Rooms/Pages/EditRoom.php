<?php

namespace App\Filament\Resources\Rooms\Pages;

use App\Filament\Resources\Rooms\RoomResource;
use App\Models\BoardType;
use App\Models\Currency;
use App\Services\RoomRateResolver;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;

class EditRoom extends EditRecord
{
    protected static string $resource = RoomResource::class;

    public function getTitle(): string
    {
        return __('admin.rooms.singular');
    }

    protected function getHeaderActions(): array
    {
        $currencyOptions = Currency::query()
            ->where('is_active', true)
            ->orderBy('sort_order')->orderBy('code')
            ->pluck('code', 'id')
            ->toArray();

        $boardOptions = BoardType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->mapWithKeys(fn (BoardType $b) => [$b->id => $b->name_l])
            ->toArray();

        return [
            Action::make('previewRates')
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
                            ->columnSpan(2),

                        DatePicker::make('date_start')
                            ->label('Önizleme Başlangıç')
                            ->native(false)->displayFormat('Y-m-d')
                            ->required()->default(now()->toDateString())
                            ->columnSpan(2),

                        DatePicker::make('date_end')
                            ->label('Önizleme Bitiş')
                            ->native(false)->displayFormat('Y-m-d')
                            ->required()
                            ->default(now()->toDateString())
                            ->minDate(fn (Get $get) => $get('date_start'))
                            ->columnSpan(2),

                        Select::make('board_type_id')
                            ->label('Konaklama Planı')
                            ->options($boardOptions)
                            ->nullable()
                            ->default(null)
                            ->native(false)
                            ->placeholder('Farketmez')
                            ->columnSpan(2),

                        TextInput::make('adults')
                            ->label('Yetişkin')
                            ->numeric()->minValue(1)
                            ->required()->default(2)
                            ->columnSpan(1),

                        TextInput::make('children')
                            ->label('Çocuk')
                            ->numeric()->minValue(0)
                            ->required()->default(0)
                            ->columnSpan(1),

                        Html::make('preview_submit')
                            ->columnSpan(2)
                            ->content('<div class="flex" style="height:100%"><button type="submit" class="fi-btn fi-btn-primary w-full py-2 px-3 text-sm" style="align-self:end">Göster</button></div>'),
                    ]),

                    View::make('filament/rooms/preview-rates')
                        ->reactive()
                        ->columnSpanFull()
                        ->viewData(function (Get $get) {
                            $room = $this->getRecord();

                            $currencyId = $get('currency_id') ?: Currency::query()
                                ->where('is_active', true)
                                ->orderBy('sort_order')->orderBy('code')
                                ->value('id');

                            $boardId  = $get('board_type_id') ?: null;
                            $adults   = (int) ($get('adults') ?? 2);
                            $children = (int) ($get('children') ?? 0);
                            $ds       = $get('date_start') ?: now()->toDateString();
                            $de       = $get('date_end') ?: now()->toDateString();

                            $rows = app(RoomRateResolver::class)->resolveRange(
                                $room,
                                $ds,
                                $de,
                                (int) $currencyId,
                                $boardId ? (int) $boardId : null,
                                $adults,
                                $children,
                            );

                            return [
                                'rows'      => $rows,
                                'currency'  => Currency::find($currencyId)?->symbol,
                                'adults'    => $adults,
                                'children'  => $children,
                                // eski blade ile uyumluluk için:
                                'occupancy' => $adults + $children,
                            ];
                        }),
                ])
                ->modalWidth('7xl')
                ->modalHeading('Fiyat Önizleme')
                ->modalSubmitAction(false)
                ->modalCancelAction(false)
                ->modalFooterActions([])
                ->action(fn (Action $action) => $action->halt())
        ];
    }
}
