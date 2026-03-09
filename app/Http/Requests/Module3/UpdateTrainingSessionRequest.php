<?php

namespace App\Http\Requests\Module3;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTrainingSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_time'  => ['sometimes', 'date'],
            'end_time'    => ['nullable', 'date', 'after_or_equal:start_time'],
            'status'      => ['sometimes', 'in:scheduled,in_progress,completed,cancelled'],
        ];
    }
}
