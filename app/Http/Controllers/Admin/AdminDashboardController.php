<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\AuditLog;
use App\Models\Internship;
use App\Models\User;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
  public function index()
  {
    return Inertia::render('Admin/Dashboard', [
      'stats' => $this->getActionStats(),
      'recentActivity' => $this->getRecentActivity(),
      'endingSoon' => $this->getEndingSoon(),
      'quickStats' => $this->getQuickStats(),
    ]);
  }

  private function getActionStats()
  {
    $pendingReview = Applicant::where('status', 'pending')->count();
    $todayApplications = Applicant::whereDate('created_at', Carbon::today())->count();
    $endingThisWeek = Internship::whereIn('status', ['active', 'extended'])
      ->whereBetween('end_date', [Carbon::today(), Carbon::today()->addDays(7)])
      ->count();
    $mentorOverload = $this->getMentorOverloadCount();

    return [
      [
        'key' => 'pending_review',
        'label' => 'Pending Review',
        'value' => $pendingReview,
        'icon' => 'clipboard-list',
        'color' => 'amber',
        'link' => route('admin.recruitment', ['status' => 'pending']),
        'description' => 'Pelamar menunggu review',
      ],
      [
        'key' => 'today_applications',
        'label' => 'Pelamar Hari Ini',
        'value' => $todayApplications,
        'icon' => 'user-plus',
        'color' => 'blue',
        'link' => route('admin.recruitment'),
        'description' => 'Lamaran masuk hari ini',
      ],
      [
        'key' => 'ending_this_week',
        'label' => 'Selesai Minggu Ini',
        'value' => $endingThisWeek,
        'icon' => 'calendar-clock',
        'color' => 'purple',
        'link' => route('admin.internships'),
        'description' => 'Anak magang berakhir dalam 7 hari',
      ],
      [
        'key' => 'mentor_overload',
        'label' => 'Mentor Overload',
        'value' => $mentorOverload,
        'icon' => 'alert-triangle',
        'color' => $mentorOverload > 0 ? 'red' : 'emerald',
        'link' => route('admin.users', ['role' => 'mentor']),
        'description' => $mentorOverload > 0 ? 'Rasio > 1:5, perlu rebalance' : 'Semua mentor OK',
      ],
    ];
  }

  private function getMentorOverloadCount()
  {
    $mentors = User::where('role', 'mentor')
      ->where('status', 'active')
      ->withCount(['mentees' => function ($q) {
        $q->whereIn('status', ['active', 'extended']);
      }])
      ->get();

    return $mentors->filter(fn($m) => $m->mentees_count > 5)->count();
  }

  private function getRecentActivity()
  {
    return AuditLog::with('user:id,name,email')
      ->orderBy('created_at', 'desc')
      ->take(8)
      ->get()
      ->map(fn($log) => [
        'id' => $log->id,
        'user' => $log->user ? $log->user->name : 'System',
        'action' => $log->getActionLabel(),
        'model' => $log->getModelTypeName(),
        'model_id' => $log->auditable_id,
        'time' => $log->created_at->diffForHumans(),
        'action_type' => $log->action,
      ]);
  }

  private function getEndingSoon()
  {
    return Internship::with(['intern:id,name,email', 'mentor:id,name'])
      ->whereIn('status', ['active', 'extended'])
      ->whereBetween('end_date', [Carbon::today(), Carbon::today()->addDays(14)])
      ->orderBy('end_date')
      ->take(5)
      ->get()
      ->map(fn($i) => [
        'id' => $i->id,
        'intern_name' => $i->intern?->name ?? 'Unknown',
        'intern_email' => $i->intern?->email,
        'mentor_name' => $i->mentor?->name ?? 'No Mentor',
        'position' => $i->custom_position ?? $i->job?->title ?? '-',
        'end_date' => $i->end_date->format('d M Y'),
        'days_left' => $i->end_date->diffInDays(Carbon::today()),
      ]);
  }

  private function getQuickStats()
  {
    return [
      'total_applicants' => Applicant::count(),
      'active_interns' => Internship::whereIn('status', ['active', 'extended'])->count(),
      'total_mentors' => User::where('role', 'mentor')->where('status', 'active')->count(),
      'this_month_applications' => Applicant::whereMonth('created_at', Carbon::now()->month)
        ->whereYear('created_at', Carbon::now()->year)
        ->count(),
    ];
  }
}
