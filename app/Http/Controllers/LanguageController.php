<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Changing application locale language.
     *
     * @return \Illuminate\Http\RedirectResponse
    */
    public function index($language = null): RedirectResponse
    {
        App::setLocale($language);
        Session::put('locale', $language);
  
        return redirect()->back();
    }
}
