<?php

namespace App\Http\Requests\Live;

use Illuminate\Foundation\Http\FormRequest;

class StartSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Fine-grained auth (archer owns this training session) is enforced
        // in the controller. Here we just require authentication.
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'arrows_per_end' => ['required', 'integer', 'in:3,6'],
        ];
    }

    public function messages(): array
    {
        return [
            'arrows_per_end.in' => 'Arrows per end must be 3 or 6.',
        ];
    }
}
