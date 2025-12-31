<?php

namespace App\Modules\Planning\Services;

use App\Modules\Planning\Models\Planning;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class PlanningService
{
    public function __construct(
        private Planning $model
    ) {}

    /**
     * Store a new planning record.
     */
    public function store(Collection $planning): Planning
    {
        $this->handleRequest($planning);

        $this->model->user_id = Auth::user()->user_id;
        $this->model->month = $planning->get('month');
        $this->model->year = $planning->get('year');
        $this->model->salary = $planning->get('salary');
        $this->model->sections = $planning->get('sections');
        $this->model->totals = $planning->get('totals');
        $this->model->save();

        return $this->model;
    }

    /**
     * Get all plannings for the authenticated user.
     */
    public function getAllPlannings(): Collection
    {
        return $this->model
            ->where('user_id', Auth::user()->user_id)
            ->latest()
            ->get();
    }

    /**
     * Get all expenses for the authenticated user.
     */
    public function getAllExpenses(): Collection
    {
        return $this->model
            ->where('user_id', Auth::user()->user_id)
            ->latest()
            ->get();
    }

    /**
     * Get a single planning by slug.
     */
    public function getSinglePlanning(string $slug): Planning
    {
        return $this->model->firstWhere('slug', $slug);
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
