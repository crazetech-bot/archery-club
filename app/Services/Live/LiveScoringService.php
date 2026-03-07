<?php

namespace App\Services\Live;

use App\Events\Live\EndSubmitted;
use App\Events\Live\SessionCompleted;
use App\Events\Live\SessionStarted;
use App\Models\LiveArrow;
use App\Models\LiveEnd;
use App\Models\LiveSession;
use App\Models\TrainingSession;
use Illuminate\Support\Facades\DB;

class LiveScoringService
{
    /**
     * Start a new live session for a training session.
     *
     * @throws \RuntimeException if the training session already has an active live session.
     */
    public function startSession(TrainingSession $trainingSession, int $tenantId, int $arrowsPerEnd = 6): LiveSession
    {
        // Prevent duplicate active sessions
        $existing = LiveSession::where('training_session_id', $trainingSession->id)
            ->where('status', 'active')
            ->first();

        if ($existing) {
            throw new \RuntimeException('This training session already has an active live session.');
        }

        $liveSession = LiveSession::create([
            'training_session_id' => $trainingSession->id,
            'status'              => 'active',
            'arrows_per_end'      => $arrowsPerEnd,
            'started_at'          => now(),
        ]);

        event(new SessionStarted($liveSession, $tenantId));

        return $liveSession;
    }

    /**
     * Submit a completed end with all its arrow scores.
     *
     * @param  array<string>  $arrows  e.g. ['X', '10', '9', '8', '7', '6']
     *
     * @throws \RuntimeException if the session is not active.
     * @throws \RuntimeException if end_number is out of sequence.
     */
    public function submitEnd(LiveSession $liveSession, array $arrows, int $endNumber, int $tenantId): LiveEnd
    {
        if ($liveSession->status !== 'active') {
            throw new \RuntimeException('Cannot submit an end to a session that is not active.');
        }

        // Guard against duplicate or out-of-order end numbers
        $expectedEndNumber = $liveSession->ends()->count() + 1;

        if ($endNumber !== $expectedEndNumber) {
            throw new \RuntimeException(
                "Expected end number {$expectedEndNumber}, got {$endNumber}."
            );
        }

        $liveEnd = DB::transaction(function () use ($liveSession, $arrows, $endNumber) {
            // 1. Create the end record (scores are computed below)
            $liveEnd = LiveEnd::create([
                'live_session_id' => $liveSession->id,
                'end_number'      => $endNumber,
                'total_score'     => 0,
                'x_count'         => 0,
                'ten_count'       => 0,
            ]);

            // 2. Create individual arrow records
            foreach ($arrows as $index => $score) {
                LiveArrow::create([
                    'live_end_id'  => $liveEnd->id,
                    'arrow_number' => $index + 1,
                    'score'        => $score,
                    'position_x'   => null,
                    'position_y'   => null,
                ]);
            }

            // 3. Recalculate totals from the arrow records
            $liveEnd->refresh();
            $liveEnd->recalculate();

            return $liveEnd->fresh('arrows');
        });

        event(new EndSubmitted($liveSession, $liveEnd, $tenantId));

        return $liveEnd;
    }

    /**
     * Mark a live session as completed.
     *
     * @throws \RuntimeException if the session is already completed.
     */
    public function completeSession(LiveSession $liveSession, int $tenantId): LiveSession
    {
        if ($liveSession->status === 'completed') {
            throw new \RuntimeException('This session is already completed.');
        }

        $liveSession->complete(); // sets status + ended_at (defined on the model)

        event(new SessionCompleted($liveSession->fresh(), $tenantId));

        return $liveSession;
    }

    /**
     * Resolve the current tenant ID from Stancl Tenancy.
     * Controllers call this helper rather than calling tenant() directly,
     * keeping the service testable without a full tenancy boot.
     */
    public static function resolveTenantId(): int
    {
        return (int) tenant('id');
    }
}
