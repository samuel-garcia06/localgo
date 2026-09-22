<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasColor, HasLabel
{
    case PendingVerification = 'pending_verification';
    case PendingEmailConfirmation = 'pending_email_confirmation';
    case Confirmed = 'confirmed';
    case Pending = 'pendiente';
    case Accepted = 'aceptado';
    case Ready = 'listo';
    case Rejected = 'rechazado';
    case Delivered = 'entregado';

    public function getLabel(): string
    {
        return match ($this) {
            self::PendingVerification => 'Pendiente SMS',
            self::PendingEmailConfirmation => 'Pendiente email',
            self::Confirmed => 'Confirmado',
            self::Pending => 'Pendiente',
            self::Accepted => 'En preparación',
            self::Ready => 'Listo',
            self::Rejected => 'Rechazado',
            self::Delivered => 'Entregado',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PendingVerification,
            self::PendingEmailConfirmation => 'gray',
            self::Confirmed => 'info',
            self::Pending => 'warning',
            self::Accepted => 'success',
            self::Ready => 'primary',
            self::Rejected => 'danger',
            self::Delivered => 'gray',
        };
    }

    /** @deprecated Usar getLabel() */
    public function label(): string
    {
        return $this->getLabel();
    }

    public static function visibleToRestaurantValues(): array
    {
        return [
            self::Confirmed->value,
            self::Pending->value,
            self::Accepted->value,
            self::Ready->value,
            self::Rejected->value,
            self::Delivered->value,
        ];
    }

    public static function activeValues(): array
    {
        return [
            self::Confirmed->value,
            self::Pending->value,
            self::Accepted->value,
            self::Ready->value,
        ];
    }

    public static function visibleToRepartidorValues(): array
    {
        return [
            self::Ready->value,
            self::Delivered->value,
        ];
    }
}
