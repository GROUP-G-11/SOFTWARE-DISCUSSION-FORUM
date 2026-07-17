<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks blacklisted users from posting/interacting in a given group.
 * See SDD 5.2 Moderation and Inactivity Management Module.
 */
class BlacklistMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $groupRoute = $request->route('group');

        // Extract ID if it's an object model, otherwise fallback to request input value
        $groupId = is_object($groupRoute) 
            ? ($groupRoute->group_id ?? $groupRoute->id) 
            : ($groupRoute ?? $request->input('group_id'));

        if ($user && $groupId && $user->isBlacklistedIn((int) $groupId)) {
            return response()->json([
                'message' => 'Your account is currently suspended from this group.',
            ], 403);
        }

        return $next($request);
    }
}
