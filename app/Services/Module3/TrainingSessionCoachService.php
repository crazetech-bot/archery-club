<?php

namespace App\Services\Module3;

use App\Models\Training\TrainingSession;
use App\Models\Coaches\Coach;
use Illuminate\Support\Facades\DB;

class TrainingSessionCoachService
{
    public function addCoach(TrainingSession $session, Coach $coach)
    {
        return DB::transaction(function () use ($session, $coach) {
            $session->coaches()->syncWithoutDetaching([$coach->id]);

            return $session->load('coaches.user');
        });
    }

    public function removeCoach(TrainingSession $session, Coach $coach)
    {
        return DB::transaction(function () use ($session, $coach) {
            $session->coaches()->detach($coach->id);

            return $session->load('coaches.user');
        });
    }

    public function listCoaches(TrainingSession $session)
    {
        return $session->coaches()->with('user')->get();
    }
}
