<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanningController extends Controller
{
    public function index(): View
    {
        return view('planning.index');
    }
}
