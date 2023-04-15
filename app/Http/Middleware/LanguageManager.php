<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class LanguageManager
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Session::has('locale') && (! is_null(Session::get('locale')))) {
            App::setLocale(Session::get('locale'));
        } else {
            Session::put('locale', Config::get('app.locale'));
            App::setLocale(Session::get('locale'));
        }

        return $next($request);
    }
}
