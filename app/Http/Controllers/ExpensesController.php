<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class ExpensesController extends Controller
{
    public function index(): View
    {

        $plannings = Auth::user()->plannings;
        return view('expenses.index', compact('plannings'));
    }
}
