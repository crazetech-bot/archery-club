<?php

namespace App\Http\Requests\Module3;

use Illuminate\Foundation\Http\FormRequest;

class StoreScoringTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'club_id' => ['required', 'exists:clubs,id'],
            'name'    => ['required', 'string', 'max:255'],
            'type'    => ['required', 'in:end_based,shot_based'],
            'config'  => ['required', 'array'],
        ];
    }
}
