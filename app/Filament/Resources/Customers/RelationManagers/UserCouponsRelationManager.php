<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Models\Coupon;
use App\Models\UserCoupon;
use App\Support\Date\DatePresenter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UserCouponsRelationManager extends RelationManager
{
    protected static string $relationship = 'userCoupons';

    protected static ?string $title = null;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.coupons.plural');
    }

    public function form(Schema $schema): Schema
    {
        $uiLocale = app()->getLocale();

        $couponOptions = Coupon::query()
            ->where('is_active', true)
            ->orderByDesc('id')
            ->get()
            ->mapWithKeys(function (Coupon $coupon) use ($uiLocale) {
                $titleData = (array) ($coupon->title ?? []);
                $title = (string) ($titleData[$uiLocale] ?? '');

                $code  = $coupon->code ?: ('#' . $coupon->id);
                $label = $title !== '' ? sprintf('%s — %s', $code, $title) : $code;

                return [$coupon->id => $label];
            })
            ->all();

        return $schema->schema([
            Select::make('coupon_id')
                ->label(__('admin.coupons.form.title'))
                ->options($couponOptions)
                ->searchable()
                ->native(false)
                ->required(),

            DateTimePicker::make('assigned_at')
                ->label(__('admin.coupons.form.valid_from'))
                ->seconds(false)
                ->native(false)
                ->default(fn () => now())
                ->required(),

            DateTimePicker::make('expires_at')
                ->label(__('admin.coupons.form.valid_until'))
                ->seconds(false)
                ->native(false),
        ]);
    }

    public function table(Table $table): Table
    {
        $uiLocale = app()->getLocale();

        return $table
            ->columns([
                TextColumn::make('coupon.code')
                    ->label(__('admin.coupons.table.code'))
                    ->sortable(),

                TextColumn::make('coupon_title')
                    ->label(__('admin.coupons.table.title'))
                    ->state(function (UserCoupon $record) use ($uiLocale): string {
                        $coupon = $record->coupon;

                        if (! $coupon instanceof Coupon) {
                            return '';
                        }

                        $data = (array) ($coupon->title ?? []);

                        return (string) ($data[$uiLocale] ?? '');
                    })
                    ->wrap(),

                TextColumn::make('assigned_at')
                    ->label(__('admin.coupons.table.created_at'))
                    ->since(),

                TextColumn::make('expires_at')
                    ->label(__('admin.coupons.form.valid_until'))
                    ->formatStateUsing(fn ($state) => DatePresenter::humanDateTimeShort($state))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('used_count')
                    ->label(__('admin.coupons.sections.usage'))
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('admin.coupons.form.assign_coupon'))
                    ->mutateDataUsing(function (array $data): array {
                        $data['assigned_at'] = $data['assigned_at'] ?? now();
                        $data['source'] = 'manual';

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRecordTitle(Model $record): string
    {
        if (! $record instanceof UserCoupon) {
            return (string) $record->getKey();
        }

        $code = $record->coupon?->code;

        return $code
            ? sprintf('%s #%s', __('admin.user_coupons.record_title'), $code)
            : sprintf('%s #%d', __('admin.user_coupons.record_title'), $record->getKey());
    }
}
