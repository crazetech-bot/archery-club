<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Training\TrainingSession;
use App\Models\Coaches\Coach;

class TrainingSessionCoachSeeder extends Seeder
{
    public function run(): void
    {
        $sessions = TrainingSession::all();
        $coaches  = Coach::all();

        if ($sessions->isEmpty() || $coaches->isEmpty()) {
            return;
        }

        foreach ($sessions as $session) {
            // Assign the first coach to all sessions
            $coach = $coaches->first();
            $session->coaches()->syncWithoutDetaching([$coach->id]);
        }
    }
}
