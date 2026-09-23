<?php

declare(strict_types=1);

namespace App\Enums;

enum GrandeurType: string
{
    case Measurement = 'measurement';
    case Source = 'source';

    /**
     * Get the translated label for the quantity type.
     */
    public function label(): string
    {
        return match ($this) {
            self::Measurement => __('Measurement (Sensor / In)'),
            self::Source => __('Source / Generation (Out)'),
        };
    }

    /**
     * Get the semantic badge variant for the quantity type.
     */
    public function badgeVariant(): string
    {
        return match ($this) {
            self::Measurement => 'success',
            self::Source => 'warning',
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
