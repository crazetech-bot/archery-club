<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Services\Module1\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    /**
     * Show the profile edit form.
     *
     * GET /profile
     */
    public function edit(): Response
    {
        $user = Auth::user();

        return Inertia::render('Profile/Edit', [
            'user' => [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'phone'       => $user->phone,
                'avatar_path' => $user->avatar_path,
                'roles'       => $user->getRoleNames(),
            ],
        ]);
    }

    /**
     * Update the authenticated user's own profile fields.
     *
     * PUT /profile
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $this->userService->updateProfile(Auth::user(), $request->validated());

        return redirect()->route('profile.edit')
            ->with('success', 'Profile updated.');
    }
}
