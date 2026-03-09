<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Only club admins may update members.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole('club_admin');
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'phone'        => ['nullable', 'string', 'max:30'],
            'is_active'    => ['required', 'boolean'],
            'primary_role' => ['required', 'string', Rule::in(['club_admin', 'coach', 'archer'])],
        ];
    }
}
