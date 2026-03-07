<?php

namespace App\Policies;

use App\Models\LiveSession;
use App\Models\Central\User;

/**
 * ScorePolicy — controls access to LiveSession (and its ends/arrows).
 *
 * club_admin — full access
 * coach      — read-only access to their archers' sessions; may add notes
 * archer     — full access to their own live sessions
 */
class ScorePolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('club_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Archers see their own session; coaches see their archers' sessions.
     */
    public function view(User $user, LiveSession $liveSession): bool
    {
        $archer = $liveSession->trainingSession?->archer;

        if ($user->hasRole('archer')) {
            return $archer?->user_id === $user->id;
        }

        if ($user->hasRole('coach')) {
            $coach = $user->coach ?? null;
            return $coach && $archer?->coach_id === $coach->id;
        }

        return false;
    }

    /**
     * Only the owning archer can start a live session.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('archer');
    }

    /**
     * Only the owning archer can submit ends / complete.
     */
    public function update(User $user, LiveSession $liveSession): bool
    {
        return $user->hasRole('archer')
            && $liveSession->trainingSession?->archer?->user_id === $user->id
            && $liveSession->isActive();
    }

    /**
     * Coaches may add notes to any live session belonging to their archers.
     */
    public function addNote(User $user, LiveSession $liveSession): bool
    {
        if ($user->hasRole('archer')) {
            return $liveSession->trainingSession?->archer?->user_id === $user->id;
        }

        if ($user->hasRole('coach')) {
            $coach = $user->coach ?? null;
            return $coach
                && $liveSession->trainingSession?->archer?->coach_id === $coach->id;
        }

        return false;
    }

    /**
     * Only the owning archer can delete their live session.
     */
    public function delete(User $user, LiveSession $liveSession): bool
    {
        return $user->hasRole('archer')
            && $liveSession->trainingSession?->archer?->user_id === $user->id
            && ! $liveSession->isActive();
    }
}
