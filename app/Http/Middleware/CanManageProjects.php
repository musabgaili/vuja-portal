<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allows managers AND global project managers through. Used for direct project
 * creation, which both roles may do (delete + bulk import stay manager-only).
 */
class CanManageProjects
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::user()?->canManageProjects()) {
            return $next($request);
        }

        abort(403, 'Managers and project managers only.');
    }
}
