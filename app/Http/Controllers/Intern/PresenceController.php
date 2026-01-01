<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use App\Models\Presence;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PresenceController extends Controller
{
  public function index(Request $request)
  {
    $user = $request->user();

    $month = $request->get('month', Carbon::now()->month);
    $year = $request->get('year', Carbon::now()->year);
    $search = $request->get('search', '');

    $query = Presence::where('user_id', $user->id)
      ->whereMonth('date', $month)
      ->whereYear('date', $year)
      ->orderBy('date', 'desc');

    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->whereDate('date', 'like', "%{$search}%")
          ->orWhere('notes', 'like', "%{$search}%");
      });
    }

    $attendances = $query->paginate(15)->withQueryString();

    $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
    $monthEnd = Carbon::create($year, $month, 1)->endOfMonth();

    $monthPresences = Presence::where('user_id', $user->id)
      ->whereBetween('date', [$monthStart, $monthEnd])
      ->get();

    $stats = [
      'present' => $monthPresences->where('status', 'present')->count(),
      'late' => $monthPresences->where('status', 'late')->count(),
      'leave' => $monthPresences->whereIn('status', ['leave', 'sick', 'permit'])->count(),
      'absent' => $monthPresences->where('status', 'absent')->count(),
    ];

    $formattedAttendances = $attendances->through(function ($presence) {
      return [
        'id' => $presence->id,
        'date' => $presence->date->format('Y-m-d'),
        'date_formatted' => $presence->date->translatedFormat('l, d M Y'),
        'check_in_time' => $presence->check_in_time ?? '-',
        'check_out_time' => $presence->check_out_time ?? '-',
        'attendance_mode' => $presence->attendance_mode,
        'status' => $presence->status,
        'notes' => $presence->notes ?? '-',
        'working_hours' => $presence->getWorkingHours(),
      ];
    });

    return Inertia::render('Intern/Absensi', [
      'attendances' => $formattedAttendances,
      'stats' => $stats,
      'filters' => [
        'month' => (int) $month,
        'year' => (int) $year,
        'search' => $search,
      ],
    ]);
  }

  public function checkIn(Request $request)
  {
    $request->validate([
      'attendance_mode' => 'required|in:WFO,WFH',
      'latitude' => 'required|numeric',
      'longitude' => 'required|numeric',
      'distance_meters' => 'required|numeric',
      'photo' => 'required|string',
      'notes' => 'nullable|string|max:500',
    ]);

    $user = $request->user();
    $today = Carbon::today();

    $existingPresence = Presence::where('user_id', $user->id)
      ->whereDate('date', $today)
      ->first();

    if ($existingPresence && $existingPresence->check_in_time) {
      return response()->json([
        'success' => false,
        'message' => 'Anda sudah melakukan Check-In hari ini.',
        'presence' => $existingPresence,
      ], 400);
    }

    if ($request->attendance_mode === 'WFO' && $request->distance_meters > 100) {
      return response()->json([
        'success' => false,
        'message' => 'Anda berada di luar jangkauan kantor. Silakan mendekat ke lokasi kantor.',
      ], 400);
    }

    $photoPath = $this->saveBase64Image($request->photo, $user->id, 'check_in');

    $checkInTime = Carbon::now();
    $status = 'present';
    $lateThreshold = Carbon::today()->setHour(8)->setMinute(0)->setSecond(0);

    if ($checkInTime->gt($lateThreshold)) {
      $status = 'late';
    }

    $presence = Presence::updateOrCreate(
      [
        'user_id' => $user->id,
        'date' => $today,
      ],
      [
        'check_in_time' => $checkInTime->format('H:i:s'),
        'check_in_latitude' => $request->latitude,
        'check_in_longitude' => $request->longitude,
        'check_in_distance_meters' => $request->distance_meters,
        'attendance_mode' => $request->attendance_mode,
        'status' => $status,
        'check_in_photo' => $photoPath,
        'notes' => $request->notes,
      ]
    );

    return response()->json([
      'success' => true,
      'message' => $status === 'late'
        ? 'Check-In berhasil! Anda terlambat hari ini.'
        : 'Check-In berhasil! Selamat bekerja!',
      'presence' => [
        'id' => $presence->id,
        'check_in_time' => $presence->check_in_time,
        'status' => $presence->status,
        'attendance_mode' => $presence->attendance_mode,
      ],
    ]);
  }

  public function checkOut(Request $request)
  {
    $request->validate([
      'latitude' => 'nullable|numeric',
      'longitude' => 'nullable|numeric',
      'distance_meters' => 'nullable|numeric',
      'photo' => 'required|string',
    ]);

    $user = $request->user();
    $today = Carbon::today();

    $presence = Presence::where('user_id', $user->id)
      ->whereDate('date', $today)
      ->first();

    if (!$presence || !$presence->check_in_time) {
      return response()->json([
        'success' => false,
        'message' => 'Anda belum melakukan Check-In hari ini.',
      ], 400);
    }

    if ($presence->check_out_time) {
      return response()->json([
        'success' => false,
        'message' => 'Anda sudah melakukan Check-Out hari ini.',
      ], 400);
    }

    $photoPath = $this->saveBase64Image($request->photo, $user->id, 'check_out');

    $presence->update([
      'check_out_time' => Carbon::now()->format('H:i:s'),
      'check_out_latitude' => $request->latitude,
      'check_out_longitude' => $request->longitude,
      'check_out_distance_meters' => $request->distance_meters,
      'check_out_photo' => $photoPath,
    ]);

    $workingHours = $presence->getWorkingHours();

    return response()->json([
      'success' => true,
      'message' => 'Check-Out berhasil! Total jam kerja: ' . number_format($workingHours, 1) . ' jam.',
      'presence' => [
        'id' => $presence->id,
        'check_in_time' => $presence->check_in_time,
        'check_out_time' => $presence->check_out_time,
        'working_hours' => $workingHours,
        'status' => $presence->status,
      ],
    ]);
  }

  public function today(Request $request)
  {
    $user = $request->user();
    $today = Carbon::today();

    $presence = Presence::where('user_id', $user->id)
      ->whereDate('date', $today)
      ->first();

    return response()->json([
      'success' => true,
      'presence' => $presence ? [
        'id' => $presence->id,
        'check_in_time' => $presence->check_in_time,
        'check_out_time' => $presence->check_out_time,
        'status' => $presence->status,
        'attendance_mode' => $presence->attendance_mode,
        'working_hours' => $presence->getWorkingHours(),
      ] : null,
    ]);
  }

  public function requestLeave(Request $request)
  {
    $request->validate([
      'date' => 'required|date|after_or_equal:today',
      'type' => 'required|in:sick,leave,permit',
      'notes' => 'required|string|max:500',
    ]);

    $user = $request->user();
    $date = Carbon::parse($request->date);

    $existingPresence = Presence::where('user_id', $user->id)
      ->whereDate('date', $date)
      ->first();

    if ($existingPresence) {
      return response()->json([
        'success' => false,
        'message' => 'Sudah ada data absensi untuk tanggal ini.',
      ], 400);
    }

    $presence = Presence::create([
      'user_id' => $user->id,
      'date' => $date,
      'status' => $request->type,
      'notes' => $request->notes,
      'attendance_mode' => 'WFH',
    ]);

    $typeLabels = [
      'sick' => 'Sakit',
      'leave' => 'Cuti',
      'permit' => 'Izin',
    ];

    return response()->json([
      'success' => true,
      'message' => "Pengajuan {$typeLabels[$request->type]} berhasil disubmit untuk tanggal " . $date->format('d M Y'),
      'presence' => [
        'id' => $presence->id,
        'date' => $presence->date->format('Y-m-d'),
        'status' => $presence->status,
        'notes' => $presence->notes,
      ],
    ]);
  }

  private function saveBase64Image(string $base64Image, string $userId, string $type): string
  {
    $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $base64Image);
    $imageData = base64_decode($imageData);

    $date = Carbon::today()->format('Y-m-d');
    $filename = "{$userId}_{$date}_{$type}.png";
    $path = "presences/{$date}/{$filename}";

    Storage::disk('public')->put($path, $imageData);

    return $path;
  }
}
