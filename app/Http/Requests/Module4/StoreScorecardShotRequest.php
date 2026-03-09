<?php

namespace App\Http\Requests\Module4;

use Illuminate\Foundation\Http\FormRequest;

class StoreScorecardShotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'scorecard_end_id' => ['nullable', 'exists:scorecard_ends,id'],
            'shot_number'      => ['required', 'integer', 'min:1'],
            'score'            => ['required', 'integer', 'min:0', 'max:11'],
            'is_x'             => ['sometimes', 'boolean'],
            'is_miss'          => ['sometimes', 'boolean'],
        ];
    }
}
