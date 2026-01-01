<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

/**
 * GoogleController
 * 
 * Handles Google OAuth authentication using Laravel Socialite.
 * 
 * This controller handles TWO different flows:
 * 1. PUBLIC PORTAL (Login.vue/Register.vue): Applicants can register/login via Google
 * 2. INTERNAL PORTAL (InternalLogin.vue): Staff use manual login only (no Google)
 * 
 * SECURITY:
 * - Public: Auto-merge if email exists, create new if not
 * - Logs all authentication attempts for audit purposes
 */
class GoogleController extends Controller
{
    /**
     * Redirect to Google OAuth for LOGIN
     * Used when user clicks "Sign in with Google" on Login.vue
     */
    public function redirectLogin(): RedirectResponse
    {
        // Store intent in session to differentiate login vs register
        session(['google_auth_intent' => 'login']);
        
        return Socialite::driver('google')
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    /**
     * Redirect to Google OAuth for REGISTER
     * Used when user clicks "Sign up with Google" on Register.vue
     */
    public function redirectRegister(): RedirectResponse
    {
        // Store intent in session to differentiate login vs register
        session(['google_auth_intent' => 'register']);
        
        return Socialite::driver('google')
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    /**
     * Handle Google OAuth callback
     * 
     * Logic:
     * 1. If email exists → link Google ID and login (auto-merge)
     * 2. If email doesn't exist and intent=register → create new user
     * 3. If email doesn't exist and intent=login → reject with helpful message
     */
    public function callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Exception $e) {
            Log::error('Google OAuth error: ' . $e->getMessage());
            
            return redirect()->route('login')
                ->with('error', 'Gagal autentikasi dengan Google. Silakan coba lagi.');
        }

        $intent = session('google_auth_intent', 'login');
        session()->forget('google_auth_intent');

        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            return $this->handleExistingUser($user, $googleUser, $request);
        }

        if ($intent === 'register') {
            return $this->handleNewRegistration($googleUser, $request);
        }

        $this->logFailedLogin($googleUser->getEmail(), $request, 'Email not registered');
        
        return redirect()->route('login')
            ->with('error', 'Email belum terdaftar. Silakan daftar terlebih dahulu.');
    }

    private function handleRedirectDashboard($user, $message = null) {
        $route = ($user->hasActiveInternship() && $user->onGoingInternship()) ? 'intern.dashboard' : 'dashboard';
        
        return redirect()
            ->route($route)
            ->with('success', $message);
    }

    /**
     * Handle existing user - Auto-merge Google ID and login
     */
    private function handleExistingUser(User $user, $googleUser, Request $request): RedirectResponse
    {
        if (!$user->isActive()) {
            $this->logFailedLogin($googleUser->getEmail(), $request, 'Account suspended');

            return redirect()->route('login')
                ->with('error', 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.');
        }

        $wasLinked = $user->google_id !== null;
        
        $user->update([
            'google_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar() ?? $user->avatar,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ]);

        Auth::login($user, true);
        $request->session()->regenerate();

        $this->logSuccessfulLogin($user, $request, $wasLinked ? 'google_login' : 'google_linked');

        $message = $wasLinked 
            ? 'Selamat datang kembali, ' . $user->name . '!'
            : 'Akun Google berhasil terhubung. Selamat datang, ' . $user->name . '!';

        return $this->handleRedirectDashboard($user, $message);
    }

    /**
     * Handle new user registration via Google
     */
    private function handleNewRegistration($googleUser, Request $request): RedirectResponse
    {
        try {
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'password' => Hash::make(Str::random(32)), 
                'role' => 'intern',
                'status' => 'active',
                'email_verified_at' => now(), 
            ]);

            Auth::login($user, true);
            $request->session()->regenerate();

            $this->logRegistration($user, $request);

            return $this->handleRedirectDashboard($user, 'Pendaftaran berhasil! Selamat datang di SINTESIS, ' . $user->name . '!');
        } catch (Exception $e) {
            Log::error('Google registration error: ' . $e->getMessage());
            
            return redirect()->route('register')
                ->with('error', 'Gagal mendaftarkan akun. Silakan coba lagi.');
        }
    }

    /**
     * Log successful login attempt for audit
     */
    private function logSuccessfulLogin(User $user, Request $request, string $type = 'google_login'): void
    {
        try {
            AuditLog::create([
                'user_id' => $user->id,
                'action' => AuditLog::ACTION_LOGIN,
                'auditable_type' => User::class,
                'auditable_id' => $user->id,
                'old_values' => null,
                'new_values' => [
                    'login_method' => 'google',
                    'email' => $user->email,
                    'type' => $type,
                ],
                'metadata' => [
                    'login_method' => 'google_oauth',
                    'auth_type' => $type,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to log login audit: ' . $e->getMessage());
        }
    }

    /**
     * Log registration for audit
     */
    private function logRegistration(User $user, Request $request): void
    {
        try {
            AuditLog::create([
                'user_id' => $user->id,
                'action' => AuditLog::ACTION_CREATED,
                'auditable_type' => User::class,
                'auditable_id' => $user->id,
                'old_values' => null,
                'new_values' => [
                    'registration_method' => 'google',
                    'email' => $user->email,
                    'name' => $user->name,
                ],
                'metadata' => [
                    'registration_method' => 'google_oauth',
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to log registration audit: ' . $e->getMessage());
        }
    }

    /**
     * Log failed login attempt for security audit
     */
    private function logFailedLogin(string $email, Request $request, string $reason = 'Email not registered'): void
    {
        try {
            AuditLog::create([
                'user_id' => null,
                'action' => AuditLog::ACTION_LOGIN_FAILED,
                'auditable_type' => User::class,
                'auditable_id' => null,
                'old_values' => null,
                'new_values' => [
                    'attempted_email' => $email,
                    'reason' => $reason,
                ],
                'metadata' => [
                    'login_method' => 'google_oauth',
                    'failure_reason' => $reason,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to log failed login audit: ' . $e->getMessage());
        }
    }
}
