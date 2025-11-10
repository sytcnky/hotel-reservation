<?php

namespace App\Filament\Resources\ViewTypes;

use App\Filament\Resources\ViewTypes\Pages\CreateViewType;
use App\Filament\Resources\ViewTypes\Pages\EditViewType;
use App\Filament\Resources\ViewTypes\Pages\ListViewTypes;
use App\Filament\Resources\ViewTypes\Schemas\ViewTypeForm;
use App\Filament\Resources\ViewTypes\Tables\ViewTypesTable;
use App\Models\ViewType;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ViewTypeResource extends Resource
{
    protected static ?string $model = ViewType::class;

    public static function getNavigationGroup(): ?string { return __('admin.nav.taxonomies'); }
    public static function getNavigationLabel(): string { return __('admin.ent.view_type.plural'); }
    public static function getModelLabel(): string { return __('admin.ent.view_type.singular'); }
    public static function getPluralModelLabel(): string { return __('admin.ent.view_type.plural'); }

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
        return ViewTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ViewTypesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListViewTypes::route('/'),
            'create' => CreateViewType::route('/create'),
            'edit' => EditViewType::route('/{record}/edit'),
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
