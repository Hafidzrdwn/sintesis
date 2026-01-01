<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     * 
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Allowed roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!in_array($user->role, $roles)) {
            return $this->redirectToUserDashboard($user);
        }
        
        if ($user->role === 'intern' && in_array('intern', $roles)) {
            if (!$user->hasActiveInternship()) {
                return redirect('/dashboard');
            }
        }

        return $next($request);
    }

    /**
     * Redirect user to their appropriate dashboard based on role
     */
    private function redirectToUserDashboard($user)
    {        
        return match ($user->role) {
            'admin' => redirect('/admin/dashboard'),
            'mentor' => redirect('/mentor/dashboard'),
            'intern' => (
                $user->getApplicationStatus() === 'accepted' 
                || $user->hasActiveInternship()) 
                ? redirect('/intern/dashboard')
                : redirect('/dashboard'),
            default => redirect('/dashboard'),
        };
    }
}
