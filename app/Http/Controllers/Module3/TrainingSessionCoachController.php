<?php

namespace App\Http\Controllers\Module3;

use App\Http\Controllers\Controller;
use App\Models\Training\TrainingSession;
use App\Models\Coaches\Coach;
use App\Services\Module3\TrainingSessionCoachService;

class TrainingSessionCoachController extends Controller
{
    public function __construct(
        protected TrainingSessionCoachService $service
    ) {}

    public function addCoach(TrainingSession $session, Coach $coach)
    {
        return $this->service->addCoach($session, $coach);
    }

    public function removeCoach(TrainingSession $session, Coach $coach)
    {
        return $this->service->removeCoach($session, $coach);
    }

    public function listCoaches(TrainingSession $session)
    {
        return $this->service->listCoaches($session);
    }
}
