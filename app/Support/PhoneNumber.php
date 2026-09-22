<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

class PhoneNumber
{
    public static function normalizeSpanish(string $phone): string
    {
        $phone = trim($phone);
        $hasInternationalPrefix = str_starts_with($phone, '+');
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '0034')) {
            $digits = substr($digits, 4);
        } elseif (str_starts_with($digits, '34') && strlen($digits) === 11) {
            $digits = substr($digits, 2);
        } elseif ($hasInternationalPrefix && ! str_starts_with($phone, '+34')) {
            throw ValidationException::withMessages([
                'customerPhone' => 'Solo se admiten telefonos espanoles.',
                'customer_phone' => 'Solo se admiten telefonos espanoles.',
            ]);
        }

        if (! preg_match('/^[6789]\d{8}$/', $digits)) {
            throw ValidationException::withMessages([
                'customerPhone' => 'Introduce un telefono espanol valido, por ejemplo +34 612 345 678.',
                'customer_phone' => 'Introduce un telefono espanol valido, por ejemplo +34 612 345 678.',
            ]);
        }

        return '+34'.$digits;
    }
}
