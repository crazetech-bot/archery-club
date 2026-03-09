<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Archers\Archer;
use App\Models\Core\User;
use Illuminate\Support\Facades\Hash;

class ArcherSeeder extends Seeder
{
    public function run(): void
    {
        // Create demo archer users
        $archerUsers = [
            [
                'name'     => 'Archer One',
                'email'    => 'archer1@example.com',
                'password' => Hash::make('password'),
            ],
            [
                'name'     => 'Archer Two',
                'email'    => 'archer2@example.com',
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($archerUsers as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                $data
            );

            Archer::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'bow_type'     => 'recurve',
                    'dominant_eye' => 'right',
                    'notes'        => null,
                ]
            );
        }
    }
}
