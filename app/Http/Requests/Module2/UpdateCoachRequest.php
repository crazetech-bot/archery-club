<?php

namespace App\Http\Requests\Module2;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCoachRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'certification_level' => ['nullable', 'string', 'max:255'],
            'bio'                 => ['nullable', 'string'],
        ];
    }
}
