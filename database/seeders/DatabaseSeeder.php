<?php

namespace Database\Seeders;

use App\Models\Central\Tenant;
use App\Models\Central\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * DatabaseSeeder — Central DB
 *
 * Entry point for all seeding. Responsible for:
 *   1. Creating the super admin user on the central DB
 *   2. Provisioning a demo tenant (creates the tenant DB and runs migrations)
 *   3. Linking central users to the demo tenant via tenant_user pivot
 *
 * Run with:
 *   php artisan db:seed
 *
 * To reset and re-seed from scratch:
 *   php artisan migrate:fresh --seed
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('── Central DB ──────────────────────────────────────');

        // ── 1. Super admin ────────────────────────────────────────────────────
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@mynetdns.info'],
            [
                'name'           => 'Super Admin',
                'password'       => Hash::make('password'),
                'is_super_admin' => true,
            ]
        );

        $this->command->info('  Super admin: ' . $superAdmin->email);

        // ── 2. Demo tenant ────────────────────────────────────────────────────
        $this->command->info('  Provisioning demo tenant…');

        $tenant = Tenant::firstOrCreate(
            ['slug' => 'demo-club'],
            [
                'name'        => 'Demo Archery Club',
                'slug'        => 'demo-club',
                'db_name'     => 'archery_tenant_demo',
                'db_host'     => env('DB_HOST', '127.0.0.1'),
                'db_username' => env('DB_USERNAME', 'root'),
                'db_password' => env('DB_PASSWORD', ''),
                'plan'        => 'pro',
                'status'      => 'active',
            ]
        );

        $this->command->info('  Tenant: ' . $tenant->name . ' (slug: ' . $tenant->slug . ')');
        $this->command->info('  Tenant DB: ' . $tenant->db_name);

        // ── 3. Run tenant migrations on the demo tenant DB ────────────────────
        // Stancl Tenancy provides artisan commands, but for seeding we
        // initialise tenancy manually and run the migrations via artisan call.
        $this->command->info('  Running tenant migrations…');

        tenancy()->initialize($tenant);

        \Artisan::call('migrate', [
            '--path'     => 'database/migrations/tenant',
            '--database' => 'tenant',
            '--force'    => true,
        ]);

        $this->command->info('  Tenant migrations complete.');

        // ── 4. Seed the tenant DB ─────────────────────────────────────────────
        $this->command->info('');
        $this->command->info('── Tenant DB: ' . $tenant->db_name . ' ──────────────────────');

        $this->call(TenantSeeder::class);

        // ── 5. End tenancy — return to central DB context ─────────────────────
        tenancy()->end();

        // ── 6. Link central users → tenant via pivot ──────────────────────────
        $this->command->info('');
        $this->command->info('── Central DB (pivot) ──────────────────────────────');
        $this->command->info('  Linking users to tenant…');

        $userRoleMap = [
            'admin@demo-club.test'  => 'club_admin',
            'coach@demo-club.test'  => 'coach',
            'alex@demo-club.test'   => 'archer',
            'jordan@demo-club.test' => 'archer',
            'morgan@demo-club.test' => 'archer',
        ];

        foreach ($userRoleMap as $email => $role) {
            $user = User::where('email', $email)->first();

            if (! $user) {
                $this->command->warn("  User not found: {$email} — skipping pivot.");
                continue;
            }

            // Attach to tenant if not already linked
            if (! $tenant->users()->where('users.id', $user->id)->exists()) {
                $tenant->users()->attach($user->id, ['role' => $role]);
            }

            $this->command->info("  Linked {$email} as {$role}");
        }

        // ── Summary ───────────────────────────────────────────────────────────
        $this->command->info('');
        $this->command->info('── Done ────────────────────────────────────────────');
        $this->command->info('');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Super Admin',  'superadmin@mynetdns.info', 'password'],
                ['Club Admin',   'admin@demo-club.test',     'password'],
                ['Coach',        'coach@demo-club.test',     'password'],
                ['Archer',       'alex@demo-club.test',      'password'],
                ['Archer',       'jordan@demo-club.test',    'password'],
                ['Archer',       'morgan@demo-club.test',    'password'],
            ]
        );
        $this->command->info('  Tenant URL: http://demo-club.mynetdns.info');
        $this->command->info('');
    }
}
