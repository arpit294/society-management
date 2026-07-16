<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedLocales = ['en', 'hi', 'gu'];
        $locale = session()->get('app_locale', $request->cookie('app_locale', config('app.locale', 'en')));

        if (in_array($locale, $allowedLocales)) {
            App::setLocale($locale);
            if (!session()->has('app_locale')) {
                session()->put('app_locale', $locale);
            }
        } else {
            App::setLocale(config('app.locale', 'en'));
        }

        return $next($request);
    }
}
