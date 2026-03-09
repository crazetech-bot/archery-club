<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coaches\Coach;
use App\Models\Core\User;
use Illuminate\Support\Facades\Hash;

class CoachSeeder extends Seeder
{
    public function run(): void
    {
        // Create demo coach users
        $coachUsers = [
            [
                'name'     => 'Coach Alpha',
                'email'    => 'coach1@example.com',
                'password' => Hash::make('password'),
            ],
            [
                'name'     => 'Coach Beta',
                'email'    => 'coach2@example.com',
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($coachUsers as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                $data
            );

            Coach::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'certification_level' => 'Level 1',
                    'bio'                 => null,
                ]
            );
        }
    }
}
