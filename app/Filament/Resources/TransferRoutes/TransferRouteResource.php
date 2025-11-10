<?php

namespace App\Filament\Resources\TransferRoutes;

use App\Filament\Resources\TransferRoutes\Pages\CreateTransferRoute;
use App\Filament\Resources\TransferRoutes\Pages\EditTransferRoute;
use App\Filament\Resources\TransferRoutes\Pages\ListTransferRoutes;
use App\Filament\Resources\TransferRoutes\Schemas\TransferRouteForm;
use App\Filament\Resources\TransferRoutes\Tables\TransferRoutesTable;
use App\Models\TransferRoute;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class TransferRouteResource extends Resource
{
    protected static ?string $model = TransferRoute::class;

    public static function getNavigationGroup(): ?string { return __('admin.nav.transfer_group'); }
    public static function getNavigationLabel(): string { return __('admin.routes.plural'); }
    public static function getModelLabel(): string { return __('admin.routes.singular'); }
    public static function getPluralModelLabel(): string { return __('admin.routes.plural'); }

    public static function form(Schema $schema): Schema { return TransferRouteForm::configure($schema); }
    public static function table(Table $table): Table { return TransferRoutesTable::configure($table); }

    public static function getPages(): array
    {
        return [
            'index' => ListTransferRoutes::route('/'),
            'create' => CreateTransferRoute::route('/create'),
            'edit' => EditTransferRoute::route('/{record}/edit'),
        ];
    }
}
