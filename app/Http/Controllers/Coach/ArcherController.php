<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Archer;
use App\Models\Coach;
use App\Models\LiveSession;
use App\Models\TrainingSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ArcherController extends Controller
{
    private function coach(): Coach
    {
        return Coach::where('user_id', Auth::id())->firstOrFail();
    }

    /**
     * GET /coach/archers
     */
    public function index(): Response
    {
        $coach = $this->coach();

        $archers = Archer::where('coach_id', $coach->id)
            ->get()
            ->map(fn (Archer $archer) => $this->archerSummary($archer));

        return Inertia::render('Coach/ArcherList', [
            'archers' => $archers,
        ]);
    }

    /**
     * GET /coach/archers/{archer}
     */
    public function show(Archer $archer): Response
    {
        $coach = $this->coach();
        abort_if($archer->coach_id !== $coach->id, 403);

        $recentSessions = TrainingSession::with('liveSession')
            ->where('archer_id', $archer->id)
            ->whereNotNull('ended_at')
            ->orderByDesc('started_at')
            ->limit(10)
            ->get()
            ->map(fn (TrainingSession $s) => [
                'id'          => $s->id,
                'round_type'  => $s->round_type,
                'started_at'  => $s->started_at,
                'ended_at'    => $s->ended_at,
                'total_score' => $s->liveSession?->total_score,
                'max_score'   => $s->max_score,
                'x_count'     => $s->liveSession?->x_count ?? 0,
                'ends_count'  => $s->liveSession?->ends()->count() ?? 0,
            ]);

        $allScores = $recentSessions->pluck('total_score')->filter()->values();

        $improvementRate = null;
        if ($allScores->count() >= 6) {
            $firstAvg = $allScores->take(3)->avg();
            $lastAvg  = $allScores->slice(-3)->avg();
            if ($firstAvg > 0) {
                $improvementRate = round((($lastAvg - $firstAvg) / $firstAvg) * 100, 1);
            }
        }

        $stats = [
            'total_sessions'  => TrainingSession::where('archer_id', $archer->id)->whereNotNull('ended_at')->count(),
            'avg_score'       => $allScores->count() ? round($allScores->avg(), 1) : null,
            'best_score'      => $allScores->max(),
            'improvement_rate' => $improvementRate,
        ];

        // Coach notes for this archer (stored as a simple JSON field or separate model)
        $notes = \DB::table('coach_notes')
            ->where('archer_id', $archer->id)
            ->where('coach_id', $coach->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($n) => [
                'id'         => $n->id,
                'content'    => $n->content,
                'created_at' => $n->created_at,
            ])
            ->toArray();

        return Inertia::render('Coach/ArcherView', [
            'archer' => array_merge(
                $this->archerSummary($archer),
                [
                    'date_of_birth'      => $archer->date_of_birth?->format('Y-m-d'),
                    'phone'              => $archer->phone,
                    'dominant_hand'      => $archer->dominant_hand,
                    'current_equipment'  => $archer->currentEquipment ? [
                        'bow_type'        => $archer->currentEquipment->bow_type,
                        'draw_weight_lbs' => $archer->currentEquipment->draw_weight_lbs,
                    ] : null,
                ]
            ),
            'stats'          => $stats,
            'recentSessions' => $recentSessions,
            'notes'          => $notes,
        ]);
    }

    /**
     * POST /coach/archers/{archer}/notes
     */
    public function storeNote(Request $request, Archer $archer)
    {
        $coach = $this->coach();
        abort_if($archer->coach_id !== $coach->id, 403);

        $data = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);

        \DB::table('coach_notes')->insert([
            'archer_id'  => $archer->id,
            'coach_id'   => $coach->id,
            'content'    => $data['content'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Note saved.');
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function archerSummary(Archer $archer): array
    {
        $recentScores = TrainingSession::with('liveSession')
            ->where('archer_id', $archer->id)
            ->whereNotNull('ended_at')
            ->orderByDesc('started_at')
            ->limit(6)
            ->get()
            ->pluck('liveSession.total_score')
            ->filter()
            ->values();

        $improvementRate = null;
        if ($recentScores->count() >= 6) {
            $firstAvg = $recentScores->take(3)->avg();
            $lastAvg  = $recentScores->slice(-3)->avg();
            if ($firstAvg > 0) {
                $improvementRate = round((($lastAvg - $firstAvg) / $firstAvg) * 100, 1);
            }
        }

        $hasActive = LiveSession::whereHas(
            'trainingSession',
            fn ($q) => $q->where('archer_id', $archer->id)
        )->where('status', 'active')->exists();

        $lastSession = TrainingSession::where('archer_id', $archer->id)
            ->whereNotNull('ended_at')
            ->orderByDesc('started_at')
            ->first();

        return [
            'id'                 => $archer->id,
            'name'               => $archer->user?->name ?? "Archer #{$archer->id}",
            'category'           => $archer->category,
            'age'                => $archer->age,
            'avg_score'          => $recentScores->count() ? round($recentScores->avg(), 1) : null,
            'improvement_rate'   => $improvementRate,
            'sessions_count'     => TrainingSession::where('archer_id', $archer->id)->count(),
            'last_session_date'  => $lastSession?->started_at,
            'has_active_session' => $hasActive,
            'created_at'         => $archer->created_at,
        ];
    }
}
