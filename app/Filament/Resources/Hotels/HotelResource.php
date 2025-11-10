<?php

namespace App\Filament\Resources\Hotels;

use App\Filament\Resources\Hotels\Pages\CreateHotel;
use App\Filament\Resources\Hotels\Pages\EditHotel;
use App\Filament\Resources\Hotels\Pages\ListHotels;
use App\Filament\Resources\Hotels\RelationManagers\RoomsRelationManager;
use App\Filament\Resources\Hotels\Schemas\HotelForm;
use App\Filament\Resources\Hotels\Tables\HotelsTable;
use App\Models\Hotel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HotelResource extends Resource
{
    protected static ?string $model = Hotel::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string { return __('admin.nav.hotel_group'); }
    public static function getNavigationLabel(): string { return __('admin.hotels.plural'); }
    public static function getModelLabel(): string { return __('admin.hotels.singular'); }
    public static function getPluralModelLabel(): string { return __('admin.hotels.plural'); }

    public static function getRecordTitle(?Model $record): string
    {
        if (! $record) {
            return '';
        }

        $name = $record->name;

        // JSONB alan (ör. {"tr":"Otel","en":"Hotel"}) ise Türkçe veya İngilizce değeri çek
        if (is_array($name)) {
            return $name['tr'] ?? $name['en'] ?? '';
        }

        // JSON string olarak saklanıyorsa decode et
        if (is_string($name) && str_starts_with($name, '{')) {
            $decoded = json_decode($name, true);

            return $decoded['tr'] ?? $decoded['en'] ?? '';
        }

        return (string) $name;
    }

    public static function form(Schema $schema): Schema
    {
        return HotelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HotelsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RoomsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHotels::route('/'),
            'create' => CreateHotel::route('/create'),
            'edit' => EditHotel::route('/{record}/edit'),
        ];
    }

    // (Opsiyonel) Kayıt binding query override’ı gerekiyorsa:
    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return static::getModel()::query();
    }
}
