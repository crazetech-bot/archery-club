<?php

namespace App\Http\Controllers\Module4;

use App\Http\Controllers\Controller;
use App\Http\Requests\Module4\StoreScorecardRequest;
use App\Http\Requests\Module4\UpdateScorecardRequest;
use App\Http\Requests\Module4\SubmitScorecardRequest;
use App\Http\Requests\Module4\LockScorecardRequest;
use App\Models\Scoring\Scorecard;
use App\Services\Module4\ScorecardService;

class ScorecardController extends Controller
{
    public function __construct(
        protected ScorecardService $service
    ) {}

    public function index()
    {
        return $this->service->listScorecards();
    }

    public function store(StoreScorecardRequest $request)
    {
        return $this->service->createScorecard($request->validated());
    }

    public function show(Scorecard $scorecard)
    {
        return $this->service->getScorecard($scorecard);
    }

    public function update(UpdateScorecardRequest $request, Scorecard $scorecard)
    {
        return $this->service->updateScorecard($scorecard, $request->validated());
    }

    public function destroy(Scorecard $scorecard)
    {
        return $this->service->deleteScorecard($scorecard);
    }

    public function submit(SubmitScorecardRequest $request, Scorecard $scorecard)
    {
        return $this->service->submitScorecard($scorecard);
    }

    public function lock(LockScorecardRequest $request, Scorecard $scorecard)
    {
        return $this->service->lockScorecard($scorecard);
    }
}
