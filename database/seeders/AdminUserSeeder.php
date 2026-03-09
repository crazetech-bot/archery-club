<?php

namespace Database\Seeders;

use App\Models\Core\Club;
use App\Models\Core\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $club = Club::where('slug', 'default-archery-club')->firstOrFail();

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'        => 'System Administrator',
                'password'    => Hash::make('password'),
                'phone'       => null,
                'avatar_path' => null,
                'is_active'   => true,
            ]
        );

        // Assign role
        $admin->assignRole('super_admin');

        // Attach to club
        $club->users()->syncWithoutDetaching([
            $admin->id => [
                'primary_role' => 'club_admin',
                'joined_at'    => now(),
            ],
        ]);
    }
}
