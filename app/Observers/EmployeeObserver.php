<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Employee;
use App\Services\MediaOptimizationService;
use Illuminate\Support\Facades\Storage;

class EmployeeObserver
{
    /**
     * Handle the Employee "saving" event.
     * Automatically computes or clears the photo_hash in the background based on profile_photo_path.
     */
    public function saving(Employee $employee): void
    {
        if ($employee->isDirty('profile_photo_path')) {
            $oldPath = $employee->getOriginal('profile_photo_path');
            if (! blank($oldPath) && $oldPath !== $employee->profile_photo_path) {
                app(MediaOptimizationService::class)->safeDelete((string) $oldPath, 'public', $employee->id);
            }

            if (blank($employee->profile_photo_path)) {
                $employee->photo_hash = null;
            } else {
                $disk = Storage::disk('public');
                if ($disk->exists($employee->profile_photo_path)) {
                    $employee->photo_hash = hash('sha256', (string) $disk->get($employee->profile_photo_path));
                } else {
                    $employee->photo_hash = hash('sha256', (string) $employee->profile_photo_path);
                }
            }
        }
    }

    /**
     * Handle the Employee "deleted" event.
     */
    public function deleted(Employee $employee): void
    {
        if ($employee->isForceDeleting() && ! blank($employee->profile_photo_path)) {
            app(MediaOptimizationService::class)->safeDelete($employee->profile_photo_path, 'public', $employee->id);
        }
    }
}
