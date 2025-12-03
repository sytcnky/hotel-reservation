<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Models\Coupon;
use App\Models\UserCoupon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;

class UserCouponsRelationManager extends RelationManager
{
    /**
     * User modelindeki ilişki adı.
     *
     * User::userCoupons()
     */
    protected static string $relationship = 'userCoupons';

    /**
     * EditCustomer sayfasındaki sekme başlığı.
     */
    protected static ?string $title = 'Kuponlar';

    /**
     * Kupon atama / düzenleme formu.
     */
    public function form(Schema $schema): Schema
    {
        $base = config('app.locale', 'tr');
        $ui   = app()->getLocale();

        // Kupon seçenekleri: aktif kuponlar, code + localized title
        $couponOptions = Coupon::query()
            ->where('is_active', true)
            ->orderByDesc('id')
            ->get()
            ->mapWithKeys(function (Coupon $coupon) use ($ui, $base) {
                $titleData = (array) ($coupon->title ?? []);

                $title = $titleData[$ui]
                    ?? $titleData[$base]
                    ?? (string) (array_values($titleData)[0] ?? '');

                $code  = $coupon->code ?: ('#' . $coupon->id);
                $label = $title ? sprintf('%s — %s', $code, $title) : $code;

                return [$coupon->id => $label];
            })
            ->all();

        return $schema->schema([
            Select::make('coupon_id')
                ->label('Kupon')
                ->options($couponOptions)
                ->searchable()
                ->native(false)
                ->required(),

            DateTimePicker::make('assigned_at')
                ->label('Atanma tarihi')
                ->seconds(false)
                ->native(false)
                ->default(fn () => now())
                ->required(),

            DateTimePicker::make('expires_at')
                ->label('Bitiş tarihi')
                ->seconds(false)
                ->native(false),
        ]);
    }

    /**
     * Kullanıcı kuponları tablosu.
     */
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('coupon.code')
                    ->label('Kod')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('coupon_title')
                    ->label('Başlık')
                    ->state(function (UserCoupon $record): string {
                        $coupon = $record->coupon;

                        if (! $coupon instanceof Coupon) {
                            return '';
                        }

                        $base  = config('app.locale', 'tr');
                        $ui    = app()->getLocale();
                        $data  = (array) ($coupon->title ?? []);
                        $title = $data[$ui]
                            ?? $data[$base]
                            ?? (string) (array_values($data)[0] ?? '');

                        return $title;
                    })
                    ->wrap(),

                TextColumn::make('assigned_at')
                    ->label('Atandı')
                    ->since(),

                TextColumn::make('expires_at')
                    ->label('Bitiş')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('used_count')
                    ->label('Kullanım')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Kupon Ata')
                    ->mutateDataUsing(function (array $data): array {
                        // assigned_at boş bırakılırsa "şimdi" kabul edilsin
                        $data['assigned_at'] = $data['assigned_at'] ?? now();

                        // Tekil panel işlemi = manuel kaynak
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

    /**
     * Relation manager başlığında kayıt başlığı olarak ne görüneceği.
     */
    public static function getRecordTitle(Model $record): string
    {
        if (! $record instanceof UserCoupon) {
            return (string) $record->getKey();
        }

        $code = $record->coupon?->code;

        return $code
            ? sprintf('Kupon #%s', $code)
            : sprintf('Kupon #%d', $record->getKey());
    }
}
