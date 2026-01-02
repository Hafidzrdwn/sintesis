<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
  public function store(Request $request)
  {
    $user = $request->user();

    $internship = $user->internshipsAsIntern()
      ->where('status', 'completed')
      ->latest('end_date')
      ->first();

    if (!$internship) {
      return response()->json([
        'success' => false,
        'message' => 'Anda belum memiliki magang yang selesai.'
      ], 400);
    }

    $existing = Testimonial::where('user_id', $user->id)
      ->where('internship_id', $internship->id)
      ->first();

    if ($existing) {
      return response()->json([
        'success' => false,
        'message' => 'Anda sudah memberikan testimoni untuk magang ini.'
      ], 400);
    }

    $validated = $request->validate([
      'content' => 'required|string|min:20|max:500',
      'rating' => 'required|integer|min:1|max:5',
    ]);

    $testimonial = Testimonial::create([
      'user_id' => $user->id,
      'internship_id' => $internship->id,
      'content' => $validated['content'],
      'rating' => $validated['rating'],
      'position' => $internship->custom_position ?? $internship->job?->title ?? 'Intern',
      'status' => 'pending',
    ]);

    return response()->json([
      'success' => true,
      'message' => 'Terima kasih! Testimoni Anda berhasil dikirim.',
      'testimonial' => $testimonial,
    ]);
  }

  public function getApproved()
  {
    $testimonials = Testimonial::approved()
      ->with(['user:id,name,avatar'])
      ->latest()
      ->take(10)
      ->get()
      ->map(fn($t) => [
        'id' => $t->id,
        'content' => $t->content,
        'rating' => $t->rating,
        'position' => $t->position,
        'user_name' => $t->user?->name,
        'user_avatar' => $t->user?->avatar,
      ]);

    return response()->json($testimonials);
  }
}
