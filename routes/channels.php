<?php

/**
 * routes/channels.php — Laravel Echo broadcast channel authorisation
 *
 * Each channel callback returns true to allow, false to deny.
 * Private channels require the user to be authenticated before the
 * callback is even reached.
 *
 * Channel naming convention:
 *   tenant.{tenantId}.live          — all live events for a club (coach monitor)
 *   tenant.{tenantId}.session.{id}  — single live session events (per-archer view)
 *   user.{userId}                   — private per-user notifications
 */

use App\Models\Central\Tenant;
use App\Models\LiveSession;
use Illuminate\Support\Facades\Broadcast;

// ── tenant.{tenantId}.live ────────────────────────────────────────────────────
//
// Used by CoachMonitor.vue to receive all live session events for the club.
//
// Authorised for:
//   - club_admin : always
//   - coach      : always (they monitor all sessions in their club)
//   - archer     : denied (archers only see their own session channel)

Broadcast::channel('tenant.{tenantId}.live', function ($user, int $tenantId) {
    // Verify the user actually belongs to this tenant
    $belongsToTenant = $user->tenants()->where('tenants.id', $tenantId)->exists();

    if (! $belongsToTenant) {
        return false;
    }

    return $user->hasRole('club_admin') || $user->hasRole('coach');
});

// ── tenant.{tenantId}.session.{liveSessionId} ─────────────────────────────────
//
// Used by Scoring.vue for the archer's own session.
// Also joinable by a coach assigned to that archer.
//
// Authorised for:
//   - club_admin        : always
//   - coach             : if they are assigned to the archer in this session
//   - archer            : if this live session belongs to their training session

Broadcast::channel('tenant.{tenantId}.session.{liveSessionId}', function ($user, int $tenantId, int $liveSessionId) {
    // Verify tenant membership
    $belongsToTenant = $user->tenants()->where('tenants.id', $tenantId)->exists();

    if (! $belongsToTenant) {
        return false;
    }

    if ($user->hasRole('club_admin')) {
        return true;
    }

    $liveSession = LiveSession::with('trainingSession.archer', 'trainingSession.coach')
        ->find($liveSessionId);

    if (! $liveSession) {
        return false;
    }

    $trainingSession = $liveSession->trainingSession;

    // Archer: only their own session
    if ($user->hasRole('archer')) {
        return $trainingSession->archer->user_id === $user->id;
    }

    // Coach: only sessions they are assigned to
    if ($user->hasRole('coach')) {
        return $trainingSession->coach?->user_id === $user->id;
    }

    return false;
});

// ── user.{userId} ─────────────────────────────────────────────────────────────
//
// Private per-user channel for notifications (e.g. coach notes, booking reminders).
// An authenticated user may only subscribe to their own channel.

Broadcast::channel('user.{userId}', function ($user, int $userId) {
    return $user->id === $userId;
});
