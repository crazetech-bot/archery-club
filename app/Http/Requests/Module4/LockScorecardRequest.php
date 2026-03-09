<?php

namespace App\Http\Requests\Module4;

use Illuminate\Foundation\Http\FormRequest;

class LockScorecardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return []; // no payload, action-only
    }
}
