<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClubRequest extends FormRequest
{
    /**
     * Only club admins may update club settings.
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
            'name'     => ['required', 'string', 'max:255'],
            'timezone' => ['required', 'string', Rule::in(\DateTimeZone::listIdentifiers())],
            'country'  => ['nullable', 'string', 'max:100'],
            'city'     => ['nullable', 'string', 'max:100'],
        ];
    }
}
