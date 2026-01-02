<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Logbook;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class LogbookController extends Controller
{
  public function index(Request $request)
  {
    $mentor = $request->user();

    $menteeIds = $mentor->mentees()->pluck('intern_id');

    $month = $request->get('month', Carbon::now()->month);
    $year = $request->get('year', Carbon::now()->year);
    $status = $request->get('status', 'all');
    $menteeId = $request->get('mentee_id', '');
    $search = $request->get('search', '');

    $query = Logbook::whereIn('user_id', $menteeIds)
      ->with(['user:id,name,avatar', 'task:id,title', 'approver:id,name'])
      ->whereMonth('date', $month)
      ->whereYear('date', $year)
      ->orderBy('date', 'desc');

    if ($status !== 'all') {
      $query->where('status', $status);
    }

    if ($menteeId) {
      $query->where('user_id', $menteeId);
    }

    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('activity', 'like', "%{$search}%")
          ->orWhere('description', 'like', "%{$search}%");
      });
    }

    $logbooks = $query->paginate(15)->withQueryString();

    $formattedLogbooks = $logbooks->through(fn($log) => [
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
      'intern_id' => $log->user_id,
      'intern_name' => $log->user?->name,
      'intern_avatar' => $log->user?->avatar,
    ]);

    $stats = [
      'all' => Logbook::whereIn('user_id', $menteeIds)->whereMonth('date', $month)->whereYear('date', $year)->count(),
      'submitted' => Logbook::whereIn('user_id', $menteeIds)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'submitted')->count(),
      'approved' => Logbook::whereIn('user_id', $menteeIds)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'approved')->count(),
      'rejected' => Logbook::whereIn('user_id', $menteeIds)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'rejected')->count(),
    ];

    $mentees = User::whereIn('id', $menteeIds)->select('id', 'name')->get();

    return Inertia::render('Mentor/LogbookReview', [
      'logbooks' => $formattedLogbooks,
      'stats' => $stats,
      'mentees' => $mentees,
      'filters' => [
        'month' => (int) $month,
        'year' => (int) $year,
        'status' => $status,
        'mentee_id' => $menteeId,
        'search' => $search,
      ],
    ]);
  }

  public function approve(Request $request, Logbook $logbook)
  {
    $mentor = $request->user();

    $menteeIds = $mentor->mentees()->pluck('intern_id');

    if (!$menteeIds->contains($logbook->user_id)) {
      return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    if ($logbook->status !== 'submitted') {
      return response()->json(['success' => false, 'message' => 'Jurnal tidak dalam status menunggu review.'], 400);
    }

    $request->validate([
      'notes' => 'nullable|string|max:2000',
    ]);

    $logbook->approve($mentor->id, $request->notes);

    return response()->json(['success' => true, 'message' => 'Jurnal berhasil disetujui!']);
  }

  public function reject(Request $request, Logbook $logbook)
  {
    $mentor = $request->user();

    $menteeIds = $mentor->mentees()->pluck('intern_id');

    if (!$menteeIds->contains($logbook->user_id)) {
      return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    if ($logbook->status !== 'submitted') {
      return response()->json(['success' => false, 'message' => 'Jurnal tidak dalam status menunggu review.'], 400);
    }

    $request->validate([
      'notes' => 'required|string|max:2000',
    ], [
      'notes.required' => 'Alasan penolakan wajib diisi.',
    ]);

    $logbook->reject($mentor->id, $request->notes);

    return response()->json(['success' => true, 'message' => 'Jurnal berhasil ditolak.']);
  }
}
