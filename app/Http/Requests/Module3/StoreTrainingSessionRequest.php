<?php

namespace App\Http\Requests\Module3;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrainingSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'club_id'     => ['required', 'exists:clubs,id'],
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_time'  => ['required', 'date'],
            'end_time'    => ['nullable', 'date', 'after_or_equal:start_time'],
            'status'      => ['nullable', 'in:scheduled,in_progress,completed,cancelled'],
        ];
    }
}
