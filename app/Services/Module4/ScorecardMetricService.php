<?php

namespace App\Services\Module4;

use App\Models\Scoring\Scorecard;
use App\Models\Scoring\ScorecardMetric;
use Illuminate\Support\Facades\DB;

class ScorecardMetricService
{
    public function recalculateMetrics(Scorecard $scorecard): ScorecardMetric
    {
        return DB::transaction(function () use ($scorecard) {
            $shots = $scorecard->shots;

            $totalScore = $shots->sum('score');
            $xCount     = $shots->where('is_x', true)->count();
            $missCount  = $shots->where('is_miss', true)->count();
            $arrowCount = $shots->count();

            $average = $arrowCount > 0
                ? round($totalScore / $arrowCount, 2)
                : 0;

            $hitRate = $arrowCount > 0
                ? round((($arrowCount - $missCount) / $arrowCount) * 100, 2)
                : 0;

            $scorecard->update([
                'total_score' => $totalScore,
                'x_count'     => $xCount,
                'arrow_count' => $arrowCount,
            ]);

            return $scorecard->metrics()->updateOrCreate(
                ['scorecard_id' => $scorecard->id],
                [
                    'average_arrow_score' => $average,
                    'hit_rate'            => $hitRate,
                    'x_count'             => $xCount,
                    'miss_count'          => $missCount,
                ]
            );
        });
    }
}
