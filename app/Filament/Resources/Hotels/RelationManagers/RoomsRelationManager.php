<?php

namespace App\Filament\Resources\Hotels\RelationManagers;

use App\Filament\Resources\Rooms\RoomResource;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
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
                TextColumn::make('name_l')
                    ->label(__('admin.field.name'))
                    ->searchable()
                    ->sortable('id'),

                TextColumn::make('viewType.name_l')
                    ->label(__('admin.rooms.form.view_type'))
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label(__('admin.field.is_active'))
                    ->boolean(),

                TextColumn::make('sort_order')
                    ->label(__('admin.field.sort_order'))
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
