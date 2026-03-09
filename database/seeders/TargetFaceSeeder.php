<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Scoring\TargetFace;

class TargetFaceSeeder extends Seeder
{
    public function run(): void
    {
        $faces = [
            [
                'name'        => 'Complete',
                'scoring_min' => 1,
                'scoring_max' => 10,
                'x_value'     => 10,
                'has_x_ring'  => true,
                'description' => 'Standard 1–10 scoring with X.',
            ],
            [
                'name'        => 'Reduced (5–10)',
                'scoring_min' => 5,
                'scoring_max' => 10,
                'x_value'     => 10,
                'has_x_ring'  => true,
                'description' => 'Reduced face, 5–10 scoring.',
            ],
            [
                'name'        => 'Reduced (6–10)',
                'scoring_min' => 6,
                'scoring_max' => 10,
                'x_value'     => 10,
                'has_x_ring'  => true,
                'description' => 'Reduced face, 6–10 scoring.',
            ],
            [
                'name'        => 'Field Archery',
                'scoring_min' => 1,
                'scoring_max' => 6,
                'x_value'     => null,
                'has_x_ring'  => false,
                'description' => 'Field archery 1–6 scoring.',
            ],
            [
                'name'        => 'Complete (X=11)',
                'scoring_min' => 1,
                'scoring_max' => 10,
                'x_value'     => 11,
                'has_x_ring'  => true,
                'description' => 'X counts as 11.',
            ],
            [
                'name'        => 'Reduced (6–10, X=11)',
                'scoring_min' => 6,
                'scoring_max' => 10,
                'x_value'     => 11,
                'has_x_ring'  => true,
                'description' => 'Reduced face with X=11.',
            ],
        ];

        foreach ($faces as $data) {
            TargetFace::firstOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}
