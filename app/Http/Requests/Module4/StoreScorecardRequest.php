<?php

namespace App\Http\Requests\Module4;

use Illuminate\Foundation\Http\FormRequest;

class StoreScorecardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'training_session_id' => ['required', 'exists:training_sessions,id'],
            'archer_id'           => ['required', 'exists:archers,id'],
            'scoring_template_id' => ['required', 'exists:scoring_templates,id'],
        ];
    }
}
