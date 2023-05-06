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
}
