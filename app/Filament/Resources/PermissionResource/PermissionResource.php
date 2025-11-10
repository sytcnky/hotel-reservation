<?php

namespace App\Filament\Resources\PermissionResource;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    public static function getNavigationGroup(): ?string { return __('admin.nav.settings_group'); }
    public static function getNavigationLabel(): string { return __('admin.permission.plural'); }
    public static function getModelLabel(): string { return __('admin.permission.singular'); }
    public static function getPluralModelLabel(): string { return __('admin.permission.plural'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('name')
                ->label(__('admin.field.name'))
                ->required()
                ->unique(ignoreRecord: true),

            Hidden::make('guard_name')->default('web'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')
                ->label(__('admin.ent.permission.singular'))
                ->searchable()
                ->sortable(),

            TextColumn::make('created_at')
                ->label(__('admin.field.created_at'))
                ->dateTime()
                ->since(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermissions::route('/'),
            'create' => Pages\CreatePermission::route('/create'),
            'edit' => Pages\EditPermission::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

}
