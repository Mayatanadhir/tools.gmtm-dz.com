<?php

declare(strict_types=1);

namespace App\Enums;

enum AccuracyType: string
{
    case Percentage = '%';
    case Absolute = 'abs';

    /**
     * Get the translated label for the accuracy type.
     */
    public function label(): string
    {
        return match ($this) {
            self::Percentage => '%',
            self::Absolute => 'Abs',
        };
    }

    /**
     * Get all enum values as an array.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
