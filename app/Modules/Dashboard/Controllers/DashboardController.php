<?php

namespace App\Modules\Dashboard\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Planning\Data\PlanningData;
use App\Modules\Planning\Models\Planning;
use App\Modules\Planning\Services\PlanningService;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly PlanningService $planningService,
    ) {}

    /**
     * Display the dashboard.
     */
    public function index(): Response
    {
        Gate::authorize('viewAny', Planning::class);

        return Inertia::render('Dashboard/Index', [
            'plannings' => PlanningData::collection($this->planningService->getAllPlannings()),
        ]);
    }
}
