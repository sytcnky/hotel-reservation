<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Group::make()
                ->columnSpanFull()
                ->schema([
                    Grid::make()
                        ->columns([
                            'default' => 1,
                            'lg'      => 12,
                        ])
                        ->gap(6)
                        ->schema([

                            // SOL (8) — Sipariş & Ödeme bilgileri (read-only)
                            Group::make()
                                ->columnSpan([
                                    'default' => 12,
                                    'lg'      => 8,
                                ])
                                ->schema([

                                    /*
                                     * SİPARİŞ BİLGİLERİ
                                     */
                                    Section::make(__('admin.orders.sections.order_info'))
                                        ->schema([
                                            Grid::make()->columns(2)->schema([
                                                TextEntry::make('code')
                                                    ->label(__('admin.orders.form.code'))
                                                    ->state(fn (?Order $record) => $record?->code ?? '-'),

                                                TextEntry::make('created_at')
                                                    ->label(__('admin.orders.form.created_at'))
                                                    ->state(
                                                        fn (?Order $record) => $record?->created_at
                                                            ? $record->created_at->format('d.m.Y H:i')
                                                            : '-'
                                                    ),
                                            ]),

                                            Grid::make()->columns(2)->schema([
                                                TextEntry::make('customer_name')
                                                    ->label(__('admin.orders.form.customer_name'))
                                                    ->state(fn (?Order $record) => $record?->customer_name ?? '-'),

                                                TextEntry::make('customer_email')
                                                    ->label(__('admin.orders.form.customer_email'))
                                                    ->state(fn (?Order $record) => $record?->customer_email ?? '-'),
                                            ]),

                                            Grid::make()->columns(2)->schema([
                                                TextEntry::make('customer_phone')
                                                    ->label(__('admin.orders.form.customer_phone'))
                                                    ->state(fn (?Order $record) => $record?->customer_phone ?? '-'),

                                                TextEntry::make('status_readonly')
                                                    ->label(__('admin.orders.form.status'))
                                                    ->state(function (?Order $record) {
                                                        $status = $record?->status;

                                                        return match ($status) {
                                                            'pending'   => __('admin.orders.status.pending'),
                                                            'confirmed' => __('admin.orders.status.confirmed'),
                                                            'cancelled' => __('admin.orders.status.cancelled'),
                                                            default     => $status ?? '-',
                                                        };
                                                    }),
                                            ]),
                                            TextEntry::make('metadata.customer_note')
                                                ->label(__('admin.orders.form.customer_note'))
                                                ->state(function (?Order $record) {
                                                    if (! $record) {
                                                        return '-';
                                                    }

                                                    $meta = $record->metadata ?? [];
                                                    $note = is_array($meta) ? ($meta['customer_note'] ?? null) : null;

                                                    return $note ?: '-';
                                                }),

                                            // Kurumsal fatura bilgileri
                                            Section::make(__('admin.orders.sections.invoice_info'))
                                                ->schema([
                                                    TextEntry::make('invoice_company')
                                                        ->label(__('admin.orders.form.invoice_company'))
                                                        ->state(fn (?Order $record) => $record->metadata['invoice']['company'] ?? '-'),

                                                    TextEntry::make('invoice_tax_office')
                                                        ->label(__('admin.orders.form.invoice_tax_office'))
                                                        ->state(fn (?Order $record) => $record->metadata['invoice']['tax_office'] ?? '-'),

                                                    TextEntry::make('invoice_tax_no')
                                                        ->label(__('admin.orders.form.invoice_tax_no'))
                                                        ->state(fn (?Order $record) => $record->metadata['invoice']['tax_no'] ?? '-'),

                                                    TextEntry::make('invoice_address')
                                                        ->label(__('admin.orders.form.invoice_address'))
                                                        ->state(fn (?Order $record) => $record->metadata['invoice']['address'] ?? '-'),
                                                ])
                                                ->hidden(function (?Order $record) {
                                                    // Eğer checkbox işaretlenmemişse (is_corporate=false) → gizle
                                                    $inv = $record->metadata['invoice'] ?? [];
                                                    return empty($inv['is_corporate']);
                                                }),
                                        ]),

                                    /*
                                     * ÖDEME BİLGİLERİ
                                     */
                                    Section::make(__('admin.orders.sections.payment_info'))
                                        ->schema([

                                            Grid::make()->columns(2)->schema([
                                                TextEntry::make('payment_status')
                                                    ->label(__('admin.orders.form.payment_status'))
                                                    ->state(function (?Order $record) {
                                                        $status = $record?->payment_status;

                                                        return match ($status) {
                                                            'paid'     => __('admin.orders.payment_status.paid'),
                                                            'unpaid'   => __('admin.orders.payment_status.unpaid'),
                                                            'refunded' => __('admin.orders.payment_status.refunded'),
                                                            default    => $status ?? '-',
                                                        };
                                                    }),

                                                TextEntry::make('currency')
                                                    ->label(__('admin.orders.form.currency'))
                                                    ->state(fn (?Order $record) => $record?->currency ?? '-'),
                                            ]),

                                            // Sipariş Toplamı (kuponsuz brüt)
                                            TextEntry::make('total_amount')
                                                ->label(__('admin.orders.form.total_amount'))
                                                ->state(function (?Order $record) {
                                                    if (! $record || $record->total_amount === null) {
                                                        return '-';
                                                    }

                                                    $amount   = number_format((float) $record->total_amount, 2, ',', '.');
                                                    $currency = strtoupper($record->currency ?? '');

                                                    return trim($amount . ' ' . $currency);
                                                }),

                                            // İndirimler listesi
                                            Section::make(__('admin.orders.sections.discounts')) // "İndirimler"
                                            ->schema([
                                                // Satır satır indirim kalemleri
                                                RepeatableEntry::make('discounts_for_infolist')
                                                    ->hiddenLabel()
                                                    ->schema([
                                                        Grid::make(['default' => 1, 'lg' => 12])
                                                            ->schema([
                                                                // Sol: Tutar
                                                                TextEntry::make('amount')
                                                                    ->hiddenLabel()
                                                                    ->columnSpan(['default' => 12, 'lg' => 3]),

                                                                // Orta: Açıklama (kupon başlığı vb.)
                                                                TextEntry::make('label')
                                                                    ->hiddenLabel()
                                                                    ->columnSpan(['default' => 12, 'lg' => 7]),

                                                                // Sağ: Badge (Kupon / Kampanya)
                                                                TextEntry::make('badge')
                                                                    ->hiddenLabel()
                                                                    ->badge()
                                                                    ->color('primary')
                                                                    ->columnSpan(['default' => 12, 'lg' => 2]),
                                                            ]),
                                                    ])
                                                    ->columns(1)
                                                    // Hiç indirim yoksa tabloyu gizle
                                                    ->hidden(fn ($record, $state) => empty($state)),

                                                // Hiç indirim yoksa gösterilecek mesaj
                                                TextEntry::make('discounts_empty')
                                                    ->hiddenLabel()
                                                    ->state(fn (?Order $record) => __('admin.orders.form.discounts_none')) // "İndirim uygulanmamıştır."
                                                    ->hidden(fn (?Order $record) => ! empty($record?->discounts_for_infolist)),
                                            ])
                                                ->contained(false),

                                            // Toplam indirim (tüm kupon/kampanya indirimlerinin toplamı)
                                            TextEntry::make('discount_total')
                                                ->label(__('admin.orders.form.discount_total')) // Örn: "Toplam İndirim"
                                                ->state(function (?Order $record) {
                                                    if (! $record || ! $record->discount_amount) {
                                                        return '-';
                                                    }

                                                    $amount   = number_format((float) $record->discount_amount, 2, ',', '.');
                                                    $currency = strtoupper($record->currency ?? '');

                                                    return trim($amount . ' ' . $currency);
                                                }),

                                            // Tahsil Edilen Tutar (bugün için: total_amount - discount_amount)
                                            TextEntry::make('payable_total')
                                                ->label(__('admin.orders.form.payable_total'))
                                                ->state(function (?Order $record) {
                                                    if (! $record || $record->total_amount === null) {
                                                        return '-';
                                                    }

                                                    $gross    = (float) $record->total_amount;
                                                    $discount = (float) ($record->discount_amount ?? 0);
                                                    $payable  = max($gross - $discount, 0);

                                                    $amount   = number_format($payable, 2, ',', '.');
                                                    $currency = strtoupper($record->currency ?? '');

                                                    return trim($amount . ' ' . $currency);
                                                }),

                                            Grid::make()->columns(2)->schema([
                                                TextEntry::make('paid_at')
                                                    ->label(__('admin.orders.form.paid_at'))
                                                    ->state(
                                                        fn (?Order $record) => $record?->paid_at
                                                            ? $record->paid_at->format('d.m.Y H:i')
                                                            : '-'
                                                    ),

                                                TextEntry::make('cancelled_at')
                                                    ->label(__('admin.orders.form.cancelled_at'))
                                                    ->state(
                                                        fn (?Order $record) => $record?->cancelled_at
                                                            ? $record->cancelled_at->format('d.m.Y H:i')
                                                            : '-'
                                                    ),
                                            ]),
                                        ]),


                                    /*
                                     * SİPARİŞ KALEMLERİ
                                     */
                                    Section::make(__('admin.orders.sections.items'))
                                        ->schema([
                                            RepeatableEntry::make('items_for_infolist')
                                                ->hiddenLabel()
                                                ->schema([
                                                    Grid::make(['default' => 1, 'lg' => 12])
                                                        ->schema([
                                                            // Sol: Görsel
                                                            ImageEntry::make('image')
                                                                ->hiddenLabel()
                                                                ->circular()
                                                                ->imageHeight(96)
                                                                ->columnSpan(['default' => 12, 'lg' => 2])
                                                                ->hidden(fn ($record, ?string $state) => blank($state)),

                                                            // Sağ: Bilgiler (label / value hizalı)
                                                            Section::make()
                                                                ->schema([
                                                                    Section::make()
                                                                        ->inlineLabel()
                                                                        ->schema([
                                                                            // OTEL
                                                                            TextEntry::make('hotel_name')
                                                                                ->label(__('admin.orders.items.hotel_name'))
                                                                                ->hidden(fn ($record, ?string $state) => blank($state)),
                                                                            TextEntry::make('room_name')
                                                                                ->label(__('admin.orders.items.room_name'))
                                                                                ->hidden(fn ($record, ?string $state) => blank($state)),
                                                                            TextEntry::make('board_type')
                                                                                ->label(__('admin.orders.items.board_type'))
                                                                                ->hidden(fn ($record, ?string $state) => blank($state)),

                                                                            // VİLLA
                                                                            TextEntry::make('villa_name')
                                                                                ->label(__('admin.orders.items.villa_name'))
                                                                                ->hidden(fn ($record, ?string $state) => blank($state)),

                                                                            // TUR
                                                                            TextEntry::make('tour_name')
                                                                                ->label(__('admin.orders.items.tour_name'))
                                                                                ->hidden(fn ($record, ?string $state) => blank($state)),
                                                                            TextEntry::make('date')
                                                                                ->label(__('admin.orders.items.date'))
                                                                                ->hidden(fn ($record, ?string $state) => blank($state)),

                                                                            // TRANSFER
                                                                            TextEntry::make('route')
                                                                                ->label(__('admin.orders.items.route'))
                                                                                ->hidden(fn ($record, ?string $state) => blank($state)),
                                                                            TextEntry::make('vehicle')
                                                                                ->label(__('admin.orders.items.vehicle'))
                                                                                ->hidden(fn ($record, ?string $state) => blank($state)),
                                                                            TextEntry::make('departure_date')
                                                                                ->label(__('admin.orders.items.departure_date'))
                                                                                ->hidden(fn ($record, ?string $state) => blank($state)),
                                                                            TextEntry::make('return_date')
                                                                                ->label(__('admin.orders.items.return_date'))
                                                                                ->hidden(fn ($record, ?string $state) => blank($state)),
                                                                            TextEntry::make('departure_flight')
                                                                                ->label(__('admin.orders.items.departure_flight'))
                                                                                ->hidden(fn ($record, ?string $state) => blank($state)),
                                                                            TextEntry::make('return_flight')
                                                                                ->label(__('admin.orders.items.return_flight'))
                                                                                ->hidden(fn ($record, ?string $state) => blank($state)),

                                                                            // ORTAK ALANLAR
                                                                            TextEntry::make('checkin')
                                                                                ->label(__('admin.orders.items.checkin'))
                                                                                ->hidden(fn ($record, ?string $state) => blank($state)),
                                                                            TextEntry::make('checkout')
                                                                                ->label(__('admin.orders.items.checkout'))
                                                                                ->hidden(fn ($record, ?string $state) => blank($state)),
                                                                            TextEntry::make('pax')
                                                                                ->label(__('admin.orders.items.pax'))
                                                                                ->hidden(fn ($record, ?string $state) => blank($state)),
                                                                            TextEntry::make('paid')
                                                                                ->label(__('admin.orders.items.paid'))
                                                                                ->weight('bold')
                                                                                ->hidden(fn ($record, ?string $state) => blank($state)),
                                                                            TextEntry::make('remaining')
                                                                                ->label(__('admin.orders.items.remaining'))
                                                                                ->hidden(fn ($record, ?string $state) => blank($state)),
                                                                            TextEntry::make('total')
                                                                                ->label(__('admin.orders.items.total'))
                                                                                ->hidden(fn ($record, ?string $state) => blank($state)),
                                                                        ])
                                                                        ->columns(1)
                                                                        ->contained(false),
                                                                ])
                                                                ->columnSpan(['default' => 12, 'lg' => 10])
                                                                ->hiddenLabel(),
                                                        ]),
                                                ])
                                                ->columns(1),
                                        ])
                                        ->contained(false),
                                ]),

                            // SAĞ (4) — Operasyon alanları (editlenebilir)
                            Group::make()
                                ->columnSpan([
                                    'default' => 12,
                                    'lg'      => 4,
                                ])
                                ->schema([
                                    Section::make(__('admin.orders.sections.operations'))
                                        ->schema([
                                            Select::make('status')
                                                ->label(__('admin.orders.form.status'))
                                                ->required()
                                                ->options([
                                                    'pending'   => __('admin.orders.status.pending'),
                                                    'confirmed' => __('admin.orders.status.confirmed'),
                                                    'cancelled' => __('admin.orders.status.cancelled'),
                                                ]),

                                            Textarea::make('note')
                                                ->label(__('admin.orders.form.operation_note'))
                                                ->rows(4),
                                        ]),
                                ]),
                        ]),
                ]),
        ]);
    }
}
