<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Scoring\Distance;

class DistanceSeeder extends Seeder
{
    public function run(): void
    {
        $distances = [90, 70, 60, 50, 40, 30, 25, 20, 18, 15, 10, 5];

        foreach ($distances as $meters) {
            Distance::firstOrCreate(['meters' => $meters]);
        }
    }
}
