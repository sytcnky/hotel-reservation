<?php

namespace App\Filament\Resources\PaymentOptions;

use App\Filament\Resources\PaymentOptions\Pages\CreatePaymentOption;
use App\Filament\Resources\PaymentOptions\Pages\EditPaymentOption;
use App\Filament\Resources\PaymentOptions\Pages\ListPaymentOptions;
use App\Filament\Resources\PaymentOptions\Schemas\PaymentOptionForm;
use App\Filament\Resources\PaymentOptions\Tables\PaymentOptionsTable;
use App\Models\PaymentOption;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentOptionResource extends Resource
{
    protected static ?string $model = PaymentOption::class;

    public static function getNavigationGroup(): ?string { return __('admin.nav.taxonomies'); }
    public static function getNavigationLabel(): string { return __('admin.ent.payment_option.plural'); }
    public static function getModelLabel(): string { return __('admin.ent.payment_option.singular'); }
    public static function getPluralModelLabel(): string { return __('admin.ent.payment_option.plural'); }

    public static function getNavigationBadge(): ?string
    {
        try {
            return (string) static::getModel()::query()->count();
        } catch (\Throwable) {
            return null;
        }
    }

    public static function form(Schema $schema): Schema
    {
        return PaymentOptionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentOptionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPaymentOptions::route('/'),
            'create' => CreatePaymentOption::route('/create'),
            'edit' => EditPaymentOption::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
