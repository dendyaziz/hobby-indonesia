<?php

namespace App\Helpers;

class Phone
{
    /**
     * Reformat phone number
     */
    public static function format($phone): array|string|null
    {
        if ($phone === null) {
            return null;
        }

        $trimmed = trim((string) $phone);
        if ($trimmed === '') {
            return null;
        }

        $cleaned = preg_replace('/[^0-9]/', '', $trimmed);

        return preg_replace('/^(0*62|0*)/', '62', $cleaned);
    }
}
