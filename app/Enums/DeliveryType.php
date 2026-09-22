<?php

namespace App\Enums;

enum DeliveryType: string
{
    case Delivery = 'domicilio';
    case Takeaway = 'para_llevar';

    public function label(): string
    {
        return match ($this) {
            self::Delivery => 'A domicilio',
            self::Takeaway => 'Para llevar',
        };
    }
}
