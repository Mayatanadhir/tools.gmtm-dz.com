<?php

declare(strict_types=1);

namespace App\Enums;

enum EquipmentStatus: string
{
    case Active = 'active';
    case Maintenance = 'maintenance';
    case Deployed = 'deployed';
    case Retired = 'retired';
    case Inactive = 'inactive';

    /**
     * Get the translated label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => __('Active'),
            self::Maintenance => __('Under Maintenance'),
            self::Deployed => __('Deployed on Mission'),
            self::Retired => __('Retired'),
            self::Inactive => __('Inactive'),
        };
    }

    /**
     * Get the semantic badge variant for the status.
     */
    public function badgeVariant(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Maintenance => 'warning',
            self::Deployed => 'info',
            self::Retired => 'neutral',
            self::Inactive => 'danger',
        };
    }

    /**
     * Check if the equipment is active and available.
     */
    public function isAvailable(): bool
    {
        return $this === self::Active;
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
