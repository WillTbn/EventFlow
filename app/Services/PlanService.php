<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Tenant;
use Illuminate\Support\Carbon;

class PlanService
{
    public const PLAN_FREE = 'free';
    public const PLAN_PLUS = 'plus';
    public const PLAN_ENTERPRISE = 'enterprise';

    public const FREE_GUESTS_LIMIT = 100;

    /**
     * Normalize plan names.
     */
    public function normalizePlan(?string $plan): string
    {
        $plan = strtolower($plan ?? self::PLAN_FREE);

        return in_array($plan, [self::PLAN_FREE, self::PLAN_PLUS, self::PLAN_ENTERPRISE], true)
            ? $plan
            : self::PLAN_FREE;
    }

    /**
     * Human-friendly plan label.
     */
    public function planLabel(Tenant $tenant): string
    {
        return match ($this->normalizePlan($tenant->plan)) {
            self::PLAN_PLUS => 'Plus',
            self::PLAN_ENTERPRISE => 'Enterprise',
            default => 'Free',
        };
    }

    /**
     * Limits for a given plan (null = unlimited).
     *
     * @return array<string, int|null>
     */
    public function limitsFor(Tenant $tenant): array
    {
        return match ($this->normalizePlan($tenant->plan)) {
            self::PLAN_PLUS => [
                'events_per_month' => 10,
                'moderators' => 50,
                'admins' => 10,
                'guests' => null,
            ],
            self::PLAN_ENTERPRISE => [
                'events_per_month' => null,
                'moderators' => null,
                'admins' => null,
                'guests' => null,
            ],
            default => [
                'events_per_month' => 1,
                'moderators' => 5,
                'admins' => 1,
                'guests' => self::FREE_GUESTS_LIMIT,
            ],
        };
    }

    /**
     * Features for a given plan.
     *
     * @return array<string, bool>
     */
    public function featuresFor(Tenant $tenant): array
    {
        return match ($this->normalizePlan($tenant->plan)) {
            self::PLAN_PLUS => [
                'ads' => false,
                'tasks' => true,
                'whatsapp' => true,
            ],
            self::PLAN_ENTERPRISE => [
                'ads' => false,
                'tasks' => true,
                'whatsapp' => true,
            ],
            default => [
                'ads' => true,
                'tasks' => true,
                'whatsapp' => false,
            ],
        };
    }

    public function eventLimitPerMonth(Tenant $tenant): ?int
    {
        return $this->limitsFor($tenant)['events_per_month'];
    }

    public function eventsCreatedThisMonth(Tenant $tenant): int
    {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        return Event::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->whereBetween('created_at', [$start, $end])
            ->count();
    }

    public function canCreateEvent(Tenant $tenant): bool
    {
        $limit = $this->eventLimitPerMonth($tenant);

        if ($limit === null) {
            return true;
        }

        return $this->eventsCreatedThisMonth($tenant) < $limit;
    }

    public function remainingEventsThisMonth(Tenant $tenant): ?int
    {
        $limit = $this->eventLimitPerMonth($tenant);

        if ($limit === null) {
            return null;
        }

        return max(0, $limit - $this->eventsCreatedThisMonth($tenant));
    }
}
