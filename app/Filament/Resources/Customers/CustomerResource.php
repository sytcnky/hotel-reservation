<?php

namespace App\Filament\Resources\Customers;

use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Customers\Schemas\CustomerForm;
use App\Filament\Resources\Customers\Tables\CustomersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.settings_group');
    }

    public static function getNavigationLabel(): string
    {
        return 'Müşteriler';
    }

    public static function getModelLabel(): string
    {
        return 'Müşteri';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Müşteriler';
    }

    public static function form(Schema $schema): Schema
    {
        return CustomerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'edit'   => EditCustomer::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        /** @var class-string<User> $model */
        $model = static::getModel();

        return $model::query()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->whereHas('roles', fn (Builder $q) => $q->where('name', 'customer'));
    }
}
