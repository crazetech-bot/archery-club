<?php

namespace App\Services\Module4;

use App\Models\Scoring\Scorecard;
use App\Models\Scoring\ScorecardShot;
use Illuminate\Support\Facades\DB;

class ScorecardShotService
{
    public function createShot(Scorecard $scorecard, array $data): ScorecardShot
    {
        return DB::transaction(function () use ($scorecard, $data) {
            return $scorecard->shots()->create([
                'scorecard_end_id' => $data['scorecard_end_id'] ?? null,
                'shot_number'      => $data['shot_number'],
                'score'            => $data['score'],
                'is_x'             => $data['is_x'] ?? false,
                'is_miss'          => $data['is_miss'] ?? false,
            ]);
        });
    }

    public function updateShot(Scorecard $scorecard, ScorecardShot $shot, array $data): ScorecardShot
    {
        return DB::transaction(function () use ($shot, $data) {
            $shot->update([
                'score'   => $data['score'] ?? $shot->score,
                'is_x'    => $data['is_x'] ?? $shot->is_x,
                'is_miss' => $data['is_miss'] ?? $shot->is_miss,
            ]);

            return $shot;
        });
    }

    public function deleteShot(Scorecard $scorecard, ScorecardShot $shot): bool
    {
        return DB::transaction(function () use ($shot) {
            return $shot->delete();
        });
    }
}
