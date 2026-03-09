<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Module2\ArcherController;
use App\Http\Controllers\Module2\CoachController;

Route::middleware(['auth:sanctum'])->group(function () {

    // Archers
    Route::get('/archers', [ArcherController::class, 'index']);
    Route::post('/archers', [ArcherController::class, 'store']);
    Route::get('/archers/{archer}', [ArcherController::class, 'show']);
    Route::put('/archers/{archer}', [ArcherController::class, 'update']);
    Route::delete('/archers/{archer}', [ArcherController::class, 'destroy']);

    // Coaches
    Route::get('/coaches', [CoachController::class, 'index']);
    Route::post('/coaches', [CoachController::class, 'store']);
    Route::get('/coaches/{coach}', [CoachController::class, 'show']);
    Route::put('/coaches/{coach}', [CoachController::class, 'update']);
    Route::delete('/coaches/{coach}', [CoachController::class, 'destroy']);

    // Archer ↔ Coach assignment
    Route::post('/archers/{archer}/assign-coach/{coach}', [ArcherController::class, 'assignCoach']);
    Route::delete('/archers/{archer}/remove-coach/{coach}', [ArcherController::class, 'removeCoach']);
    Route::get('/archers/{archer}/coaches', [ArcherController::class, 'listCoaches']);

    Route::get('/coaches/{coach}/archers', [CoachController::class, 'listArchers']);
});
