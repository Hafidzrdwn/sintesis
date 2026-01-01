<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use App\Models\Logbook;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class LogbookController extends Controller
{
  public function index(Request $request)
  {
    $user = $request->user();

    $month = $request->get('month', Carbon::now()->month);
    $year = $request->get('year', Carbon::now()->year);
    $status = $request->get('status', 'all');
    $search = $request->get('search', '');

    $query = Logbook::where('user_id', $user->id)
      ->with(['task:id,title', 'approver:id,name'])
      ->whereMonth('date', $month)
      ->whereYear('date', $year)
      ->orderBy('date', 'desc');

    if ($status !== 'all') {
      $query->where('status', $status);
    }

    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('activity', 'like', "%{$search}%")
          ->orWhere('description', 'like', "%{$search}%");
      });
    }

    $logbooks = $query->paginate(15)->withQueryString();

    $formattedLogbooks = $logbooks->through(function ($log) {
      return [
        'id' => $log->id,
        'date' => $log->date->format('Y-m-d'),
        'date_formatted' => $log->date->translatedFormat('l, d M Y'),
        'activity' => $log->activity,
        'description' => $log->description,
        'duration_hours' => (float) $log->duration_hours,
        'task_id' => $log->task_id,
        'task_title' => $log->task?->title,
        'status' => $log->status,
        'mentor_notes' => $log->mentor_notes,
        'approved_by_name' => $log->approver?->name,
        'approved_at' => $log->approved_at?->format('d M Y H:i'),
      ];
    });

    $stats = [
      'all' => Logbook::where('user_id', $user->id)->whereMonth('date', $month)->whereYear('date', $year)->count(),
      'draft' => Logbook::where('user_id', $user->id)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'draft')->count(),
      'submitted' => Logbook::where('user_id', $user->id)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'submitted')->count(),
      'approved' => Logbook::where('user_id', $user->id)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'approved')->count(),
      'rejected' => Logbook::where('user_id', $user->id)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'rejected')->count(),
      'total_hours' => Logbook::where('user_id', $user->id)->whereMonth('date', $month)->whereYear('date', $year)->sum('duration_hours'),
    ];

    $tasks = Task::where('intern_id', $user->id)
      ->whereIn('status', ['in_progress', 'pending'])
      ->select('id', 'title')
      ->get();

    return Inertia::render('Intern/BukuLog', [
      'logbooks' => $formattedLogbooks,
      'stats' => $stats,
      'tasks' => $tasks,
      'filters' => [
        'month' => (int) $month,
        'year' => (int) $year,
        'status' => $status,
        'search' => $search,
      ],
    ]);
  }

  public function store(Request $request)
  {
    $request->validate([
      'date' => 'required|date',
      'activity' => 'required|string|max:255',
      'description' => 'nullable|string|max:2000',
      'duration_hours' => 'required|numeric|min:0.5|max:24',
      'task_id' => 'nullable|uuid|exists:tasks,id',
    ]);

    $user = $request->user();
    $internship = $user->currentInternship();

    $existingLog = Logbook::where('user_id', $user->id)
      ->whereDate('date', $request->date)
      ->first();

    if ($existingLog) {
      return response()->json([
        'success' => false,
        'message' => 'Anda sudah membuat jurnal untuk tanggal ini.',
      ], 400);
    }

    $logbook = Logbook::create([
      'user_id' => $user->id,
      'internship_id' => $internship?->id,
      'task_id' => $request->task_id,
      'date' => $request->date,
      'activity' => $request->activity,
      'description' => $request->description,
      'duration_hours' => $request->duration_hours,
      'status' => 'draft',
    ]);

    return response()->json([
      'success' => true,
      'message' => 'Jurnal berhasil disimpan!',
      'logbook' => [
        'id' => $logbook->id,
        'date' => $logbook->date->format('Y-m-d'),
        'activity' => $logbook->activity,
      ],
    ]);
  }

  public function update(Request $request, Logbook $logbook)
  {
    if ($logbook->user_id !== $request->user()->id) {
      return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    if (!in_array($logbook->status, ['draft', 'rejected'])) {
      return response()->json([
        'success' => false,
        'message' => 'Jurnal yang sudah disubmit tidak dapat diedit.',
      ], 400);
    }

    $request->validate([
      'activity' => 'required|string|max:255',
      'description' => 'nullable|string|max:2000',
      'duration_hours' => 'required|numeric|min:0.5|max:24',
      'task_id' => 'nullable|uuid|exists:tasks,id',
    ]);

    $logbook->update([
      'task_id' => $request->task_id,
      'activity' => $request->activity,
      'description' => $request->description,
      'duration_hours' => $request->duration_hours,
      'status' => 'draft',
    ]);

    return response()->json([
      'success' => true,
      'message' => 'Jurnal berhasil diperbarui!',
    ]);
  }

  public function destroy(Request $request, Logbook $logbook)
  {
    if ($logbook->user_id !== $request->user()->id) {
      return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    if (!in_array($logbook->status, ['draft', 'rejected'])) {
      return response()->json([
        'success' => false,
        'message' => 'Jurnal yang sudah disubmit tidak dapat dihapus.',
      ], 400);
    }

    $logbook->delete();

    return response()->json([
      'success' => true,
      'message' => 'Jurnal berhasil dihapus!',
    ]);
  }

  public function submit(Request $request, Logbook $logbook)
  {
    if ($logbook->user_id !== $request->user()->id) {
      return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    if (!in_array($logbook->status, ['draft', 'rejected'])) {
      return response()->json([
        'success' => false,
        'message' => 'Jurnal sudah disubmit sebelumnya.',
      ], 400);
    }

    $logbook->submit();

    return response()->json([
      'success' => true,
      'message' => 'Jurnal berhasil dikirim untuk review!',
    ]);
  }
}
