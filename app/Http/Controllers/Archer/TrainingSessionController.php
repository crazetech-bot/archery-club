<?php

namespace App\Http\Controllers\Archer;

use App\Http\Controllers\Controller;
use App\Models\Archer;
use App\Models\TrainingSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TrainingSessionController extends Controller
{
    private function archer(): Archer
    {
        return Archer::where('user_id', Auth::id())->firstOrFail();
    }

    /**
     * GET /archer/sessions
     */
    public function index(Request $request): Response
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
                'ends_count'      => $s->liveSession?->ends()->count() ?? 0,
                'arrows_per_end'  => $s->liveSession?->arrows_per_end,
            ]);

        // Aggregate stats across all sessions
        $allSessions = TrainingSession::with('liveSession')
            ->where('archer_id', $archer->id)
            ->whereNotNull('ended_at')
            ->get();

        $allScores = $allSessions->pluck('liveSession.total_score')->filter();

        $stats = [
            'total_sessions'      => $allSessions->count(),
            'avg_score'           => $allScores->count() ? round($allScores->avg(), 1) : null,
            'best_score'          => $allScores->max(),
            'sessions_this_month' => $allSessions
                ->filter(fn ($s) => $s->started_at >= now()->startOfMonth())
                ->count(),
        ];

        return Inertia::render('Archer/TrainingSessions', [
            'sessions' => $sessions,
            'stats'    => $stats,
        ]);
    }

    /**
     * GET /archer/sessions/{session}
     */
    public function show(TrainingSession $session): Response
    {
        $archer = $this->archer();

        abort_if($session->archer_id !== $archer->id, 403);

        $liveSession = $session->liveSession;

        $ends = $liveSession
            ? $liveSession->ends()
                ->with('arrows')
                ->orderBy('end_number')
                ->get()
                ->map(fn ($end) => [
                    'id'          => $end->id,
                    'end_number'  => $end->end_number,
                    'total_score' => $end->total_score,
                    'x_count'     => $end->x_count,
                    'ten_count'   => $end->ten_count,
                    'notes'       => $end->notes,
                    'arrows'      => $end->arrows->map(fn ($a) => [
                        'id'    => $a->id,
                        'score' => $a->score,
                        'x'     => $a->x,
                        'y'     => $a->y,
                    ]),
                ])
            : collect();

        return Inertia::render('Archer/TrainingSessionView', [
            'session'     => [
                'id'              => $session->id,
                'round_type'      => $session->round_type,
                'distance_metres' => $session->distance_metres,
                'started_at'      => $session->started_at,
                'ended_at'        => $session->ended_at,
                'max_score'       => $session->max_score,
                'coach_name'      => $session->coach?->user?->name,
                'equipment'       => $session->equipmentSetup ? [
                    'bow_type'        => $session->equipmentSetup->bow_type,
                    'bow_brand'       => $session->equipmentSetup->bow_brand,
                    'bow_model'       => $session->equipmentSetup->bow_model,
                    'draw_weight_lbs' => $session->equipmentSetup->draw_weight_lbs,
                    'arrow_brand'     => $session->equipmentSetup->arrow_brand,
                    'arrow_model'     => $session->equipmentSetup->arrow_model,
                ] : null,
            ],
            'liveSession' => $liveSession ? [
                'id'              => $liveSession->id,
                'arrows_per_end'  => $liveSession->arrows_per_end,
                'total_score'     => $liveSession->total_score,
                'x_count'         => $liveSession->x_count,
                'ten_count'       => $liveSession->ten_count,
                'average_per_end' => $liveSession->average_per_end,
                'status'          => $liveSession->status,
            ] : null,
            'ends'        => $ends,
        ]);
    }

    /**
     * GET /archer/sessions/{session}/export   — PDF download
     */
    public function export(TrainingSession $session): StreamedResponse|\Illuminate\Http\Response
    {
        $archer = $this->archer();
        abort_if($session->archer_id !== $archer->id, 403);

        $liveSession = $session->liveSession;
        $ends        = $liveSession?->ends()->with('arrows')->orderBy('end_number')->get() ?? collect();
        $archerModel = $session->archer;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'pdf.session-summary',
            compact('session', 'liveSession', 'ends', 'archer')
        )->setPaper('a4', 'portrait');

        $filename = 'session-' . $session->id . '-' . now()->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }
}
