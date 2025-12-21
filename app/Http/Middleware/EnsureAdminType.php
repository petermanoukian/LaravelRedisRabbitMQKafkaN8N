<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int  $requiredType
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, int $requiredType): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Rule: type 1 can access everything, type 2 only its own level
        if ($user->is_admin === 1) {
            // Super admin → always allowed
            return $next($request);
        }

        if ($user->is_admin === $requiredType) {
            return $next($request);
        }

        return response()->json(['error' => 'Unauthorized'], 403);
    }
}
