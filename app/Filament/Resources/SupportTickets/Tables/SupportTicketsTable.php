<?php

namespace App\Filament\Resources\SupportTickets\Tables;

use App\Models\SupportTicket;
use App\Support\Date\DatePresenter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SupportTicketsTable
{
    public static function configure(Table $table): Table
    {

        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('category.name_l')
                    ->label(__('admin.support_tickets.field.category'))
                    ->badge()
                    ->sortable(),

                TextColumn::make('subject')
                    ->label(__('admin.support_tickets.field.subject'))
                    ->searchable()
                    ->limit(60),

                TextColumn::make('user.email')
                    ->label(__('admin.support_tickets.field.user'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('admin.support_tickets.field.status'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        SupportTicket::STATUS_WAITING_AGENT => __('admin.support_tickets.status.waiting_agent'),
                        SupportTicket::STATUS_WAITING_CUSTOMER => __('admin.support_tickets.status.waiting_customer'),
                        SupportTicket::STATUS_CLOSED => __('admin.support_tickets.status.closed'),
                        default => __('admin.support_tickets.status.open'),
                    })
                    ->color(fn (?string $state) => match ($state) {
                        SupportTicket::STATUS_WAITING_AGENT => 'warning',
                        SupportTicket::STATUS_WAITING_CUSTOMER => 'info',
                        SupportTicket::STATUS_CLOSED => 'gray',
                        default => 'success',
                    })
                    ->sortable(),

                TextColumn::make('last_message_at')
                    ->label(__('admin.support_tickets.field.last_message_at'))
                    ->formatStateUsing(fn ($state) => DatePresenter::humanDateTimeShort($state))
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('admin.field.created_at'))
                    ->formatStateUsing(fn ($state) => DatePresenter::humanDateTimeShort($state))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('admin.field.updated_at'))
                    ->formatStateUsing(fn ($state) => DatePresenter::humanDateTimeShort($state))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label(__('admin.field.deleted_at'))
                    ->formatStateUsing(fn ($state) => DatePresenter::humanDateTimeShort($state))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('last_message_at', 'desc')
            ->filters([
                TrashedFilter::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
