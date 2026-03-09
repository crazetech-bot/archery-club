<?php

namespace App\Http\Requests\Module3;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrainingSessionNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'coach_id'  => ['nullable', 'exists:coaches,id'],
            'archer_id' => ['nullable', 'exists:archers,id'],
            'note'      => ['required', 'string'],
        ];
    }
}
