<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [JobController::class, 'landing']);

Route::get('/lowongan/{slug}', [JobController::class, 'show'])->name('job.detail');

/*
|--------------------------------------------------------------------------
| Google OAuth Routes
|--------------------------------------------------------------------------
*/

Route::get('/auth/google/login', [GoogleController::class, 'redirectLogin'])->name('auth.google.login');
Route::get('/auth/google/register', [GoogleController::class, 'redirectRegister'])->name('auth.google.register');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');

/*
|--------------------------------------------------------------------------
| Internal Login (for Staff)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/internal/login', [App\Http\Controllers\Auth\InternalLoginController::class, 'create'])
        ->name('auth.internal.login');
    Route::post('/internal/login', [App\Http\Controllers\Auth\InternalLoginController::class, 'store'])
        ->name('auth.internal.login.store');
});

Route::post('/internal/logout', [App\Http\Controllers\Auth\InternalLoginController::class, 'destroy'])
    ->middleware(['auth', 'verified', 'active'])
    ->name('auth.internal.logout');

/*
|--------------------------------------------------------------------------
| Candidate/Applicant Routes
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/internship/apply/{slug?}', [JobController::class, 'apply'])
    ->middleware(['auth', 'verified'])->name('internship.apply');

Route::post('/internship/apply', [App\Http\Controllers\ApplicantController::class, 'store'])
    ->middleware(['auth', 'verified'])->name('internship.store');

/*
|--------------------------------------------------------------------------
| Intern Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'active', 'role:intern'])->prefix('intern')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('InternDashboard');
    })->name('intern.dashboard');

    Route::get('/attendance', function () {
        return Inertia::render('Intern/Absensi');
    })->name('intern.attendance');

    Route::get('/tasks', function () {
        return Inertia::render('Intern/TugasSaya');
    })->name('intern.tasks');

    Route::get('/logbook', function () {
        return Inertia::render('Intern/BukuLog');
    })->name('intern.logbook');

    Route::get('/analytics', function () {
        return Inertia::render('Intern/Analitik');
    })->name('intern.analytics');
});

/*
|--------------------------------------------------------------------------
| Mentor Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'active', 'role:mentor'])->prefix('mentor')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Mentor/Dashboard');
    })->name('mentor.dashboard');

    Route::get('/tasks', function () {
        return Inertia::render('Mentor/TaskManagement');
    })->name('mentor.tasks');

    Route::get('/logbook', function () {
        return Inertia::render('Mentor/LogbookReview');
    })->name('mentor.logbook');

    Route::get('/evaluation', function () {
        return Inertia::render('Mentor/Evaluation');
    })->name('mentor.evaluation');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'active', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Admin/Dashboard');
    })->name('admin.dashboard');

    Route::get('/recruitment', function () {
        return Inertia::render('Admin/Recruitment');
    })->name('admin.recruitment');

    Route::get('/users', function () {
        return Inertia::render('Admin/UserManagement');
    })->name('admin.users');

    Route::get('/monitoring', function () {
        return Inertia::render('Admin/GlobalMonitoring');
    })->name('admin.monitoring');

    Route::get('/audit', function () {
        return Inertia::render('Admin/AuditLog');
    })->name('admin.audit');
});

/*
|--------------------------------------------------------------------------
| Profile Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
