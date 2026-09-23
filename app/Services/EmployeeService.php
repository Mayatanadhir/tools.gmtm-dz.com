<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\CannotDeleteAssignedEmployeeException;
use App\Interfaces\EmployeeRepositoryInterface;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class EmployeeService extends BaseService
{
    public function __construct(
        protected EmployeeRepositoryInterface $employeeRepository,
        protected MediaOptimizationService $mediaService
    ) {}

    /**
     * Create a new employee record and process profile photo.
     *
     * @param  array<string, mixed>  $data
     */
    public function createEmployee(array $data, ?UploadedFile $photo = null): Employee
    {
        return $this->executeInTransaction(function () use ($data, $photo): Employee {
            if ($photo !== null) {
                $storedPath = $this->mediaService->optimizeImage($photo, 'employees/avatars');
                $data['profile_photo_path'] = $storedPath;

                $disk = Storage::disk('public');
                if ($disk->exists($storedPath)) {
                    $data['photo_hash'] = hash('sha256', (string) $disk->get($storedPath));
                }

                // If linked to a user account, sync the user's photo with employee photo
                if (! empty($data['user_id'])) {
                    User::where('id', $data['user_id'])->update([
                        'profile_photo_path' => $storedPath,
                        'photo_hash' => $data['photo_hash'] ?? null,
                    ]);
                }
            } elseif (! empty($data['user_id'])) {
                // If no photo was uploaded, adopt the linked user's photo
                $user = User::find($data['user_id']);
                if ($user && ! blank($user->profile_photo_path)) {
                    $data['profile_photo_path'] = $user->profile_photo_path;
                    $data['photo_hash'] = $user->photo_hash;
                }
            }

            /** @var Employee */
            return $this->employeeRepository->create($data);
        });
    }

    /**
     * Update an employee record, handle photo changes, and sync with linked user.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateEmployee(Employee $employee, array $data, ?UploadedFile $photo = null, bool $removePhoto = false): bool
    {
        return $this->executeInTransaction(function () use ($employee, $data, $photo, $removePhoto): bool {
            $targetUserId = $data['user_id'] ?? $employee->user_id;

            if ($removePhoto) {
                if (! blank($employee->profile_photo_path)) {
                    $this->mediaService->safeDelete($employee->profile_photo_path, 'public', $employee->id);
                    $data['profile_photo_path'] = null;
                    $data['photo_hash'] = null;

                    if ($targetUserId) {
                        User::where('id', $targetUserId)->update([
                            'profile_photo_path' => null,
                            'photo_hash' => null,
                        ]);
                    }
                }
            } elseif ($photo !== null) {
                $storedPath = $this->mediaService->optimizeImage($photo, 'employees/avatars');
                $data['profile_photo_path'] = $storedPath;

                $disk = Storage::disk('public');
                if ($disk->exists($storedPath)) {
                    $data['photo_hash'] = hash('sha256', (string) $disk->get($storedPath));
                }

                // Sync with linked user photo
                if ($targetUserId) {
                    User::where('id', $targetUserId)->update([
                        'profile_photo_path' => $storedPath,
                        'photo_hash' => $data['photo_hash'] ?? null,
                    ]);
                }
            } else {
                // When linked to a user without a separate photo, inherit user's photo
                if (! empty($data['user_id']) && blank($employee->profile_photo_path)) {
                    $user = User::find($data['user_id']);
                    if ($user && ! blank($user->profile_photo_path)) {
                        $data['profile_photo_path'] = $user->profile_photo_path;
                        $data['photo_hash'] = $user->photo_hash;
                    }
                } elseif (! empty($data['user_id']) && ! blank($employee->profile_photo_path)) {
                    // Conversely, update user if user has no photo
                    $user = User::find($data['user_id']);
                    if ($user && blank($user->profile_photo_path)) {
                        $user->update([
                            'profile_photo_path' => $employee->profile_photo_path,
                            'photo_hash' => $employee->photo_hash,
                        ]);
                    }
                }
            }

            return $this->employeeRepository->update($employee->id, $data);
        });
    }

    /**
     * Delete an employee record after checking operational mission constraints.
     *
     * @throws CannotDeleteAssignedEmployeeException
     */
    public function deleteEmployee(Employee $employee): bool
    {
        return $this->executeInTransaction(function () use ($employee): bool {
            if (Schema::hasTable('mission_orders') && DB::table('mission_orders')->where('employee_id', $employee->id)->exists()) {
                throw new CannotDeleteAssignedEmployeeException;
            }

            if (Schema::hasTable('missions') && DB::table('missions')->where('leader_id', $employee->id)->exists()) {
                throw new CannotDeleteAssignedEmployeeException;
            }

            return $this->employeeRepository->delete($employee->id);
        });
    }
}
