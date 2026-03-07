<?php

/**
 * routes/web.php — Central application routes
 *
 * These routes run on the root domain (mynetdns.info) and are NOT tenant-aware.
 * They handle: authentication, super-admin tenant management, and Stripe billing.
 *
 * Tenant-scoped routes live in routes/tenant.php.
 */

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Central\BillingController;
use App\Http\Controllers\Central\TenantController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ── Public ────────────────────────────────────────────────────────────────────

Route::get('/', fn () => Inertia::render('Welcome'))->name('home');

// TEMPORARY — remove after running once
Route::get('/flush-opcache', function () {
    if (function_exists('opcache_reset')) {
        opcache_reset();
        return response()->json(['status' => 'OPcache cleared']);
    }
    return response()->json(['status' => 'OPcache not available']);
});

// TEMPORARY — clears encrypted db_password from all tenants so middleware
// falls back to env('DB_PASSWORD'). Run once then remove.
Route::get('/fix-tenant-passwords', function () {
    $count = \App\Models\Tenant::query()->update(['db_password' => '']);
    return response()->json(['updated' => $count, 'status' => 'done']);
});

// TEMPORARY — seeds Spatie roles in a tenant DB and assigns user as club_admin.
// Usage: /setup-tenant-roles/{slug}/{user_id}
Route::get('/setup-tenant-roles/{slug}/{userId}', function (string $slug, int $userId) {
    $tenant = \App\Models\Tenant::where('slug', $slug)->firstOrFail();

    \Illuminate\Support\Facades\Config::set('database.connections.tenant', [
        'driver'    => 'mysql',
        'host'      => $tenant->db_host ?: env('DB_HOST', '127.0.0.1'),
        'port'      => env('DB_PORT', '3306'),
        'database'  => $tenant->db_name,
        'username'  => $tenant->db_username ?: env('DB_USERNAME'),
        'password'  => $tenant->db_password ?: env('DB_PASSWORD'),
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix'    => '',
        'strict'    => true,
    ]);
    \Illuminate\Support\Facades\DB::purge('tenant');
    \Illuminate\Support\Facades\DB::setDefaultConnection('tenant');

    // Run tenant migrations to ensure all tables (including Spatie) exist
    \Illuminate\Support\Facades\Artisan::call('migrate', [
        '--path'  => 'database/migrations/tenant',
        '--force' => true,
    ]);
    $migrateOutput = \Illuminate\Support\Facades\Artisan::output();

    // Create Spatie roles
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    foreach (['club_admin', 'coach', 'archer'] as $role) {
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
    }

    // Assign club_admin role to the specified user
    $user = \App\Models\User::on('mysql')->findOrFail($userId);
    $user->setConnection('tenant');
    $user->assignRole('club_admin');

    \Illuminate\Support\Facades\DB::setDefaultConnection('mysql');

    return response()->json([
        'tenant'     => $tenant->slug,
        'db'         => $tenant->db_name,
        'user_id'    => $userId,
        'role'       => 'club_admin',
        'migrations' => trim($migrateOutput) ?: 'Nothing to migrate.',
    ]);
});

// TEMPORARY — remove after wildcard proxy is set up
Route::get('/setup-wildcard', function () {
    $archeryPublic = '/home/fmsport/archery/public';
    $wildcardDir   = '/home/fmsport/public_html/_wildcard_.fmsport.biz';

    // Remove existing symlink
    if (is_link($wildcardDir)) {
        unlink($wildcardDir);
    }

    // Recreate as real directory
    if (!is_dir($wildcardDir)) {
        mkdir($wildcardDir, 0755, true);
    }

    // Proxy index.php — bootstraps the real Laravel app
    file_put_contents($wildcardDir . '/index.php',
        '<?php' . "\n" .
        "define('LARAVEL_START', microtime(true));\n" .
        "chdir('$archeryPublic');\n" .
        "require '$archeryPublic/index.php';\n"
    );

    // .htaccess — route all non-file requests to index.php
    file_put_contents($wildcardDir . '/.htaccess',
        "Options -Indexes\n\n" .
        "<IfModule mod_rewrite.c>\n" .
        "    RewriteEngine On\n" .
        "    RewriteCond %{REQUEST_FILENAME} -f [OR]\n" .
        "    RewriteCond %{REQUEST_FILENAME} -d\n" .
        "    RewriteRule ^ - [L]\n" .
        "    RewriteRule ^ index.php [L]\n" .
        "</IfModule>\n"
    );

    // Symlink build assets so CSS/JS are served directly
    $buildLink = $wildcardDir . '/build';
    if (!file_exists($buildLink) && !is_link($buildLink)) {
        symlink($archeryPublic . '/build', $buildLink);
    }

    return 'Done! Proxy directory created with build symlink.';
});

// TEMPORARY — run once to migrate DB and create super admin, then remove
Route::get('/setup-app', function () {
    // Run all pending migrations
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    $migrateOutput = \Illuminate\Support\Facades\Artisan::output();

    // Create or reset super admin
    $newPassword = 'Admin@1234';
    $user = \App\Models\User::where('is_super_admin', true)->first();
    if ($user) {
        $user->update(['password' => \Illuminate\Support\Facades\Hash::make($newPassword)]);
        $action = 'password_reset';
    } else {
        \App\Models\User::create([
            'name'          => 'Super Admin',
            'email'         => 'admin@fmsport.biz',
            'password'      => \Illuminate\Support\Facades\Hash::make($newPassword),
            'is_super_admin'=> true,
        ]);
        $action = 'created';
    }

    return response()->json([
        'migrations'     => trim($migrateOutput) ?: 'Nothing to migrate.',
        'action'         => $action,
        'login_email'    => $user ? $user->email : 'admin@fmsport.biz',
        'login_password' => $newPassword,
    ]);
});

// ── Authentication ────────────────────────────────────────────────────────────

Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/forgot-password',          [\App\Http\Controllers\Auth\PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password',         [\App\Http\Controllers\Auth\PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}',   [\App\Http\Controllers\Auth\NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password',          [\App\Http\Controllers\Auth\NewPasswordController::class, 'store'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Redirect authenticated users to the right dashboard
    Route::get('/dashboard', function () {
        return auth()->user()->is_super_admin
            ? redirect()->route('admin.dashboard')
            : redirect('/');
    })->name('dashboard');
});

// ── Super Admin — Tenant Management ──────────────────────────────────────────
//    Accessible only to users with is_super_admin = true (checked via middleware).

Route::prefix('admin')
    ->middleware(['auth', 'super_admin'])
    ->name('admin.')
    ->group(function () {
        Route::get('/', fn () => Inertia::render('SuperAdmin/Dashboard'))->name('dashboard');

        // Tenant CRUD
        Route::resource('tenants', TenantController::class);

        // Impersonate a tenant (switch context to a club for support)
        Route::post('tenants/{tenant}/impersonate', [TenantController::class, 'impersonate'])
            ->name('tenants.impersonate');

        // Manually suspend / reactivate a tenant
        Route::patch('tenants/{tenant}/status', [TenantController::class, 'updateStatus'])
            ->name('tenants.status');
    });

// ── Billing — Stripe Cashier ──────────────────────────────────────────────────
//    Tenant admins manage their subscription from the central domain.

Route::prefix('billing')
    ->middleware(['auth'])
    ->name('billing.')
    ->group(function () {
        // Subscription overview / upgrade page
        Route::get('/',             [BillingController::class, 'index'])->name('index');

        // Redirect to Stripe Checkout
        Route::post('/subscribe',   [BillingController::class, 'subscribe'])->name('subscribe');

        // Stripe Customer Portal (manage card, cancel, etc.)
        Route::post('/portal',      [BillingController::class, 'portal'])->name('portal');

        // Stripe webhook receiver — must be excluded from CSRF via VerifyCsrfToken
        Route::post('/webhook',     [BillingController::class, 'webhook'])->name('webhook');
    });
