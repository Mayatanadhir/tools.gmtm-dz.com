<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CalibrationPointStatus;

class MetrologyCalculationService
{
    /**
     * Compute metrological capability for a nominal point based on physical quantity, operation type, fluid, and instrument range.
     *
     * @param  string  $grandeurName  Physical quantity name (e.g. Température, Pression, etc.)
     * @param  string  $operationType  Operation type: 'measurement'/'mesure' or 'source'/'generation'
     * @param  float  $nominalValue  Setpoint nominal value
     * @param  float  $range  Instrument full scale range (Echelle / Portée)
     * @param  string|null  $fluid  Medium fluid: 'gaz' or 'liquid'
     */
    public function computeCapability(
        string $grandeurName,
        string $operationType,
        float $nominalValue,
        float $range = 0.0,
        ?string $fluid = null
    ): ?float {
        $paramLower = mb_strtolower($grandeurName, 'UTF-8');
        $typeOpLower = mb_strtolower(trim($operationType), 'UTF-8');

        $isTemperature = str_contains($paramLower, 'température') || str_contains($paramLower, 'temperature');
        $isPressure = str_contains($paramLower, 'pression') || str_contains($paramLower, 'pressure');
        $isMeasurement = ($typeOpLower === 'measurement' || $typeOpLower === 'mesure');

        if ($isTemperature) {
            if ($isMeasurement) {
                // Linear capability formula: (0.15 + 0.002 * |nominal|) / 2
                return (0.15 + (0.002 * abs($nominalValue))) / 2.0;
            }

            // Fixed capability for temperature generator based on bath fluid
            if ($fluid === 'liquid') {
                return 0.12; // Liquid bath calibration
            }
            if ($fluid === 'gaz') {
                return 0.25; // Dry block / air furnace
            }

            return 0.20; // Default nominal temperature capability
        }

        if ($isPressure) {
            if ($isMeasurement) {
                if ($fluid === 'gaz') {
                    return $range > 0 ? (0.001 * $range) : null;
                }
                if ($fluid === 'liquid') {
                    if ($range < 10) {
                        return 0.2;
                    }
                    if ($range <= 40) {
                        return 0.02 * $range;
                    }

                    return 0.8;
                }

                return $range > 0 ? (0.001 * $range) : null;
            }

            return null;
        }

        return null;
    }

    /**
     * Evaluate tolerance conformity status for a calibration point.
     *
     * @param  float  $correction  Measured correction value
     * @param  float|null  $capability  Metrological capability / EMT limit
     */
    public function evaluateToleranceStatus(float $correction, ?float $capability): CalibrationPointStatus
    {
        if ($capability === null) {
            return CalibrationPointStatus::InTolerance;
        }

        return abs($correction) > $capability
            ? CalibrationPointStatus::OutTolerance
            : CalibrationPointStatus::InTolerance;
    }

    /**
     * Compute confidence interval limits [C - U, C + U].
     *
     * @return array{lower: float, upper: float}
     */
    public function computeConfidenceInterval(float $correction, float $uncertainty): array
    {
        return [
            'lower' => round($correction - $uncertainty, 4),
            'upper' => round($correction + $uncertainty, 4),
        ];
    }
}
