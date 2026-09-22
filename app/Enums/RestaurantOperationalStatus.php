<?php

namespace App\Enums;

enum RestaurantOperationalStatus: string
{
    case Open = 'open';
    case Busy = 'busy';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Aceptando pedidos',
            self::Busy => 'Cocina saturada',
            self::Closed => 'No aceptar pedidos',
        };
    }

    public function emoji(): string
    {
        return match ($this) {
            self::Open => '🟢',
            self::Busy => '🟠',
            self::Closed => '🔴',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Open => 'success',
            self::Busy => 'warning',
            self::Closed => 'danger',
        };
    }

    public function acceptingOrders(): bool
    {
        return $this !== self::Closed;
    }

    public function customerMessage(): ?string
    {
        return match ($this) {
            self::Open => null,
            self::Busy => 'Alta demanda. El tiempo de espera puede ser superior al habitual.',
            self::Closed => 'El restaurante no está aceptando pedidos en este momento. Vuelve a intentarlo más tarde.',
        };
    }
}
