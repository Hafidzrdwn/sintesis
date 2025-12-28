<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureUserIsActive
 * 
 * Middleware to ensure the authenticated user has active status.
 * Prevents suspended or inactive users from accessing the application.
 */
class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && !auth()->user()->isActive()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your account has been suspended. Please contact administrator.',
                ], 403);
            }

            return redirect()->route('login')
                ->with('error', 'Your account has been suspended. Please contact administrator.');
        }

        return $next($request);
    }
}
