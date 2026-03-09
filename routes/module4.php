<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Module4\ScorecardController;
use App\Http\Controllers\Module4\ScorecardEndController;
use App\Http\Controllers\Module4\ScorecardShotController;
use App\Http\Controllers\Module4\ScorecardMetricController;

Route::middleware(['auth:sanctum'])->group(function () {

    // Scorecards
    Route::get('/scorecards',                   [ScorecardController::class, 'index']);
    Route::post('/scorecards',                  [ScorecardController::class, 'store']);
    Route::get('/scorecards/{scorecard}',       [ScorecardController::class, 'show']);
    Route::put('/scorecards/{scorecard}',       [ScorecardController::class, 'update']);
    Route::delete('/scorecards/{scorecard}',    [ScorecardController::class, 'destroy']);

    // Scorecard status actions
    Route::post('/scorecards/{scorecard}/submit', [ScorecardController::class, 'submit']);
    Route::post('/scorecards/{scorecard}/lock',   [ScorecardController::class, 'lock']);

    // Ends
    Route::post('/scorecards/{scorecard}/ends',               [ScorecardEndController::class, 'store']);
    Route::put('/scorecards/{scorecard}/ends/{end}',          [ScorecardEndController::class, 'update']);
    Route::delete('/scorecards/{scorecard}/ends/{end}',       [ScorecardEndController::class, 'destroy']);

    // Shots
    Route::post('/scorecards/{scorecard}/shots',              [ScorecardShotController::class, 'store']);
    Route::put('/scorecards/{scorecard}/shots/{shot}',        [ScorecardShotController::class, 'update']);
    Route::delete('/scorecards/{scorecard}/shots/{shot}',     [ScorecardShotController::class, 'destroy']);

    // Metrics
    Route::post('/scorecards/{scorecard}/metrics/recalculate', [ScorecardMetricController::class, 'recalculate']);

});
