<?php

namespace Database\Seeders;

use App\Models\Core\Club;
use Illuminate\Database\Seeder;

class ClubSeeder extends Seeder
{
    public function run(): void
    {
        Club::firstOrCreate(
            ['slug' => 'default-archery-club'],
            [
                'name'      => 'Default Archery Club',
                'timezone'  => 'Asia/Kuala_Lumpur',
                'country'   => 'Malaysia',
                'city'      => 'Kuala Lumpur',
                'logo_path' => null,
            ]
        );
    }
}
