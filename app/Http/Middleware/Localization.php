<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Localization
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale');

        if (is_string($locale) && in_array($locale, config('app.supported_locales', ['en', 'ar']), true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
