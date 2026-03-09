<?php

namespace App\Http\Controllers\Module3;

use App\Http\Controllers\Controller;
use App\Models\Training\TrainingSession;
use App\Http\Requests\Module3\StoreTrainingSessionRequest;
use App\Http\Requests\Module3\UpdateTrainingSessionRequest;
use App\Services\Module3\TrainingSessionService;

class TrainingSessionController extends Controller
{
    public function __construct(
        protected TrainingSessionService $service
    ) {}

    public function index()
    {
        return $this->service->listSessions();
    }

    public function store(StoreTrainingSessionRequest $request)
    {
        return $this->service->createSession($request->validated());
    }

    public function show(TrainingSession $session)
    {
        return $this->service->getSession($session);
    }

    public function update(UpdateTrainingSessionRequest $request, TrainingSession $session)
    {
        return $this->service->updateSession($session, $request->validated());
    }

    public function destroy(TrainingSession $session)
    {
        return $this->service->deleteSession($session);
    }
}
