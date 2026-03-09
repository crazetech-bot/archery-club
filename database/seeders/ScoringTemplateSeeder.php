<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Training\ScoringTemplate;

class ScoringTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'club_id' => 1,
                'name'    => '30-Arrow Round',
                'type'    => 'end_based',
                'config'  => [
                    'ends'                => 5,
                    'arrows_per_end'      => 6,
                    'max_score_per_arrow' => 10,
                ],
            ],
            [
                'club_id' => 1,
                'name'    => 'Single Arrow Drill',
                'type'    => 'shot_based',
                'config'  => [
                    'shots'               => 20,
                    'max_score_per_arrow' => 10,
                ],
            ],
        ];

        foreach ($templates as $data) {
            ScoringTemplate::firstOrCreate(
                [
                    'club_id' => $data['club_id'],
                    'name'    => $data['name'],
                ],
                $data
            );
        }
    }
}
