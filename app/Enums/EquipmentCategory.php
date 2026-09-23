<?php

declare(strict_types=1);

namespace App\Enums;

enum EquipmentCategory: string
{
    case MeasuringInstrument = 'measuring_instrument';
    case WorkTool = 'work_tool';
    case Vehicle = 'vehicle';
    case Other = 'other';

    /**
     * Get the translated label for the category.
     */
    public function label(): string
    {
        return match ($this) {
            self::MeasuringInstrument => __('Measuring Instrument'),
            self::WorkTool => __('Work Tool'),
            self::Vehicle => __('Vehicle'),
            self::Other => __('Other'),
        };
    }

    /**
     * Get the semantic badge variant for the category.
     */
    public function badgeVariant(): string
    {
        return match ($this) {
            self::MeasuringInstrument => 'info',
            self::WorkTool => 'neutral',
            self::Vehicle => 'warning',
            self::Other => 'secondary',
        };
    }

    /**
     * Get the icon name for the category.
     */
    public function icon(): string
    {
        return match ($this) {
            self::MeasuringInstrument => 'fa-tachometer-alt',
            self::WorkTool => 'fa-wrench',
            self::Vehicle => 'fa-truck-pickup',
            self::Other => 'fa-cube',
        };
    }

    /**
     * Check if this category typically requires metrological calibration.
     */
    public function requiresCalibrationByDefault(): bool
    {
        return $this === self::MeasuringInstrument;
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
