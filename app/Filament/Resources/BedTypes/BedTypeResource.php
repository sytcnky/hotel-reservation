<?php

namespace App\Filament\Resources\BedTypes;

use App\Filament\Resources\BedTypes\Pages\CreateBedType;
use App\Filament\Resources\BedTypes\Pages\EditBedType;
use App\Filament\Resources\BedTypes\Pages\ListBedTypes;
use App\Filament\Resources\BedTypes\Schemas\BedTypeForm;
use App\Filament\Resources\BedTypes\Tables\BedTypesTable;
use App\Models\BedType;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Throwable;

class BedTypeResource extends Resource
{
    protected static ?string $model = BedType::class;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.taxonomies');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.ent.bed_type.plural');
    }

    public static function getModelLabel(): string
    {
        return __('admin.ent.bed_type.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.ent.bed_type.plural');
    }

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
        return BedTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BedTypesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListBedTypes::route('/'),
            'create' => CreateBedType::route('/create'),
            'edit'   => EditBedType::route('/{record}/edit'),
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
