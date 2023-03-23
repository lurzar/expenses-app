<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanningController extends Controller
{
    public function index(): View
    {
        $allowed_date = 23; //later get from db
        $formIsUnlock = checkUnlockForm($allowed_date);
        $currentMonthYear = getCurrentMonthYear();

        return view('planning.index', compact('formIsUnlock', 'allowed_date', 'currentMonthYear'));
    }
}
