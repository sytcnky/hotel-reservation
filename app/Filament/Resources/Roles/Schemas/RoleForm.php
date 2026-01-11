<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('name')
                ->label(__('admin.field.name'))
                ->required()
                ->maxLength(255)
                ->disabled(fn ($record) => $record?->name === 'admin')
                ->unique(
                    ignoreRecord: true,
                    modifyRuleUsing: fn (Unique $rule) => $rule->where('guard_name', 'web')
                ),

            Select::make('permissions')
                ->label(__('admin.user.direct_permissions'))
                ->relationship('permissions', 'name')
                ->multiple()
                ->preload()
                ->searchable(),

            Hidden::make('guard_name')
                ->default('web'),
        ]);
    }
}
