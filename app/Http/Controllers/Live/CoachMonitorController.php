<?php

namespace App\Http\Controllers\Live;

use App\Http\Controllers\Controller;
use App\Models\Archer;
use App\Models\Coach;
use App\Models\LiveSession;
use App\Models\TrainingSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CoachMonitorController extends Controller
{
    /**
     * GET /live/monitor
     * Accessible by coach and club_admin.
     */
    public function index(): Response
    {
        $user = Auth::user();

        // Admins see all archers; coaches see only their own
        $archerQuery = Archer::query();

        if (! $user->hasRole('club_admin')) {
            $coach = Coach::where('user_id', $user->id)->firstOrFail();
            $archerQuery->where('coach_id', $coach->id);
        }

        $archers = $archerQuery->get();

        // Build per-archer session data
        $archerSessions = $archers->map(function (Archer $archer) {
            $activeSession = LiveSession::whereHas(
                'trainingSession',
                fn ($q) => $q->where('archer_id', $archer->id)
            )
                ->where('status', 'active')
                ->with(['trainingSession', 'ends.arrows'])
                ->first();

            $ends = $activeSession
                ? $activeSession->ends->map(fn ($end) => [
                    'id'          => $end->id,
                    'end_number'  => $end->end_number,
                    'total_score' => $end->total_score,
                    'x_count'     => $end->x_count,
                    'ten_count'   => $end->ten_count,
                    'arrows'      => $end->arrows->map(fn ($a) => [
                        'id'    => $a->id,
                        'score' => $a->score,
                    ]),
                ])
                : [];

            return [
                'archer'       => [
                    'id'       => $archer->id,
                    'name'     => $archer->user?->name ?? "Archer #{$archer->id}",
                    'category' => $archer->category,
                ],
                'live_session' => $activeSession ? [
                    'id'              => $activeSession->id,
                    'status'          => $activeSession->status,
                    'arrows_per_end'  => $activeSession->arrows_per_end,
                    'total_score'     => $activeSession->total_score,
                    'x_count'         => $activeSession->x_count,
                    'average_per_end' => $activeSession->average_per_end,
                    'started_at'      => $activeSession->created_at,
                    'round_type'      => $activeSession->trainingSession?->round_type,
                ] : null,
                'ends'         => $ends,
            ];
        })->values();

        return Inertia::render('Live/CoachMonitor', [
            'archerSessions' => $archerSessions,
            'tenantId'       => (int) tenant('id'),
        ]);
    }

    /**
     * GET /live/coach/sessions  — JSON polling fallback for Echo.
     * Returns the same shape as the Inertia prop so the frontend can merge it.
     */
    public function sessions(): JsonResponse
    {
        $user        = Auth::user();
        $archerQuery = Archer::query();

        if (! $user->hasRole('club_admin')) {
            $coach = Coach::where('user_id', $user->id)->firstOrFail();
            $archerQuery->where('coach_id', $coach->id);
        }

        $archerSessions = $archerQuery->get()->map(function (Archer $archer) {
            $activeSession = LiveSession::whereHas(
                'trainingSession',
                fn ($q) => $q->where('archer_id', $archer->id)
            )
                ->where('status', 'active')
                ->with(['trainingSession', 'ends.arrows'])
                ->first();

            return [
                'archer'       => [
                    'id'       => $archer->id,
                    'name'     => $archer->user?->name ?? "Archer #{$archer->id}",
                    'category' => $archer->category,
                ],
                'live_session' => $activeSession ? [
                    'id'              => $activeSession->id,
                    'status'          => $activeSession->status,
                    'arrows_per_end'  => $activeSession->arrows_per_end,
                    'total_score'     => $activeSession->total_score,
                    'x_count'         => $activeSession->x_count,
                    'average_per_end' => $activeSession->average_per_end,
                    'started_at'      => $activeSession->created_at,
                    'round_type'      => $activeSession->trainingSession?->round_type,
                ] : null,
                'ends' => $activeSession
                    ? $activeSession->ends->map(fn ($end) => [
                        'id'          => $end->id,
                        'end_number'  => $end->end_number,
                        'total_score' => $end->total_score,
                        'x_count'     => $end->x_count,
                        'ten_count'   => $end->ten_count,
                        'arrows'      => $end->arrows->map(fn ($a) => ['id' => $a->id, 'score' => $a->score]),
                    ])
                    : [],
            ];
        })->values();

        return response()->json(['archerSessions' => $archerSessions]);
    }

    /**
     * POST /live/session/{liveSession}/note
     * Coach adds a note to a live session.
     */
    public function addNote(Request $request, LiveSession $liveSession): JsonResponse
    {
        $data = $request->validate([
            'note' => ['required', 'string', 'max:500'],
        ]);

        // Store as metadata on the live session — or log it
        \Illuminate\Support\Facades\Log::info('Coach note added', [
            'live_session_id' => $liveSession->id,
            'coach_id'        => Auth::id(),
            'note'            => $data['note'],
        ]);

        // Attach to the most recent end's notes field if the end exists
        $latestEnd = $liveSession->ends()->orderByDesc('end_number')->first();
        if ($latestEnd) {
            $latestEnd->update(['notes' => $data['note']]);
        }

        return response()->json(['ok' => true]);
    }
}
