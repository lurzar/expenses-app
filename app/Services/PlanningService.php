<?php

namespace App\Services;

use App\Models\Planning;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class PlanningService
{
    private $model;

    public function __construct(Planning $planning)
    {
        $this->model = $planning;
    }

    public function getAllPlannings(): Paginator
    {
        return $this->model
                    ->select('month', 'year', 'slug', 'salary')
                    ->where('user_id', Auth::id())
                    ->latest()
                    ->paginate(10);
    }

    public function getThisMonthPlanning(): Collection
    {
        return $this->model
                    ->where('user_id', Auth::id())
                    ->thisMonth()
                    ->latest()
                    ->get();
    }

    public function getSinglePlanning($slug): Planning
    {
        return $this->model->firstWhere('slug', $slug);
    }

    public function getAllExpenses(): Paginator
    {
        return $this->model
                    ->select('month', 'year', 'slug', 'sections', 'totals')
                    ->where('user_id', Auth::id())
                    ->paginate(12);
    }

    public function store($planning): Planning
    {
        $this->handleRequest($planning);

        $this->model->user_id = Auth::id();
        $this->model->month = $planning->get('month');
        $this->model->year = $planning->get('year');
        $this->model->salary = $planning->get('salary');
        $this->model->sections = $planning->get('sections');
        $this->model->totals = $planning->get('totals');
        $this->model->save();

        return $this->model;
    }

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
