<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlanningStoreRequest as StoreRequest;
use App\Services\PlanningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlanningController extends Controller
{
    private $service;

    public function __construct(PlanningService $planning)
    {
        $this->service = $planning;
    }

    public function index(): View
    {
        return view('planning.index', [
            'plannings' => $this->service->getAllPlannings(),
        ]);
    }

    public function create(): View
    {
        return view('planning.create', [
            'form_is_unlock' => true, // unlockForm()
            'form_open_date' => getOpenDate(), 
            'form_close_date' => getCloseDate(),
        ]);
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $this->service->store(collect($request->validated()));
        return redirect()->route('planning.index');
    }

    public function show($slug): View
    {
        return view('planning.show', [
            'plannings' => $this->service->getSinglePlanning($slug),
        ]);
    }
}
