<?php

namespace App\Modules\Expenses\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Planning\Services\PlanningService;
use Illuminate\View\View;

class ExpensesController extends Controller
{
    public function __construct(
        private PlanningService $service
    ) {}

    public function index(): View
    {
        return view('expenses.index', [
            'expenses' => $this->service->getAllExpenses(),
        ]);
    }

    public function show(string $slug): View
    {
        return view('expenses.show', [
            'expenses' => $this->service->getSinglePlanning($slug),
        ]);
    }
}
