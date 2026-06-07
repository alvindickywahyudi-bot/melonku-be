<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        // ❌ belum login
        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        // ❌ role tidak sesuai
        if (!$user->hasRoles($roles)) {
            return response()->json([
                'message' => 'Forbidden - role tidak sesuai'
            ], 403);
        }

        return $next($request);
    }
}