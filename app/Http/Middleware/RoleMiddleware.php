<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforces role-based access control across endpoints for Admins, Lecturers, and Students.
 * See SDD 5.1 Membership and On-boarding Module ("handles role-based access control
 * across all endpoints for Admins, Lecturers, and Students").
 *
 * Usage in routes: ->middleware('role:Administrator') or ->middleware('role:Administrator,Lecturer')
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $hasRole = collect($roles)->contains(fn ($role) => $user->hasRole($role));

        if (! $hasRole) {
            // SDD 5.1: "the Laravel middleware blocks the request, returns a strict
            // access-denied error payload"
            return response()->json(['message' => 'Access denied. Insufficient role privileges.'], 403);
        }

        return $next($request);
    }
}
