<?php

namespace App\Modules\Planning\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Planning\Models\Planning;
use App\Modules\Planning\Requests\PlanningStoreRequest as StoreRequest;
use App\Modules\Planning\Services\PlanningService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PlanningController extends Controller
{
    public function __construct(
        private readonly PlanningService $service,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Planning/Index', [
            'plannings' => $this->service->getAllPlannings(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Planning/Create');
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $this->service->store(collect($request->validated()));

        return redirect()->route('planning.index');
    }

    public function show(Planning $planning): Response
    {
        return Inertia::render('Planning/Show', [
            'planning' => $planning,
        ]);
    }

    public function destroy(Planning $planning): RedirectResponse
    {
        $planning->delete();

        return redirect()->route('planning.index');
    }
}
