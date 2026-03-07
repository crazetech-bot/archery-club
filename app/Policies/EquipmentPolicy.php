<?php

namespace App\Policies;

use App\Models\EquipmentSetup;
use App\Models\Central\User;

/**
 * EquipmentPolicy — controls access to EquipmentSetup records.
 *
 * club_admin — full access (for admin support)
 * coach      — read-only view of their archers' equipment
 * archer     — full access to their own equipment setups
 */
class EquipmentPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('club_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, EquipmentSetup $setup): bool
    {
        if ($user->hasRole('archer')) {
            return $setup->archer?->user_id === $user->id;
        }

        if ($user->hasRole('coach')) {
            $coach = $user->coach ?? null;
            return $coach && $setup->archer?->coach_id === $coach->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('archer');
    }

    public function update(User $user, EquipmentSetup $setup): bool
    {
        return $user->hasRole('archer')
            && $setup->archer?->user_id === $user->id;
    }

    public function delete(User $user, EquipmentSetup $setup): bool
    {
        return $user->hasRole('archer')
            && $setup->archer?->user_id === $user->id
            && ! $setup->is_current;
    }
}
