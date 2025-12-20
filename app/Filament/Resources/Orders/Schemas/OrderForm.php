<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use App\Models\RefundAttempt;
use App\Services\RefundService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;

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
                            // SOL (8)
                            Group::make()
                                ->columnSpan([
                                    'default' => 12,
                                    'lg'      => 8,
                                ])
                                ->schema([
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

                                                TextEntry::make('customer_type')
                                                    ->label(__('admin.orders.table.type'))
                                                    ->badge()
                                                    ->formatStateUsing(fn (?string $state) => $state === 'guest'
                                                        ? __('admin.orders.table.guest')
                                                        : __('admin.orders.table.member')
                                                    )
                                                    ->color(fn (?string $state) => $state === 'guest' ? 'gray' : 'primary'),

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
                                                    ->badge()
                                                    ->state(function (?Order $record) {
                                                        $meta = Order::statusMeta($record?->status);
                                                        return $meta['label_key'] ? __($meta['label_key']) : (string) ($meta['label'] ?? '-');
                                                    })
                                                    ->color(function (?Order $record) {
                                                        $meta = Order::statusMeta($record?->status);
                                                        return (string) ($meta['filament_color'] ?? 'gray');
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
                                                    $inv = $record->metadata['invoice'] ?? [];
                                                    return empty($inv['is_corporate']);
                                                }),
                                        ]),

                                    Section::make(__('admin.orders.sections.payment_info'))
                                        ->schema([
                                            Grid::make()->columns(2)->schema([
                                                TextEntry::make('payment_status')
                                                    ->label(__('admin.orders.form.payment_status'))
                                                    ->state(function (?Order $record) {
                                                        $status = $record?->payment_status;

                                                        return match ($status) {
                                                            'pending_payment' => __('admin.orders.payment_status.pending_payment'),
                                                            'paid'            => __('admin.orders.payment_status.paid'),
                                                            'cancelled'       => __('admin.orders.payment_status.cancelled'),
                                                            'refunded'        => __('admin.orders.payment_status.refunded'),
                                                            default           => $status ?? '-',
                                                        };
                                                    }),

                                                TextEntry::make('currency')
                                                    ->label(__('admin.orders.form.currency'))
                                                    ->state(fn (?Order $record) => $record?->currency ?? '-'),
                                            ]),

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

                                            Section::make(__('admin.orders.sections.discounts'))
                                                ->schema([
                                                    RepeatableEntry::make('discounts_for_infolist')
                                                        ->hiddenLabel()
                                                        ->schema([
                                                            Grid::make(['default' => 1, 'lg' => 12])
                                                                ->schema([
                                                                    TextEntry::make('amount')
                                                                        ->hiddenLabel()
                                                                        ->columnSpan(['default' => 12, 'lg' => 3]),

                                                                    TextEntry::make('label')
                                                                        ->hiddenLabel()
                                                                        ->columnSpan(['default' => 12, 'lg' => 7]),

                                                                    TextEntry::make('badge')
                                                                        ->hiddenLabel()
                                                                        ->badge()
                                                                        ->color('primary')
                                                                        ->columnSpan(['default' => 12, 'lg' => 2]),
                                                                ]),
                                                        ])
                                                        ->columns(1)
                                                        ->hidden(fn ($record, $state) => empty($state)),

                                                    TextEntry::make('discounts_empty')
                                                        ->hiddenLabel()
                                                        ->state(fn (?Order $record) => __('admin.orders.form.discounts_none'))
                                                        ->hidden(fn (?Order $record) => ! empty($record?->discounts_for_infolist)),
                                                ])
                                                ->contained(false),

                                            TextEntry::make('discount_total')
                                                ->label(__('admin.orders.form.discount_total'))
                                                ->state(function (?Order $record) {
                                                    if (! $record || ! $record->discount_amount) {
                                                        return '-';
                                                    }

                                                    $amount   = number_format((float) $record->discount_amount, 2, ',', '.');
                                                    $currency = strtoupper($record->currency ?? '');

                                                    return trim($amount . ' ' . $currency);
                                                }),

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

                                    Section::make(__('admin.orders.sections.items'))
                                        ->schema([
                                            RepeatableEntry::make('items_for_infolist')
                                                ->hiddenLabel()
                                                ->schema([
                                                    Grid::make(['default' => 1, 'lg' => 12])
                                                        ->schema([
                                                            ImageEntry::make('image')
                                                                ->hiddenLabel()
                                                                ->circular()
                                                                ->imageHeight(96)
                                                                ->columnSpan(['default' => 12, 'lg' => 2])
                                                                ->hidden(fn ($record, ?string $state) => blank($state)),

                                                            Section::make()
                                                                ->schema([
                                                                    Section::make()
                                                                        ->inlineLabel()
                                                                        ->schema([
                                                                            TextEntry::make('hotel_name')
                                                                                ->label(__('admin.orders.items.hotel_name'))
                                                                                ->hidden(fn ($record, ?string $state) => blank($state)),
                                                                            TextEntry::make('room_name')
                                                                                ->label(__('admin.orders.items.room_name'))
                                                                                ->hidden(fn ($record, ?string $state) => blank($state)),
                                                                            TextEntry::make('board_type')
                                                                                ->label(__('admin.orders.items.board_type'))
                                                                                ->hidden(fn ($record, ?string $state) => blank($state)),

                                                                            TextEntry::make('villa_name')
                                                                                ->label(__('admin.orders.items.villa_name'))
                                                                                ->hidden(fn ($record, ?string $state) => blank($state)),

                                                                            TextEntry::make('tour_name')
                                                                                ->label(__('admin.orders.items.tour_name'))
                                                                                ->hidden(fn ($record, ?string $state) => blank($state)),
                                                                            TextEntry::make('date')
                                                                                ->label(__('admin.orders.items.date'))
                                                                                ->hidden(fn ($record, ?string $state) => blank($state)),

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

                                    Section::make(__('admin.payment_attempts.sections.refunds'))
                                        ->visible(function (?Order $record) {
                                            if (! $record) {
                                                return false;
                                            }

                                            return (bool) $record->successfulPaymentAttempt();
                                        })
                                        ->description(function (?Order $record) {
                                            if (! $record) {
                                                return null;
                                            }

                                            $attempt = $record->successfulPaymentAttempt();
                                            if (! $attempt) {
                                                return null;
                                            }

                                            $successSum = (float) RefundAttempt::query()
                                                ->where('payment_attempt_id', $attempt->id)
                                                ->where('status', RefundAttempt::STATUS_SUCCESS)
                                                ->sum('amount');

                                            if ($successSum <= 0) {
                                                return null;
                                            }

                                            $remaining = max(((float) $attempt->amount) - $successSum, 0);
                                            $cur = strtoupper((string) $attempt->currency);

                                            return
                                                'İade edilen: ' . number_format($successSum, 2, ',', '.') . ' ' . $cur .
                                                '  Kalan: ' . number_format($remaining, 2, ',', '.') . ' ' . $cur;
                                        })
                                        ->afterHeader([
                                            Action::make('refund')
                                                ->label(__('admin.payment_attempts.actions.refund'))
                                                ->color('danger')
                                                ->icon('heroicon-o-arrow-uturn-left')
                                                ->visible(function (?Order $record) {
                                                    if (! $record) {
                                                        return false;
                                                    }

                                                    $attempt = $record->successfulPaymentAttempt();
                                                    if (! $attempt) {
                                                        return false;
                                                    }

                                                    $refunded = (float) RefundAttempt::query()
                                                        ->where('payment_attempt_id', $attempt->id)
                                                        ->where('status', RefundAttempt::STATUS_SUCCESS)
                                                        ->sum('amount');

                                                    return ((float) $attempt->amount - $refunded) > 0;
                                                })
                                                ->schema([
                                                    TextInput::make('amount')
                                                        ->label(__('admin.payment_attempts.fields.refund_amount'))
                                                        ->numeric()
                                                        ->required()
                                                        ->default(function (Order $record) {
                                                            $attempt = $record->successfulPaymentAttempt();
                                                            if (! $attempt) {
                                                                return 0;
                                                            }

                                                            $refunded = (float) RefundAttempt::query()
                                                                ->where('payment_attempt_id', $attempt->id)
                                                                ->where('status', RefundAttempt::STATUS_SUCCESS)
                                                                ->sum('amount');

                                                            return max(((float) $attempt->amount) - $refunded, 0);
                                                        }),

                                                    Textarea::make('reason')
                                                        ->label(__('admin.payment_attempts.fields.refund_reason'))
                                                        ->rows(3),
                                                ])
                                                ->action(function (array $data, Order $record) {
                                                    try {
                                                        $attempt = $record->successfulPaymentAttempt();

                                                        if (! $attempt) {
                                                            throw new \RuntimeException('Bu sipariş için başarılı ödeme bulunamadı.');
                                                        }

                                                        $admin = auth()->user();

                                                        $initiatorRole = null;
                                                        if ($admin && method_exists($admin, 'getRoleNames')) {
                                                            $roles = $admin->getRoleNames()->values()->all();
                                                            $initiatorRole = $roles[0] ?? null;
                                                        }

                                                        $meta = [
                                                            'initiator_user_id' => $admin?->id,
                                                            'initiator_name'    => $admin?->name,
                                                            'initiator_role'    => $initiatorRole,
                                                        ];

                                                        app(RefundService::class)->refundPayment(
                                                            order: $record,
                                                            paymentAttempt: $attempt,
                                                            amount: (float) $data['amount'],
                                                            reason: $data['reason'] ?? null,
                                                            meta: $meta,
                                                        );

                                                        Notification::make()
                                                            ->title(__('admin.payment_attempts.actions.refund_started'))
                                                            ->success()
                                                            ->send();
                                                    } catch (\Throwable $e) {
                                                        Notification::make()
                                                            ->title(__('admin.payment_attempts.actions.refund_failed'))
                                                            ->body($e->getMessage())
                                                            ->danger()
                                                            ->send();
                                                    }
                                                }),
                                        ])
                                        ->schema([
                                            RepeatableEntry::make('refunds_for_infolist')
                                                ->hiddenLabel()
                                                ->table([
                                                    TableColumn::make(__('admin.payment_attempts.fields.name')),
                                                    TableColumn::make(__('admin.payment_attempts.fields.role')),
                                                    TableColumn::make(__('admin.payment_attempts.fields.description')),
                                                    TableColumn::make(__('admin.payment_attempts.fields.date')),
                                                    TableColumn::make(__('admin.payment_attempts.fields.total')),
                                                ])
                                                ->schema([
                                                    TextEntry::make('name')->hiddenLabel(),
                                                    TextEntry::make('badge')->badge()->hiddenLabel(),
                                                    TextEntry::make('reason')->placeholder('-')->hiddenLabel(),
                                                    TextEntry::make('time')->hiddenLabel(),
                                                    TextEntry::make('amount')->hiddenLabel(),
                                                ])
                                                ->hidden(fn ($record, $state) => empty($state)),
                                        ])
                                        ->contained(false),
                                ]),

                            // SAĞ (4)
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
                                                ->options(Order::filamentStatusOptions())
                                                ->disabled(fn (?Order $record) => $record?->status === Order::STATUS_COMPLETED),
                                        ]),
                                ]),
                        ]),
                ]),
        ]);
    }
}
