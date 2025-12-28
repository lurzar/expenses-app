<?php

namespace App\Modules\Landing\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class LandingController extends Controller
{
    /**
     * Display the landing page.
     */
    public function index(): View
    {
        return view('welcome');
    }
}
