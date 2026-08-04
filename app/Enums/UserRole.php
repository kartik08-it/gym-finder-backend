<?php

namespace App\Enums;

enum UserRole: string
{
    case CUSTOMER = 'customer';
    case GYM_OWNER = 'gym_owner';
    case ADMIN = 'admin';

    public static function values(): array
    {
        return array_map(fn ($c) => $c->value, self::cases());
    }
}
