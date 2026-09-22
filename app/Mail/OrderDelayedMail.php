<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderDelayedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly int $additionalMinutes,
        public readonly int $previousPreparationTime,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu pedido tardará un poco más',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.delayed',
        );
    }
}
