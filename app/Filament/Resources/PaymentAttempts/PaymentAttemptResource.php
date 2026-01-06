<?php

namespace App\Filament\Resources\PaymentAttempts;

use App\Filament\Resources\PaymentAttempts\Pages\EditPaymentAttempt;
use App\Filament\Resources\PaymentAttempts\Pages\ListPaymentAttempts;
use App\Filament\Resources\PaymentAttempts\Schemas\PaymentAttemptForm;
use App\Filament\Resources\PaymentAttempts\Tables\PaymentAttemptsTable;
use App\Models\PaymentAttempt;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentAttemptResource extends Resource
{
    protected static ?string $model = PaymentAttempt::class;

    public static function getNavigationGroup(): ?string { return __('admin.nav.order_group'); }
    public static function getNavigationLabel(): string { return __('admin.payment_attempts.plural'); }
    public static function getModelLabel(): string { return __('admin.payment_attempts.singular'); }
    public static function getPluralModelLabel(): string { return __('admin.payment_attempts.plural'); }

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
        return PaymentAttemptForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentAttemptsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPaymentAttempts::route('/'),
            'edit'  => EditPaymentAttempt::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
