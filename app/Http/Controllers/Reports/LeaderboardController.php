<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Archer;
use App\Models\TrainingSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class LeaderboardController extends Controller
{
    /**
     * GET /reports/leaderboard
     * Show the leaderboard page (initial server render).
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Reports/Leaderboard', [
            'initialData' => $this->buildLeaderboard($request),
            'filters'     => [
                'round_type' => $request->get('round_type'),
                'category'   => $request->get('category'),
                'period'     => $request->get('period', '90'),
            ],
            'roundTypes'  => $this->distinctRoundTypes(),
            'categories'  => $this->distinctCategories(),
        ]);
    }

    /**
     * GET /reports/leaderboard/data
     * JSON endpoint for client-side filter updates (no full Inertia reload).
     */
    public function data(Request $request): JsonResponse
    {
        return response()->json($this->buildLeaderboard($request));
    }

    // -------------------------------------------------------------------------
    // Private
    // -------------------------------------------------------------------------

    private function buildLeaderboard(Request $request): array
    {
        $roundType = $request->get('round_type');
        $category  = $request->get('category');
        $period    = (int) ($request->get('period', 90)); // days — 0 = all time

        $query = TrainingSession::with(['archer.user', 'liveSession.ends'])
            ->whereNotNull('ended_at')
            ->whereHas('liveSession', fn ($q) => $q->where('status', 'completed'));

        if ($roundType) {
            $query->where('round_type', $roundType);
        }

        if ($period > 0) {
            $query->where('started_at', '>=', now()->subDays($period));
        }

        if ($category) {
            $query->whereHas('archer', fn ($q) => $q->where('category', $category));
        }

        $sessions = $query->get();

        // Group by archer and build stats
        $ranked = $sessions
            ->groupBy('archer_id')
            ->map(function (Collection $archerSessions, int $archerId) {
                $archer = $archerSessions->first()->archer;
                $scores = $archerSessions
                    ->map(fn ($s) => $s->liveSession?->total_score)
                    ->filter()
                    ->values();

                if ($scores->isEmpty()) return null;

                return [
                    'archer_id'       => $archerId,
                    'name'            => $archer?->user?->name ?? "Archer #{$archerId}",
                    'category'        => $archer?->category,
                    'session_count'   => $archerSessions->count(),
                    'best_score'      => $scores->max(),
                    'avg_score'       => round($scores->avg(), 1),
                    'total_x'         => $archerSessions->sum(fn ($s) => $s->liveSession?->x_count ?? 0),
                    'improvement'     => $this->improvementRate($scores),
                ];
            })
            ->filter()
            ->sortByDesc('best_score')
            ->values()
            ->map(function ($entry, $idx) {
                return array_merge($entry, ['rank' => $idx + 1]);
            });

        return [
            'entries'      => $ranked->values(),
            'total_archers'=> $ranked->count(),
            'generated_at' => now()->toISOString(),
        ];
    }

    /**
     * Calculate percentage improvement from first 3 sessions to last 3 sessions.
     */
    private function improvementRate(Collection $scores): ?float
    {
        if ($scores->count() < 4) return null;

        $firstAvg = $scores->take(3)->avg();
        $lastAvg  = $scores->slice(-3)->avg();

        if ($firstAvg <= 0) return null;

        return round((($lastAvg - $firstAvg) / $firstAvg) * 100, 1);
    }

    private function distinctRoundTypes(): array
    {
        return TrainingSession::whereNotNull('round_type')
            ->distinct()
            ->orderBy('round_type')
            ->pluck('round_type')
            ->all();
    }

    private function distinctCategories(): array
    {
        return Archer::whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->all();
    }
}
