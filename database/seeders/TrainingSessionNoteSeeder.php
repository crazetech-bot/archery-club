<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Training\TrainingSession;
use App\Models\Training\TrainingSessionNote;
use App\Models\Coaches\Coach;
use App\Models\Archers\Archer;

class TrainingSessionNoteSeeder extends Seeder
{
    public function run(): void
    {
        $sessions = TrainingSession::all();
        $coach    = Coach::first();
        $archer   = Archer::first();

        if ($sessions->isEmpty() || ! $coach || ! $archer) {
            return;
        }

        foreach ($sessions as $session) {
            TrainingSessionNote::firstOrCreate(
                [
                    'training_session_id' => $session->id,
                    'note'                => 'Good form improvement observed.',
                ],
                [
                    'coach_id'  => $coach->id,
                    'archer_id' => $archer->id,
                ]
            );
        }
    }
}
