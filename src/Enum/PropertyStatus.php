<?php

namespace App\Enum;

/**
 * Enum representing property statuses.
 */
enum PropertyStatus: int
{
    case ACTIVE = 1;
    case DELETED = 2;

    /**
     * Converts a PropertyStatus enum value to its string representation.
     *
     * @return string The string representation of the property status.
     */
    public function toString(): string
    {
        return match ($this) {
            self::ACTIVE => 'active',
            self::DELETED => 'deleted',
        };
    }
}
