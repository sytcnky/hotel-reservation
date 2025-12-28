<?php

namespace App\Mail;

use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupportTicketCustomerMessageOpsMail extends Mailable
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
        $subject = 'Destek talebi #' . $this->ticket->id . ' — Yeni müşteri mesajı' . ($code ? ' — ' . $code : '');

        return $this
            ->subject($subject)
            ->view('emails.support.ops-customer-message', [
                'ticket' => $this->ticket,
                'supportMessage' => $this->supportMessage,
            ]);
    }
}
