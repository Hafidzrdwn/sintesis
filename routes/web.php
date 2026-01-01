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

Route::post('/institutions', [App\Http\Controllers\InstitutionController::class, 'store'])
    ->middleware(['auth', 'verified'])->name('institutions.store');

/*
|--------------------------------------------------------------------------
| Intern Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'active', 'role:intern'])->prefix('intern')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('InternDashboard');
    })->name('intern.dashboard');

    Route::get('/attendance', [App\Http\Controllers\Intern\PresenceController::class, 'index'])
        ->name('intern.attendance');

    // Tasks
    Route::get('/tasks', [App\Http\Controllers\Intern\TaskController::class, 'index'])
        ->name('intern.tasks');
    Route::get('/tasks/{task}', [App\Http\Controllers\Intern\TaskController::class, 'show'])
        ->name('intern.tasks.show');
    Route::post('/tasks/{task}/start', [App\Http\Controllers\Intern\TaskController::class, 'start'])
        ->name('intern.tasks.start');
    Route::post('/tasks/{task}/submit', [App\Http\Controllers\Intern\TaskController::class, 'submitForReview'])
        ->name('intern.tasks.submit');

    // Logbook
    Route::get('/logbook', [App\Http\Controllers\Intern\LogbookController::class, 'index'])
        ->name('intern.logbook');
    Route::post('/logbook', [App\Http\Controllers\Intern\LogbookController::class, 'store'])
        ->name('intern.logbook.store');
    Route::put('/logbook/{logbook}', [App\Http\Controllers\Intern\LogbookController::class, 'update'])
        ->name('intern.logbook.update');
    Route::delete('/logbook/{logbook}', [App\Http\Controllers\Intern\LogbookController::class, 'destroy'])
        ->name('intern.logbook.destroy');
    Route::post('/logbook/{logbook}/submit', [App\Http\Controllers\Intern\LogbookController::class, 'submit'])
        ->name('intern.logbook.submit');

    Route::get('/analytics', [App\Http\Controllers\Intern\AnalyticsController::class, 'index'])
        ->name('intern.analytics');

    // Presence/Attendance API
    Route::post('/presence/check-in', [App\Http\Controllers\Intern\PresenceController::class, 'checkIn'])
        ->name('intern.presence.checkIn');
    Route::post('/presence/check-out', [App\Http\Controllers\Intern\PresenceController::class, 'checkOut'])
        ->name('intern.presence.checkOut');
    Route::get('/presence/today', [App\Http\Controllers\Intern\PresenceController::class, 'today'])
        ->name('intern.presence.today');
    Route::post('/presence/leave', [App\Http\Controllers\Intern\PresenceController::class, 'requestLeave'])
        ->name('intern.presence.requestLeave');
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
    Route::get('/dashboard', [App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // Recruitment routes
    Route::get('/recruitment', [App\Http\Controllers\Admin\RecruitmentController::class, 'index'])->name('admin.recruitment');
    Route::get('/recruitment/{applicant}', [App\Http\Controllers\Admin\RecruitmentController::class, 'show'])->name('admin.recruitment.show');
    Route::patch('/recruitment/{applicant}/status', [App\Http\Controllers\Admin\RecruitmentController::class, 'updateStatus'])->name('admin.recruitment.update-status');

    // User Management routes
    Route::get('/users', [App\Http\Controllers\Admin\UserManagementController::class, 'index'])->name('admin.users');
    Route::post('/users', [App\Http\Controllers\Admin\UserManagementController::class, 'store'])->name('admin.users.store');
    Route::get('/users/{user}', [App\Http\Controllers\Admin\UserManagementController::class, 'show'])->name('admin.users.show');
    Route::put('/users/{user}', [App\Http\Controllers\Admin\UserManagementController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [App\Http\Controllers\Admin\UserManagementController::class, 'destroy'])->name('admin.users.destroy');

    // Internship Management routes
    Route::get('/internships', [App\Http\Controllers\Admin\InternshipController::class, 'index'])->name('admin.internships');
    Route::patch('/internships/{internship}/status', [App\Http\Controllers\Admin\InternshipController::class, 'updateStatus'])->name('admin.internships.update-status');
    Route::patch('/internships/{internship}/mentor', [App\Http\Controllers\Admin\InternshipController::class, 'updateMentor'])->name('admin.internships.update-mentor');

    // Job Management routes
    Route::get('/jobs', [App\Http\Controllers\Admin\JobManagementController::class, 'index'])->name('admin.jobs');
    Route::post('/jobs', [App\Http\Controllers\Admin\JobManagementController::class, 'store'])->name('admin.jobs.store');
    Route::put('/jobs/{job}', [App\Http\Controllers\Admin\JobManagementController::class, 'update'])->name('admin.jobs.update');
    Route::patch('/jobs/{job}/status', [App\Http\Controllers\Admin\JobManagementController::class, 'updateStatus'])->name('admin.jobs.update-status');
    Route::delete('/jobs/{job}', [App\Http\Controllers\Admin\JobManagementController::class, 'destroy'])->name('admin.jobs.destroy');

    // Global Monitoring routes
    Route::get('/monitoring', [App\Http\Controllers\Admin\MonitoringController::class, 'index'])->name('admin.monitoring');

    // Audit Log routes
    Route::get('/audit', [App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('admin.audit');
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

require __DIR__ . '/auth.php';
