<?php

namespace App\Http\Requests\Module3;

use Illuminate\Foundation\Http\FormRequest;

class AddCoachToSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'coach_id' => ['required', 'exists:coaches,id'],
        ];
    }
}
