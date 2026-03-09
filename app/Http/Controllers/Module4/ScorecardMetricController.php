<?php

namespace App\Http\Controllers\Module4;

use App\Http\Controllers\Controller;
use App\Http\Requests\Module4\RecalculateMetricsRequest;
use App\Models\Scoring\Scorecard;
use App\Services\Module4\ScorecardMetricService;

class ScorecardMetricController extends Controller
{
    public function __construct(
        protected ScorecardMetricService $service
    ) {}

    public function recalculate(RecalculateMetricsRequest $request, Scorecard $scorecard)
    {
        return $this->service->recalculateMetrics($scorecard);
    }
}
