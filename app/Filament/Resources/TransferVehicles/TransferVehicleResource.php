<?php

namespace App\Filament\Resources\TransferVehicles;

use App\Filament\Resources\TransferVehicles\Pages\CreateTransferVehicle;
use App\Filament\Resources\TransferVehicles\Pages\EditTransferVehicle;
use App\Filament\Resources\TransferVehicles\Pages\ListTransferVehicles;
use App\Filament\Resources\TransferVehicles\Schemas\TransferVehicleForm;
use App\Filament\Resources\TransferVehicles\Tables\TransferVehiclesTable;
use App\Models\TransferVehicle;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class TransferVehicleResource extends Resource
{
    protected static ?string $model = TransferVehicle::class;

    public static function getNavigationGroup(): ?string { return __('admin.nav.transfer_group'); }
    public static function getNavigationLabel(): string { return __('admin.vehicles.plural'); }
    public static function getModelLabel(): string { return __('admin.vehicles.singular'); }
    public static function getPluralModelLabel(): string { return __('admin.vehicles.plural'); }

    public static function form(Schema $schema): Schema
    {
        return TransferVehicleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TransferVehiclesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTransferVehicles::route('/'),
            'create' => CreateTransferVehicle::route('/create'),
            'edit' => EditTransferVehicle::route('/{record}/edit'),
        ];
    }
}
