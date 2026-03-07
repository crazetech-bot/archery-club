<?php

namespace App\Services;

use App\Models\LiveSession;

/**
 * ScoreCalculator — centralised arrow-score arithmetic.
 *
 * Converts arrow score strings (X, 10, 9 … 1, M) to integers, calculates
 * end totals, and produces session summaries. All logic that was previously
 * inline in ScoringController now lives here so it can be tested and reused
 * across HTTP controllers, API controllers, and console commands.
 */
class ScoreCalculator
{
    // -------------------------------------------------------------------------
    // Arrow value
    // -------------------------------------------------------------------------

    /**
     * Convert an arrow score string to its integer value.
     *
     * Scoring conventions:
     *   X  → 10 (inner gold — counts as 10 for scoring, separately counted for tiebreak)
     *   10 → 10
     *   9  → 9  …  1 → 1
     *   M  → 0  (miss)
     */
    public static function arrowValue(string $score): int
    {
        $normalised = strtoupper(trim($score));

        if ($normalised === 'X')  return 10;
        if ($normalised === 'M')  return 0;

        $int = (int) $normalised;

        return ($int >= 1 && $int <= 10) ? $int : 0;
    }

    // -------------------------------------------------------------------------
    // End calculation
    // -------------------------------------------------------------------------

    /**
     * Calculate the aggregate values for a single end given its arrow scores.
     *
     * @param  string[] $scores  e.g. ['X', '9', '8', 'M']
     * @return array{total_score: int, x_count: int, ten_count: int}
     */
    public static function calculateEnd(array $scores): array
    {
        $total    = 0;
        $xCount   = 0;
        $tenCount = 0;

        foreach ($scores as $raw) {
            $normalised = strtoupper(trim((string) $raw));

            if ($normalised === 'X') {
                $xCount++;
                $tenCount++;
                $total += 10;
            } elseif ($normalised === '10') {
                $tenCount++;
                $total += 10;
            } else {
                $total += self::arrowValue($normalised);
            }
        }

        return [
            'total_score' => $total,
            'x_count'     => $xCount,
            'ten_count'   => $tenCount,
        ];
    }

    // -------------------------------------------------------------------------
    // Session summary
    // -------------------------------------------------------------------------

    /**
     * Build a summary array from a fully-loaded LiveSession.
     *
     * The session MUST have its ends and ends.arrows relations loaded.
     *
     * @return array{
     *   total_score: int,
     *   x_count: int,
     *   ten_count: int,
     *   ends_count: int,
     *   average_per_end: float,
     *   highest_end: int,
     *   lowest_end: int,
     *   arrow_count: int,
     *   average_per_arrow: float,
     * }
     */
    public static function sessionSummary(LiveSession $session): array
    {
        $ends = $session->ends;

        if ($ends->isEmpty()) {
            return [
                'total_score'      => 0,
                'x_count'          => 0,
                'ten_count'        => 0,
                'ends_count'       => 0,
                'average_per_end'  => 0.0,
                'highest_end'      => 0,
                'lowest_end'       => 0,
                'arrow_count'      => 0,
                'average_per_arrow'=> 0.0,
            ];
        }

        $endScores  = $ends->pluck('total_score');
        $totalScore = $endScores->sum();
        $arrowCount = $ends->sum(fn ($e) => $e->arrows->count());

        return [
            'total_score'       => $totalScore,
            'x_count'           => $ends->sum('x_count'),
            'ten_count'         => $ends->sum('ten_count'),
            'ends_count'        => $ends->count(),
            'average_per_end'   => round($totalScore / $ends->count(), 2),
            'highest_end'       => $endScores->max(),
            'lowest_end'        => $endScores->min(),
            'arrow_count'       => $arrowCount,
            'average_per_arrow' => $arrowCount > 0 ? round($totalScore / $arrowCount, 2) : 0.0,
        ];
    }

    // -------------------------------------------------------------------------
    // Handicap (basic linear interpolation — replace with official AGB tables)
    // -------------------------------------------------------------------------

    /**
     * Estimate a handicap rating (lower is better, 0–150 scale).
     * This is a placeholder approximation; integrate AGB/World Archery
     * handicap tables for accurate results.
     *
     * @param  int    $score       Total score achieved
     * @param  int    $maxScore    Maximum possible score for the round
     * @param  string $bowType     e.g. 'recurve', 'compound', 'barebow'
     */
    public static function estimateHandicap(int $score, int $maxScore, string $bowType = 'recurve'): float
    {
        if ($maxScore <= 0) return 150.0;

        $percentage = $score / $maxScore;

        // Crude linear approximation per bow type
        // Compound archers generally shoot higher percentages
        $offset = match (strtolower($bowType)) {
            'compound' => 10,
            'barebow'  => -10,
            default    => 0,  // recurve
        };

        $handicap = round((1 - $percentage) * 100 + $offset, 1);

        return max(0.0, min(150.0, (float) $handicap));
    }
}
