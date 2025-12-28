<?php

namespace App\Modules\Dashboard\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard');
    }
}
