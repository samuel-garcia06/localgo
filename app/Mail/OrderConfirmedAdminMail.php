<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmedAdminMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Order $order,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Nuevo pedido confirmado! #'.$this->order->public_id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.confirmed-admin',
        );
    }
}
