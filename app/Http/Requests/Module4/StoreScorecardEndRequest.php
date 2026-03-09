<?php

namespace App\Http\Requests\Module4;

use Illuminate\Foundation\Http\FormRequest;

class StoreScorecardEndRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'end_number' => ['required', 'integer', 'min:1'],
        ];
    }
}
