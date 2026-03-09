<?php

namespace App\Http\Controllers\Module3;

use App\Http\Controllers\Controller;
use App\Models\Training\TrainingSession;
use App\Models\Archers\Archer;
use App\Http\Requests\Module3\UpdateAttendanceRequest;
use App\Services\Module3\TrainingSessionArcherService;

class TrainingSessionArcherController extends Controller
{
    public function __construct(
        protected TrainingSessionArcherService $service
    ) {}

    public function addArcher(TrainingSession $session, Archer $archer)
    {
        return $this->service->addArcher($session, $archer);
    }

    public function removeArcher(TrainingSession $session, Archer $archer)
    {
        return $this->service->removeArcher($session, $archer);
    }

    public function updateAttendance(UpdateAttendanceRequest $request, TrainingSession $session, Archer $archer)
    {
        return $this->service->updateAttendance($session, $archer, $request->validated()['attendance_status']);
    }

    public function listArchers(TrainingSession $session)
    {
        return $this->service->listArchers($session);
    }
}
