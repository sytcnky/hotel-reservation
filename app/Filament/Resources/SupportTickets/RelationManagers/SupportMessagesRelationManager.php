<?php

namespace App\Filament\Resources\SupportTickets\RelationManagers;

use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Support\Date\DatePresenter;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class SupportMessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'asc')
            ->columns([
                TextColumn::make('author_type')
                    ->label(__('admin.support_tickets.messages.author_type'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => $state === SupportMessage::AUTHOR_AGENT
                        ? __('admin.support_tickets.messages.agent')
                        : __('admin.support_tickets.messages.customer')
                    )
                    ->color(fn (?string $state) => $state === SupportMessage::AUTHOR_AGENT ? 'info' : 'warning'),

                TextColumn::make('author.email')
                    ->label(__('admin.support_tickets.messages.author'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('body')
                    ->label(__('admin.support_tickets.messages.body'))
                    ->wrap()
                    ->limit(180),

                TextColumn::make('attachments')
                    ->label(__('admin.support_tickets.messages.attachments'))
                    ->state(function (SupportMessage $record): array {
                        return $record->getMedia('attachments')->map(fn ($m) => [
                            'name' => $m->file_name,
                            'url'  => $m->getUrl(),
                        ])->all();
                    })
                    ->formatStateUsing(function ($state): string {
                        $items = is_array($state) ? $state : [];

                        if (empty($items)) {
                            return '-';
                        }

                        $links = [];
                        foreach ($items as $item) {
                            $name = e((string) ($item['name'] ?? 'file'));
                            $url  = e((string) ($item['url'] ?? '#'));

                            $links[] = '<a href="' . $url . '" target="_blank" rel="noopener noreferrer">' . $name . '</a>';
                        }

                        return implode('<br>', $links);
                    })
                    ->html()
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('admin.field.created_at'))
                    ->formatStateUsing(fn ($state) => DatePresenter::humanDateTimeShort($state))
                    ->sortable(),
            ])
            ->headerActions([
                Action::make('reply')
                    ->label(__('admin.support_tickets.messages.action.reply'))
                    ->color('primary')
                    ->schema([
                        Textarea::make('body')
                            ->label(__('admin.support_tickets.messages.body'))
                            ->required()
                            ->rows(6),

                        FileUpload::make('attachments')
                            ->label(__('admin.support_tickets.messages.attachments'))
                            ->multiple()
                            ->maxFiles(5)
                            ->preserveFilenames()
                            ->storeFiles(false),
                    ])
                    ->action(function (array $data): void {
                        /** @var SupportTicket $ticket */
                        $ticket = $this->getOwnerRecord();

                        DB::transaction(function () use ($ticket, $data) {
                            /** @var SupportMessage $message */
                            $message = $ticket->messages()->create([
                                'author_user_id' => (int) auth()->id(),
                                'author_type'    => SupportMessage::AUTHOR_AGENT,
                                'body'           => (string) $data['body'],
                            ]);

                            foreach (($data['attachments'] ?? []) as $file) {
                                if (! $file instanceof TemporaryUploadedFile) {
                                    continue;
                                }

                                $message->addMedia($file->getRealPath())
                                    ->usingFileName($file->getClientOriginalName())
                                    ->toMediaCollection('attachments');
                            }

                            $ticket->forceFill([
                                'status'          => SupportTicket::STATUS_WAITING_CUSTOMER,
                                'closed_at'       => null,
                                'last_message_at' => now(),
                            ])->save();
                        });
                    }),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
