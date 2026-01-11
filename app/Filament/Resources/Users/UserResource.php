<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUser;

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

    public static function shouldRegisterNavigation(): bool
    {
        $u = auth()->user();

        return $u?->hasAnyRole(['admin', 'editor']) ?? false;
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
            'index'  => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit'   => EditUser::route('/{record}/edit'),
        ];
    }

    /**
     * SoftDeletes kullandığı için customer role'lü kullanıcıları admin listeden hariç tutar.
     */
    public static function getEloquentQuery(): Builder
    {
        /** @var class-string<User> $model */
        $model = static::getModel();

        return $model::query()
            ->whereDoesntHave('roles', fn (Builder $q) => $q->where('name', 'customer'));
    }

    /**
     * SoftDeletes binding query standardı.
     */
    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
