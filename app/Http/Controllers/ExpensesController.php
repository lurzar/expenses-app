<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Planning;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redirect;
class ExpensesController extends Controller
{
    public function index(): View
    {
        $plannings = Auth::user()->plannings;
        return view('expenses.index', compact('plannings'));
    }

    public function show($planningSlug)
    {
        $plannings = Planning::where('slug', $planningSlug)->get();
        return view('expenses.index', compact('plannings'));
    }
}
