<?php

namespace App\Http\Controllers\Module3;

use App\Http\Controllers\Controller;
use App\Models\Training\ScoringTemplate;
use App\Http\Requests\Module3\StoreScoringTemplateRequest;
use App\Http\Requests\Module3\UpdateScoringTemplateRequest;
use App\Services\Module3\ScoringTemplateService;

class ScoringTemplateController extends Controller
{
    public function __construct(
        protected ScoringTemplateService $service
    ) {}

    public function index()
    {
        return $this->service->listTemplates();
    }

    public function store(StoreScoringTemplateRequest $request)
    {
        return $this->service->createTemplate($request->validated());
    }

    public function show(ScoringTemplate $template)
    {
        return $this->service->getTemplate($template);
    }

    public function update(UpdateScoringTemplateRequest $request, ScoringTemplate $template)
    {
        return $this->service->updateTemplate($template, $request->validated());
    }

    public function destroy(ScoringTemplate $template)
    {
        return $this->service->deleteTemplate($template);
    }
}
