<?php

namespace App\Http\Controllers\Admin;

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
    $month = $request->get('month', Carbon::now()->month);
    $year = $request->get('year', Carbon::now()->year);
    $search = $request->get('search', '');
    $status = $request->get('status', 'all');
    $mentorId = $request->get('mentor_id', '');

    $startDate = Carbon::create($year, $month, 1)->startOfMonth();
    $endDate = Carbon::create($year, $month, 1)->endOfMonth();

    $internQuery = Internship::where('status', 'active')
      ->with(['intern:id,name,email,avatar', 'mentor:id,name']);

    if ($mentorId) {
      $internQuery->where('mentor_id', $mentorId);
    }

    $internships = $internQuery->get();
    $internIds = $internships->pluck('intern_id');

    $attendanceQuery = Presence::whereIn('user_id', $internIds)
      ->whereBetween('date', [$startDate, $endDate])
      ->with('user:id,name,avatar')
      ->orderBy('date', 'desc');

    if ($status !== 'all') {
      $attendanceQuery->where('status', $status);
    }

    if ($search) {
      $attendanceQuery->whereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"));
    }

    $attendances = $attendanceQuery->paginate(20)->withQueryString();

    $formattedAttendances = $attendances->through(fn($p) => [
      'id' => $p->id,
      'user_id' => $p->user_id,
      'user_name' => $p->user->name,
      'user_avatar' => $p->user->avatar,
      'date' => Carbon::parse($p->date)->format('Y-m-d'),
      'date_formatted' => Carbon::parse($p->date)->translatedFormat('l, d M Y'),
      'check_in_time' => $p->check_in_time ? Carbon::parse($p->check_in_time)->format('H:i') : null,
      'check_out_time' => $p->check_out_time ? Carbon::parse($p->check_out_time)->format('H:i') : null,
      'status' => $p->status,
      'attendance_mode' => $p->attendance_mode,
      'notes' => $p->notes,
      'working_hours' => round($p->getWorkingHours() ?? 0, 1),
      'check_in_photo' => $p->check_in_photo ? '/storage/' . $p->check_in_photo : null,
      'check_out_photo' => $p->check_out_photo ? '/storage/' . $p->check_out_photo : null,
      'check_in_latitude' => $p->check_in_latitude,
      'check_in_longitude' => $p->check_in_longitude,
      'check_in_distance' => $p->check_in_distance_meters,
      'check_out_latitude' => $p->check_out_latitude,
      'check_out_longitude' => $p->check_out_longitude,
      'check_out_distance' => $p->check_out_distance_meters,
    ]);

    $internStats = [];
    foreach ($internships as $internship) {
      $userPresences = Presence::where('user_id', $internship->intern_id)
        ->whereBetween('date', [$startDate, $endDate])
        ->get();

      $workDays = $this->countWorkDays($startDate, Carbon::now()->min($endDate));

      $internStats[] = [
        'id' => $internship->intern_id,
        'name' => $internship->intern?->name ?? '-',
        'email' => $internship->intern?->email ?? '-',
        'avatar' => $internship->intern?->avatar,
        'mentor_name' => $internship->mentor?->name ?? '-',
        'present' => $userPresences->whereIn('status', ['present', 'late'])->count(),
        'late' => $userPresences->where('status', 'late')->count(),
        'leave' => $userPresences->whereIn('status', ['leave', 'sick', 'permit'])->count(),
        'absent' => max(0, $workDays - $userPresences->count()),
        'attendance_rate' => $workDays > 0
          ? round(($userPresences->whereIn('status', ['present', 'late'])->count() / $workDays) * 100, 1)
          : 0,
        'total_hours' => round($userPresences->sum(fn($p) => $p->getWorkingHours()), 1),
      ];
    }

    usort($internStats, fn($a, $b) => $b['attendance_rate'] <=> $a['attendance_rate']);

    $allPresences = Presence::whereIn('user_id', $internIds)
      ->whereBetween('date', [$startDate, $endDate])
      ->get();

    $overallStats = [
      'total_interns' => $internships->count(),
      'total_present' => $allPresences->whereIn('status', ['present', 'late'])->count(),
      'total_late' => $allPresences->where('status', 'late')->count(),
      'total_leave' => $allPresences->whereIn('status', ['leave', 'sick', 'permit'])->count(),
      'avg_working_hours' => round($allPresences->avg(fn($p) => $p->getWorkingHours()) ?? 0, 1),
    ];

    $mentors = User::role('mentor')->where('status', 'active')->get(['id', 'name']);

    return Inertia::render('Admin/AttendanceMonitoring', [
      'attendances' => $formattedAttendances,
      'internStats' => $internStats,
      'overallStats' => $overallStats,
      'mentors' => $mentors,
      'filters' => [
        'month' => (int) $month,
        'year' => (int) $year,
        'search' => $search,
        'status' => $status,
        'mentor_id' => $mentorId,
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
