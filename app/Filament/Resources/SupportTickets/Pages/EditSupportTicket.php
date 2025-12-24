<?php

namespace App\Filament\Resources\SupportTickets\Pages;

use App\Filament\Resources\SupportTickets\SupportTicketResource;
use App\Models\SupportTicket;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditSupportTicket extends EditRecord
{
    protected static string $resource = SupportTicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('close')
                ->label(__('admin.support_tickets.action.close'))
                ->color('gray')
                ->visible(fn () => $this->record instanceof SupportTicket && $this->record->status !== SupportTicket::STATUS_CLOSED)
                ->requiresConfirmation()
                ->action(function (): void {
                    /** @var SupportTicket $ticket */
                    $ticket = $this->record;

                    DB::transaction(function () use ($ticket) {
                        $ticket->forceFill([
                            'status' => SupportTicket::STATUS_CLOSED,
                            'closed_at' => now(),
                        ])->save();
                    });

                    $this->refreshFormData(['status', 'closed_at']);
                }),

            Action::make('reopen')
                ->label(__('admin.support_tickets.action.reopen'))
                ->color('warning')
                ->visible(fn () => $this->record instanceof SupportTicket && $this->record->status === SupportTicket::STATUS_CLOSED)
                ->requiresConfirmation()
                ->action(function (): void {
                    /** @var SupportTicket $ticket */
                    $ticket = $this->record;

                    DB::transaction(function () use ($ticket) {
                        $ticket->forceFill([
                            'status' => SupportTicket::STATUS_OPEN,
                            'closed_at' => null,
                        ])->save();
                    });

                    $this->refreshFormData(['status', 'closed_at']);
                }),
        ];
    }
}
