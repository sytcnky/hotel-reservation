<?php

namespace App\Filament\Resources\BeachTypes;

use App\Filament\Resources\BeachTypes\Pages\CreateBeachType;
use App\Filament\Resources\BeachTypes\Pages\EditBeachType;
use App\Filament\Resources\BeachTypes\Pages\ListBeachTypes;
use App\Filament\Resources\BeachTypes\Schemas\BeachTypeForm;
use App\Filament\Resources\BeachTypes\Tables\BeachTypesTable;
use App\Models\BeachType;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Throwable;

class BeachTypeResource extends Resource
{
    protected static ?string $model = BeachType::class;

    public static function getNavigationGroup(): ?string { return __('admin.nav.taxonomies'); }
    public static function getNavigationLabel(): string { return __('admin.ent.beach_type.plural'); }
    public static function getModelLabel(): string { return __('admin.ent.beach_type.singular'); }
    public static function getPluralModelLabel(): string { return __('admin.ent.beach_type.plural'); }

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
        return BeachTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BeachTypesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBeachTypes::route('/'),
            'create' => CreateBeachType::route('/create'),
            'edit' => EditBeachType::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        $query = static::getModel()::query();

        if (in_array(SoftDeletingScope::class, class_uses_recursive(static::$model))) {
            $query->withoutGlobalScopes([SoftDeletingScope::class]);
        }

        return $query;
    }
}
