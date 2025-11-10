<?php

namespace App\Filament\Resources\RoleResource;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    public static function getNavigationGroup(): ?string { return __('admin.nav.settings_group'); }
    public static function getNavigationLabel(): string { return __('admin.user.plural'); }
    public static function getModelLabel(): string { return __('admin.user.singular'); }
    public static function getPluralModelLabel(): string { return __('admin.user.plural'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('name')
                ->label(__('admin.field.name'))
                ->required()
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

            Hidden::make('guard_name')->default('web'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')
                ->label(__('admin.ent.role.singular'))
                ->searchable()
                ->sortable(),

            TextColumn::make('permissions.name')
                ->label(__('admin.user.direct_permissions'))
                ->badge()
                ->separator(', '),

            TextColumn::make('created_at')
                ->label(__('admin.field.created_at'))
                ->dateTime()
                ->since(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }
}
