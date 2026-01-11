<?php

namespace App\Filament\Resources\Villas;

use App\Filament\Resources\Villas\Pages\CreateVilla;
use App\Filament\Resources\Villas\Pages\EditVilla;
use App\Filament\Resources\Villas\Pages\ListVillas;
use App\Filament\Resources\Villas\Schemas\VillaForm;
use App\Filament\Resources\Villas\Tables\VillasTable;
use App\Models\Villa;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VillaResource extends Resource
{
    protected static ?string $model = Villa::class;

    /**
     * Filament kayıt başlığı accessor üzerinden.
     * Kontrat: fallback yok (name_l sadece UI locale okur).
     */
    protected static ?string $recordTitleAttribute = 'name_l';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.villa_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.villas.plural');
    }

    public static function getModelLabel(): string
    {
        return __('admin.villas.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.villas.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return VillaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VillasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListVillas::route('/'),
            'create' => CreateVilla::route('/create'),
            'edit'   => EditVilla::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
