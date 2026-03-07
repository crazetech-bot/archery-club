<?php

/**
 * routes/tenant.php — Tenant-scoped application routes
 *
 * Resolved on tenant subdomains ({tenant}.mynetdns.info).
 * Middleware stack initialises the correct database connection before any route runs.
 *
 * Global middleware: 'tenant' | 'auth' | 'subscribed'
 * Role middleware applied per-group via Spatie: ->middleware('role:club_admin')
 */

use App\Http\Controllers\Admin\ArcherController as AdminArcherController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\CompetitionController as AdminCompetitionController;
use App\Http\Controllers\Admin\LaneBookingController;
use App\Http\Controllers\Admin\LaneController as AdminLaneController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Archer\CompetitionController as ArcherCompetitionController;
use App\Http\Controllers\Archer\EquipmentController;
use App\Http\Controllers\Archer\EquipmentMaintenanceController;
use App\Http\Controllers\Archer\ProfileController;
use App\Http\Controllers\Archer\TrainingSessionController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Coach\ArcherController as CoachArcherController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Live\CoachMonitorController;
use App\Http\Controllers\Live\ScoringController;
use App\Http\Controllers\Reports\LeaderboardController;
use App\Http\Controllers\Reports\ScoreProgressionController;
use Illuminate\Support\Facades\Route;

// ── Auth routes on tenant subdomains ─────────────────────────────────────────
Route::middleware('tenant')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login',  [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    });
    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    });
});

// ── Authenticated tenant routes ───────────────────────────────────────────────
Route::middleware(['tenant', 'auth', 'subscribed'])->group(function () {

    // ── Root redirect to role dashboard ──────────────────────────────────────

    Route::get('/', function () {
        $user = auth()->user();
        if ($user->hasRole('club_admin')) return redirect('/admin/dashboard');
        if ($user->hasRole('coach'))      return redirect('/coach/dashboard');
        return redirect('/archer/dashboard');
    })->name('home');

    // =========================================================================
    // ARCHER ROUTES  (/archer/*)
    // =========================================================================

    Route::prefix('archer')
        ->middleware('role:archer|club_admin')
        ->name('archer.')
        ->group(function () {

            Route::get('/dashboard', [DashboardController::class, 'archer'])
                ->name('dashboard');

            // Training sessions
            Route::get('/sessions',                         [TrainingSessionController::class, 'index'])->name('sessions.index');
            Route::get('/sessions/{session}',               [TrainingSessionController::class, 'show'])->name('sessions.show');
            Route::get('/sessions/{session}/export',        [TrainingSessionController::class, 'export'])->name('sessions.export');

            // Equipment setups
            Route::get('/equipment',                        [EquipmentController::class, 'index'])->name('equipment.index');
            Route::post('/equipment',                       [EquipmentController::class, 'store'])->name('equipment.store');
            Route::put('/equipment/{setup}',                [EquipmentController::class, 'update'])->name('equipment.update');
            Route::put('/equipment/{setup}/set-current',    [EquipmentController::class, 'setCurrent'])->name('equipment.set-current');
            Route::delete('/equipment/{setup}',             [EquipmentController::class, 'destroy'])->name('equipment.destroy');

            // Equipment maintenance log
            Route::get('/equipment/{setup}/maintenance',            [EquipmentMaintenanceController::class, 'index'])->name('equipment.maintenance.index');
            Route::post('/equipment/{setup}/maintenance',           [EquipmentMaintenanceController::class, 'store'])->name('equipment.maintenance.store');
            Route::delete('/equipment/{setup}/maintenance/{log}',   [EquipmentMaintenanceController::class, 'destroy'])->name('equipment.maintenance.destroy');

            // Competition results (read-only; admin records them)
            Route::get('/competitions', [ArcherCompetitionController::class, 'index'])->name('competitions.index');

            // Profile
            Route::get('/profile',  [ProfileController::class, 'show'])->name('profile.show');
            Route::put('/profile',  [ProfileController::class, 'update'])->name('profile.update');
            Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
            Route::delete('/account', [ProfileController::class, 'destroy'])->name('account.destroy');
        });

    // =========================================================================
    // COACH ROUTES  (/coach/*)
    // =========================================================================

    Route::prefix('coach')
        ->middleware('role:coach|club_admin')
        ->name('coach.')
        ->group(function () {

            Route::get('/dashboard', [DashboardController::class, 'coach'])
                ->name('dashboard');

            Route::get('/archers',                              [CoachArcherController::class, 'index'])->name('archers.index');
            Route::get('/archers/{archer}',                     [CoachArcherController::class, 'show'])->name('archers.show');
            Route::post('/archers/{archer}/notes',              [CoachArcherController::class, 'storeNote'])->name('archers.notes.store');
        });

    // =========================================================================
    // CLUB ADMIN ROUTES  (/admin/*)
    // =========================================================================

    Route::prefix('admin')
        ->middleware('role:club_admin')
        ->name('admin.')
        ->group(function () {

            Route::get('/dashboard', [DashboardController::class, 'admin'])
                ->name('dashboard');

            // Members
            Route::get('/members',                          [MemberController::class, 'index'])->name('members.index');
            Route::post('/members/archers',                 [MemberController::class, 'storeArcher'])->name('members.archers.store');
            Route::put('/members/archers/{archer}',         [MemberController::class, 'updateArcher'])->name('members.archers.update');
            Route::delete('/members/archers/{archer}',      [MemberController::class, 'destroyArcher'])->name('members.archers.destroy');

            // Lanes
            Route::get('/lanes',                            [AdminLaneController::class, 'index'])->name('lanes.index');
            Route::post('/lanes',                           [AdminLaneController::class, 'store'])->name('lanes.store');
            Route::put('/lanes/{lane}',                     [AdminLaneController::class, 'update'])->name('lanes.update');
            Route::delete('/lanes/{lane}',                  [AdminLaneController::class, 'destroy'])->name('lanes.destroy');

            // Lane bookings (admin manages all bookings)
            Route::get('/bookings',                              [LaneBookingController::class, 'index'])->name('bookings.index');
            Route::get('/lanes/{lane}/bookings',                 [LaneBookingController::class, 'index'])->name('lanes.bookings.index');
            Route::post('/lanes/{lane}/bookings',                [LaneBookingController::class, 'store'])->name('lanes.bookings.store');
            Route::put('/lanes/{lane}/bookings/{booking}',       [LaneBookingController::class, 'update'])->name('lanes.bookings.update');
            Route::delete('/lanes/{lane}/bookings/{booking}',    [LaneBookingController::class, 'destroy'])->name('lanes.bookings.destroy');

            // Group sessions + attendance
            Route::get('/sessions',                              [AttendanceController::class, 'index'])->name('sessions.index');
            Route::post('/sessions',                             [AttendanceController::class, 'store'])->name('sessions.store');
            Route::patch('/sessions/{session}',                  [AttendanceController::class, 'update'])->name('sessions.update');
            Route::delete('/sessions/{session}',                 [AttendanceController::class, 'destroy'])->name('sessions.destroy');
            Route::get('/sessions/{session}/attendance',         [AttendanceController::class, 'show'])->name('sessions.attendance.show');
            Route::post('/sessions/{session}/attendance',        [AttendanceController::class, 'mark'])->name('sessions.attendance.mark');

            // Archers (admin CRUD)
            Route::get('/archers',                   [AdminArcherController::class, 'index'])->name('archers.index');
            Route::get('/archers/create',            [AdminArcherController::class, 'create'])->name('archers.create');
            Route::post('/archers',                  [AdminArcherController::class, 'store'])->name('archers.store');
            Route::get('/archers/{archer}',          [AdminArcherController::class, 'show'])->name('archers.show');
            Route::get('/archers/{archer}/edit',     [AdminArcherController::class, 'edit'])->name('archers.edit');
            Route::put('/archers/{archer}',          [AdminArcherController::class, 'update'])->name('archers.update');
            Route::delete('/archers/{archer}',       [AdminArcherController::class, 'destroy'])->name('archers.destroy');

            // Competitions
            Route::get('/competitions',                          [AdminCompetitionController::class, 'index'])->name('competitions.index');
            Route::post('/competitions',                         [AdminCompetitionController::class, 'store'])->name('competitions.store');
            Route::get('/competitions/{competition}',            [AdminCompetitionController::class, 'show'])->name('competitions.show');
            Route::put('/competitions/{competition}',            [AdminCompetitionController::class, 'update'])->name('competitions.update');
            Route::delete('/competitions/{competition}',         [AdminCompetitionController::class, 'destroy'])->name('competitions.destroy');
            Route::post('/competitions/{competition}/results',   [AdminCompetitionController::class, 'storeResult'])->name('competitions.results.store');
        });

    // =========================================================================
    // LIVE SCORING ROUTES  (/live/*)
    // =========================================================================

    Route::prefix('live')
        ->name('live.')
        ->group(function () {

            // Archer scoring
            Route::middleware('role:archer|club_admin')->group(function () {
                Route::get('/session/{trainingSession}',            [ScoringController::class, 'show'])->name('session.show');
                Route::post('/session/{trainingSession}/start',     [ScoringController::class, 'start'])->name('session.start');
                Route::post('/session/{liveSession}/end',           [ScoringController::class, 'submitEnd'])->name('session.end.submit');
                Route::patch('/session/{liveSession}/complete',     [ScoringController::class, 'complete'])->name('session.complete');
            });

            // Coach / admin monitor
            Route::middleware('role:coach|club_admin')->group(function () {
                Route::get('/monitor',                              [CoachMonitorController::class, 'index'])->name('monitor');
                Route::get('/coach/sessions',                       [CoachMonitorController::class, 'sessions'])->name('coach.sessions');
                Route::post('/session/{liveSession}/note',          [CoachMonitorController::class, 'addNote'])->name('session.note');
            });
        });

    // =========================================================================
    // REPORTS  (/reports/*)
    // =========================================================================

    Route::prefix('reports')
        ->name('reports.')
        ->group(function () {

            Route::prefix('archer/{archer}')
                ->name('archer.')
                ->group(function () {
                    Route::get('/score-progression',        [ScoreProgressionController::class, 'show'])->name('score-progression');
                    Route::get('/score-progression/data',   [ScoreProgressionController::class, 'data'])->name('score-progression.data');
                });

            Route::get('/leaderboard',      [LeaderboardController::class, 'index'])->name('leaderboard');
            Route::get('/leaderboard/data', [LeaderboardController::class, 'data'])->name('leaderboard.data');
        });
});
