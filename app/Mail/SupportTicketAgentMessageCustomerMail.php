<?php

namespace App\Mail;

use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupportTicketAgentMessageCustomerMail extends Mailable
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
        $subject = 'Destek yanıtı — Talep #' . $this->ticket->id;

        return $this
            ->subject($subject)
            ->view('emails.support.customer-agent-message', [
                'ticket' => $this->ticket,
                'supportMessage' => $this->supportMessage,
            ]);
    }
}
