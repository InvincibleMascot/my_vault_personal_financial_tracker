<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserTypeAccess
{
    public function handle(Request $request, Closure $next, string $area): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403);
        }

        if (!$user->canAccessArea($area)) {
            abort(403, 'You do not have access to this page.');
        }

        return $next($request);
    }
}
