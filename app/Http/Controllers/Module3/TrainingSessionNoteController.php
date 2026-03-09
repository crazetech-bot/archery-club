<?php

namespace App\Http\Controllers\Module3;

use App\Http\Controllers\Controller;
use App\Models\Training\TrainingSession;
use App\Models\Training\TrainingSessionNote;
use App\Http\Requests\Module3\StoreTrainingSessionNoteRequest;
use App\Http\Requests\Module3\UpdateTrainingSessionNoteRequest;
use App\Services\Module3\TrainingSessionNoteService;

class TrainingSessionNoteController extends Controller
{
    public function __construct(
        protected TrainingSessionNoteService $service
    ) {}

    public function index(TrainingSession $session)
    {
        return $this->service->listNotes($session);
    }

    public function store(StoreTrainingSessionNoteRequest $request, TrainingSession $session)
    {
        return $this->service->createNote($session, $request->validated());
    }

    public function update(UpdateTrainingSessionNoteRequest $request, TrainingSession $session, TrainingSessionNote $note)
    {
        return $this->service->updateNote($session, $note, $request->validated());
    }

    public function destroy(TrainingSession $session, TrainingSessionNote $note)
    {
        return $this->service->deleteNote($session, $note);
    }
}
