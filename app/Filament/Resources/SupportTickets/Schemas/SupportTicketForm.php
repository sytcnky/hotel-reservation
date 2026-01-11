<?php

namespace App\Filament\Resources\SupportTickets\Schemas;

use App\Filament\Resources\Orders\OrderResource;
use App\Jobs\SendSupportTicketAgentMessageCustomerEmail;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class SupportTicketForm
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

                            /* -----------------------------------------------------------------
                             | SOL (8) – INFO + MESAJLAR
                             |-----------------------------------------------------------------*/
                            Group::make()
                                ->columnSpan(['default' => 12, 'lg' => 8])
                                ->schema([

                                    /* -------- INFO -------- */
                                    Section::make(__('admin.support_tickets.sections.info'))
                                        ->schema([
                                            TextEntry::make('subject')
                                                ->label(__('admin.support_tickets.field.subject'))
                                                ->state(fn (?SupportTicket $r) => $r?->subject ?? '-'),

                                            Grid::make()
                                                ->columns()
                                                ->schema([
                                                    TextEntry::make('category')
                                                        ->label(__('admin.support_tickets.field.category'))
                                                        ->badge()
                                                        ->state(function (?SupportTicket $r): string {
                                                            if (! $r || ! $r->category) {
                                                                return '-';
                                                            }

                                                            // Admin panel locale tek kaynak
                                                            $uiLocale = app()->getLocale();

                                                            $name = $r->category->name ?? null;

                                                            if (is_array($name)) {
                                                                return (string) ($name[$uiLocale] ?? '');
                                                            }

                                                            if (is_string($name)) {
                                                                $decoded = json_decode($name, true);
                                                                if (is_array($decoded)) {
                                                                    return (string) ($decoded[$uiLocale] ?? '');
                                                                }

                                                                return trim($name) !== '' ? $name : '-';
                                                            }

                                                            return '-';
                                                        }),

                                                    TextEntry::make('order_code')
                                                        ->label(__('admin.support_tickets.field.order'))
                                                        ->badge()
                                                        ->state(fn (?SupportTicket $r) => $r?->order?->code ?? '-')
                                                        ->hidden(fn (?SupportTicket $r) => ! $r?->order_id)
                                                        ->url(fn (?SupportTicket $r) => $r?->order_id
                                                            ? OrderResource::getUrl('edit', ['record' => $r->order_id])
                                                            : null
                                                        )
                                                        ->openUrlInNewTab(),
                                                ]),

                                            Grid::make()->columns()->schema([
                                                TextEntry::make('user_name')
                                                    ->label(__('admin.support_tickets.field.user'))
                                                    ->state(fn (?SupportTicket $r) => $r?->user?->name ?? '-'),

                                                TextEntry::make('user_email')
                                                    ->label(__('admin.support_tickets.field.user'))
                                                    ->state(fn (?SupportTicket $r) => $r?->user?->email ?? '-'),
                                            ]),

                                            Grid::make()->columns()->schema([
                                                TextEntry::make('user_phone')
                                                    ->label(__('admin.support_tickets.field.user_phone'))
                                                    ->state(fn (?SupportTicket $r) => $r?->user?->phone ?? '-'),

                                                TextEntry::make('last_message_at')
                                                    ->label(__('admin.support_tickets.field.last_message_at'))
                                                    ->state(fn (?SupportTicket $r) => $r?->last_message_at?->format('d.m.Y H:i') ?? '-'),
                                            ]),

                                            TextEntry::make('status')
                                                ->label(__('admin.support_tickets.field.status'))
                                                ->badge()
                                                ->state(fn (?SupportTicket $r) => match ($r?->status) {
                                                    SupportTicket::STATUS_WAITING_AGENT    => __('admin.support_tickets.status.waiting_agent'),
                                                    SupportTicket::STATUS_WAITING_CUSTOMER => __('admin.support_tickets.status.waiting_customer'),
                                                    SupportTicket::STATUS_CLOSED           => __('admin.support_tickets.status.closed'),
                                                    default                                 => __('admin.support_tickets.status.open'),
                                                })
                                                ->color(fn (?SupportTicket $r) => match ($r?->status) {
                                                    SupportTicket::STATUS_WAITING_AGENT    => 'warning',
                                                    SupportTicket::STATUS_WAITING_CUSTOMER => 'info',
                                                    SupportTicket::STATUS_CLOSED           => 'gray',
                                                    default                                 => 'success',
                                                }),
                                        ]),

                                    /* -------- MESAJ KARTLARI -------- */
                                    RepeatableEntry::make('messages')
                                        ->hiddenLabel()
                                        ->schema([
                                            Grid::make()
                                                ->columns(['default' => 1, 'lg' => 12])
                                                ->schema([

                                                    Group::make()
                                                        ->dense()
                                                        ->columnSpan(['default' => 12, 'lg' => 3])
                                                        ->schema([
                                                            TextEntry::make('author_label')
                                                                ->label(__('admin.support_tickets.messages.author'))
                                                                ->hiddenLabel()
                                                                ->badge()
                                                                ->state(function (SupportMessage $m) {
                                                                    if ($m->author_type === SupportMessage::AUTHOR_AGENT) {
                                                                        $role = $m->author?->getRoleNames()?->first();
                                                                        return $role ?: __('admin.support_tickets.messages.agent');
                                                                    }

                                                                    return __('admin.support_tickets.messages.customer');
                                                                })
                                                                ->color(fn (SupportMessage $m) =>
                                                                $m->author_type === SupportMessage::AUTHOR_AGENT ? 'info' : 'success'
                                                                ),

                                                            TextEntry::make('user_name')
                                                                ->hiddenLabel()
                                                                ->state(function (SupportMessage $m) {
                                                                    return $m->author?->name
                                                                        ?? __('admin.support_tickets.messages.unknown_user');
                                                                }),

                                                            TextEntry::make('created_at')
                                                                ->hiddenLabel()
                                                                ->state(fn (SupportMessage $m) => $m->created_at?->format('d.m.Y H:i')),
                                                        ]),

                                                    Group::make()
                                                        ->columnSpan(['default' => 12, 'lg' => 9])
                                                        ->schema([
                                                            TextEntry::make('body')
                                                                ->label(__('admin.support_tickets.messages.body'))
                                                                ->hiddenLabel()
                                                                ->markdown(),

                                                            Section::make([
                                                                TextEntry::make('attachments')
                                                                    ->label(__('admin.support_tickets.messages.attachments'))
                                                                    ->html()
                                                                    ->hidden(function (SupportMessage $m) {
                                                                        if (! method_exists($m, 'getMedia')) {
                                                                            return true;
                                                                        }

                                                                        return $m->getMedia('attachments')->isEmpty();
                                                                    })
                                                                    ->state(function (SupportMessage $m) {
                                                                        $items = $m->getMedia('attachments');

                                                                        $lines = $items->map(function ($media) {
                                                                            $name = htmlspecialchars((string) $media->file_name, ENT_QUOTES, 'UTF-8');
                                                                            $url  = htmlspecialchars((string) $media->getUrl(), ENT_QUOTES, 'UTF-8');

                                                                            return '<div><a href="' . $url . '" target="_blank" rel="noopener noreferrer">' . $name . '</a></div>';
                                                                        });

                                                                        return $lines->implode('');
                                                                    }),
                                                            ])
                                                                ->compact()
                                                                ->hidden(function (SupportMessage $m) {
                                                                    if (! method_exists($m, 'getMedia')) {
                                                                        return true;
                                                                    }

                                                                    return $m->getMedia('attachments')->isEmpty();
                                                                }),
                                                        ]),
                                                ]),
                                        ])
                                        ->state(fn (?SupportTicket $r) =>
                                            $r?->messages()
                                                ->with(['media', 'author'])
                                                ->orderBy('created_at')
                                                ->get() ?? []
                                        ),

                                    /* -------- YENİ MESAJ -------- */
                                    Section::make(__('admin.support_tickets.sections.new_message'))
                                        ->schema([
                                            Textarea::make('reply_body')
                                                ->label(__('admin.support_tickets.messages.body'))
                                                ->rows(5)
                                                ->dehydrated(false),

                                            FileUpload::make('reply_attachments')
                                                ->label(__('admin.support_tickets.messages.attachments'))
                                                ->multiple()
                                                ->maxFiles(5)
                                                ->maxSize(2048)
                                                ->acceptedFileTypes([
                                                    'image/jpeg',
                                                    'image/png',
                                                    'image/webp',
                                                ])
                                                ->preserveFilenames()
                                                ->storeFiles(false)
                                                ->dehydrated(false),

                                            Actions::make([
                                                Action::make('sendReply')
                                                    ->label(__('admin.support_tickets.messages.action.reply'))
                                                    ->color('primary')
                                                    ->action(function (
                                                        \Livewire\Component $livewire,
                                                        ?SupportTicket $record,
                                                        Get $schemaGet,
                                                        Set $schemaSet,
                                                    ): void {
                                                        if (! $record) {
                                                            return;
                                                        }

                                                        $body = trim((string) $schemaGet('reply_body'));
                                                        if ($body === '') {
                                                            Notification::make()
                                                                ->title(__('admin.support_tickets.messages.validation.body_required'))
                                                                ->danger()
                                                                ->send();

                                                            return;
                                                        }

                                                        $files = $schemaGet('reply_attachments') ?? [];

                                                        $newMessageId = null;

                                                        DB::transaction(function () use ($record, $body, $files, &$newMessageId) {
                                                            /** @var SupportMessage $message */
                                                            $message = $record->messages()->create([
                                                                'author_user_id' => (int) auth()->id(),
                                                                'author_type'    => SupportMessage::AUTHOR_AGENT,
                                                                'body'           => $body,
                                                            ]);

                                                            $newMessageId = $message->id;

                                                            foreach ($files as $file) {
                                                                if (! $file instanceof TemporaryUploadedFile) {
                                                                    continue;
                                                                }

                                                                $mime = (string) $file->getMimeType();
                                                                if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
                                                                    continue;
                                                                }

                                                                $message->addMedia($file->getRealPath())
                                                                    ->usingFileName($file->getClientOriginalName())
                                                                    ->toMediaCollection('attachments');
                                                            }

                                                            $record->forceFill([
                                                                'status'          => SupportTicket::STATUS_WAITING_CUSTOMER,
                                                                'closed_at'       => null,
                                                                'last_message_at' => now(),
                                                            ])->save();
                                                        });

                                                        if ($newMessageId) {
                                                            dispatch(new SendSupportTicketAgentMessageCustomerEmail($newMessageId));
                                                        }

                                                        $schemaSet('reply_body', '');
                                                        $schemaSet('reply_attachments', []);
                                                        $livewire->dispatch('$refresh');

                                                        Notification::make()
                                                            ->title(__('admin.support_tickets.messages.sent'))
                                                            ->success()
                                                            ->send();
                                                    }),
                                            ])
                                                ->alignEnd(),
                                        ]),
                                ]),

                            /* -----------------------------------------------------------------
                             | SAĞ (4) – OPERASYON
                             |-----------------------------------------------------------------*/
                            Group::make()
                                ->columnSpan(['default' => 12, 'lg' => 4])
                                ->schema([
                                    Section::make(__('admin.support_tickets.sections.operations'))
                                        ->schema([
                                            Select::make('status')
                                                ->label(__('admin.support_tickets.field.status'))
                                                ->required()
                                                ->options([
                                                    SupportTicket::STATUS_OPEN             => __('admin.support_tickets.status.open'),
                                                    SupportTicket::STATUS_WAITING_AGENT    => __('admin.support_tickets.status.waiting_agent'),
                                                    SupportTicket::STATUS_WAITING_CUSTOMER => __('admin.support_tickets.status.waiting_customer'),
                                                    SupportTicket::STATUS_CLOSED           => __('admin.support_tickets.status.closed'),
                                                ]),
                                        ]),
                                ]),
                        ]),
                ]),
        ]);
    }
}
