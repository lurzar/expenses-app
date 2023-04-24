<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Http\Requests\PlanningStoreRequest as StoreRequest;
use App\Services\PlanningService;

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
            'form_is_unlock' => false, // unlockForm()
            'form_open_date' => getOpenDate(), 
            'form_close_date' => getCloseDate(),
            'plannings' => $this->service->thisMonth(),
        ]);
    }

    public function store(StoreRequest $request)
    {
        $this->service->store(collect($request->validated()));
        return Redirect::route('expenses.index');
    }
}
