<?php

/**
 * routes/web.php — Module-1: Core Accounts & Roles
 *
 * Single-tenant routing. All routes live here.
 * No subdomain switching. No tenant() helper.
 */

use App\Http\Controllers\Admin\ClubController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Scoring\ScorecardPageController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ── Public ────────────────────────────────────────────────────────────────────

Route::get('/', fn () => Inertia::render('Welcome'))->name('home');

// ── Guest (unauthenticated only) ──────────────────────────────────────────────

Route::middleware('guest')->group(function () {

    Route::get('/login',  [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/forgot-password',        [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password',       [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password',        [NewPasswordController::class, 'store'])->name('password.update');

});

// ── Authenticated ─────────────────────────────────────────────────────────────

Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Role-based dashboard redirect
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile — any authenticated user
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // ── Scoring pages (all authenticated roles) ────────────────────────────────

    Route::prefix('scoring')->name('scoring.')->group(function () {
        Route::get('/scorecards',             [ScorecardPageController::class, 'index'])->name('scorecards.index');
        Route::get('/scorecards/create',      [ScorecardPageController::class, 'create'])->name('scorecards.create');
        Route::get('/scorecards/{scorecard}', [ScorecardPageController::class, 'show'])->name('scorecards.show');
    });

    // ── Club Admin ─────────────────────────────────────────────────────────────

    Route::middleware('role:club_admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            // Club settings
            Route::get('/club', [ClubController::class, 'edit'])->name('club.edit');
            Route::put('/club', [ClubController::class, 'update'])->name('club.update');

            // Member management
            Route::get('/members',          [MemberController::class, 'index'])->name('members.index');
            Route::post('/members',         [MemberController::class, 'store'])->name('members.store');
            Route::get('/members/{user}',   [MemberController::class, 'show'])->name('members.show');
            Route::put('/members/{user}',   [MemberController::class, 'update'])->name('members.update');
            Route::delete('/members/{user}',[MemberController::class, 'destroy'])->name('members.destroy');

        });

});
