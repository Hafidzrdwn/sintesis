<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('LandingPage');
});

Route::get('/lowongan/detail', function () {
    return Inertia::render('JobDetailPage');
})->name('job.detail');

Route::get('/dashboard', function () {
    return Inertia::render('CandidateDashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    // Intern Routes
    Route::get('/intern/dashboard', function () {
        return Inertia::render('InternDashboard');
    })->name('intern.dashboard');

    Route::get('/intern/attendance', function () {
        return Inertia::render('Intern/Absensi');
    })->name('intern.attendance');

    Route::get('/intern/tasks', function () {
        return Inertia::render('Intern/TugasSaya');
    })->name('intern.tasks');

    Route::get('/intern/logbook', function () {
        return Inertia::render('Intern/BukuLog');
    })->name('intern.logbook');

    Route::get('/intern/analytics', function () {
        return Inertia::render('Intern/Analitik');
    })->name('intern.analytics');

    Route::get('/intern/settings', function () {
        return Inertia::render('Intern/Pengaturan');
    })->name('intern.settings');
});

// Internal Login
Route::get('/internal/login', function () {
    return Inertia::render('Auth/InternalLogin');
})->name('auth.internal.login');

// Admin Routes 
Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () { return Inertia::render('Admin/Dashboard'); })->name('admin.dashboard');
    Route::get('/recruitment', function () { return Inertia::render('Admin/Recruitment'); })->name('admin.recruitment');
    Route::get('/users', function () { return Inertia::render('Admin/UserManagement'); })->name('admin.users');
    Route::get('/monitoring', function () { return Inertia::render('Admin/GlobalMonitoring'); })->name('admin.monitoring');
    Route::get('/audit', function () { return Inertia::render('Admin/AuditLog'); })->name('admin.audit');
});

// Mentor Routes 
Route::middleware(['auth', 'verified'])->prefix('mentor')->group(function () {
    Route::get('/dashboard', function () { return Inertia::render('Mentor/Dashboard'); })->name('mentor.dashboard');
    Route::get('/tasks', function () { return Inertia::render('Mentor/TaskManagement'); })->name('mentor.tasks');
    Route::get('/logbook', function () { return Inertia::render('Mentor/LogbookReview'); })->name('mentor.logbook');
    Route::get('/evaluation', function () { return Inertia::render('Mentor/Evaluation'); })->name('mentor.evaluation');
});

Route::get('/internship/apply', function () {
    return Inertia::render('InternshipApplication');
})->middleware(['auth', 'verified'])->name('internship.apply');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
