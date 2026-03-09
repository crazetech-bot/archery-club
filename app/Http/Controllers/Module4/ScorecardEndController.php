<?php

namespace App\Http\Controllers\Module4;

use App\Http\Controllers\Controller;
use App\Http\Requests\Module4\StoreScorecardEndRequest;
use App\Http\Requests\Module4\UpdateScorecardEndRequest;
use App\Models\Scoring\Scorecard;
use App\Models\Scoring\ScorecardEnd;
use App\Services\Module4\ScorecardEndService;

class ScorecardEndController extends Controller
{
    public function __construct(
        protected ScorecardEndService $service
    ) {}

    public function store(StoreScorecardEndRequest $request, Scorecard $scorecard)
    {
        return $this->service->createEnd($scorecard, $request->validated());
    }

    public function update(UpdateScorecardEndRequest $request, Scorecard $scorecard, ScorecardEnd $end)
    {
        return $this->service->updateEnd($scorecard, $end, $request->validated());
    }

    public function destroy(Scorecard $scorecard, ScorecardEnd $end)
    {
        return $this->service->deleteEnd($scorecard, $end);
    }
}
