<?php

namespace App\Http\Requests\Live;

use Illuminate\Foundation\Http\FormRequest;

class SubmitEndRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'arrows'   => ['required', 'array', 'min:1', 'max:12'],
            // Each score must be X, 10–1, or M (miss)
            'arrows.*' => ['required', 'string', 'regex:/^(X|10|[1-9]|M)$/'],
            // end_number must match the next expected end in the session
            'end_number' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'arrows.required'   => 'At least one arrow score is required.',
            'arrows.*.regex'    => 'Each arrow score must be X, 10, 9–1, or M.',
            'end_number.required' => 'End number is required.',
        ];
    }
}
