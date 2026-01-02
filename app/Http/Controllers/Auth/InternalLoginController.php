<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\InternalLoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class InternalLoginController extends Controller
{
    /**
     * Display the internal login view
     */
    public function create(): Response
    {
        return Inertia::render('Auth/InternalLogin');
    }

    /**
     * Handle internal login request
     */
    public function store(InternalLoginRequest $request): RedirectResponse
    {
        $identity = $request->input('identity');
        $password = $request->input('password');

        $user = User::where('email', $identity)
            ->orWhere('username', $identity)
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'identity' => 'Username atau email tidak ditemukan.',
            ]);
        }

        if (!in_array($user->role, ['admin', 'mentor'])) {
            throw ValidationException::withMessages([
                'identity' => 'Akun ini tidak memiliki akses ke portal internal.',
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'identity' => 'Akun Anda tidak aktif. Hubungi administrator.',
            ]);
        }

        if (!Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'Password salah.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended($this->getRedirectUrl($user));
    }

    /**
     * Get redirect URL based on user role
     */
    private function getRedirectUrl(User $user): string
    {
        return match ($user->role) {
            'admin' => '/admin/dashboard',
            'mentor' => '/mentor/dashboard',
            default => '/dashboard',
        };
    }

    /**
     * Destroy internal session / logout
     */
    public function destroy(): RedirectResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('auth.internal.login');
    }
}
