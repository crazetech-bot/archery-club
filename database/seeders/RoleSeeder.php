<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * RoleSeeder — Tenant DB
 *
 * Creates the three Spatie roles and their associated permissions
 * inside the currently initialised tenant database.
 *
 * Must be called from within a tenant context:
 *   $tenant->run(fn () => $this->call(RoleSeeder::class));
 */
class RoleSeeder extends Seeder
{
    /**
     * Permissions grouped by the role that owns them.
     * Each permission string follows the pattern: action.resource
     */
    private array $permissions = [
        // Visible to all roles
        'common' => [
            'view.own_profile',
            'edit.own_profile',
            'view.competitions',
            'view.lanes',
        ],

        // Archer — own data only
        'archer' => [
            'view.own_sessions',
            'create.live_session',
            'submit.end',
            'view.own_reports',
            'view.own_equipment',
            'create.equipment_setup',
            'edit.equipment_setup',
            'create.lane_booking',
            'cancel.own_lane_booking',
        ],

        // Coach — assigned archers
        'coach' => [
            'view.assigned_archers',
            'view.archer_sessions',
            'monitor.live_sessions',
            'add.coach_note',
            'view.coach_reports',
            'create.training_session',
        ],

        // Club admin — full club management
        'club_admin' => [
            'manage.members',
            'assign.roles',
            'manage.lanes',
            'manage.competitions',
            'record.competition_results',
            'view.all_reports',
            'view.all_sessions',
            'monitor.all_live_sessions',
            'export.reports',
        ],
    ];

    public function run(): void
    {
        // Reset cached roles/permissions to avoid stale state
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create all permissions first
        $allPermissions = array_unique(array_merge(...array_values($this->permissions)));

        foreach ($allPermissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        // ── archer ────────────────────────────────────────────────────────────
        $archerRole = Role::firstOrCreate(['name' => 'archer']);
        $archerRole->syncPermissions(
            array_merge($this->permissions['common'], $this->permissions['archer'])
        );

        // ── coach ─────────────────────────────────────────────────────────────
        $coachRole = Role::firstOrCreate(['name' => 'coach']);
        $coachRole->syncPermissions(
            array_merge(
                $this->permissions['common'],
                $this->permissions['archer'],
                $this->permissions['coach']
            )
        );

        // ── club_admin ────────────────────────────────────────────────────────
        // Club admin inherits all permissions
        $adminRole = Role::firstOrCreate(['name' => 'club_admin']);
        $adminRole->syncPermissions($allPermissions);

        $this->command->info('  Roles created: archer, coach, club_admin');
        $this->command->info('  Permissions created: ' . count($allPermissions));
    }
}
