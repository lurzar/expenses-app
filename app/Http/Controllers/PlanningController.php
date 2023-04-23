<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Http\Requests\PlanningStoreRequest as StoreRequest;
use App\Models\Planning;

class PlanningController extends Controller
{
    protected $model;

    public function __construct()
    {
        $this->model = new Planning;
    }

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
        $request = $this->handleSectionsRequest($request);
        $request['slug'] = $this->generateSlug($request);

        $this->model->user_id = Auth::id();
        $this->model->month = $request->month;
        $this->model->year = $request->year;
        $this->model->salary = $request->salary;
        $this->model->sections = $request->sections;
        $this->model->totals = $request->totals;
        $this->model->save();

        return Redirect::to('/dashboard');
    }

    private function handleSectionsRequest(Request $request)
    {
        $sections = [
            'savings' => $request->savings_values ?? [],
            'commitments' => $request->commitments_values ?? [],
            'others' => $request->others_values ?? [],
        ];

        $request['sections'] = $sections;

        unset($request['savings_values']);
        unset($request['commitments_values']);
        unset($request['others_values']);

        return $request;
    }

    private function generateSlug(Request $request): string
    {
        $slug = 'Planning-'.$request['month'].'-'.$request['year'];

        return $slug;
    }
}
