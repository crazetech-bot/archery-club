<?php

/**
 * routes/api.php — Tenant API (Sanctum token auth)
 *
 * Used by the mobile scoring app and any external integrations.
 * All routes run inside the tenant context (Stancl middleware initialises
 * the correct DB connection before the request hits the controller).
 *
 * Authentication: Bearer token via Laravel Sanctum.
 * Rate limiting:  'api' throttle (60 req/min by default).
 *
 * Response format: JSON (no Inertia).
 */

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LiveScoringApiController;
use App\Http\Controllers\Api\MeController;
use App\Http\Controllers\Api\SessionApiController;
use App\Http\Controllers\Reports\LeaderboardController;
use Illuminate\Support\Facades\Route;

// ── Public ────────────────────────────────────────────────────────────────────

Route::prefix('v1')->middleware(['tenant', 'throttle:api'])->group(function () {

    /**
     * POST /api/v1/login
     * Exchange credentials for a Sanctum token.
     */
    Route::post('/login', [AuthController::class, 'login']);

    // ── Authenticated ─────────────────────────────────────────────────────────

    Route::middleware('auth:sanctum')->group(function () {

        /**
         * POST /api/v1/logout
         * Revoke the current token.
         */
        Route::post('/logout', [AuthController::class, 'logout']);

        /**
         * GET /api/v1/me
         * Return the authenticated user's profile and role.
         */
        Route::get('/me', [MeController::class, 'show']);

        // ── Archer — training sessions ────────────────────────────────────────

        Route::prefix('sessions')->middleware('role:archer|club_admin')->group(function () {

            /**
             * GET  /api/v1/sessions
             * List training sessions for the authenticated archer.
             */
            Route::get('/',                       [SessionApiController::class, 'index']);

            /**
             * POST /api/v1/sessions
             * Create a new training session and return it.
             */
            Route::post('/',                      [SessionApiController::class, 'store']);

            /**
             * GET  /api/v1/sessions/{session}
             */
            Route::get('/{session}',              [SessionApiController::class, 'show']);
        });

        // ── Live scoring (archer) ─────────────────────────────────────────────

        Route::prefix('live')->middleware('role:archer|club_admin')->group(function () {

            /**
             * POST /api/v1/live/{trainingSession}/start
             * Start a live session for the given training session.
             */
            Route::post('/{trainingSession}/start',    [LiveScoringApiController::class, 'start']);

            /**
             * GET  /api/v1/live/{liveSession}
             * Return current state of a live session (ends, arrows, totals).
             */
            Route::get('/{liveSession}',               [LiveScoringApiController::class, 'show']);

            /**
             * POST /api/v1/live/{liveSession}/end
             * Submit an end (array of arrow scores).
             * Body: { arrows: ['X', '10', '9', '9', '8', '7'] }
             */
            Route::post('/{liveSession}/end',          [LiveScoringApiController::class, 'submitEnd']);

            /**
             * PATCH /api/v1/live/{liveSession}/complete
             * Mark the live session as completed.
             */
            Route::patch('/{liveSession}/complete',    [LiveScoringApiController::class, 'complete']);
        });

        // ── Coach / admin — monitor ───────────────────────────────────────────

        Route::prefix('monitor')->middleware('role:coach|club_admin')->group(function () {

            /**
             * GET /api/v1/monitor/sessions
             * Return all archer session cards for the coach monitor.
             */
            Route::get('/sessions', [\App\Http\Controllers\Live\CoachMonitorController::class, 'sessions']);

            /**
             * POST /api/v1/monitor/sessions/{liveSession}/note
             * Send a coach note to a live session.
             */
            Route::post('/sessions/{liveSession}/note', [\App\Http\Controllers\Live\CoachMonitorController::class, 'addNote']);
        });

        // ── Leaderboard (any authenticated user) ─────────────────────────────

        /**
         * GET /api/v1/leaderboard?round_type=&category=&period=90
         * Return leaderboard data as JSON.
         */
        Route::get('/leaderboard', [LeaderboardController::class, 'data']);
    });
});
