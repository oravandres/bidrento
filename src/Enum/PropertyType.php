<?php

namespace App\Enum;

enum PropertyType: int
{
    case PROPERTY = 1;
    case PARKING_SPACE = 2;

    public static function fromString(string $type): self
    {
        return match ($type) {
            'property' => self::PROPERTY,
            'parking_space' => self::PARKING_SPACE,
            default => throw new \InvalidArgumentException('Invalid property type'),
        };
    }

    public function toString(): string
    {
        return match ($this) {
            self::PROPERTY => 'property',
            self::PARKING_SPACE => 'parking_space',
        };
    }
}
