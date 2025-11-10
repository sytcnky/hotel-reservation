<?php

namespace App\Filament\Resources\Activities\Tables;

use Filament\Tables;
use Filament\Tables\Table;

class ActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('admin.activity.id'))
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('event')
                    ->label(__('admin.activity.event'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('causer.name')
                    ->label(__('admin.activity.causer'))
                    ->default('System')
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject_type')
                    ->label(__('admin.activity.subject_type'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject_id')
                    ->label(__('admin.activity.subject_id')),

                Tables\Columns\TextColumn::make('description')
                    ->label(__('admin.activity.description'))
                    ->limit(40),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.activity.created_at'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->label(__('admin.activity.event'))
                    ->options([
                        'created' => __('admin.activity.created'),
                        'updated' => __('admin.activity.updated'),
                        'deleted' => __('admin.activity.deleted'),
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
