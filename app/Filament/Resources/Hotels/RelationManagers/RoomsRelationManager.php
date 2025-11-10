<?php

namespace App\Filament\Resources\Hotels\RelationManagers;

use App\Filament\Resources\Rooms\RoomResource;
use Filament\Actions\{CreateAction, DeleteAction, EditAction, ForceDeleteAction, RestoreAction};
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\{IconColumn, TextColumn};
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RoomsRelationManager extends RelationManager
{
    protected static string $relationship = 'rooms';
    protected static ?string $relatedResource = RoomResource::class;

    public function table(Table $table): Table
    {
        return $table
            // İlişkileri önceden yükle (viewType)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('viewType'))

            ->columns([
                // Oda adı
                TextColumn::make('name_l')
                    ->label('Ad')
                    ->searchable()
                    ->sortable('id'),

                // Manzara
                TextColumn::make('viewType.name_l')
                    ->label('Manzara')
                    ->sortable(),

                // Aktif
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                // Sıra
                TextColumn::make('sort_order')
                    ->label('Sıra')
                    ->sortable(),
            ])

            ->headerActions([
                CreateAction::make()
                    ->url(fn () => RoomResource::getUrl('create', [
                        'hotel_id' => $this->ownerRecord->getKey(),
                    ]))
                    ->openUrlInNewTab(false),
            ])

            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ]);
    }
}
