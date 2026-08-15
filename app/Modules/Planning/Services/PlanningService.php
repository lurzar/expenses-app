<?php

namespace App\Modules\Planning\Services;

use App\Modules\ActivityLog\ActivityEvent;
use App\Modules\ActivityLog\Services\ActivityRecorder;
use App\Modules\Planning\Models\Planning;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PlanningService
{
    public function __construct(
        private Planning $model,
        private ActivityRecorder $activityRecorder,
    ) {}

    /**
     * Store a new planning record.
     */
    public function store(Collection $planning): Planning
    {
        return DB::transaction(function () use ($planning): Planning {
            $this->handleRequest($planning);

            $this->model->user_id = Auth::id();
            $this->model->month = $planning->get('month');
            $this->model->year = $planning->get('year');
            $this->model->salary = $planning->get('salary');
            $this->model->sections = $planning->get('sections');
            $this->model->totals = $planning->get('totals');
            $this->model->save();

            $this->activityRecorder->record(
                ActivityEvent::PlanningCreated,
                Auth::user()?->user_id,
                'planning',
                $this->model->planning_id,
            );

            return $this->model;
        });
    }

    /**
     * Soft-delete a planning record and its activity atomically.
     */
    public function delete(Planning $planning): void
    {
        DB::transaction(function () use ($planning): void {
            $planning->delete();

            $this->activityRecorder->record(
                ActivityEvent::PlanningDeleted,
                Auth::user()?->user_id,
                'planning',
                $planning->planning_id,
            );
        });
    }

    /**
     * Get all plannings for the authenticated user.
     */
    public function getAllPlannings(): Collection
    {
        return $this->model
            ->where('user_id', Auth::id())
            ->latest()
            ->get();
    }

    /**
     * Get all expenses for the authenticated user.
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
        return $this->model->firstWhere('planning_id', $planningId);
    }

    /**
     * Handle and transform the request data.
     */
    private function handleRequest(Collection $planning): Collection
    {
        $sections = [
            'savings' => $planning->get('savings_values') ?? [],
            'commitments' => $planning->get('commitments_values') ?? [],
            'others' => $planning->get('others_values') ?? [],
        ];

        $planning->put('sections', $sections);
        $planning->forget('savings_values');
        $planning->forget('commitments_values');
        $planning->forget('others_values');
        $planning->forget('saving_rate');

        return $planning;
    }
}
