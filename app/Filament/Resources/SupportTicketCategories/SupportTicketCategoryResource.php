<?php

namespace App\Filament\Resources\SupportTicketCategories;

use App\Filament\Resources\SupportTicketCategories\Pages\CreateSupportTicketCategory;
use App\Filament\Resources\SupportTicketCategories\Pages\EditSupportTicketCategory;
use App\Filament\Resources\SupportTicketCategories\Pages\ListSupportTicketCategories;
use App\Filament\Resources\SupportTicketCategories\Schemas\SupportTicketCategoryForm;
use App\Filament\Resources\SupportTicketCategories\Tables\SupportTicketCategoriesTable;
use App\Models\SupportTicketCategory;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SupportTicketCategoryResource extends Resource
{
    protected static ?string $model = SupportTicketCategory::class;

    public static function getNavigationGroup(): ?string { return __('admin.nav.taxonomies'); }
    public static function getNavigationLabel(): string { return __('admin.ent.support_ticket_category.plural'); }
    public static function getModelLabel(): string { return __('admin.ent.support_ticket_category.singular'); }
    public static function getPluralModelLabel(): string { return __('admin.ent.support_ticket_category.plural'); }

    public static function getNavigationBadge(): ?string
    {
        try {
            return (string) static::getModel()::query()->count();
        } catch (\Throwable) {
            return null;
        }
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    /** canonical (v4) */
    public static function form(Schema $schema): Schema
    {
        return SupportTicketCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SupportTicketCategoriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSupportTicketCategories::route('/'),
            'create' => CreateSupportTicketCategory::route('/create'),
            'edit' => EditSupportTicketCategory::route('/{record}/edit'),
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
