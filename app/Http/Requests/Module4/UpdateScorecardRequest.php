<?php

namespace App\Http\Requests\Module4;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScorecardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'in:draft,submitted,locked'],
        ];
    }
}
