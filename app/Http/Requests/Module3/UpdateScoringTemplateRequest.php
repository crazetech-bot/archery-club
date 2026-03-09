<?php

namespace App\Http\Requests\Module3;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScoringTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'   => ['sometimes', 'string', 'max:255'],
            'type'   => ['sometimes', 'in:end_based,shot_based'],
            'config' => ['sometimes', 'array'],
        ];
    }
}
