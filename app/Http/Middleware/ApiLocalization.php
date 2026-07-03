<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sets the application locale for stateless API requests from the
 * Accept-Language header (e.g. "ar-SA,ar;q=0.9" -> "ar"), so validation
 * messages and translated strings are returned in the mobile user's language.
 * The web app keeps using the session-based Localization middleware.
 */
class ApiLocalization
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Accept-Language');

        if (is_string($header) && $header !== '') {
            $primary = trim(explode(',', $header)[0]);        // first language range
            $locale = strtolower(substr($primary, 0, 2));     // primary subtag only

            if (in_array($locale, config('app.supported_locales', ['en', 'ar']), true)) {
                app()->setLocale($locale);
            }
        }

        return $next($request);
    }
}
