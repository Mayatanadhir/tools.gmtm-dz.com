<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Equipment;
use App\Services\MediaOptimizationService;
use Illuminate\Support\Facades\Storage;

class EquipmentObserver
{
    /**
     * Handle the Equipment "saving" event.
     * Automatically computes or clears the image_hash in the background based on image_path.
     */
    public function saving(Equipment $equipment): void
    {
        if ($equipment->isDirty('image_path')) {
            $oldPath = $equipment->getOriginal('image_path');
            if (! blank($oldPath) && $oldPath !== $equipment->image_path) {
                app(MediaOptimizationService::class)->safeDelete((string) $oldPath, 'public', $equipment->id);
            }

            if (blank($equipment->image_path)) {
                $equipment->image_hash = null;
            } else {
                $disk = Storage::disk('public');
                if ($disk->exists($equipment->image_path)) {
                    $equipment->image_hash = hash('sha256', (string) $disk->get($equipment->image_path));
                } else {
                    $equipment->image_hash = hash('sha256', (string) $equipment->image_path);
                }
            }
        }

        if ($equipment->isDirty('certificate_path')) {
            $oldCert = $equipment->getOriginal('certificate_path');
            if (! blank($oldCert) && $oldCert !== $equipment->certificate_path) {
                app(MediaOptimizationService::class)->safeDelete((string) $oldCert, 'public', $equipment->id);
            }
        }
    }

    /**
     * Handle the Equipment "forceDeleted" event.
     */
    public function forceDeleted(Equipment $equipment): void
    {
        $mediaService = app(MediaOptimizationService::class);
        if (! blank($equipment->image_path)) {
            $mediaService->safeDelete($equipment->image_path, 'public', $equipment->id);
        }
        if (! blank($equipment->certificate_path)) {
            $mediaService->safeDelete($equipment->certificate_path, 'public', $equipment->id);
        }
    }
}
