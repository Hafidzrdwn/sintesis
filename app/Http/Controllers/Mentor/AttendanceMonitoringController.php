<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Internship;
use App\Models\Presence;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class AttendanceMonitoringController extends Controller
{
  public function index(Request $request)
  {
    $mentor = $request->user();

    $month = $request->get('month', Carbon::now()->month);
    $year = $request->get('year', Carbon::now()->year);
    $search = $request->get('search', '');
    $status = $request->get('status', 'all');

    $menteeIds = Internship::where('mentor_id', $mentor->id)
      ->where('status', 'active')
      ->pluck('intern_id');

    $mentees = User::whereIn('id', $menteeIds)
      ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
      ->get(['id', 'name', 'email', 'avatar']);

    // Get attendance data for the month
    $startDate = Carbon::create($year, $month, 1)->startOfMonth();
    $endDate = Carbon::create($year, $month, 1)->endOfMonth();

    $attendances = Presence::whereIn('user_id', $menteeIds)
      ->whereBetween('date', [$startDate, $endDate])
      ->when($status !== 'all', fn($q) => $q->where('status', $status))
      ->with('user:id,name,avatar')
      ->orderBy('date', 'desc')
      ->get()
      ->map(fn($p) => [
        'id' => $p->id,
        'user_id' => $p->user_id,
        'user_name' => $p->user->name,
        'user_avatar' => $p->user->avatar,
        'date' => Carbon::parse($p->date)->format('Y-m-d'),
        'date_formatted' => Carbon::parse($p->date)->translatedFormat('l, d M'),
        'check_in_time' => $p->check_in_time ? Carbon::parse($p->check_in_time)->format('H:i') : null,
        'check_out_time' => $p->check_out_time ? Carbon::parse($p->check_out_time)->format('H:i') : null,
        'status' => $p->status,
        'attendance_mode' => $p->attendance_mode,
        'notes' => $p->notes,
        'working_hours' => $p->getWorkingHours(),
        'check_in_photo' => $p->check_in_photo ? '/storage/' . $p->check_in_photo : null,
        'check_out_photo' => $p->check_out_photo ? '/storage/' . $p->check_out_photo : null,
        'check_in_latitude' => $p->check_in_latitude,
        'check_in_longitude' => $p->check_in_longitude,
        'check_in_distance' => $p->check_in_distance_meters,
      ]);

    // Stats per mentee
    $menteeStats = [];
    foreach ($mentees as $mentee) {
      $userPresences = Presence::where('user_id', $mentee->id)
        ->whereBetween('date', [$startDate, $endDate])
        ->get();

      $menteeStats[$mentee->id] = [
        'id' => $mentee->id,
        'name' => $mentee->name,
        'email' => $mentee->email,
        'avatar' => $mentee->avatar,
        'present' => $userPresences->whereIn('status', ['present', 'late'])->count(),
        'late' => $userPresences->where('status', 'late')->count(),
        'leave' => $userPresences->whereIn('status', ['leave', 'sick', 'permit'])->count(),
        'absent' => $this->countWorkDays($startDate, Carbon::now()->min($endDate)) - $userPresences->count(),
        'total_hours' => round($userPresences->sum(fn($p) => $p->getWorkingHours()), 1),
      ];
    }

    // Overall stats
    $overallStats = [
      'total_mentees' => $mentees->count(),
      'total_present' => $attendances->whereIn('status', ['present', 'late'])->count(),
      'total_late' => $attendances->where('status', 'late')->count(),
      'total_leave' => $attendances->whereIn('status', ['leave', 'sick', 'permit'])->count(),
    ];

    return Inertia::render('Mentor/AttendanceMonitoring', [
      'attendances' => $attendances,
      'menteeStats' => array_values($menteeStats),
      'overallStats' => $overallStats,
      'filters' => [
        'month' => (int) $month,
        'year' => (int) $year,
        'search' => $search,
        'status' => $status,
      ],
    ]);
  }

  private function countWorkDays(Carbon $start, Carbon $end): int
  {
    $count = 0;
    $current = $start->copy();
    while ($current <= $end) {
      if ($current->isWeekday()) $count++;
      $current->addDay();
    }
    return $count;
  }
}
