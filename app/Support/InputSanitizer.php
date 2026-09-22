<?php

namespace App\Support;

class InputSanitizer
{
    public static function text(?string $value, int $maxLength = 255): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = strip_tags($value);
        $value = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $value) ?? '';
        $value = preg_replace('/\s+/u', ' ', trim($value)) ?? '';

        if ($value === '') {
            return null;
        }

        return mb_substr($value, 0, $maxLength);
    }

    public static function phone(?string $value, int $maxLength = 30): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = preg_replace('/[^0-9+\s().-]/u', '', $value) ?? '';
        $value = preg_replace('/\s+/u', ' ', trim($value)) ?? '';

        if ($value === '') {
            return null;
        }

        return mb_substr($value, 0, $maxLength);
    }

    public static function choices(array|string|null $value, int $maxItems = 5, int $maxLength = 120): array|string|null
    {
        if (is_array($value)) {
            return collect($value)
                ->map(fn ($choice) => self::text(is_scalar($choice) ? (string) $choice : null, $maxLength))
                ->filter()
                ->take($maxItems)
                ->values()
                ->all();
        }

        return self::text($value, $maxLength);
    }
}
