<?php

namespace App\Services\Module4;

use App\Models\Scoring\Scorecard;
use App\Models\Training\TrainingSession;
use App\Models\Archers\Archer;
use App\Models\Training\ScoringTemplate;
use Illuminate\Support\Facades\DB;

class ScorecardService
{
    public function listScorecards()
    {
        return Scorecard::with(['archer.user', 'session', 'template'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    public function createScorecard(array $data): Scorecard
    {
        return DB::transaction(function () use ($data) {
            return Scorecard::create([
                'training_session_id' => $data['training_session_id'],
                'archer_id'           => $data['archer_id'],
                'scoring_template_id' => $data['scoring_template_id'],
                'status'              => 'draft',
            ]);
        });
    }

    public function getScorecard(Scorecard $scorecard): Scorecard
    {
        return $scorecard->load([
            'archer.user',
            'session',
            'template',
            'ends.shots',
            'shots',
            'metrics',
        ]);
    }

    public function updateScorecard(Scorecard $scorecard, array $data): Scorecard
    {
        return DB::transaction(function () use ($scorecard, $data) {
            $scorecard->update([
                'status' => $data['status'] ?? $scorecard->status,
            ]);

            return $scorecard;
        });
    }

    public function deleteScorecard(Scorecard $scorecard): bool
    {
        return DB::transaction(function () use ($scorecard) {
            $scorecard->ends()->delete();
            $scorecard->shots()->delete();
            $scorecard->metrics()->delete();

            return $scorecard->delete();
        });
    }

    public function submitScorecard(Scorecard $scorecard): Scorecard
    {
        return DB::transaction(function () use ($scorecard) {
            $scorecard->update(['status' => 'submitted']);

            return $scorecard;
        });
    }

    public function lockScorecard(Scorecard $scorecard): Scorecard
    {
        return DB::transaction(function () use ($scorecard) {
            $scorecard->update(['status' => 'locked']);

            return $scorecard;
        });
    }
}
