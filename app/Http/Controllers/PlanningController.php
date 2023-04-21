<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Http\Requests\PlanningStoreRequest as StoreRequest;

class PlanningController extends Controller
{
    public function index(): View
    {
        return view('planning.index', [
            'form_is_unlock' => true, // unlockForm()
            'form_open_date' => getOpenDate(), 
            'form_close_date' => getCloseDate(),
        ]);
    }

    public function store(StoreRequest $request)
    {
        dd($request->validated());
    }
}
