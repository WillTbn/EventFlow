<?php

namespace App\Http\Requests\Admin;

use App\Services\TenantContext;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $tenant = app(TenantContext::class)->get();
        $roleOptions = $this->user()?->hasTenantRole('admin', $tenant)
            ? ['admin', 'moderator', 'member']
            : ['member'];

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->route('user')),
            ],
            'password' => ['nullable', 'string', Password::default(), 'confirmed'],
            'role' => ['required', Rule::in($roleOptions)],
        ];
    }
}
