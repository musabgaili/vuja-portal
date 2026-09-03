<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** JSON 403 for mobile API routes that are internal-staff only. */
class EnsureApiInternal
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isInternal()) {
            return $next($request);
        }

        return response()->json(['message' => 'Forbidden.'], 403);
    }
}
