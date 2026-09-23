<?php

declare(strict_types=1);

namespace App\Enums;

enum EquipmentPackage: string
{
    case Lot01 = 'lot_01';
    case Lot02 = 'lot_02';
    case VehicleLot = 'vehicle_lot';
    case None = 'none';

    /**
     * Get the translated label for the package.
     */
    public function label(): string
    {
        return match ($this) {
            self::Lot01 => __('Lot 01'),
            self::Lot02 => __('Lot 02'),
            self::VehicleLot => __('Vehicle Lot'),
            self::None => __('No Lot / Unassigned'),
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
