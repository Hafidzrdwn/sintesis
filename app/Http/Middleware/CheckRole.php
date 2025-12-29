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
            $status = $user->getApplicationStatus();
            if ($status !== 'active_intern') {
                return redirect('/dashboard')->with('error', 'Anda belum memiliki akses ke halaman ini. Silakan tunggu proses seleksi selesai.');
            }
        }

        return $next($request);
    }

    /**
     * Redirect user to their appropriate dashboard based on role
     */
    private function redirectToUserDashboard($user)
    {
        $message = 'Anda tidak memiliki akses ke halaman tersebut.';
        
        return match ($user->role) {
            'admin' => redirect('/admin/dashboard')->with('error', $message),
            'mentor' => redirect('/mentor/dashboard')->with('error', $message),
            'intern' => $user->getApplicationStatus() === 'active_intern' 
                ? redirect('/intern/dashboard')->with('error', $message)
                : redirect('/dashboard')->with('error', $message),
            default => redirect('/dashboard')->with('error', $message),
        };
    }
}
