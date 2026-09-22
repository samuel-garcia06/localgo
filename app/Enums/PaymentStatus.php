<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pendiente';
    case Paid = 'pagado';
    case Failed = 'fallido';
    case Refunded = 'reembolsado';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
