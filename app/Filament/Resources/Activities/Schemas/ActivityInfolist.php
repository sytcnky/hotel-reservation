<?php

namespace App\Filament\Resources\Activities\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ActivityInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            TextEntry::make('id')
                ->label(__('admin.activity.id')),

            TextEntry::make('event')
                ->label(__('admin.activity.event')),

            TextEntry::make('causer.name')
                ->label(__('admin.activity.causer'))
                ->default('System'),

            TextEntry::make('subject_type')
                ->label(__('admin.activity.subject_type')),

            TextEntry::make('subject_id')
                ->label(__('admin.activity.subject_id')),

            TextEntry::make('description')
                ->label(__('admin.activity.description')),

            TextEntry::make('created_at')
                ->label(__('admin.activity.created_at'))
                ->dateTime('d.m.Y H:i'),

            // Ham JSON (pretty-print)
            TextEntry::make('properties')
                ->label(__('admin.activity.properties'))
                ->formatStateUsing(fn ($state) => is_array($state) || is_object($state)
                    ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                    : (string) $state
                )
                ->default('—')
                ->extraAttributes(['class' => 'font-mono whitespace-pre-wrap text-xs'])
                ->copyable()
                ->columnSpanFull(),
        ]);
    }
}
