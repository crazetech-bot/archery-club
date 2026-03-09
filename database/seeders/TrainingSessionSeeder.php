<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Training\TrainingSession;

class TrainingSessionSeeder extends Seeder
{
    public function run(): void
    {
        $sessions = [
            [
                'club_id'     => 1,
                'title'       => 'Morning Recurve Training',
                'description' => 'Warm-up, form practice, and 30-arrow scoring round.',
                'start_time'  => now()->subDays(2)->setTime(9, 0),
                'end_time'    => now()->subDays(2)->setTime(11, 0),
                'status'      => 'completed',
            ],
            [
                'club_id'     => 1,
                'title'       => 'Evening Compound Session',
                'description' => 'Strength drills and release technique.',
                'start_time'  => now()->subDay()->setTime(18, 0),
                'end_time'    => now()->subDay()->setTime(20, 0),
                'status'      => 'completed',
            ],
            [
                'club_id'     => 1,
                'title'       => 'Weekend Group Training',
                'description' => 'Mixed group session with coaches.',
                'start_time'  => now()->addDay()->setTime(9, 0),
                'end_time'    => now()->addDay()->setTime(12, 0),
                'status'      => 'scheduled',
            ],
        ];

        foreach ($sessions as $data) {
            TrainingSession::firstOrCreate(
                [
                    'club_id'    => $data['club_id'],
                    'title'      => $data['title'],
                    'start_time' => $data['start_time'],
                ],
                $data
            );
        }
    }
}
