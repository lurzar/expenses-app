<?php

namespace App\Modules\Expenses\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Planning\Data\PlanningData;
use App\Modules\Planning\Models\Planning;
use App\Modules\Planning\Services\PlanningService;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ExpensesController extends Controller
{
    public function __construct(
        private readonly PlanningService $planningService,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Expenses/Index', [
            'plannings' => PlanningData::collection($this->planningService->getAllPlannings()),
        ]);
    }

    public function show(Planning $expense): Response
    {
        Gate::authorize('view', $expense);

        return Inertia::render('Expenses/Show', [
            'planning' => PlanningData::fromModel($expense),
        ]);
    }
}
