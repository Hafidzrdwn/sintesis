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

Route::get('/intern-dashboard', function () {
    return Inertia::render('InternDashboard');
})->middleware(['auth', 'verified'])->name('intern.dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
