<?php

namespace App\Http\Requests\Module2;

use Illuminate\Foundation\Http\FormRequest;

class StoreCoachRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'             => ['required', 'exists:users,id', 'unique:coaches,user_id'],
            'certification_level' => ['nullable', 'string', 'max:255'],
            'bio'                 => ['nullable', 'string'],
        ];
    }
}
