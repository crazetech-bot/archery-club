<?php

namespace App\Http\Controllers\Live;

use App\Http\Controllers\Controller;
use App\Http\Requests\Live\StartSessionRequest;
use App\Http\Requests\Live\SubmitEndRequest;
use App\Models\LiveSession;
use App\Models\TrainingSession;
use App\Services\Live\LiveScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ScoringController extends Controller
{
    public function __construct(
        private readonly LiveScoringService $scoringService
    ) {}

    // -------------------------------------------------------------------------
    // GET /live/session/{trainingSession}
    // -------------------------------------------------------------------------

    /**
     * Render the archer's live scoring page (Inertia).
     *
     * Passes the existing live session (if any) and pre-loaded ends so the
     * Vue component can hydrate without an extra round-trip.
     */
    public function show(Request $request, TrainingSession $trainingSession): InertiaResponse
    {
        $this->authoriseArcher($request, $trainingSession);

        $liveSession = LiveSession::where('training_session_id', $trainingSession->id)
            ->with(['ends' => fn ($q) => $q->orderBy('end_number'), 'ends.arrows'])
            ->latest()
            ->first();

        $archer = $trainingSession->archer;

        return Inertia::render('Live/Scoring', [
            'archer'          => [
                'id'       => $archer->id,
                'name'     => $archer->user?->name ?? "Archer #{$archer->id}",
                'category' => $archer->category,
            ],
            'trainingSession' => [
                'id'              => $trainingSession->id,
                'round_type'      => $trainingSession->round_type,
                'distance_metres' => $trainingSession->distance_metres,
                'started_at'      => $trainingSession->started_at,
            ],
            'liveSession'     => $liveSession ? [
                'id'             => $liveSession->id,
                'status'         => $liveSession->status,
                'arrows_per_end' => $liveSession->arrows_per_end,
                'started_at'     => $liveSession->started_at,
                'ended_at'       => $liveSession->ended_at,
            ] : null,
            'initialEnds' => $liveSession
                ? $liveSession->ends->map(fn ($end) => [
                    'id'          => $end->id,
                    'end_number'  => $end->end_number,
                    'total_score' => $end->total_score,
                    'x_count'     => $end->x_count,
                    'ten_count'   => $end->ten_count,
                    'tag'         => $end->tag,
                    'notes'       => $end->notes,
                    'arrows'      => $end->arrows->map(fn ($a) => [
                        'id'           => $a->id,
                        'arrow_number' => $a->arrow_number,
                        'score'        => $a->score,
                        'position_x'   => $a->position_x,
                        'position_y'   => $a->position_y,
                    ])->all(),
                ])->all()
                : [],
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /live/session/{trainingSession}/start
    // -------------------------------------------------------------------------

    /**
     * Start a new live session for the given training session.
     * Returns the created LiveSession as JSON.
     */
    public function start(StartSessionRequest $request, TrainingSession $trainingSession): JsonResponse
    {
        $this->authoriseArcher($request, $trainingSession);

        try {
            $liveSession = $this->scoringService->startSession(
                $trainingSession,
                LiveScoringService::resolveTenantId(),
                (int) $request->validated('arrows_per_end')
            );
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json([
            'live_session' => [
                'id'             => $liveSession->id,
                'status'         => $liveSession->status,
                'arrows_per_end' => $liveSession->arrows_per_end,
                'started_at'     => $liveSession->started_at,
                'ended_at'       => null,
            ],
        ], 201);
    }

    // -------------------------------------------------------------------------
    // POST /live/session/{liveSession}/end
    // -------------------------------------------------------------------------

    /**
     * Submit a completed end to the live session.
     * Validates arrow scores, creates the end + arrows, fires EndSubmitted event.
     */
    public function submitEnd(SubmitEndRequest $request, LiveSession $liveSession): JsonResponse
    {
        $this->authoriseLiveSession($request, $liveSession);

        try {
            $liveEnd = $this->scoringService->submitEnd(
                $liveSession,
                $request->validated('arrows'),
                $request->validated('end_number'),
                LiveScoringService::resolveTenantId()
            );
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'end' => [
                'id'          => $liveEnd->id,
                'end_number'  => $liveEnd->end_number,
                'total_score' => $liveEnd->total_score,
                'x_count'     => $liveEnd->x_count,
                'ten_count'   => $liveEnd->ten_count,
                'tag'         => $liveEnd->tag,
                'notes'       => $liveEnd->notes,
                'arrows'      => $liveEnd->arrows->map(fn ($a) => [
                    'id'           => $a->id,
                    'arrow_number' => $a->arrow_number,
                    'score'        => $a->score,
                    'position_x'   => $a->position_x,
                    'position_y'   => $a->position_y,
                ])->all(),
            ],
        ], 201);
    }

    // -------------------------------------------------------------------------
    // PATCH /live/session/{liveSession}/complete
    // -------------------------------------------------------------------------

    /**
     * Mark the live session as completed.
     * Fires SessionCompleted event, which queues the PDF generation job.
     */
    public function complete(Request $request, LiveSession $liveSession): JsonResponse
    {
        $this->authoriseLiveSession($request, $liveSession);

        try {
            $liveSession = $this->scoringService->completeSession(
                $liveSession,
                LiveScoringService::resolveTenantId()
            );
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json([
            'live_session' => [
                'id'       => $liveSession->id,
                'status'   => $liveSession->status,
                'ended_at' => $liveSession->ended_at,
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // Authorisation helpers
    // -------------------------------------------------------------------------

    /**
     * Ensure the authenticated user is allowed to score this training session.
     *
     * - club_admin : always allowed
     * - archer     : only their own training session (matched via user_id on archer)
     */
    private function authoriseArcher(Request $request, TrainingSession $trainingSession): void
    {
        $user = $request->user();

        if ($user->hasRole('club_admin')) {
            return;
        }

        $trainingSession->loadMissing('archer');

        abort_unless(
            $trainingSession->archer->user_id === $user->id,
            403,
            'You are not authorised to score this session.'
        );
    }

    /**
     * Ensure the authenticated user is allowed to interact with this live session.
     * Resolves the training session from the live session and delegates.
     */
    private function authoriseLiveSession(Request $request, LiveSession $liveSession): void
    {
        $liveSession->loadMissing('trainingSession');
        $this->authoriseArcher($request, $liveSession->trainingSession);
    }
}
