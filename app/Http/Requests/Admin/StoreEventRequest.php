<?php

namespace App\Http\Requests\Admin;

use App\Services\PlanService;
use App\Services\TenantContext;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'location' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'status' => ['required', Rule::in(['draft', 'published', 'canceled'])],
            'is_public' => [ 'boolean'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'main_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $tenant = app(TenantContext::class)->get();

            if (! $tenant) {
                return;
            }

            $planService = app(PlanService::class);

            if (! $planService->canCreateEvent($tenant)) {
                $limit = $planService->eventLimitPerMonth($tenant);
                $plan = $planService->planLabel($tenant);
                $limitLabel = $limit ? $limit.' evento(s)/mes' : 'eventos ilimitados';

                $validator->errors()->add(
                    'event',
                    "Limite do plano {$plan} atingido ({$limitLabel})."
                );
            }
        });
    }
}
