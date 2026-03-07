<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Archer;
use App\Models\LiveArrow;
use App\Models\LiveEnd;
use App\Models\LiveSession;
use App\Models\TrainingSession;
use App\Services\ScoreCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LiveScoringApiController extends Controller
{
    private function archer(): Archer
    {
        return Archer::where('user_id', Auth::id())->firstOrFail();
    }

    /**
     * POST /api/v1/live/{trainingSession}/start
     * Start a live session, or return the existing active one.
     */
    public function start(Request $request, TrainingSession $trainingSession): JsonResponse
    {
        $archer = $this->archer();
        abort_if($trainingSession->archer_id !== $archer->id, 403);

        $validated = $request->validate([
            'arrows_per_end' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        // Return existing active session if one already exists
        $existing = $trainingSession->liveSession;
        if ($existing && $existing->isActive()) {
            return response()->json($this->liveSessionShape($existing), 200);
        }

        $liveSession = LiveSession::create([
            'training_session_id' => $trainingSession->id,
            'status'              => 'active',
            'arrows_per_end'      => $validated['arrows_per_end'],
            'started_at'          => now(),
        ]);

        return response()->json($this->liveSessionShape($liveSession), 201);
    }

    /**
     * GET /api/v1/live/{liveSession}
     * Return current state of a live session.
     */
    public function show(LiveSession $liveSession): JsonResponse
    {
        $archer = $this->archer();
        abort_if($liveSession->trainingSession?->archer_id !== $archer->id, 403);

        $liveSession->load('ends.arrows');

        return response()->json($this->liveSessionShape($liveSession));
    }

    /**
     * POST /api/v1/live/{liveSession}/end
     * Submit an end with arrow scores.
     * Body: { arrows: ['X', '10', '9', '8', '7', '6'] }
     */
    public function submitEnd(Request $request, LiveSession $liveSession): JsonResponse
    {
        $archer = $this->archer();
        abort_if($liveSession->trainingSession?->archer_id !== $archer->id, 403);
        abort_unless($liveSession->isActive(), 422, 'Session is not active.');

        $validated = $request->validate([
            'arrows'   => ['required', 'array', 'min:1', 'max:12'],
            'arrows.*' => ['required', 'string', 'regex:/^(X|10|[1-9]|M)$/i'],
            'notes'    => ['nullable', 'string', 'max:500'],
        ]);

        $scores    = array_map('strtoupper', $validated['arrows']);
        $totals    = ScoreCalculator::calculateEnd($scores);
        $endNumber = $liveSession->ends()->count() + 1;

        $end = DB::transaction(function () use ($liveSession, $scores, $totals, $endNumber, $validated) {
            $end = LiveEnd::create([
                'live_session_id' => $liveSession->id,
                'end_number'      => $endNumber,
                'total_score'     => $totals['total_score'],
                'x_count'         => $totals['x_count'],
                'ten_count'       => $totals['ten_count'],
                'notes'           => $validated['notes'] ?? null,
            ]);

            foreach ($scores as $i => $score) {
                LiveArrow::create([
                    'live_end_id'  => $end->id,
                    'arrow_number' => $i + 1,
                    'score'        => $score,
                ]);
            }

            return $end;
        });

        // Reload session totals
        $liveSession->load('ends.arrows');

        return response()->json([
            'end'     => [
                'id'          => $end->id,
                'end_number'  => $end->end_number,
                'total_score' => $end->total_score,
                'x_count'     => $end->x_count,
                'ten_count'   => $end->ten_count,
            ],
            'session' => [
                'total_score'     => $liveSession->total_score,
                'x_count'         => $liveSession->x_count,
                'average_per_end' => $liveSession->average_per_end,
                'ends_count'      => $liveSession->ends->count(),
            ],
        ], 201);
    }

    /**
     * PATCH /api/v1/live/{liveSession}/complete
     * Mark the live session as completed.
     */
    public function complete(LiveSession $liveSession): JsonResponse
    {
        $archer = $this->archer();
        abort_if($liveSession->trainingSession?->archer_id !== $archer->id, 403);
        abort_unless($liveSession->isActive(), 422, 'Session is not active.');

        $liveSession->complete();
        $liveSession->trainingSession->update(['ended_at' => now()]);
        $liveSession->load('ends');

        return response()->json([
            'id'              => $liveSession->id,
            'status'          => $liveSession->status,
            'total_score'     => $liveSession->total_score,
            'x_count'         => $liveSession->x_count,
            'ends_count'      => $liveSession->ends->count(),
            'average_per_end' => $liveSession->average_per_end,
            'ended_at'        => $liveSession->ended_at,
        ]);
    }

    // -------------------------------------------------------------------------

    private function liveSessionShape(LiveSession $s): array
    {
        $s->loadMissing('ends.arrows');
        return [
            'id'              => $s->id,
            'status'          => $s->status,
            'arrows_per_end'  => $s->arrows_per_end,
            'started_at'      => $s->started_at,
            'total_score'     => $s->total_score,
            'x_count'         => $s->x_count,
            'ten_count'       => $s->ten_count,
            'average_per_end' => $s->average_per_end,
            'ends_count'      => $s->ends->count(),
        ];
    }
}
