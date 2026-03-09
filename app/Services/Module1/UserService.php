<?php

namespace App\Services\Module1;

use App\Models\Core\Club;
use App\Models\Core\ClubUser;
use App\Models\Core\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * UserService — Module 1: Core Accounts & Roles
 *
 * Centralises all user lifecycle logic:
 *   - Creation with club attachment and role assignment
 *   - Profile and admin updates
 *   - Role management via Spatie
 *   - Activation / deactivation / removal
 *
 * Controllers must delegate all user mutations to this service.
 */
class UserService
{
    // -------------------------------------------------------------------------
    // Creation
    // -------------------------------------------------------------------------

    /**
     * Create a new user account, attach them to the given club, and assign
     * their Spatie role. Password is set to a secure random string; the user
     * will receive a password-set link (dispatched by the caller).
     *
     * @param  array{name: string, email: string, phone?: string|null, primary_role: string}  $data
     */
    public function create(array $data, Club $club): User
    {
        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'phone'     => $data['phone'] ?? null,
            'password'  => Hash::make(Str::random(24)),
            'is_active' => true,
        ]);

        $this->attachToClub($user, $club, $data['primary_role']);
        $this->assignRole($user, $data['primary_role']);

        return $user->fresh();
    }

    // -------------------------------------------------------------------------
    // Updates
    // -------------------------------------------------------------------------

    /**
     * Update a member's core fields and role.
     * Called by club admin. Email is intentionally excluded.
     *
     * @param  array{name: string, phone?: string|null, is_active: bool, primary_role: string}  $data
     */
    public function update(User $user, array $data): User
    {
        $user->update([
            'name'      => $data['name'],
            'phone'     => $data['phone'] ?? null,
            'is_active' => $data['is_active'],
        ]);

        $membership = ClubUser::where('user_id', $user->id)->firstOrFail();

        if ($membership->primary_role !== $data['primary_role']) {
            $membership->update(['primary_role' => $data['primary_role']]);
            $this->syncRole($user, $data['primary_role']);
        }

        return $user->fresh();
    }

    /**
     * Update only the fields a user may edit on their own profile.
     * Email and role are excluded from self-service updates.
     *
     * @param  array{name: string, phone?: string|null}  $data
     */
    public function updateProfile(User $user, array $data): User
    {
        $user->update([
            'name'  => $data['name'],
            'phone' => $data['phone'] ?? null,
        ]);

        return $user->fresh();
    }

    // -------------------------------------------------------------------------
    // Club Membership
    // -------------------------------------------------------------------------

    /**
     * Attach a user to a club with the given primary role.
     */
    public function attachToClub(User $user, Club $club, string $role): ClubUser
    {
        return ClubUser::create([
            'club_id'      => $club->id,
            'user_id'      => $user->id,
            'primary_role' => $role,
            'joined_at'    => now(),
        ]);
    }

    // -------------------------------------------------------------------------
    // Role Management
    // -------------------------------------------------------------------------

    /**
     * Assign a Spatie role to a user (creation flow).
     */
    public function assignRole(User $user, string $role): void
    {
        $user->assignRole($role);
    }

    /**
     * Replace all of a user's current roles with a single new role.
     */
    public function syncRole(User $user, string $role): void
    {
        $user->syncRoles([$role]);
    }

    // -------------------------------------------------------------------------
    // Activation
    // -------------------------------------------------------------------------

    /**
     * Restore access for a previously deactivated user.
     */
    public function activate(User $user): void
    {
        $user->update(['is_active' => true]);
    }

    /**
     * Suspend a user account without deleting any data.
     */
    public function deactivate(User $user): void
    {
        $user->update(['is_active' => false]);
    }

    /**
     * Remove a user from the club and deactivate their account.
     * User row is preserved for referential integrity across module tables.
     */
    public function remove(User $user): void
    {
        ClubUser::where('user_id', $user->id)->delete();

        $this->deactivate($user);
    }
}
