<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRsvpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $termsUrl = config('app.terms_url');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone_whatsapp' => [
                'nullable',
                'string',
                'max:40',
                Rule::requiredIf(function (): bool {
                    $preference = $this->input('communication_preference');

                    return in_array($preference, ['whatsapp', 'sms'], true);
                }),
            ],
            'communication_preference' => ['required', Rule::in(['email', 'whatsapp', 'sms'])],
            'notifications_scope' => ['required', Rule::in(['event_only', 'workspace', 'platform'])],
            'accept_terms' => $termsUrl ? ['required', 'accepted'] : ['nullable', 'boolean'],
            'company' => ['nullable', 'string', 'max:0'],
        ];
    }
}
