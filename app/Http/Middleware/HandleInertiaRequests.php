<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $internPosition = null;

        if ($user && $user->role === 'intern' && $user->hasActiveInternship()) {
            $activeInternship = $user->currentInternship()
                ->with('job:id,title')
                ->first();

            if ($activeInternship) {
                $internPosition = $activeInternship->custom_position
                    ?? $activeInternship->job?->title
                    ?? null;
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'intern_position' => $internPosition,
            ],
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error' => fn() => $request->session()->get('error'),
                'warning' => fn() => $request->session()->get('warning'),
                'info' => fn() => $request->session()->get('info'),
            ],
        ];
    }
}
