<?php

namespace App\Filament\Resources\Villas;

use App\Filament\Resources\Villas\Pages\CreateVilla;
use App\Filament\Resources\Villas\Pages\EditVilla;
use App\Filament\Resources\Villas\Pages\ListVillas;
use App\Filament\Resources\Villas\Schemas\VillaForm;
use App\Filament\Resources\Villas\Tables\VillasTable;
use App\Models\Villa;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class VillaResource extends Resource
{
    protected static ?string $model = Villa::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string { return __('admin.nav.villa_group'); }
    public static function getNavigationLabel(): string { return __('admin.villas.plural'); }
    public static function getModelLabel(): string { return __('admin.villas.singular'); }
    public static function getPluralModelLabel(): string { return __('admin.villas.plural'); }

    public static function getRecordTitle(?Model $record): string
    {
        if (! $record) {
            return '';
        }

        $name = $record->name;

        // JSONB alan (ör. {"tr":"Villa","en":"Villa"}) ise TR/EN öncelikli
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
        return VillaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VillasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            // villa tarafında relation manager yok (oda ilişkisi yok).
        ];
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
        // HotelResource ile aynı pattern
        return static::getModel()::query();
    }
}
