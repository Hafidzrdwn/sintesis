<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use App\Models\Presence;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class PresenceController extends Controller
{
  public function checkIn(Request $request)
  {
    $request->validate([
      'attendance_mode' => 'required|in:WFO,WFH',
      'latitude' => 'required|numeric',
      'longitude' => 'required|numeric',
      'distance_meters' => 'required|numeric',
      'photo' => 'required|string', // base64 image
    ]);

    $user = $request->user();
    $today = Carbon::today();

    // Check if already checked in today
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

    // Validate distance for WFO mode (max 100 meters)
    if ($request->attendance_mode === 'WFO' && $request->distance_meters > 100) {
      return response()->json([
        'success' => false,
        'message' => 'Anda berada di luar jangkauan kantor. Silakan mendekat ke lokasi kantor.',
      ], 400);
    }

    // Save photo to storage
    $photoPath = $this->saveBase64Image($request->photo, $user->id, 'check_in');

    // Determine status (late if after 09:00)
    $checkInTime = Carbon::now();
    $status = 'present';
    $lateThreshold = Carbon::today()->setHour(9)->setMinute(0)->setSecond(0);

    if ($checkInTime->gt($lateThreshold)) {
      $status = 'late';
    }

    // Create or update presence record
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
      'photo' => 'required|string', // base64 image
    ]);

    $user = $request->user();
    $today = Carbon::today();

    // Find today's presence record
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

    // Save photo to storage
    $photoPath = $this->saveBase64Image($request->photo, $user->id, 'check_out');

    // Update presence with check-out data
    $presence->update([
      'check_out_time' => Carbon::now()->format('H:i:s'),
      'check_out_latitude' => $request->latitude,
      'check_out_longitude' => $request->longitude,
      'check_out_distance_meters' => $request->distance_meters,
      'check_out_photo' => $photoPath,
    ]);

    // Calculate working hours
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

  private function saveBase64Image(string $base64Image, string $userId, string $type): string
  {
    // Remove data URL prefix if exists
    $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $base64Image);
    $imageData = base64_decode($imageData);

    // Generate filename
    $date = Carbon::today()->format('Y-m-d');
    $filename = "{$userId}_{$date}_{$type}.png";
    $path = "presences/{$date}/{$filename}";

    // Store file
    Storage::disk('public')->put($path, $imageData);

    return $path;
  }
}
