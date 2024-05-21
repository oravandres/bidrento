<?php

namespace App\Enum;

enum PropertyStatus: int
{
    case ACTIVE = 1;
    case DELETED = 2;

    public function toString(): string
    {
        return match ($this) {
            self::ACTIVE => 'active',
            self::DELETED => 'deleted',
        };
    }
}
