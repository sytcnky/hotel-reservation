<?php

namespace App\Mail;

use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupportTicketCreatedOpsMail extends Mailable
{
    use Queueable, SerializesModels;

    public SupportTicket $ticket;
    public SupportMessage $supportMessage;

    public function __construct(SupportTicket $ticket, SupportMessage $supportMessage)
    {
        $this->ticket = $ticket;
        $this->supportMessage = $supportMessage;
    }

    public function build(): self
    {
        $code = $this->ticket->order?->code;
        $subject = 'Yeni destek talebi #' . $this->ticket->id . ($code ? ' — ' . $code : '');

        return $this
            ->subject($subject)
            ->view('emails.support.ops-ticket-created', [
                'ticket' => $this->ticket,
                'supportMessage' => $this->supportMessage,
                'layoutVariant' => 'ops',
            ]);
    }
}
