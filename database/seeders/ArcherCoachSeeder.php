<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Archers\Archer;
use App\Models\Coaches\Coach;

class ArcherCoachSeeder extends Seeder
{
    public function run(): void
    {
        $archers = Archer::all();
        $coaches = Coach::all();

        if ($archers->isEmpty() || $coaches->isEmpty()) {
            return;
        }

        foreach ($archers as $archer) {
            // Assign each archer to the first available coach
            $coach = $coaches->first();

            $archer->coaches()->syncWithoutDetaching([
                $coach->id => [
                    'relationship_type' => 'primary',
                    'started_at'        => now(),
                ],
            ]);
        }
    }
}
