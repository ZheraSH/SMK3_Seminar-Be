<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseHelper;
use App\Models\User;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return ResponseHelper::error('Unauthenticated', 401);
        }

        $user = Auth::user();

        if (!$user instanceof User || !$user->hasAnyRole($roles)) {
            return ResponseHelper::error('Insufficient permissions', 403);
        }

        return $next($request);
    }
}
