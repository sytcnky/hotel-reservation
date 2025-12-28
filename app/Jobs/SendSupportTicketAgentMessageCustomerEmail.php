<?php

namespace App\Jobs;

use App\Mail\SupportTicketAgentMessageCustomerMail;
use App\Models\SupportMessage;
use App\Support\Mail\MailDefaults;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class SendSupportTicketAgentMessageCustomerEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $messageId;

    public function __construct(int $messageId)
    {
        $this->messageId = $messageId;
        // $this->onQueue('mail');
    }

    public function handle(): void
    {
        $message = SupportMessage::query()
            ->with(['ticket.user', 'ticket.category', 'ticket.order', 'author', 'media'])
            ->find($this->messageId);

        if (! $message || ! $message->ticket) {
            return;
        }

        $ticket = $message->ticket;

        $toCustomer = $ticket->user?->email;
        if (! is_string($toCustomer) || trim($toCustomer) === '') {
            return;
        }

        // Idempotency: one mail per message
        $key = 'mail:support-ticket-msg-customer:' . $message->id;
        if (! Cache::add($key, 1, now()->addDays(MailDefaults::idempotencyDays()))) {
            return;
        }

        $mailable = new SupportTicketAgentMessageCustomerMail($ticket, $message);
        MailDefaults::applyFrom($mailable);

        $locale = MailDefaults::mailLocale($ticket->locale);

        Mail::to($toCustomer)
            ->locale($locale)
            ->send($mailable);
    }
}
