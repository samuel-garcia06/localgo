<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'efectivo';
    case Stripe = 'stripe';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Efectivo',
            self::Stripe => 'Tarjeta',
        };
    }
}
