<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Core\Club;
use App\Models\Core\ClubUser;
use App\Models\Core\User;
use App\Services\Module1\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MemberController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    /**
     * List all club members with optional search and role filter.
     *
     * GET /admin/members
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search');
        $role   = $request->string('role');

        $club = Club::firstOrFail();

        $members = $club->users()
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            }))
            ->when($role, fn ($q) => $q->wherePivot('primary_role', $role))
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => [
                'id'           => $user->id,
                'name'         => $user->name,
                'email'        => $user->email,
                'phone'        => $user->phone,
                'avatar_path'  => $user->avatar_path,
                'is_active'    => $user->is_active,
                'primary_role' => $user->pivot->primary_role,
                'joined_at'    => $user->pivot->joined_at,
            ]);

        return Inertia::render('Admin/Members', [
            'members' => $members,
            'filters' => [
                'search' => (string) $search,
                'role'   => (string) $role,
            ],
        ]);
    }

    /**
     * Show a single member profile.
     *
     * GET /admin/members/{user}
     */
    public function show(User $user): Response
    {
        $membership = ClubUser::where('user_id', $user->id)->firstOrFail();

        return Inertia::render('Admin/MemberDetail', [
            'member' => [
                'id'           => $user->id,
                'name'         => $user->name,
                'email'        => $user->email,
                'phone'        => $user->phone,
                'avatar_path'  => $user->avatar_path,
                'is_active'    => $user->is_active,
                'primary_role' => $membership->primary_role,
                'joined_at'    => $membership->joined_at,
                'roles'        => $user->getRoleNames(),
            ],
        ]);
    }

    /**
     * Create a new user and attach them to the club.
     *
     * POST /admin/members
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $club = Club::firstOrFail();

        $user = $this->userService->create($request->validated(), $club);

        // TODO: dispatch welcome email with password-set link (future module)

        return redirect()->route('admin.members.index')
            ->with('success', "{$user->name} added as {$request->validated()['primary_role']}.");
    }

    /**
     * Update a member's details and role.
     *
     * PUT /admin/members/{user}
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->userService->update($user, $request->validated());

        return redirect()->route('admin.members.index')
            ->with('success', "{$user->name} updated.");
    }

    /**
     * Remove a member from the club and deactivate their account.
     *
     * DELETE /admin/members/{user}
     */
    public function destroy(User $user): RedirectResponse
    {
        $name = $user->name;

        $this->userService->remove($user);

        return redirect()->route('admin.members.index')
            ->with('success', "{$name} removed from the club.");
    }
}
