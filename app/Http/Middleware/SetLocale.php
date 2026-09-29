<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('lang')) {
            $lang = strtolower((string) $request->get('lang'));
            if (in_array($lang, ['en', 'sw'])) {
                Session::put('locale', $lang);
                cookie()->queue('locale', $lang, 60 * 24 * 365);
            }
        }

        $defaultLocale = setting('default_locale', config('app.locale', 'sw'));
        $locale = Session::get('locale', $request->cookie('locale', $defaultLocale));

        if (! in_array($locale, ['sw', 'en'])) {
            $locale = 'sw';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
