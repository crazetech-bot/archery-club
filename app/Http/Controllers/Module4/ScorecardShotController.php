<?php

namespace App\Http\Controllers\Module4;

use App\Http\Controllers\Controller;
use App\Http\Requests\Module4\StoreScorecardShotRequest;
use App\Http\Requests\Module4\UpdateScorecardShotRequest;
use App\Models\Scoring\Scorecard;
use App\Models\Scoring\ScorecardShot;
use App\Services\Module4\ScorecardShotService;

class ScorecardShotController extends Controller
{
    public function __construct(
        protected ScorecardShotService $service
    ) {}

    public function store(StoreScorecardShotRequest $request, Scorecard $scorecard)
    {
        return $this->service->createShot($scorecard, $request->validated());
    }

    public function update(UpdateScorecardShotRequest $request, Scorecard $scorecard, ScorecardShot $shot)
    {
        return $this->service->updateShot($scorecard, $shot, $request->validated());
    }

    public function destroy(Scorecard $scorecard, ScorecardShot $shot)
    {
        return $this->service->deleteShot($scorecard, $shot);
    }
}
