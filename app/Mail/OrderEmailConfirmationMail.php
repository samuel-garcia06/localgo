<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderEmailConfirmationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly string $confirmationUrl,
    ) {}

    public function envelope(): Envelope
    {
        $name = filled($this->order->customer_name) ? $this->order->customer_name : null;
        $subject = $name ? "Confirma tu pedido, {$name}" : 'Confirma tu pedido';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.confirmation',
        );
    }
}
