<?php

namespace App\Http\Requests\Module2;

use Illuminate\Foundation\Http\FormRequest;

class StoreArcherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Gate/Policy can be added later
    }

    public function rules(): array
    {
        return [
            'user_id'       => ['required', 'exists:users,id', 'unique:archers,user_id'],
            'bow_type'      => ['required', 'in:recurve,compound,barebow'],
            'dominant_eye'  => ['nullable', 'in:left,right'],
            'date_of_birth' => ['nullable', 'date'],
            'notes'         => ['nullable', 'string'],
        ];
    }
}
