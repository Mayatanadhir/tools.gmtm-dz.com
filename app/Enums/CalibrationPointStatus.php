<?php

declare(strict_types=1);

namespace App\Enums;

enum CalibrationPointStatus: string
{
    case InTolerance = 'in_tolerance';
    case OutTolerance = 'out_tolerance';

    /**
     * Get the translated label for the tolerance status.
     */
    public function label(): string
    {
        return match ($this) {
            self::InTolerance => __('In Tolerance'),
            self::OutTolerance => __('Out of Tolerance'),
        };
    }

    /**
     * Get the semantic badge variant for the tolerance status.
     */
    public function badgeVariant(): string
    {
        return match ($this) {
            self::InTolerance => 'success',
            self::OutTolerance => 'danger',
        };
    }

    /**
     * Check if the point complies with tolerance limits.
     */
    public function isCompliant(): bool
    {
        return $this === self::InTolerance;
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
