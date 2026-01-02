<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\Internship;
use App\Models\Logbook;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
  public function index(Request $request)
  {
    $mentor = $request->user();

    $mentees = Internship::where('mentor_id', $mentor->id)
      ->whereIn('status', ['active', 'extended', 'completed'])
      ->with(['intern:id,name,email,avatar', 'job:id,title', 'evaluation'])
      ->orderBy('end_date', 'asc')
      ->get()
      ->map(fn($internship) => [
        'id' => $internship->id,
        'intern_id' => $internship->intern_id,
        'intern_name' => $internship->intern?->name,
        'intern_email' => $internship->intern?->email,
        'intern_avatar' => $internship->intern?->avatar,
        'position' => $internship->custom_position ?? $internship->job?->title ?? 'Intern',
        'department' => $internship->department,
        'start_date' => $internship->start_date->format('d M Y'),
        'end_date' => $internship->end_date->format('d M Y'),
        'days_remaining' => max(0, now()->diffInDays($internship->end_date, false)),
        'progress' => $internship->getProgressPercentage(),
        'status' => $internship->status,
        'is_evaluated' => $internship->evaluation?->status === 'submitted',
        'can_evaluate' => $internship->getProgressPercentage() >= 90,
      ]);

    $taskStats = [
      'total' => Task::where('mentor_id', $mentor->id)->count(),
      'pending' => Task::where('mentor_id', $mentor->id)->where('status', 'pending')->count(),
      'in_progress' => Task::where('mentor_id', $mentor->id)->where('status', 'in_progress')->count(),
      'review' => Task::where('mentor_id', $mentor->id)->where('status', 'review')->count(),
      'completed' => Task::where('mentor_id', $mentor->id)->where('status', 'completed')->count(),
      'overdue' => Task::where('mentor_id', $mentor->id)
        ->whereIn('status', ['pending', 'in_progress'])
        ->where('due_date', '<', now())
        ->count(),
    ];

    $logbookStats = [
      'pending_review' => Logbook::whereHas('internship', fn($q) => $q->where('mentor_id', $mentor->id))
        ->where('status', 'pending')
        ->count(),
      'approved_today' => Logbook::whereHas('internship', fn($q) => $q->where('mentor_id', $mentor->id))
        ->where('status', 'approved')
        ->whereDate('updated_at', today())
        ->count(),
    ];

    $recentTasksForReview = Task::where('mentor_id', $mentor->id)
      ->where('status', 'review')
      ->with('intern:id,name,avatar')
      ->orderBy('updated_at', 'desc')
      ->take(5)
      ->get()
      ->map(fn($task) => [
        'id' => $task->id,
        'title' => $task->title,
        'intern_name' => $task->intern?->name,
        'intern_avatar' => $task->intern?->avatar,
        'submitted_at' => $task->updated_at->diffForHumans(),
        'priority' => $task->priority,
      ]);

    $recentLogbooks = Logbook::whereHas('internship', fn($q) => $q->where('mentor_id', $mentor->id))
      ->where('status', 'pending')
      ->with(['internship.intern:id,name,avatar'])
      ->orderBy('date', 'desc')
      ->take(5)
      ->get()
      ->map(fn($logbook) => [
        'id' => $logbook->id,
        'date' => $logbook->date->format('d M Y'),
        'intern_name' => $logbook->internship?->intern?->name,
        'intern_avatar' => $logbook->internship?->intern?->avatar,
        'activities_count' => count($logbook->activities ?? []),
      ]);

    $evaluationStats = [
      'completed' => Evaluation::where('mentor_id', $mentor->id)->where('status', 'submitted')->count(),
      'pending' => $mentees->filter(fn($m) => $m['can_evaluate'] && !$m['is_evaluated'])->count(),
    ];

    return Inertia::render('Mentor/Dashboard', [
      'mentees' => $mentees,
      'taskStats' => $taskStats,
      'logbookStats' => $logbookStats,
      'evaluationStats' => $evaluationStats,
      'recentTasksForReview' => $recentTasksForReview,
      'recentLogbooks' => $recentLogbooks,
      'greeting' => $this->getGreeting(),
    ]);
  }

  private function getGreeting(): string
  {
    $hour = now()->hour;
    if ($hour < 12) return 'Selamat Pagi';
    if ($hour < 15) return 'Selamat Siang';
    if ($hour < 18) return 'Selamat Sore';
    if ($hour < 24) return 'Selamat Malam';
    return 'Selamat Pagi';
  }
}
