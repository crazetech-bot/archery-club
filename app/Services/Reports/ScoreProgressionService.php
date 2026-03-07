<?php

namespace App\Services\Reports;

use App\Models\Archer;
use App\Models\TrainingSession;
use Illuminate\Support\Collection;

class ScoreProgressionService
{
    /**
     * Build the full score progression report for an archer.
     *
     * @param  array{
     *   archer_id: int,
     *   date_from: string,
     *   date_to: string,
     *   round_type: string|null,
     *   distance: int|null,
     *   group_by: string|null,
     * } $filters
     */
    public function generate(array $filters): array
    {
        $archer = Archer::with([
            'trainingSessions' => function ($query) use ($filters) {
                $query
                    ->whereBetween('started_at', [$filters['date_from'], $filters['date_to']])
                    ->when($filters['round_type'], fn ($q) => $q->where('round_type', $filters['round_type']))
                    ->when($filters['distance'],   fn ($q) => $q->where('distance_metres', $filters['distance']))
                    ->has('liveSession')
                    ->with([
                        'liveSession' => fn ($q) => $q->where('status', 'completed'),
                        'liveSession.ends',
                        'liveSession.ends.arrows',
                    ])
                    ->orderBy('started_at');
            },
        ])->findOrFail($filters['archer_id']);

        // Only sessions that have a completed live session
        $sessions = $archer->trainingSessions->filter(
            fn ($s) => $s->liveSession !== null
        );

        $dataPoints = $this->buildDataPoints($sessions);

        if ($filters['group_by']) {
            $dataPoints = $this->groupDataPoints($dataPoints, $filters['group_by']);
        }

        return [
            'archer'      => $archer,
            'filters'     => $filters,
            'data_points' => $dataPoints,
            'trend'       => $this->movingAverage($dataPoints, window: 3),
            'summary'     => $this->buildSummary($dataPoints),
            'best_session'  => $this->bestSession($dataPoints),
            'worst_session' => $this->worstSession($dataPoints),
            'by_round_type' => $this->groupByRoundType($sessions),
        ];
    }

    // -------------------------------------------------------------------------
    // Data point construction
    // -------------------------------------------------------------------------

    /**
     * Map each training session → a flat data point array.
     */
    private function buildDataPoints(Collection $sessions): Collection
    {
        return $sessions->map(function (TrainingSession $session) {
            $live = $session->liveSession;

            $totalScore = $live->ends->sum('total_score');
            $xCount     = $live->ends->sum('x_count');
            $tenCount   = $live->ends->sum('ten_count');
            $endCount   = $live->ends->count();
            $arrowCount = $live->ends->flatMap->arrows->count();

            return [
                'session_id'        => $session->id,
                'live_session_id'   => $live->id,
                'date'              => $session->started_at->toDateString(),
                'round_type'        => $session->round_type,
                'distance_metres'   => $session->distance_metres,
                'total_score'       => $totalScore,
                'ends_count'        => $endCount,
                'arrows_count'      => $arrowCount,
                'x_count'           => $xCount,
                'ten_count'         => $tenCount,
                'avg_per_end'       => $endCount > 0 ? round($totalScore / $endCount, 2) : 0,
                'avg_per_arrow'     => $arrowCount > 0 ? round($totalScore / $arrowCount, 2) : 0,
            ];
        })->values();
    }

    /**
     * Group data points by week or month, averaging scores per period.
     */
    private function groupDataPoints(Collection $dataPoints, string $groupBy): Collection
    {
        $format = $groupBy === 'month' ? 'Y-m' : 'o-W'; // ISO week

        return $dataPoints
            ->groupBy(fn ($dp) => \Carbon\Carbon::parse($dp['date'])->format($format))
            ->map(function (Collection $group, string $period) use ($groupBy) {
                $sessionCount = $group->count();

                return [
                    'period'        => $period,
                    'label'         => $this->periodLabel($period, $groupBy),
                    'sessions'      => $sessionCount,
                    'total_score'   => $group->sum('total_score'),
                    'avg_score'     => round($group->avg('total_score'), 1),
                    'x_count'       => $group->sum('x_count'),
                    'ten_count'     => $group->sum('ten_count'),
                    'avg_per_end'   => round($group->avg('avg_per_end'), 2),
                    'avg_per_arrow' => round($group->avg('avg_per_arrow'), 2),
                    // Keep underlying session dates for tooltip display
                    'dates'         => $group->pluck('date')->all(),
                ];
            })
            ->values();
    }

    // -------------------------------------------------------------------------
    // Trend / analytics
    // -------------------------------------------------------------------------

    /**
     * Calculate a simple moving average of total_score over N data points.
     * Returns a Collection parallel to $dataPoints with 'value' at each index.
     */
    private function movingAverage(Collection $dataPoints, int $window): Collection
    {
        $scores = $dataPoints->pluck('total_score')->values();

        return $scores->map(function ($_, int $i) use ($scores, $window) {
            $start  = max(0, $i - $window + 1);
            $slice  = $scores->slice($start, $i - $start + 1);

            return round($slice->average(), 2);
        });
    }

    /**
     * Aggregate summary statistics across all data points.
     */
    private function buildSummary(Collection $dataPoints): array
    {
        if ($dataPoints->isEmpty()) {
            return [
                'total_sessions'   => 0,
                'total_arrows'     => 0,
                'avg_score'        => null,
                'best_score'       => null,
                'worst_score'      => null,
                'improvement_rate' => null,
                'total_x_count'    => 0,
                'total_ten_count'  => 0,
            ];
        }

        $scores       = $dataPoints->pluck('total_score');
        $improvementRate = $this->improvementRate($scores);

        return [
            'total_sessions'   => $dataPoints->count(),
            'total_arrows'     => $dataPoints->sum('arrows_count'),
            'avg_score'        => round($scores->average(), 1),
            'best_score'       => $scores->max(),
            'worst_score'      => $scores->min(),
            'improvement_rate' => $improvementRate, // % change first → last (moving avg)
            'total_x_count'    => $dataPoints->sum('x_count'),
            'total_ten_count'  => $dataPoints->sum('ten_count'),
        ];
    }

    /**
     * % improvement from the average of the first 3 sessions to the last 3.
     * Returns null when there are fewer than 4 sessions.
     */
    private function improvementRate(Collection $scores): ?float
    {
        if ($scores->count() < 4) {
            return null;
        }

        $firstAvg = $scores->take(3)->average();
        $lastAvg  = $scores->slice(-3)->average();

        if ($firstAvg == 0) {
            return null;
        }

        return round((($lastAvg - $firstAvg) / $firstAvg) * 100, 1);
    }

    /**
     * The data point with the highest total_score.
     */
    private function bestSession(Collection $dataPoints): ?array
    {
        return $dataPoints->sortByDesc('total_score')->first();
    }

    /**
     * The data point with the lowest total_score.
     */
    private function worstSession(Collection $dataPoints): ?array
    {
        return $dataPoints->sortBy('total_score')->first();
    }

    /**
     * Average score per round type — useful for chart breakdown.
     */
    private function groupByRoundType(Collection $sessions): Collection
    {
        return $sessions
            ->filter(fn ($s) => $s->round_type !== null)
            ->groupBy('round_type')
            ->map(function (Collection $group, string $roundType) {
                $scores = $group->map(
                    fn ($s) => $s->liveSession->ends->sum('total_score')
                );

                return [
                    'round_type'     => $roundType,
                    'sessions_count' => $group->count(),
                    'avg_score'      => round($scores->average(), 1),
                    'best_score'     => $scores->max(),
                ];
            })
            ->values();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function periodLabel(string $period, string $groupBy): string
    {
        if ($groupBy === 'month') {
            return \Carbon\Carbon::createFromFormat('Y-m', $period)->format('M Y');
        }

        // ISO week: "2026-W10" → "Week 10, 2026"
        [$year, $week] = explode('-W', $period);
        return "Week {$week}, {$year}";
    }
}
