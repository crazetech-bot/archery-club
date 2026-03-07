<?php

namespace App\Http\Controllers\Live;

use App\Http\Controllers\Controller;
use App\Models\Archer;
use App\Models\Coach;
use App\Models\CoachNote;
use App\Models\LiveSession;
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

        [$archerQuery, $coach] = $this->resolveScope($user);

        $cards = $this->buildCards($archerQuery->get());

        return Inertia::render('Live/CoachMonitor', [
            'coach'       => [
                'id'    => $coach?->id,
                'name'  => $user->name,
                'level' => $coach?->level,
            ],
            'archerCards' => $cards,
            'tenantId'    => (int) tenant('id'),
        ]);
    }

    /**
     * GET /live/coach/sessions — JSON polling fallback.
     * Returns the same flat card shape as the Inertia prop.
     */
    public function sessions(): JsonResponse
    {
        $user = Auth::user();

        [$archerQuery] = $this->resolveScope($user);

        $cards = $this->buildCards($archerQuery->get());

        return response()->json(['archerCards' => $cards]);
    }

    /**
     * POST /live/session/{liveSession}/note
     * Coach adds a real-time note — persisted as a CoachNote and tagged on the latest end.
     */
    public function addNote(Request $request, LiveSession $liveSession): JsonResponse
    {
        $data = $request->validate([
            'note' => ['required', 'string', 'max:500'],
        ]);

        $user  = Auth::user();
        $coach = Coach::where('user_id', $user->id)->first();

        if ($coach) {
            $archerId = $liveSession->trainingSession?->archer_id;

            if ($archerId) {
                CoachNote::create([
                    'archer_id' => $archerId,
                    'coach_id'  => $coach->id,
                    'content'   => $data['note'],
                ]);
            }
        }

        // Tag the latest end so the archer can see it in session
        $latestEnd = $liveSession->ends()->orderByDesc('end_number')->first();
        if ($latestEnd) {
            $latestEnd->update(['notes' => $data['note']]);
        }

        return response()->json(['ok' => true]);
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    /**
     * Returns [$archerQuery, $coach|null].
     * Admins see all archers; coaches see only their assigned archers.
     */
    private function resolveScope($user): array
    {
        $archerQuery = Archer::query();
        $coach       = null;

        if (! $user->hasRole('club_admin')) {
            $coach = Coach::where('user_id', $user->id)->firstOrFail();
            $archerQuery->where('coach_id', $coach->id);
        }

        return [$archerQuery, $coach];
    }

    /**
     * Build normalized flat card objects from a collection of Archer models.
     * Each card has the same shape whether or not an active session exists.
     */
    private function buildCards(\Illuminate\Support\Collection $archers): \Illuminate\Support\Collection
    {
        return $archers->map(function (Archer $archer) {
            // Load the most recent live session (active preferred, then completed)
            $liveSession = LiveSession::whereHas(
                'trainingSession',
                fn ($q) => $q->where('archer_id', $archer->id)
            )
                ->whereIn('status', ['active', 'completed'])
                ->with(['trainingSession', 'ends.arrows'])
                ->orderByRaw("FIELD(status, 'active', 'completed')")
                ->latest()
                ->first();

            $ends = $liveSession
                ? $liveSession->ends->map(fn ($end) => [
                    'id'          => $end->id,
                    'end_number'  => $end->end_number,
                    'total_score' => $end->total_score,
                    'x_count'     => $end->x_count,
                    'ten_count'   => $end->ten_count,
                    'notes'       => $end->notes,
                    'arrows'      => $end->arrows->map(fn ($a) => [
                        'id'    => $a->id,
                        'score' => $a->score,
                    ])->values()->all(),
                ])->values()->all()
                : [];

            return [
                'id'              => $liveSession?->id,
                'status'          => $liveSession?->status ?? 'idle',
                'archer'          => [
                    'id'       => $archer->id,
                    'name'     => $archer->user?->name ?? "Archer #{$archer->id}",
                    'category' => $archer->category,
                ],
                'round_type'      => $liveSession?->trainingSession?->round_type,
                'distance_metres' => $liveSession?->trainingSession?->distance_metres,
                'arrows_per_end'  => $liveSession?->arrows_per_end ?? 6,
                'total_score'     => $liveSession?->total_score ?? 0,
                'x_count'         => $liveSession?->x_count ?? 0,
                'average_per_end' => $liveSession?->average_per_end ?? 0,
                'started_at'      => $liveSession?->started_at,
                'ends'            => $ends,
            ];
        })->values();
    }
}
