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
use Illuminate\Database\Eloquent\Model;

class TourResource extends Resource
{
    protected static ?string $model = Tour::class;

    public static function getNavigationGroup(): ?string { return __('admin.nav.tour_group'); }
    public static function getNavigationLabel(): string { return __('admin.tours.plural'); }
    public static function getModelLabel(): string { return __('admin.tours.singular'); }
    public static function getPluralModelLabel(): string { return __('admin.tours.plural'); }

    protected static ?string $recordTitleAttribute = 'name';

    public static function getRecordTitle(?Model $record): string
    {
        if (! $record) return '';
        $v = $record->name;
        if (is_array($v)) return $v[app()->getLocale()] ?? reset($v) ?: '';
        if (is_string($v) && str_starts_with($v, '{')) {
            $d = json_decode($v, true);
            return $d[app()->getLocale()] ?? reset($d) ?: '';
        }
        return (string) $v;
    }

    public static function form(Schema $schema): Schema
    {
        return TourForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ToursTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()->with('category');
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
        return static::getModel()::query();
    }
}
