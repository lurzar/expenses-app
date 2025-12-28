<?php

namespace App\Modules\Language\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Change the application locale language.
     */
    public function index(?string $language = null): RedirectResponse
    {
        App::setLocale($language);
        Session::put('locale', $language);
  
        return redirect()->back();
    }
}
