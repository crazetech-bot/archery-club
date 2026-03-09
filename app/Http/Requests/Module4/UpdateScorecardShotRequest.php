<?php

namespace App\Http\Requests\Module4;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScorecardShotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'score'   => ['sometimes', 'integer', 'min:0', 'max:11'],
            'is_x'    => ['sometimes', 'boolean'],
            'is_miss' => ['sometimes', 'boolean'],
        ];
    }
}
