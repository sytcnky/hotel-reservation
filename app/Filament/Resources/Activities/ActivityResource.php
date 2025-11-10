<?php

namespace App\Filament\Resources\Activities;

use App\Filament\Resources\Activities\Pages\ListActivities;
use App\Filament\Resources\Activities\Pages\ViewActivity;
use App\Filament\Resources\Activities\Schemas\ActivityInfolist;
use App\Filament\Resources\Activities\Tables\ActivitiesTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function table(Table $table): Table
    {
        return ActivitiesTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ActivityInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getNavigationGroup(): ?string { return __('admin.nav.settings_group'); }
    public static function getNavigationLabel(): string { return __('admin.activity.plural'); }
    public static function getModelLabel(): string { return __('admin.activity.singular'); }
    public static function getPluralModelLabel(): string { return __('admin.activity.plural'); }
    protected static ?int $navigationSort = 40;

    public static function getPages(): array
    {
        return [
            'index' => ListActivities::route('/'),
            'view' => ViewActivity::route('/{record}'),
        ];
    }
}
