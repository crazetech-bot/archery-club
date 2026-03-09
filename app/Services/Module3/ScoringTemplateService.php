<?php

namespace App\Services\Module3;

use App\Models\Training\ScoringTemplate;
use Illuminate\Support\Facades\DB;

class ScoringTemplateService
{
    public function listTemplates()
    {
        return ScoringTemplate::orderBy('name')->paginate(20);
    }

    public function createTemplate(array $data): ScoringTemplate
    {
        return DB::transaction(function () use ($data) {
            return ScoringTemplate::create([
                'club_id' => $data['club_id'],
                'name'    => $data['name'],
                'type'    => $data['type'],
                'config'  => $data['config'],
            ]);
        });
    }

    public function getTemplate(ScoringTemplate $template): ScoringTemplate
    {
        return $template;
    }

    public function updateTemplate(ScoringTemplate $template, array $data): ScoringTemplate
    {
        return DB::transaction(function () use ($template, $data) {
            $template->update([
                'name'   => $data['name'] ?? $template->name,
                'type'   => $data['type'] ?? $template->type,
                'config' => $data['config'] ?? $template->config,
            ]);

            return $template;
        });
    }

    public function deleteTemplate(ScoringTemplate $template): bool
    {
        return DB::transaction(function () use ($template) {
            return $template->delete();
        });
    }
}
