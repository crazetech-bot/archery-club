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
