<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class TaskController extends Controller
{
  public function index(Request $request)
  {
    $user = $request->user();

    $status = $request->get('status', 'all');
    $search = $request->get('search', '');
    $priority = $request->get('priority', '');

    $query = Task::where('intern_id', $user->id)
      ->with(['mentor:id,name'])
      ->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
      ->orderBy('due_date', 'asc');

    if ($status !== 'all') {
      $query->where('status', $status);
    }

    if ($priority) {
      $query->where('priority', $priority);
    }

    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('title', 'like', "%{$search}%")
          ->orWhere('description', 'like', "%{$search}%");
      });
    }

    $tasks = $query->get()->map(function ($task) {
      return [
        'id' => $task->id,
        'title' => $task->title,
        'description' => $task->description,
        'due_date' => $task->due_date->format('Y-m-d'),
        'due_date_formatted' => $task->due_date->translatedFormat('d M Y'),
        'priority' => $task->priority,
        'status' => $task->status,
        'mentor_name' => $task->mentor?->name ?? '-',
        'estimated_hours' => $task->estimated_hours,
        'actual_hours' => $task->actual_hours,
        'feedback' => $task->feedback,
        'rating' => $task->rating,
        'is_overdue' => $task->isOverdue(),
        'days_until_due' => $task->getDaysUntilDue(),
        'started_at' => $task->started_at?->format('d M Y H:i'),
        'completed_at' => $task->completed_at?->format('d M Y H:i'),
      ];
    });

    $stats = [
      'all' => Task::where('intern_id', $user->id)->count(),
      'pending' => Task::where('intern_id', $user->id)->where('status', 'pending')->count(),
      'in_progress' => Task::where('intern_id', $user->id)->where('status', 'in_progress')->count(),
      'review' => Task::where('intern_id', $user->id)->where('status', 'review')->count(),
      'completed' => Task::where('intern_id', $user->id)->where('status', 'completed')->count(),
    ];

    return Inertia::render('Intern/TugasSaya', [
      'tasks' => $tasks,
      'stats' => $stats,
      'filters' => [
        'status' => $status,
        'search' => $search,
        'priority' => $priority,
      ],
    ]);
  }

  public function start(Request $request, Task $task)
  {
    if ($task->intern_id !== $request->user()->id) {
      return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    if ($task->status !== 'pending') {
      return response()->json(['success' => false, 'message' => 'Tugas sudah dimulai.'], 400);
    }

    $task->start();

    return response()->json([
      'success' => true,
      'message' => 'Tugas berhasil dimulai!',
    ]);
  }

  public function submitForReview(Request $request, Task $task)
  {
    if ($task->intern_id !== $request->user()->id) {
      return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    if (!in_array($task->status, ['pending', 'in_progress'])) {
      return response()->json(['success' => false, 'message' => 'Tugas tidak dapat disubmit.'], 400);
    }

    $task->update(['status' => 'review']);

    return response()->json([
      'success' => true,
      'message' => 'Tugas berhasil dikirim untuk review!',
    ]);
  }

  public function show(Request $request, Task $task)
  {
    if ($task->intern_id !== $request->user()->id) {
      abort(403);
    }

    return Inertia::render('Intern/TaskDetail', [
      'task' => [
        'id' => $task->id,
        'title' => $task->title,
        'description' => $task->description,
        'due_date' => $task->due_date->format('Y-m-d'),
        'due_date_formatted' => $task->due_date->translatedFormat('l, d F Y'),
        'priority' => $task->priority,
        'status' => $task->status,
        'mentor_name' => $task->mentor?->name ?? '-',
        'estimated_hours' => $task->estimated_hours,
        'actual_hours' => $task->actual_hours,
        'feedback' => $task->feedback,
        'rating' => $task->rating,
        'is_overdue' => $task->isOverdue(),
        'days_until_due' => $task->getDaysUntilDue(),
        'started_at' => $task->started_at?->format('d M Y H:i'),
        'completed_at' => $task->completed_at?->format('d M Y H:i'),
      ],
    ]);
  }
}
