<?php

namespace App\Filament\Resources\Tours;

use App\Filament\Resources\Tours\Pages\CreateTour;
use App\Filament\Resources\Tours\Pages\EditTour;
use App\Filament\Resources\Tours\Pages\ListTours;
use App\Filament\Resources\Tours\Schemas\TourForm;
use App\Filament\Resources\Tours\Tables\ToursTable;
use App\Models\Tour;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TourResource extends Resource
{
    protected static ?string $model = Tour::class;

    /**
     * Kayıt başlığı accessor üzerinden.
     * Kontrat: fallback yok (name_l).
     */
    protected static ?string $recordTitleAttribute = 'name_l';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.tour_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.tours.plural');
    }

    public static function getModelLabel(): string
    {
        return __('admin.tours.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.tours.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return TourForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ToursTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListTours::route('/'),
            'create' => CreateTour::route('/create'),
            'edit'   => EditTour::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
