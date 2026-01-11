<?php

namespace App\Filament\Resources\Permissions\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PermissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('name')
                ->label(__('admin.field.name'))
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),

            Hidden::make('guard_name')
                ->default('web'),
        ]);
    }
}
