<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Only club admins may create members.
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
            'email'        => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone'        => ['nullable', 'string', 'max:30'],
            'primary_role' => ['required', 'string', Rule::in(['club_admin', 'coach', 'archer'])],
        ];
    }
}
