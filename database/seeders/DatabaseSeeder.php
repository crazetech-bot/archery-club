<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Module-1
            RoleSeeder::class,
            ClubSeeder::class,
            AdminUserSeeder::class,
            // Module-2
            ArcherSeeder::class,
            CoachSeeder::class,
            ArcherCoachSeeder::class,
            // Module-3
            TrainingSessionSeeder::class,
            TrainingSessionArcherSeeder::class,
            TrainingSessionCoachSeeder::class,
            TrainingSessionNoteSeeder::class,
            ScoringTemplateSeeder::class,
            // Module-4
            DivisionSeeder::class,
            ClassificationSeeder::class,
            DistanceSeeder::class,
            TargetFaceSeeder::class,
            TargetFaceSizeSeeder::class,
        ]);
    }
}
