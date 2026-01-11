<?php

namespace App\Filament\Resources\BoardTypes;

use App\Filament\Resources\BoardTypes\Pages\CreateBoardType;
use App\Filament\Resources\BoardTypes\Pages\EditBoardType;
use App\Filament\Resources\BoardTypes\Pages\ListBoardTypes;
use App\Filament\Resources\BoardTypes\Schemas\BoardTypeForm;
use App\Filament\Resources\BoardTypes\Tables\BoardTypesTable;
use App\Models\BoardType;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Throwable;

class BoardTypeResource extends Resource
{
    protected static ?string $model = BoardType::class;

    public static function getNavigationGroup(): ?string { return __('admin.nav.taxonomies'); }
    public static function getNavigationLabel(): string { return __('admin.ent.board_type.plural'); }
    public static function getModelLabel(): string { return __('admin.ent.board_type.singular'); }
    public static function getPluralModelLabel(): string { return __('admin.ent.board_type.plural'); }

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
        return BoardTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BoardTypesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListBoardTypes::route('/'),
            'create' => CreateBoardType::route('/create'),
            'edit'   => EditBoardType::route('/{record}/edit'),
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
