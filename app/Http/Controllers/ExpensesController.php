<?php

namespace App\Http\Controllers;

use App\Services\PlanningService as ExpenseService;
use Illuminate\View\View;
class ExpensesController extends Controller
{
    private $service;

    public function __construct(ExpenseService $planning)
    {
        $this->service = $planning;
    }

    public function index(): View
    {
        return view('expenses.index', [
            'expenses' => $this->service->getAllExpenses(),
        ]);
    }

    public function show($slug)
    {
        return view('expenses.show', [
            'expenses' => $this->service->getSinglePlanning($slug),
        ]);
    }
}
