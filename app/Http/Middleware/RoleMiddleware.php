<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !$user->is_active || !in_array($user->role, $roles, true)) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Access denied.'], 403)
                : redirect('/access-denied');
        }

        return $next($request);
    }
}
