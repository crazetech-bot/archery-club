<?php

namespace App\Policies;

use App\Models\TrainingSession;
use App\Models\Central\User;

/**
 * SessionPolicy — controls access to TrainingSession records.
 *
 * Role assumptions (via Spatie):
 *   club_admin — full access to all sessions
 *   coach      — read their own archers' sessions; cannot create/delete
 *   archer     — can only view/delete their own sessions
 */
class SessionPolicy
{
    /**
     * club_admin bypasses all checks.
     */
    public function before(User $user): ?bool
    {
        return $user->hasRole('club_admin') ? true : null;
    }

    /**
     * Any authenticated tenant user may list sessions (filtered in controller).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Archers see only their own; coaches see their assigned archers'.
     */
    public function view(User $user, TrainingSession $session): bool
    {
        if ($user->hasRole('archer')) {
            return $session->archer?->user_id === $user->id;
        }

        if ($user->hasRole('coach')) {
            $coach = $user->coach ?? null;
            return $coach && $session->archer?->coach_id === $coach->id;
        }

        return false;
    }

    /**
     * Only archers can create sessions (for themselves).
     */
    public function create(User $user): bool
    {
        return $user->hasRole('archer');
    }

    /**
     * Archers can update only their own sessions while active.
     */
    public function update(User $user, TrainingSession $session): bool
    {
        if ($user->hasRole('archer')) {
            return $session->archer?->user_id === $user->id
                && $session->liveSession?->status === 'active';
        }

        return false;
    }

    /**
     * Archers can delete their own sessions; coaches cannot delete.
     */
    public function delete(User $user, TrainingSession $session): bool
    {
        return $user->hasRole('archer')
            && $session->archer?->user_id === $user->id;
    }
}
