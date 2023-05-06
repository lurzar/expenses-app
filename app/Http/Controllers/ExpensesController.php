<?php

namespace App\Http\Controllers;

use App\Services\PlanningService;
use Illuminate\View\View;
class ExpensesController extends Controller
{
    private $service;

    public function __construct(PlanningService $planning)
    {
        $this->service = $planning;
    }

    public function index(): View
    {
        return view('expenses.index', [
            'plannings' => $this->service->getAllListPlanning(),
        ]);
    }

    public function show($planningSlug)
    {
        // $plannings = Planning::where('slug', $planningSlug)->get();
        // return view('expenses.index', [
        //     'plannings' => $plannings,
        //     'slug' => $planningSlug,
        // ]);
    }
}
