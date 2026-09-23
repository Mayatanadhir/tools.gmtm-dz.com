<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\MediaOptimizationService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        protected UserService $userService,
        protected MediaOptimizationService $mediaOptimizationService,
    ) {}

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if ($request->hasFile('photo')) {
            if ($user->profile_photo_path) {
                $this->mediaOptimizationService->safeDelete($user->profile_photo_path, 'public', $user->id);
            }
            $updateData['profile_photo_path'] = $this->mediaOptimizationService->optimizeAvatar($request->file('photo'), 'photos', 'public');
        } elseif ($request->boolean('remove_photo')) {
            if ($user->profile_photo_path) {
                $this->mediaOptimizationService->safeDelete($user->profile_photo_path, 'public', $user->id);
            }
            $updateData['profile_photo_path'] = null;
        }

        $this->userService->updateProfile($user, $updateData);

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Anti-lockout guard: The Super-Admin account is protected and cannot be deleted from the profile
        if ($user->isSuperAdmin() || $user->hasRole('Super-Admin')) {
            return Redirect::route('profile.edit')->withErrors(
                ['userDeletion' => __('The Super-Admin account is protected by the anti-lockout mechanism and cannot be deleted.')],
                'userDeletion'
            );
        }

        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        Auth::logout();

        $this->userService->deleteAccount($user);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
