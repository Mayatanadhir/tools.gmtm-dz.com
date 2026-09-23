<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CalibrationPointStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\HasActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable([
    'calibration_certificate_id',
    'equipment_specification_id',
    'nominal_value',
    'correction',
    'uncertainty',
    'status',
])]
class CalibrationPoint extends Model
{
    use HasActivity, HasFactory;

    protected $table = 'calibration_points';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nominal_value' => 'float',
            'correction' => 'float',
            'uncertainty' => 'float',
            'status' => CalibrationPointStatus::class,
        ];
    }

    /**
     * Lower confidence interval limit (C - U).
     */
    public function getLowerLimitAttribute(): float
    {
        return (float) ($this->correction - $this->uncertainty);
    }

    /**
     * Upper confidence interval limit (C + U).
     */
    public function getUpperLimitAttribute(): float
    {
        return (float) ($this->correction + $this->uncertainty);
    }

    /**
     * The parent calibration certificate.
     */
    public function calibrationCertificate(): BelongsTo
    {
        return $this->belongsTo(CalibrationCertificate::class, 'calibration_certificate_id');
    }

    /**
     * The targeted equipment specification parameter (if mapped).
     */
    public function equipmentSpecification(): BelongsTo
    {
        return $this->belongsTo(EquipmentSpecification::class, 'equipment_specification_id');
    }

    /**
     * Spatie Activity Log Options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'calibration_certificate_id',
                'equipment_specification_id',
                'nominal_value',
                'correction',
                'uncertainty',
                'status',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('calibration_point');
    }
}
