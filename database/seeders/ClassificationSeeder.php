<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Scoring\Classification;

class ClassificationSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'U12',  'min_age' => 0,    'max_age' => 11],
            ['name' => 'U15',  'min_age' => 12,   'max_age' => 14],
            ['name' => 'U18',  'min_age' => 15,   'max_age' => 17],
            ['name' => 'U21',  'min_age' => 18,   'max_age' => 20],
            ['name' => 'Open', 'min_age' => null,  'max_age' => null],
        ];

        foreach ($items as $data) {
            Classification::firstOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}
