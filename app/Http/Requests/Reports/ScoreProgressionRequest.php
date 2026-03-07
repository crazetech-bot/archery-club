<?php

namespace App\Http\Requests\Reports;

use Illuminate\Foundation\Http\FormRequest;

class ScoreProgressionRequest extends FormRequest
{
    /**
     * All roles can view score progression, but only for archers they have
     * access to. Authorisation is enforced in the controller.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // The archer whose progression to report on (required)
            'archer_id' => ['required', 'integer', 'exists:archers,id'],

            // Optional date window — defaults applied in the service
            'date_from' => ['nullable', 'date', 'before_or_equal:date_to'],
            'date_to'   => ['nullable', 'date', 'after_or_equal:date_from'],

            // Filter by round type (e.g. "WA 18m", "Portsmouth")
            'round_type' => ['nullable', 'string', 'max:100'],

            // Filter by distance in metres
            'distance' => ['nullable', 'integer', 'min:1', 'max:1000'],

            // Group data points by time period
            'group_by' => ['nullable', 'string', 'in:week,month'],
        ];
    }

    public function messages(): array
    {
        return [
            'archer_id.required' => 'An archer must be selected.',
            'archer_id.exists'   => 'The selected archer does not exist in this club.',
            'date_from.before_or_equal' => 'Start date must be before or equal to the end date.',
            'date_to.after_or_equal'    => 'End date must be after or equal to the start date.',
            'group_by.in' => 'Group by must be either "week" or "month".',
        ];
    }

    /**
     * Prepare validated data with sensible defaults before the service uses it.
     */
    public function filters(): array
    {
        return [
            'archer_id'  => (int) $this->validated('archer_id'),
            'date_from'  => $this->validated('date_from') ?? now()->subMonths(3)->toDateString(),
            'date_to'    => $this->validated('date_to')   ?? now()->toDateString(),
            'round_type' => $this->validated('round_type'),
            'distance'   => $this->validated('distance') ? (int) $this->validated('distance') : null,
            'group_by'   => $this->validated('group_by'),
        ];
    }
}
