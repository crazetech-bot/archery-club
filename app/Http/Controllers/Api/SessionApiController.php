<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Archer;
use App\Models\TrainingSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionApiController extends Controller
{
    private function archer(): Archer
    {
        return Archer::where('user_id', Auth::id())->firstOrFail();
    }

    /**
     * GET /api/v1/sessions
     * Paginated list of training sessions for the authenticated archer.
     */
    public function index(Request $request): JsonResponse
    {
        $archer = $this->archer();

        $sessions = TrainingSession::with('liveSession')
            ->where('archer_id', $archer->id)
            ->orderByDesc('started_at')
            ->paginate(20)
            ->through(fn (TrainingSession $s) => [
                'id'              => $s->id,
                'round_type'      => $s->round_type,
                'distance_metres' => $s->distance_metres,
                'started_at'      => $s->started_at,
                'ended_at'        => $s->ended_at,
                'total_score'     => $s->liveSession?->total_score,
                'max_score'       => $s->max_score,
                'x_count'         => $s->liveSession?->x_count ?? 0,
                'status'          => $s->liveSession?->status ?? 'no_live_session',
            ]);

        return response()->json($sessions);
    }

    /**
     * POST /api/v1/sessions
     * Create a new training session ready for live scoring.
     */
    public function store(Request $request): JsonResponse
    {
        $archer = $this->archer();

        $validated = $request->validate([
            'round_type'      => ['nullable', 'string', 'max:100'],
            'distance_metres' => ['nullable', 'integer', 'min:1', 'max:200'],
            'max_score'       => ['nullable', 'integer', 'min:1'],
            'notes'           => ['nullable', 'string', 'max:2000'],
        ]);

        $session = TrainingSession::create(array_merge($validated, [
            'archer_id'  => $archer->id,
            'started_at' => now(),
        ]));

        return response()->json([
            'id'              => $session->id,
            'round_type'      => $session->round_type,
            'distance_metres' => $session->distance_metres,
            'started_at'      => $session->started_at,
        ], 201);
    }

    /**
     * GET /api/v1/sessions/{session}
     * Full session detail with live session data and ends.
     */
    public function show(TrainingSession $session): JsonResponse
    {
        $archer = $this->archer();
        abort_if($session->archer_id !== $archer->id, 403);

        $liveSession = $session->liveSession?->load('ends.arrows');

        return response()->json([
            'id'              => $session->id,
            'round_type'      => $session->round_type,
            'distance_metres' => $session->distance_metres,
            'started_at'      => $session->started_at,
            'ended_at'        => $session->ended_at,
            'max_score'       => $session->max_score,
            'live_session'    => $liveSession ? [
                'id'              => $liveSession->id,
                'status'          => $liveSession->status,
                'arrows_per_end'  => $liveSession->arrows_per_end,
                'total_score'     => $liveSession->total_score,
                'x_count'         => $liveSession->x_count,
                'ten_count'       => $liveSession->ten_count,
                'average_per_end' => $liveSession->average_per_end,
                'ends'            => $liveSession->ends->map(fn ($end) => [
                    'id'          => $end->id,
                    'end_number'  => $end->end_number,
                    'total_score' => $end->total_score,
                    'x_count'     => $end->x_count,
                    'ten_count'   => $end->ten_count,
                    'arrows'      => $end->arrows->map(fn ($a) => [
                        'id'    => $a->id,
                        'score' => $a->score,
                    ]),
                ]),
            ] : null,
        ]);
    }
}
