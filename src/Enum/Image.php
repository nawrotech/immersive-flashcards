<?php

namespace App\Enum;

enum Image: string
{
    case GIF = 'gif';
    case IMAGE = 'image';
    case OWN = 'own';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
