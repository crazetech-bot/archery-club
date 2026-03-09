<?php

namespace App\Http\Controllers\Module2;

use App\Http\Controllers\Controller;
use App\Models\Coaches\Coach;
use App\Http\Requests\Module2\StoreCoachRequest;
use App\Http\Requests\Module2\UpdateCoachRequest;
use App\Services\Module2\CoachService;

class CoachController extends Controller
{
    public function __construct(
        protected CoachService $coachService
    ) {}

    public function index()
    {
        return $this->coachService->listCoaches();
    }

    public function store(StoreCoachRequest $request)
    {
        return $this->coachService->createCoach($request->validated());
    }

    public function show(Coach $coach)
    {
        return $this->coachService->getCoach($coach);
    }

    public function update(UpdateCoachRequest $request, Coach $coach)
    {
        return $this->coachService->updateCoach($coach, $request->validated());
    }

    public function destroy(Coach $coach)
    {
        return $this->coachService->deleteCoach($coach);
    }

    public function listArchers(Coach $coach)
    {
        return $this->coachService->listArchers($coach);
    }
}
