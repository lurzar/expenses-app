<?php

namespace App\Modules\Planning\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Planning\Data\PlanningData;
use App\Modules\Planning\Models\Planning;
use App\Modules\Planning\Requests\PlanningStoreRequest as StoreRequest;
use App\Modules\Planning\Services\PlanningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class PlanningController extends Controller
{
    public function __construct(
        private readonly PlanningService $service,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Planning/Index', [
            'plannings' => PlanningData::collection($this->service->getAllPlannings()),
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
        Gate::authorize('view', $planning);

        return Inertia::render('Planning/Show', [
            'planning' => PlanningData::fromModel($planning),
        ]);
    }

    public function destroy(Planning $planning): RedirectResponse
    {
        Gate::authorize('delete', $planning);

        $name = $planning->name;

        try {
            $cacheInvalidated = $this->service->delete($planning);
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'The plan could not be deleted. Try again.');
        }

        if (! $cacheInvalidated) {
            return redirect()->route('planning.index')->with(
                'warning',
                "{$name} plan deleted. Planning lists may take up to five minutes to refresh.",
            );
        }

        return redirect()->route('planning.index')->with('success', "{$name} plan deleted.");
    }
}
