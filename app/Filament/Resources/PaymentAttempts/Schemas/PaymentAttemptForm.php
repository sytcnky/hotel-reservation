<?php

namespace App\Filament\Resources\PaymentAttempts\Schemas;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\PaymentAttempt;
use App\Models\RefundAttempt;
use App\Services\RefundService;
use App\Support\Currency\CurrencyPresenter;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentAttemptForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Group::make()
                ->columnSpanFull()
                ->schema([
                    Grid::make()
                        ->columns(['default' => 1, 'lg' => 12])
                        ->gap(6)
                        ->schema([
                            // Ödeme bilgileri (read-only)
                            Group::make()
                                ->columnSpan(['default' => 12, 'lg' => 12])
                                ->schema([
                                    Section::make(__('admin.payment_attempts.sections.payment_info'))
                                        ->schema([
                                            Grid::make()->columns(4)->schema([
                                                TextEntry::make('id')
                                                    ->label(__('admin.payment_attempts.fields.id'))
                                                    ->state(fn (?PaymentAttempt $record) => $record?->id ?? '-'),

                                                TextEntry::make('checkoutSession.code')
                                                    ->label(__('admin.payment_attempts.fields.checkout'))
                                                    ->state(fn (?PaymentAttempt $record) => $record?->checkoutSession?->code ?? '-')
                                                    ->url(function (?PaymentAttempt $record) {
                                                        if (! $record?->checkout_session_id) {
                                                            return null;
                                                        }

                                                        // Ödeme sayfasına gitmek istersen:
                                                        // return localized_route('payment', ['code' => $record->checkoutSession->code]);

                                                        return null;
                                                    }),

                                                TextEntry::make('order.code')
                                                    ->label(__('admin.payment_attempts.fields.order'))
                                                    ->state(fn (?PaymentAttempt $record) => $record?->order?->code ?? '-')
                                                    ->url(function (?PaymentAttempt $record) {
                                                        if (! $record?->order_id) {
                                                            return null;
                                                        }

                                                        return OrderResource::getUrl('edit', ['record' => $record->order_id]);
                                                    }),

                                                TextEntry::make('status')
                                                    ->label(__('admin.payment_attempts.fields.status'))
                                                    ->badge()
                                                    ->state(fn (?PaymentAttempt $record) => $record?->status ?? null)
                                                    ->formatStateUsing(fn (?string $state): string => PaymentAttempt::labelForStatus($state))
                                                    ->color(fn (?string $state): string => PaymentAttempt::colorForStatus($state)),

                                                TextEntry::make('gateway')
                                                    ->label(__('admin.payment_attempts.fields.gateway'))
                                                    ->state(fn (?PaymentAttempt $record) => $record?->gateway ?? '-'),

                                                TextEntry::make('amount')
                                                    ->label(__('admin.payment_attempts.fields.amount'))
                                                    ->state(function (?PaymentAttempt $record) {
                                                        if (! $record) {
                                                            return '-';
                                                        }

                                                        $amount = number_format((float) $record->amount, 2, ',', '.');
                                                        $cur    = strtoupper((string) $record->currency);

                                                        return trim($amount . ' ' . $cur);
                                                    }),

                                                TextEntry::make('gateway_reference')
                                                    ->label(__('admin.payment_attempts.fields.gateway_reference'))
                                                    ->state(fn (?PaymentAttempt $record) => $record?->gateway_reference ?? '-'),

                                                TextEntry::make('idempotency_key')
                                                    ->label(__('admin.payment_attempts.fields.idempotency_key'))
                                                    ->state(fn (?PaymentAttempt $record) => $record?->idempotency_key ?? '-'),

                                                TextEntry::make('ip_address')
                                                    ->label(__('admin.payment_attempts.fields.ip'))
                                                    ->state(fn (?PaymentAttempt $record) => $record?->ip_address ?? '-'),

                                                TextEntry::make('started_at')
                                                    ->label(__('admin.payment_attempts.fields.started_at'))
                                                    ->state(fn (?PaymentAttempt $record) => $record?->started_at?->format('d.m.Y H:i') ?? '-'),

                                                TextEntry::make('completed_at')
                                                    ->label(__('admin.payment_attempts.fields.completed_at'))
                                                    ->state(fn (?PaymentAttempt $record) => $record?->completed_at?->format('d.m.Y H:i') ?? '-'),

                                                TextEntry::make('error_code')
                                                    ->label(__('admin.payment_attempts.fields.error_code'))
                                                    ->state(fn (?PaymentAttempt $record) => $record?->error_code ?: '-')
                                                    ->hidden(fn (?PaymentAttempt $record) => empty($record?->error_code)),

                                                TextEntry::make('error_message')
                                                    ->label(__('admin.payment_attempts.fields.error_message'))
                                                    ->state(fn (?PaymentAttempt $record) => $record?->error_message ?: '-')
                                                    ->hidden(fn (?PaymentAttempt $record) => empty($record?->error_message)),
                                            ]),

                                            Grid::make()->columns(1)->schema([
                                                TextEntry::make('user_agent')
                                                    ->label(__('admin.payment_attempts.fields.user_agent'))
                                                    ->state(fn (?PaymentAttempt $record) => $record?->user_agent ?: '-')
                                                    ->hidden(fn (?PaymentAttempt $record) => empty($record?->user_agent)),
                                            ]),

                                            Grid::make()->columns(2)->schema([
                                                TextEntry::make('raw_request')
                                                    ->label(__('admin.payment_attempts.fields.raw_request'))
                                                    ->state(function (?PaymentAttempt $record) {
                                                        if (! $record || empty($record->raw_request)) {
                                                            return null;
                                                        }

                                                        return json_encode($record->raw_request, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                                                    })
                                                    ->prose()
                                                    ->hidden(fn (?PaymentAttempt $record) => empty($record?->raw_request)),

                                                TextEntry::make('raw_response')
                                                    ->label(__('admin.payment_attempts.fields.raw_response'))
                                                    ->state(function (?PaymentAttempt $record) {
                                                        if (! $record || empty($record->raw_response)) {
                                                            return null;
                                                        }

                                                        return json_encode($record->raw_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                                                    })
                                                    ->prose()
                                                    ->hidden(fn (?PaymentAttempt $record) => empty($record?->raw_response)),
                                            ]),
                                        ]),
                                ]),

                            // Refund işlemleri + geçmiş
                            Group::make()
                                ->columnSpan(['default' => 12, 'lg' => 12])
                                ->schema([
                                    Section::make(__('admin.payment_attempts.sections.refunds'))
                                        ->visible(fn (?PaymentAttempt $record) => (bool) $record && $record->status === PaymentAttempt::STATUS_SUCCESS)
                                        ->description(function (?PaymentAttempt $record) {
                                            if (! $record) {
                                                return null;
                                            }

                                            $successSum = (float) RefundAttempt::query()
                                                ->where('payment_attempt_id', $record->id)
                                                ->where('status', RefundAttempt::STATUS_SUCCESS)
                                                ->sum('amount');

                                            if ($successSum <= 0) {
                                                return null;
                                            }

                                            $remaining = max(((float) $record->amount) - $successSum, 0);
                                            $cur = (string) ($record->currency ?? null);

                                            return 'İade edilen: '
                                                . (CurrencyPresenter::formatAdmin($successSum, $cur) ?? '-')
                                                . '  Kalan: '
                                                . (CurrencyPresenter::formatAdmin($remaining, $cur) ?? '-');

                                        })
                                        ->afterHeader([
                                            Action::make('refund')
                                                ->label(__('admin.payment_attempts.actions.refund'))
                                                ->color('warning')
                                                ->icon('heroicon-o-arrow-uturn-left')
                                                ->visible(function (?PaymentAttempt $record) {
                                                    if (! $record) {
                                                        return false;
                                                    }

                                                    if ($record->status !== PaymentAttempt::STATUS_SUCCESS) {
                                                        return false;
                                                    }

                                                    $refunded = (float) RefundAttempt::query()
                                                        ->where('payment_attempt_id', $record->id)
                                                        ->where('status', RefundAttempt::STATUS_SUCCESS)
                                                        ->sum('amount');

                                                    return ((float) $record->amount - $refunded) > 0;
                                                })
                                                ->schema([
                                                    TextInput::make('amount')
                                                        ->label(__('admin.payment_attempts.fields.refund_amount'))
                                                        ->numeric()
                                                        ->required()
                                                        ->default(function (PaymentAttempt $record) {
                                                            $refunded = (float) RefundAttempt::query()
                                                                ->where('payment_attempt_id', $record->id)
                                                                ->where('status', RefundAttempt::STATUS_SUCCESS)
                                                                ->sum('amount');

                                                            return max(((float) $record->amount) - $refunded, 0);
                                                        }),

                                                    Textarea::make('reason')
                                                        ->label(__('admin.payment_attempts.fields.refund_reason'))
                                                        ->rows(3),
                                                ])
                                                ->action(function (array $data, PaymentAttempt $record) {
                                                    try {
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
                                                            order: $record->order,
                                                            paymentAttempt: $record,
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
                                            RepeatableEntry::make('refunds')
                                                ->hiddenLabel()
                                                ->state(function (?PaymentAttempt $record) {
                                                    if (! $record) {
                                                        return [];
                                                    }

                                                    return RefundAttempt::query()
                                                        ->where('payment_attempt_id', $record->id)
                                                        ->where('status', RefundAttempt::STATUS_SUCCESS)
                                                        ->orderByDesc('id')
                                                        ->get()
                                                        ->map(function (RefundAttempt $r) use ($record) {
                                                            $cur = strtoupper((string) $record->currency);

                                                            return [
                                                                'badge'  => $r->initiator_role,
                                                                'name'   => $r->initiator_name,
                                                                'reason' => $r->reason ?: null,
                                                                'amount' => number_format((float) $r->amount, 2, ',', '.') . ' ' . $cur,
                                                                'time'   => $r->created_at?->format('d.m.Y H:i') ?? null,
                                                            ];
                                                        })
                                                        ->all();
                                                })
                                                ->table([
                                                    TableColumn::make(__('admin.payment_attempts.fields.name')),
                                                    TableColumn::make(__('admin.payment_attempts.fields.role')),
                                                    TableColumn::make(__('admin.payment_attempts.fields.description')),
                                                    TableColumn::make(__('admin.payment_attempts.fields.date')),
                                                    TableColumn::make(__('admin.payment_attempts.fields.total')),
                                                ])
                                                ->schema([
                                                    TextEntry::make('name')
                                                        ->hiddenLabel()
                                                        ->hidden(fn ($r, $state) => blank($state)),

                                                    TextEntry::make('badge')
                                                        ->badge()
                                                        ->hiddenLabel()
                                                        ->hidden(fn ($r, $state) => blank($state)),

                                                    TextEntry::make('reason')
                                                        ->hiddenLabel()
                                                        ->placeholder('-')
                                                        ->hidden(fn ($r, $state) => blank($state)),

                                                    TextEntry::make('time')->hiddenLabel(),
                                                    TextEntry::make('amount')->hiddenLabel(),
                                                ])
                                                ->hidden(fn ($record, $state) => empty($state)),
                                        ]),
                                ]),
                        ]),
                ]),
        ]);
    }
}
