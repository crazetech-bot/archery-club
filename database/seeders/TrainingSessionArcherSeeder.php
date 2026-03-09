<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Training\TrainingSession;
use App\Models\Archers\Archer;

class TrainingSessionArcherSeeder extends Seeder
{
    public function run(): void
    {
        $sessions = TrainingSession::all();
        $archers  = Archer::all();

        if ($sessions->isEmpty() || $archers->isEmpty()) {
            return;
        }

        foreach ($sessions as $session) {
            foreach ($archers as $archer) {
                $session->archers()->syncWithoutDetaching([
                    $archer->id => [
                        'attendance_status' => 'present',
                    ],
                ]);
            }
        }
    }
}
