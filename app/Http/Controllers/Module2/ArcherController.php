<?php

namespace App\Http\Controllers\Module2;

use App\Http\Controllers\Controller;
use App\Models\Archers\Archer;
use App\Models\Coaches\Coach;
use App\Http\Requests\Module2\StoreArcherRequest;
use App\Http\Requests\Module2\UpdateArcherRequest;
use App\Services\Module2\ArcherService;

class ArcherController extends Controller
{
    public function __construct(
        protected ArcherService $archerService
    ) {}

    public function index()
    {
        return $this->archerService->listArchers();
    }

    public function store(StoreArcherRequest $request)
    {
        return $this->archerService->createArcher($request->validated());
    }

    public function show(Archer $archer)
    {
        return $this->archerService->getArcher($archer);
    }

    public function update(UpdateArcherRequest $request, Archer $archer)
    {
        return $this->archerService->updateArcher($archer, $request->validated());
    }

    public function destroy(Archer $archer)
    {
        return $this->archerService->deleteArcher($archer);
    }

    public function assignCoach(Archer $archer, Coach $coach)
    {
        return $this->archerService->assignCoach($archer, $coach);
    }

    public function removeCoach(Archer $archer, Coach $coach)
    {
        return $this->archerService->removeCoach($archer, $coach);
    }

    public function listCoaches(Archer $archer)
    {
        return $this->archerService->listCoaches($archer);
    }
}
