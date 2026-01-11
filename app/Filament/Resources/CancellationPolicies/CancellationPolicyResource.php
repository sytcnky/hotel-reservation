<?php

namespace App\Filament\Resources\CancellationPolicies;

use App\Filament\Resources\CancellationPolicies\Pages\CreateCancellationPolicy;
use App\Filament\Resources\CancellationPolicies\Pages\EditCancellationPolicy;
use App\Filament\Resources\CancellationPolicies\Pages\ListCancellationPolicies;
use App\Filament\Resources\CancellationPolicies\Schemas\CancellationPolicyForm;
use App\Filament\Resources\CancellationPolicies\Tables\CancellationPoliciesTable;
use App\Models\CancellationPolicy;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Throwable;

class CancellationPolicyResource extends Resource
{
    protected static ?string $model = CancellationPolicy::class;

    protected static ?string $recordTitleAttribute = 'name_l';

    public static function getNavigationGroup(): ?string { return __('admin.nav.taxonomies'); }
    public static function getNavigationLabel(): string { return __('admin.ent.cancellation_policy.plural'); }
    public static function getModelLabel(): string { return __('admin.ent.cancellation_policy.singular'); }
    public static function getPluralModelLabel(): string { return __('admin.ent.cancellation_policy.plural'); }

    public static function getNavigationBadge(): ?string
    {
        try {
            return (string) static::getModel()::query()->count();
        } catch (Throwable) {
            return null;
        }
    }

    public static function form(Schema $schema): Schema
    {
        return CancellationPolicyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CancellationPoliciesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListCancellationPolicies::route('/'),
            'create' => CreateCancellationPolicy::route('/create'),
            'edit'   => EditCancellationPolicy::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return static::getModel()::query()->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
    }
}
