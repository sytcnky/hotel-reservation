<?php

namespace App\Filament\Resources\TourServices;

use App\Filament\Resources\TourServices\Pages\CreateTourService;
use App\Filament\Resources\TourServices\Pages\EditTourService;
use App\Filament\Resources\TourServices\Pages\ListTourServices;
use App\Filament\Resources\TourServices\Schemas\TourServiceForm;
use App\Filament\Resources\TourServices\Tables\TourServicesTable;
use App\Models\TourService;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TourServiceResource extends Resource
{
    protected static ?string $model = TourService::class;

    public static function getNavigationGroup(): ?string { return __('admin.nav.taxonomies'); }
    public static function getNavigationLabel(): string { return __('admin.ent.tour_service.plural'); }
    public static function getModelLabel(): string { return __('admin.ent.tour_service.singular'); }
    public static function getPluralModelLabel(): string { return __('admin.ent.tour_service.plural'); }

    public static function getNavigationBadge(): ?string
    {
        try { return (string) static::getModel()::query()->count(); } catch (\Throwable) { return null; }
    }
    public static function getNavigationBadgeColor(): ?string { return 'primary'; }

    public static function form(Schema $schema): Schema { return TourServiceForm::configure($schema); }
    public static function table(Table $table): Table { return TourServicesTable::configure($table); }

    public static function getPages(): array
    {
        return [
            'index'  => ListTourServices::route('/'),
            'create' => CreateTourService::route('/create'),
            'edit'   => EditTourService::route('/{record}/edit'),
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
