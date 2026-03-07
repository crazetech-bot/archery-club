<?php

namespace Database\Seeders;

use App\Models\Archer;
use App\Models\Central\User;
use App\Models\Coach;
use App\Models\Lane;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * TenantSeeder — Tenant DB
 *
 * Bootstraps a freshly provisioned tenant database with:
 *   - Spatie roles + permissions (via RoleSeeder)
 *   - A club admin, one coach, and three archers
 *   - Demo lanes
 *   - Full training history (via DemoArcherSeeder)
 *
 * This seeder must run within a tenant context:
 *   $tenant->run(fn () => (new TenantSeeder)->run());
 *
 * Central users are created on the central DB connection so the
 * tenant_user pivot can be populated by DatabaseSeeder afterwards.
 */
class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('  Running RoleSeeder…');
        $this->call(RoleSeeder::class);

        $this->command?->info('  Seeding lanes…');
        $this->seedLanes();

        $this->command?->info('  Seeding users, coach, and archers…');
        $this->seedMembers();

        $this->command?->info('  Seeding demo training data…');
        $this->call(DemoArcherSeeder::class);

        $this->command?->info('  Seeding coach notes…');
        $this->call(CoachNoteSeeder::class);

        $this->command?->info('  Seeding lane bookings…');
        $this->call(LaneBookingSeeder::class);
    }

    // ── Lanes ─────────────────────────────────────────────────────────────────

    private function seedLanes(): void
    {
        $lanes = [
            ['number' => 1, 'name' => 'Lane 1', 'distance_metres' => 18, 'target_face' => '40cm',  'is_active' => true],
            ['number' => 2, 'name' => 'Lane 2', 'distance_metres' => 18, 'target_face' => '40cm',  'is_active' => true],
            ['number' => 3, 'name' => 'Lane 3', 'distance_metres' => 18, 'target_face' => '40cm',  'is_active' => true],
            ['number' => 4, 'name' => 'Lane 4', 'distance_metres' => 18, 'target_face' => '40cm',  'is_active' => true],
            ['number' => 5, 'name' => 'Lane A', 'distance_metres' => 70, 'target_face' => '122cm', 'is_active' => true],
            ['number' => 6, 'name' => 'Lane B', 'distance_metres' => 70, 'target_face' => '122cm', 'is_active' => false],
        ];

        foreach ($lanes as $lane) {
            Lane::firstOrCreate(['number' => $lane['number']], $lane);
        }
    }

    // ── Members ───────────────────────────────────────────────────────────────

    private function seedMembers(): void
    {
        // ── Club admin ────────────────────────────────────────────────────────
        $adminUser = $this->createCentralUser(
            name: 'Demo Admin',
            email: 'admin@demo-club.test',
            password: 'password',
        );

        // No Archer/Coach record needed for admin — just the Spatie role
        $adminUser->assignRole('club_admin');

        // ── Coach ─────────────────────────────────────────────────────────────
        $coachUser = $this->createCentralUser(
            name: 'Sarah Coach',
            email: 'coach@demo-club.test',
            password: 'password',
        );

        $coach = Coach::firstOrCreate(
            ['user_id' => $coachUser->id],
            [
                'name'  => 'Sarah Coach',
                'level' => 'Level 2',
                'notes' => 'Specialises in recurve technique and form.',
            ]
        );

        $coachUser->assignRole('coach');

        // ── Archers ───────────────────────────────────────────────────────────
        $archerDefinitions = [
            [
                'name'  => 'Alex Archer',
                'email' => 'alex@demo-club.test',
                'archer' => [
                    'category'      => 'Senior',
                    'date_of_birth' => '1995-06-15',
                    'dominant_hand' => 'right',
                    'phone'         => '+44 7700 900001',
                    'coach_id'      => $coach->id,
                ],
            ],
            [
                'name'  => 'Jordan Beginner',
                'email' => 'jordan@demo-club.test',
                'archer' => [
                    'category'      => 'U21',
                    'date_of_birth' => '2002-03-22',
                    'dominant_hand' => 'right',
                    'phone'         => '+44 7700 900002',
                    'coach_id'      => $coach->id,
                ],
            ],
            [
                'name'  => 'Morgan Advanced',
                'email' => 'morgan@demo-club.test',
                'archer' => [
                    'category'      => 'Senior',
                    'date_of_birth' => '1988-11-07',
                    'dominant_hand' => 'left',
                    'phone'         => '+44 7700 900003',
                    'coach_id'      => $coach->id,
                ],
            ],
        ];

        foreach ($archerDefinitions as $definition) {
            $user = $this->createCentralUser(
                name: $definition['name'],
                email: $definition['email'],
                password: 'password',
            );

            Archer::firstOrCreate(
                ['user_id' => $user->id],
                array_merge($definition['archer'], ['user_id' => $user->id])
            );

            $user->assignRole('archer');
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Create (or retrieve) a user on the **central** database.
     * We specify the connection explicitly because TenantSeeder runs
     * inside a tenant DB context where the default connection is the tenant DB.
     */
    private function createCentralUser(string $name, string $email, string $password): User
    {
        return User::on('mysql')->firstOrCreate(
            ['email' => $email],
            [
                'name'     => $name,
                'password' => Hash::make($password),
            ]
        );
    }
}
