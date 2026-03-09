<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Scoring\Distance;
use App\Models\Scoring\TargetFace;
use App\Models\Scoring\TargetFaceSize;

class TargetFaceSizeSeeder extends Seeder
{
    public function run(): void
    {
        $mappings = [
            // Outdoor WA
            ['meters' => 70, 'face' => 'Complete',          'size' => 122],
            ['meters' => 50, 'face' => 'Reduced (6–10)',    'size' => 80],
            ['meters' => 30, 'face' => 'Complete',          'size' => 80],
            // Indoor WA
            ['meters' => 18, 'face' => 'Reduced (6–10)',    'size' => 40],
            ['meters' => 18, 'face' => 'Reduced (5–10)',    'size' => 40],
            // Short distance training
            ['meters' => 10, 'face' => 'Complete',          'size' => 60],
            ['meters' => 5,  'face' => 'Complete',          'size' => 40],
        ];

        foreach ($mappings as $map) {
            $distance = Distance::where('meters', $map['meters'])->first();
            $face     = TargetFace::where('name', $map['face'])->first();

            if ($distance && $face) {
                TargetFaceSize::firstOrCreate([
                    'distance_id'    => $distance->id,
                    'target_face_id' => $face->id,
                    'size_cm'        => $map['size'],
                ]);
            }
        }
    }
}
