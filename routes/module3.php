<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Module3\TrainingSessionController;
use App\Http\Controllers\Module3\TrainingSessionArcherController;
use App\Http\Controllers\Module3\TrainingSessionCoachController;
use App\Http\Controllers\Module3\TrainingSessionNoteController;
use App\Http\Controllers\Module3\ScoringTemplateController;

Route::middleware(['auth:sanctum'])->group(function () {

    // Training Sessions
    Route::get('/sessions', [TrainingSessionController::class, 'index']);
    Route::post('/sessions', [TrainingSessionController::class, 'store']);
    Route::get('/sessions/{session}', [TrainingSessionController::class, 'show']);
    Route::put('/sessions/{session}', [TrainingSessionController::class, 'update']);
    Route::delete('/sessions/{session}', [TrainingSessionController::class, 'destroy']);

    // Session Archers
    Route::post('/sessions/{session}/archers/{archer}', [TrainingSessionArcherController::class, 'addArcher']);
    Route::delete('/sessions/{session}/archers/{archer}', [TrainingSessionArcherController::class, 'removeArcher']);
    Route::put('/sessions/{session}/archers/{archer}/attendance', [TrainingSessionArcherController::class, 'updateAttendance']);
    Route::get('/sessions/{session}/archers', [TrainingSessionArcherController::class, 'listArchers']);

    // Session Coaches
    Route::post('/sessions/{session}/coaches/{coach}', [TrainingSessionCoachController::class, 'addCoach']);
    Route::delete('/sessions/{session}/coaches/{coach}', [TrainingSessionCoachController::class, 'removeCoach']);
    Route::get('/sessions/{session}/coaches', [TrainingSessionCoachController::class, 'listCoaches']);

    // Session Notes
    Route::get('/sessions/{session}/notes', [TrainingSessionNoteController::class, 'index']);
    Route::post('/sessions/{session}/notes', [TrainingSessionNoteController::class, 'store']);
    Route::put('/sessions/{session}/notes/{note}', [TrainingSessionNoteController::class, 'update']);
    Route::delete('/sessions/{session}/notes/{note}', [TrainingSessionNoteController::class, 'destroy']);

    // Scoring Templates
    Route::get('/scoring-templates', [ScoringTemplateController::class, 'index']);
    Route::post('/scoring-templates', [ScoringTemplateController::class, 'store']);
    Route::get('/scoring-templates/{template}', [ScoringTemplateController::class, 'show']);
    Route::put('/scoring-templates/{template}', [ScoringTemplateController::class, 'update']);
    Route::delete('/scoring-templates/{template}', [ScoringTemplateController::class, 'destroy']);
});
