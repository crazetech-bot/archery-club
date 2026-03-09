<?php

namespace App\Http\Requests\Module3;

use Illuminate\Foundation\Http\FormRequest;

class AddArcherToSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'archer_id' => ['required', 'exists:archers,id'],
        ];
    }
}
