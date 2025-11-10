<?php

namespace App\Filament\Resources\UserResource;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\Schemas\UserForm;
use App\Filament\Resources\UserResource\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.settings_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.user.plural');
    }

    public static function getModelLabel(): string
    {
        return __('admin.user.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.user.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $u = auth()->user();

        return $u?->hasAnyRole(['admin', 'editor']) ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        /** @var class-string<User> $model */
        $model = static::getModel();

        return $model::query()
            ->whereDoesntHave('roles', fn (Builder $q) => $q->where('name', 'customer'));
    }
}
