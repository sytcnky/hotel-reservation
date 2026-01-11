<?php

namespace App\Filament\Resources\StarRatings;

use App\Filament\Resources\StarRatings\Pages\CreateStarRating;
use App\Filament\Resources\StarRatings\Pages\EditStarRating;
use App\Filament\Resources\StarRatings\Pages\ListStarRatings;
use App\Filament\Resources\StarRatings\Schemas\StarRatingForm;
use App\Filament\Resources\StarRatings\Tables\StarRatingsTable;
use App\Models\StarRating;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StarRatingResource extends Resource
{
    protected static ?string $model = StarRating::class;

    public static function getNavigationGroup(): ?string { return __('admin.nav.taxonomies'); }
    public static function getNavigationLabel(): string { return __('admin.ent.star_rating.plural'); }
    public static function getModelLabel(): string { return __('admin.ent.star_rating.singular'); }
    public static function getPluralModelLabel(): string { return __('admin.ent.star_rating.plural'); }

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
        return StarRatingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StarRatingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStarRatings::route('/'),
            'create' => CreateStarRating::route('/create'),
            'edit' => EditStarRating::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
