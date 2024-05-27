<?php

namespace App\Enum;

/**
 * Enum representing property types.
 */
enum PropertyType: int
{
    case PROPERTY = 1;
    case PARKING_SPACE = 2;

    /**
     * Converts a string to a PropertyType enum value.
     *
     * @param string $type The string representation of the property type.
     * @return self The corresponding PropertyType enum value.
     * @throws \InvalidArgumentException If the provided string does not match any enum value.
     */
    public static function fromString(string $type): self
    {
        return match ($type) {
            'property' => self::PROPERTY,
            'parking_space' => self::PARKING_SPACE,
            default => throw new \InvalidArgumentException('Invalid property type'),
        };
    }

    /**
     * Converts a PropertyType enum value to its string representation.
     *
     * @return string The string representation of the property type.
     */
    public function toString(): string
    {
        return match ($this) {
            self::PROPERTY => 'property',
            self::PARKING_SPACE => 'parking_space',
        };
    }
}
