<?php

namespace App\Services\Module4;

use App\Models\Scoring\Scorecard;
use App\Models\Scoring\ScorecardEnd;
use Illuminate\Support\Facades\DB;

class ScorecardEndService
{
    public function createEnd(Scorecard $scorecard, array $data): ScorecardEnd
    {
        return DB::transaction(function () use ($scorecard, $data) {
            return $scorecard->ends()->create([
                'end_number' => $data['end_number'],
                'end_score'  => 0,
            ]);
        });
    }

    public function updateEnd(Scorecard $scorecard, ScorecardEnd $end, array $data): ScorecardEnd
    {
        return DB::transaction(function () use ($end, $data) {
            $end->update([
                'end_score' => $data['end_score'] ?? $end->end_score,
            ]);

            return $end;
        });
    }

    public function deleteEnd(Scorecard $scorecard, ScorecardEnd $end): bool
    {
        return DB::transaction(function () use ($end) {
            $end->shots()->delete();

            return $end->delete();
        });
    }
}
