<?php

namespace App\Http\Requests\Module2;

use Illuminate\Foundation\Http\FormRequest;

class UpdateArcherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bow_type'      => ['required', 'in:recurve,compound,barebow'],
            'dominant_eye'  => ['nullable', 'in:left,right'],
            'date_of_birth' => ['nullable', 'date'],
            'notes'         => ['nullable', 'string'],
        ];
    }
}
