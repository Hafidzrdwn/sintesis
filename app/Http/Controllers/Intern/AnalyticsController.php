<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use App\Models\Internship;
use App\Models\Logbook;
use App\Models\Presence;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
  public function index(Request $request)
  {
    $user = $request->user();
    $internship = $user->currentInternship();

    $startOfMonth = Carbon::now()->startOfMonth();
    $endOfMonth = Carbon::now()->endOfMonth();

    $monthPresences = Presence::where('user_id', $user->id)
      ->whereBetween('date', [$startOfMonth, $endOfMonth])
      ->get();

    $totalWorkDays = $this->countWorkDays($startOfMonth, Carbon::now());
    $presentDays = $monthPresences->whereIn('status', ['present', 'late'])->count();
    $lateDays = $monthPresences->where('status', 'late')->count();
    $leaveDays = $monthPresences->whereIn('status', ['leave', 'sick', 'permit'])->count();

    $attendanceRate = $totalWorkDays > 0
      ? round(($presentDays / $totalWorkDays) * 100, 1)
      : 0;

    $onTimeRate = $presentDays > 0
      ? round((($presentDays - $lateDays) / $presentDays) * 100, 1)
      : 0;

    $avgWorkingHours = $monthPresences
      ->filter(fn($p) => $p->check_in_time && $p->check_out_time)
      ->avg(fn($p) => $p->getWorkingHours()) ?? 0;

    $tasks = Task::where('intern_id', $user->id)->get();
    $completedTasks = $tasks->where('status', 'completed')->count();
    $inProgressTasks = $tasks->where('status', 'in_progress')->count();
    $reviewTasks = $tasks->where('status', 'review')->count();
    $pendingTasks = $tasks->where('status', 'pending')->count();
    $totalTasks = $tasks->count();

    $avgRating = $tasks->where('rating', '>', 0)->avg('rating') ?? 0;

    $startOfWeek = Carbon::now()->startOfWeek();
    $endOfWeek = Carbon::now()->endOfWeek();

    $weeklyLogbooks = Logbook::where('user_id', $user->id)
      ->whereBetween('date', [$startOfWeek, $endOfWeek])
      ->selectRaw('DAYOFWEEK(date) as day_num, COUNT(*) as count')
      ->groupBy('day_num')
      ->pluck('count', 'day_num')
      ->toArray();

    $weeklyActivity = [];
    for ($i = 2; $i <= 6; $i++) {
      $weeklyActivity[] = $weeklyLogbooks[$i] ?? 0;
    }

    $internshipProgress = null;
    if ($internship) {
      $startDate = Carbon::parse($internship->start_date);
      $endDate = Carbon::parse($internship->end_date);
      $now = Carbon::now();

      $totalDays = $startDate->diffInDays($endDate);
      $elapsedDays = $startDate->diffInDays($now);
      $remainingDays = $now->diffInDays($endDate, false);

      $progress = $totalDays > 0
        ? min(100, round(($elapsedDays / $totalDays) * 100, 1))
        : 0;

      $internshipProgress = [
        'start_date' => $internship->start_date->translatedFormat('d M Y'),
        'end_date' => $internship->end_date->translatedFormat('d M Y'),
        'progress' => (int) $progress,
        'elapsed_days' => max(0, (int) $elapsedDays),
        'remaining_days' => max(0, (int) $remainingDays),
        'total_days' => (int) $totalDays,
      ];
    }

    $prevMonthStart = Carbon::now()->subMonth()->startOfMonth();
    $prevMonthEnd = Carbon::now()->subMonth()->endOfMonth();
    $prevPresences = Presence::where('user_id', $user->id)
      ->whereBetween('date', [$prevMonthStart, $prevMonthEnd])
      ->get();

    $prevWorkDays = $this->countWorkDays($prevMonthStart, $prevMonthEnd);
    $prevPresentDays = $prevPresences->whereIn('status', ['present', 'late'])->count();
    $prevAttendanceRate = $prevWorkDays > 0 ? round(($prevPresentDays / $prevWorkDays) * 100, 1) : 0;

    $attendanceTrend = $attendanceRate - $prevAttendanceRate;

    return Inertia::render('Intern/Analitik', [
      'stats' => [
        'attendance_rate' => $attendanceRate,
        'attendance_trend' => $attendanceTrend,
        'on_time_rate' => $onTimeRate,
        'late_days' => $lateDays,
        'leave_days' => $leaveDays,
        'avg_working_hours' => round($avgWorkingHours, 1),
        'total_tasks' => $totalTasks,
        'completed_tasks' => $completedTasks,
        'in_progress_tasks' => $inProgressTasks,
        'review_tasks' => $reviewTasks,
        'pending_tasks' => $pendingTasks,
        'avg_rating' => round($avgRating, 1),
      ],
      'weeklyActivity' => $weeklyActivity,
      'taskDistribution' => [
        'completed' => $completedTasks,
        'in_progress' => $inProgressTasks,
        'review' => $reviewTasks,
        'pending' => $pendingTasks,
      ],
      'internshipProgress' => $internshipProgress,
    ]);
  }

  private function countWorkDays(Carbon $start, Carbon $end): int
  {
    $count = 0;
    $current = $start->copy();
    while ($current <= $end) {
      if ($current->isWeekday()) {
        $count++;
      }
      $current->addDay();
    }
    return $count;
  }
}
