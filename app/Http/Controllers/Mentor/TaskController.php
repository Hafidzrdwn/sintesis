<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Internship;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class TaskController extends Controller
{
  public function index(Request $request)
  {
    $mentor = $request->user();

    $status = $request->get('status', 'all');
    $priority = $request->get('priority', '');
    $search = $request->get('search', '');
    $internId = $request->get('intern_id', '');

    $query = Task::where('mentor_id', $mentor->id)
      ->with(['intern:id,name,email,avatar'])
      ->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
      ->orderBy('due_date', 'asc');

    if ($status !== 'all') {
      $query->where('status', $status);
    }

    if ($priority) {
      $query->where('priority', $priority);
    }

    if ($internId) {
      $query->where('intern_id', $internId);
    }

    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('title', 'like', "%{$search}%")
          ->orWhere('description', 'like', "%{$search}%");
      });
    }

    $tasks = $query->get()->map(fn($task) => [
      'id' => $task->id,
      'title' => $task->title,
      'description' => $task->description,
      'due_date' => $task->due_date->format('Y-m-d'),
      'due_date_formatted' => $task->due_date->translatedFormat('d M Y'),
      'priority' => $task->priority,
      'status' => $task->status,
      'intern_id' => $task->intern_id,
      'intern_name' => $task->intern?->name ?? '-',
      'intern_avatar' => $task->intern?->avatar,
      'estimated_hours' => $task->estimated_hours,
      'actual_hours' => $task->actual_hours,
      'feedback' => $task->feedback,
      'rating' => $task->rating,
      'is_overdue' => $task->isOverdue(),
      'started_at' => $task->started_at?->format('d M Y H:i'),
      'completed_at' => $task->completed_at?->format('d M Y H:i'),
      'submission_notes' => $task->submission_notes,
      'submission_files' => $task->submission_files,
    ]);

    $stats = [
      'all' => Task::where('mentor_id', $mentor->id)->count(),
      'pending' => Task::where('mentor_id', $mentor->id)->where('status', 'pending')->count(),
      'in_progress' => Task::where('mentor_id', $mentor->id)->where('status', 'in_progress')->count(),
      'review' => Task::where('mentor_id', $mentor->id)->where('status', 'review')->count(),
      'completed' => Task::where('mentor_id', $mentor->id)->where('status', 'completed')->count(),
    ];

    $mentees = Internship::where('mentor_id', $mentor->id)
      ->where('status', 'active')
      ->with('intern:id,name')
      ->get()
      ->map(fn($i) => ['id' => $i->intern_id, 'name' => $i->intern?->name]);

    return Inertia::render('Mentor/TaskManagement', [
      'tasks' => $tasks,
      'stats' => $stats,
      'mentees' => $mentees,
      'filters' => [
        'status' => $status,
        'priority' => $priority,
        'search' => $search,
        'intern_id' => $internId,
      ],
    ]);
  }

  public function store(Request $request)
  {
    $request->validate([
      'title' => 'required|string|max:255',
      'description' => 'nullable|string|max:2000',
      'due_date' => 'required|date|after:today',
      'priority' => 'required|in:low,medium,high,urgent',
      'intern_id' => 'required|uuid|exists:users,id',
      'estimated_hours' => 'nullable|numeric|min:0.5|max:100',
    ], [
      'title.required' => 'Judul tugas harus diisi.',
      'description.max' => 'Deskripsi tugas tidak boleh lebih dari 2000 karakter.',
      'due_date.required' => 'Tanggal deadline harus diisi.',
      'due_date.after' => 'Tanggal deadline harus setelah hari ini.',
      'priority.required' => 'Prioritas harus diisi.',
      'intern_id.required' => 'Intern harus diisi.',
      'estimated_hours.max' => 'Waktu yang diperkirakan tidak boleh lebih dari 100 jam.',
    ]);

    $mentor = $request->user();

    $internship = Internship::where('mentor_id', $mentor->id)
      ->where('intern_id', $request->intern_id)
      ->where('status', 'active')
      ->first();

    if (!$internship) {
      return response()->json(['success' => false, 'message' => 'Intern tidak ditemukan dalam daftar mentee Anda.'], 400);
    }

    $task = Task::create([
      'title' => $request->title,
      'description' => $request->description,
      'due_date' => $request->due_date,
      'priority' => $request->priority,
      'status' => 'pending',
      'mentor_id' => $mentor->id,
      'intern_id' => $request->intern_id,
      'internship_id' => $internship->id,
      'estimated_hours' => $request->estimated_hours,
    ]);

    return response()->json([
      'success' => true,
      'message' => 'Tugas berhasil dibuat!',
      'task' => ['id' => $task->id, 'title' => $task->title],
    ]);
  }

  public function update(Request $request, Task $task)
  {
    if ($task->mentor_id !== $request->user()->id) {
      return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    $request->validate([
      'title' => 'required|string|max:255',
      'description' => 'nullable|string|max:2000',
      'due_date' => 'required|date',
      'priority' => 'required|in:low,medium,high,urgent',
      'estimated_hours' => 'nullable|numeric|min:0.5|max:100',
    ]);

    $task->update([
      'title' => $request->title,
      'description' => $request->description,
      'due_date' => $request->due_date,
      'priority' => $request->priority,
      'estimated_hours' => $request->estimated_hours,
    ]);

    return response()->json(['success' => true, 'message' => 'Tugas berhasil diperbarui!']);
  }

  public function destroy(Request $request, Task $task)
  {
    if ($task->mentor_id !== $request->user()->id) {
      return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    if (!in_array($task->status, ['pending', 'cancelled'])) {
      return response()->json(['success' => false, 'message' => 'Tugas yang sudah dikerjakan tidak dapat dihapus.'], 400);
    }

    $task->delete();
    return response()->json(['success' => true, 'message' => 'Tugas berhasil dihapus!']);
  }

  public function approve(Request $request, Task $task)
  {
    if ($task->mentor_id !== $request->user()->id) {
      return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    if ($task->status !== 'review') {
      return response()->json(['success' => false, 'message' => 'Tugas belum disubmit untuk review.'], 400);
    }

    $request->validate([
      'feedback' => 'nullable|string|max:1000',
      'rating' => 'required|integer|min:1|max:5',
      'actual_hours' => 'nullable|numeric|min:0.5|max:100',
    ]);

    $task->update([
      'status' => 'completed',
      'feedback' => $request->feedback,
      'rating' => $request->rating,
      'actual_hours' => $request->actual_hours,
      'completed_at' => Carbon::now(),
    ]);

    return response()->json(['success' => true, 'message' => 'Tugas telah disetujui dan selesai!']);
  }

  public function reject(Request $request, Task $task)
  {
    if ($task->mentor_id !== $request->user()->id) {
      return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    if ($task->status !== 'review') {
      return response()->json(['success' => false, 'message' => 'Tugas belum disubmit untuk review.'], 400);
    }

    $request->validate([
      'feedback' => 'required|string|max:1000',
    ]);

    $task->update([
      'status' => 'in_progress',
      'feedback' => $request->feedback,
    ]);

    return response()->json(['success' => true, 'message' => 'Tugas dikembalikan untuk perbaikan.']);
  }

  public function cancel(Request $request, Task $task)
  {
    if ($task->mentor_id !== $request->user()->id) {
      return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    if ($task->status === 'completed') {
      return response()->json(['success' => false, 'message' => 'Tugas yang sudah selesai tidak dapat dibatalkan.'], 400);
    }

    $task->update(['status' => 'cancelled']);
    return response()->json(['success' => true, 'message' => 'Tugas berhasil dibatalkan.']);
  }
}
