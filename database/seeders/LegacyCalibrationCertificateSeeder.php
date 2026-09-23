<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CalibrationCertificateStatus;
use App\Enums\CalibrationPointStatus;
use App\Models\CalibrationCertificate;
use App\Models\CalibrationPoint;
use App\Models\Equipment;
use App\Models\EquipmentSpecification;
use App\Services\MediaOptimizationService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LegacyCalibrationCertificateSeeder extends Seeder
{
    /** Parse one SQL row into an array of raw string values. */
    private function parseSqlRow(string $row): array
    {
        // Strip leading/trailing parens and trailing comma/semicolon
        $row = preg_replace('/^\(|\)[;,]?\s*$/', '', $row);
        $values = [];
        $i = 0;
        $len = strlen($row);

        while ($i < $len) {
            // Skip whitespace/comma between fields
            while ($i < $len && in_array($row[$i], [',', ' ', "\t"])) {
                $i++;
            }
            if ($i >= $len) {
                break;
            }

            if ($row[$i] === "'") {
                // Quoted string field
                $i++; // skip opening quote
                $val = '';
                while ($i < $len) {
                    if ($row[$i] === "'" && ($i + 1 >= $len || $row[$i + 1] !== "'")) {
                        $i++; // skip closing quote
                        break;
                    }
                    if ($row[$i] === "'" && isset($row[$i + 1]) && $row[$i + 1] === "'") {
                        $val .= "'";
                        $i += 2;

                        continue;
                    }
                    $val .= $row[$i];
                    $i++;
                }
                $values[] = $val;
            } else {
                // Unquoted (NULL or number)
                $end = $i;
                while ($end < $len && $row[$end] !== ',') {
                    $end++;
                }
                $values[] = trim(substr($row, $i, $end - $i));
                $i = $end;
            }
        }

        return $values;
    }

    public function run(): void
    {
        $backupSqlPath = 'd:/HARD Project/app.gmtm-dz.com/public/assets/backups/backup_2026_017.sql';
        $certsSourceDir = 'd:/HARD Project/app.gmtm-dz.com/public/assets/certificates';

        if (! File::exists($backupSqlPath)) {
            $this->command?->error("Backup SQL file not found at [{$backupSqlPath}].");

            return;
        }

        $mediaService = app(MediaOptimizationService::class);

        $handle = fopen($backupSqlPath, 'r');
        if (! $handle) {
            $this->command?->error('Failed to open backup SQL file.');

            return;
        }

        $currentTable = '';
        $legacyEquipment = [];
        $certificates = [];
        $points = [];

        while (($line = fgets($handle)) !== false) {
            $t = trim($line);

            if (str_starts_with($t, 'INSERT INTO `equipment`')) {
                $currentTable = 'equipment';

                continue;
            } elseif (str_starts_with($t, 'INSERT INTO `calibration_certificates`')) {
                $currentTable = 'certificates';

                continue;
            } elseif (str_starts_with($t, 'INSERT INTO `calibration_points`')) {
                $currentTable = 'points';

                continue;
            } elseif (str_starts_with($t, 'INSERT INTO `') || str_starts_with($t, 'DROP TABLE') || str_starts_with($t, 'CREATE TABLE')) {
                $currentTable = '';
            }

            if (! str_starts_with($t, "('") && ! str_starts_with($t, '(N')) {
                continue;
            }

            $cols = $this->parseSqlRow($t);

            if ($currentTable === 'equipment' && count($cols) >= 11) {
                // 0:id 1:internal_code 2:full_name 3:short_name 4:serial_number
                // 5:category 6:package 7:requires_calibration 8:designation
                // 9:image_path 10:certificate_path ...
                $legacyId = (int) $cols[0];
                $legacyEquipment[$legacyId] = [
                    'full_name' => trim($cols[2]),
                    'requires_calibration' => $cols[7] === '1',
                    'certificate_path' => ($cols[10] !== 'NULL') ? trim($cols[10]) : '',
                ];
            } elseif ($currentTable === 'certificates' && count($cols) >= 7) {
                // 0:id 1:reference 2:equipment_id 3:laboratory_name
                // 4:calibration_date 5:price 6:is_locked
                $certificates[] = [
                    'id' => (int) $cols[0],
                    'reference' => trim($cols[1]),
                    'legacy_eq_id' => (int) $cols[2],
                    'laboratory_name' => trim($cols[3]),
                    'calibration_date' => trim($cols[4]),
                    'price' => (float) $cols[5],
                    'is_locked' => (int) $cols[6] === 1,
                ];
            } elseif ($currentTable === 'points' && count($cols) >= 7) {
                // 0:id 1:certificate_id 2:specification_id 3:nominal_value
                // 4:correction 5:uncertainty 6:status
                $points[] = [
                    'id' => (int) $cols[0],
                    'certificate_id' => (int) $cols[1],
                    'specification_id' => (int) $cols[2],
                    'nominal_value' => (float) $cols[3],
                    'correction' => (float) $cols[4],
                    'uncertainty' => (float) $cols[5],
                    'status' => $cols[6] === 'out_tolerance'
                        ? CalibrationPointStatus::OutTolerance
                        : CalibrationPointStatus::InTolerance,
                ];
            }
        }
        fclose($handle);

        $this->command?->info(sprintf(
            'Parsed %d equipment, %d certificates, %d points.',
            count($legacyEquipment), count($certificates), count($points)
        ));

        // Build legacy_id => ERP id via full_name matching
        $erpByName = Equipment::all()->keyBy(fn ($eq) => $this->normaliseName($eq->full_name));

        $legacyIdToErpId = [];
        foreach ($legacyEquipment as $legacyId => $legacyEq) {
            if (! $legacyEq['requires_calibration']) {
                $this->command?->warn("  [SKIP] id={$legacyId} '{$legacyEq['full_name']}' requires_calibration=0");
                $legacyIdToErpId[$legacyId] = null;

                continue;
            }
            $norm = $this->normaliseName($legacyEq['full_name']);
            $erpEq = $erpByName->get($norm);
            if ($erpEq) {
                $legacyIdToErpId[$legacyId] = $erpEq->id;
                $this->command?->line("  [MAP] id={$legacyId} '{$legacyEq['full_name']}' => ERP id={$erpEq->id}");
            } else {
                $legacyIdToErpId[$legacyId] = null;
                $this->command?->warn("  [MISS] id={$legacyId} '{$legacyEq['full_name']}' no ERP match");
            }
        }

        $certFiles = [];
        foreach ($legacyEquipment as $legacyId => $legacyEq) {
            if (! empty($legacyEq['certificate_path'])) {
                $certFiles[$legacyId] = $legacyEq['certificate_path'];
            }
        }

        $groupedPoints = [];
        foreach ($points as $p) {
            $groupedPoints[$p['certificate_id']][] = $p;
        }

        $imported = 0;
        $skipped = 0;

        DB::transaction(function () use (
            $certificates, $groupedPoints, $legacyIdToErpId,
            $certFiles, $certsSourceDir, $mediaService,
            &$imported, &$skipped
        ): void {
            $today = Carbon::today();

            foreach ($certificates as $c) {
                $legacyEqId = $c['legacy_eq_id'];
                $erpEqId = $legacyIdToErpId[$legacyEqId] ?? null;

                if ($erpEqId === null) {
                    $this->command?->warn("  [CERT SKIP] id={$c['id']} ref='{$c['reference']}' eq={$legacyEqId} no match");
                    $skipped++;

                    continue;
                }

                $calDate = $c['calibration_date'] ? Carbon::parse($c['calibration_date']) : null;
                $expiryDate = $calDate ? $calDate->copy()->addYear() : null;

                $status = CalibrationCertificateStatus::Draft;
                if ($c['is_locked']) {
                    $status = ($expiryDate && $expiryDate->lt($today))
                        ? CalibrationCertificateStatus::Expired
                        : CalibrationCertificateStatus::Approved;
                }

                $certPath = $certHash = $fileName = $fileSize = $mimeType = null;
                if (isset($certFiles[$legacyEqId]) && ! empty($certFiles[$legacyEqId])) {
                    $src = $certsSourceDir.'/'.$certFiles[$legacyEqId];
                    if (File::exists($src)) {
                        try {
                            $certPath = $mediaService->optimizePdf($src, 'equipment/certificates');
                            $disk = Storage::disk('public');
                            if ($disk->exists($certPath)) {
                                $raw = (string) $disk->get($certPath);
                                $certHash = hash('sha256', $raw);
                                $fileSize = strlen($raw);
                                $mimeType = 'application/pdf';
                                $fileName = $certFiles[$legacyEqId];
                            }
                        } catch (\Throwable $e) {
                            Log::warning("Could not optimise PDF: {$e->getMessage()}");
                        }
                    }
                }

                CalibrationCertificate::updateOrCreate(
                    ['id' => $c['id']],
                    [
                        'reference' => $c['reference'],
                        'certificate_type' => 'periodic',
                        'equipment_id' => $erpEqId,
                        'laboratory_name' => $c['laboratory_name'],
                        'calibration_date' => $calDate,
                        'expiry_date' => $expiryDate,
                        'validity_period_months' => 12,
                        'price' => $c['price'],
                        'is_locked' => $c['is_locked'],
                        'status' => $status,
                        'certificate_path' => $certPath,
                        'certificate_hash' => $certHash,
                        'file_name' => $fileName,
                        'file_size' => $fileSize,
                        'mime_type' => $mimeType,
                        'environmental_conditions' => [
                            'temperature_celsius' => 20.0,
                            'humidity_percent' => 50.0,
                            'atmospheric_pressure_hpa' => 1013.2,
                        ],
                        'remarks' => 'Imported from legacy system (ISO 17025 conformity archive).',
                        'approved_at' => $c['is_locked'] && $calDate ? $calDate->toDateTimeString() : null,
                        'locked_at' => $c['is_locked'] && $calDate ? $calDate->toDateTimeString() : null,
                    ]
                );

                if (isset($groupedPoints[$c['id']])) {
                    $cert = CalibrationCertificate::find($c['id']);
                    foreach ($groupedPoints[$c['id']] as $pt) {
                        $specId = $pt['specification_id'];
                        $specExists = EquipmentSpecification::where('id', $specId)->exists();
                        CalibrationPoint::updateOrCreate(
                            ['id' => $pt['id']],
                            [
                                'calibration_certificate_id' => $cert->id,
                                'equipment_specification_id' => $specExists ? $specId : null,
                                'nominal_value' => $pt['nominal_value'],
                                'correction' => $pt['correction'],
                                'uncertainty' => $pt['uncertainty'],
                                'status' => $pt['status'],
                            ]
                        );
                    }
                }

                $imported++;
            }
        });

        $this->command?->info("Migration finished - {$imported} imported, {$skipped} skipped.");
    }

    private function normaliseName(string $name): string
    {
        return mb_strtolower(preg_replace('/\s+/', ' ', trim($name)));
    }
}
