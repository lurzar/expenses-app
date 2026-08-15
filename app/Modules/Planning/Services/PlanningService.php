<?php

namespace App\Modules\Planning\Services;

use App\Modules\ActivityLog\ActivityEvent;
use App\Modules\ActivityLog\Services\ActivityRecorder;
use App\Modules\Planning\Models\Planning;
use App\Modules\Planning\Support\PlanningCalculator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class PlanningService
{
    public function __construct(
        private Planning $model,
        private ActivityRecorder $activityRecorder,
        private PlanningCache $cache,
        private PlanningCalculator $calculator,
    ) {}

    /**
     * Store a new planning record.
     *
     * @param  Collection<string, mixed>  $planning
     */
    public function store(Collection $planning): Planning
    {
        $userId = Auth::id();
        abort_unless(is_int($userId), 401);

        $storedPlanning = DB::transaction(function () use ($planning, $userId): Planning {
            $calculation = $this->calculator->calculate(
                income: (string) $planning->get('salary'),
                savingRate: (string) $planning->get('saving_rate'),
                sections: $this->sectionsFromRequest($planning),
            );

            $storedPlanning = $this->model->newInstance();
            $storedPlanning->user_id = $userId;
            $storedPlanning->month = (int) $planning->get('month');
            $storedPlanning->year = (int) $planning->get('year');
            $storedPlanning->salary = $calculation->income;
            $storedPlanning->saving_rate = $calculation->savingRate;
            $storedPlanning->setAttribute('sections', $calculation->sections);
            $storedPlanning->setAttribute('totals', $calculation->totals);
            $storedPlanning->save();

            $this->activityRecorder->record(
                ActivityEvent::PlanningCreated,
                Auth::user()?->user_id,
                'planning',
                $storedPlanning->planning_id,
            );

            return $storedPlanning;
        });

        $this->forgetIndexAfterCommit((int) $storedPlanning->user_id);

        return $storedPlanning;
    }

    /**
     * Soft-delete a planning record and its activity atomically.
     */
    public function delete(Planning $planning): bool
    {
        $userId = (int) $planning->user_id;

        DB::transaction(function () use ($planning): void {
            $planning->delete();

            $this->activityRecorder->record(
                ActivityEvent::PlanningDeleted,
                Auth::user()?->user_id,
                'planning',
                $planning->planning_id,
            );
        });

        return $this->forgetIndexAfterCommit($userId);
    }

    /**
     * Get all plannings for the authenticated user.
     *
     * @return Collection<int, Planning>
     */
    public function getAllPlannings(): Collection
    {
        $userId = Auth::id();

        if (! is_int($userId)) {
            return collect();
        }

        return $this->cache->rememberIndex(
            $userId,
            fn (): Collection => $this->model
                ->where('user_id', $userId)
                ->latest()
                ->get(),
        );
    }

    /**
     * Get all expenses for the authenticated user.
     *
     * @return Collection<int, Planning>
     */
    public function getAllExpenses(): Collection
    {
        return $this->model
            ->where('user_id', Auth::id())
            ->latest()
            ->get();
    }

    /**
     * Get a single planning by slug.
     */
    public function getSinglePlanning(string $planningId): Planning
    {
        return $this->model->where('planning_id', $planningId)->firstOrFail();
    }

    /**
     * @param  Collection<string, mixed>  $planning
     * @return array{savings: mixed, commitments: mixed, others: mixed}
     */
    private function sectionsFromRequest(Collection $planning): array
    {
        return [
            'savings' => $planning->get('savings_values') ?? [],
            'commitments' => $planning->get('commitments_values') ?? [],
            'others' => $planning->get('others_values') ?? [],
        ];
    }

    /**
     * Keep a committed database mutation authoritative when cache invalidation
     * is temporarily unavailable. The entry expires after five minutes.
     */
    private function forgetIndexAfterCommit(int $userId): bool
    {
        try {
            $this->cache->forgetIndex($userId);
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }

        return true;
    }
}
