<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TestimonialController extends Controller
{
  public function index(Request $request)
  {
    $status = $request->get('status', 'all');
    $search = $request->get('search', '');

    $query = Testimonial::with(['user:id,name,email,avatar', 'internship.job:id,title'])
      ->orderBy('created_at', 'desc');

    if ($status !== 'all') {
      $query->where('status', $status);
    }

    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"))
          ->orWhere('content', 'like', "%{$search}%");
      });
    }

    $testimonials = $query->paginate(10)->through(fn($t) => [
      'id' => $t->id,
      'content' => $t->content,
      'rating' => $t->rating,
      'position' => $t->position,
      'status' => $t->status,
      'created_at' => $t->created_at->format('d M Y H:i'),
      'user_name' => $t->user?->name,
      'user_email' => $t->user?->email,
      'user_avatar' => $t->user?->avatar,
    ]);

    $stats = [
      'all' => Testimonial::count(),
      'pending' => Testimonial::where('status', 'pending')->count(),
      'approved' => Testimonial::where('status', 'approved')->count(),
      'rejected' => Testimonial::where('status', 'rejected')->count(),
    ];

    return Inertia::render('Admin/Testimonial', [
      'testimonials' => $testimonials,
      'stats' => $stats,
      'filters' => [
        'status' => $status,
        'search' => $search,
      ],
    ]);
  }

  public function approve(Request $request, $id)
  {
    $testimonial = Testimonial::findOrFail($id);
    $testimonial->update(['status' => 'approved']);

    return response()->json([
      'success' => true,
      'message' => 'Testimoni berhasil disetujui',
    ]);
  }

  public function reject(Request $request, $id)
  {
    $testimonial = Testimonial::findOrFail($id);
    $testimonial->update(['status' => 'rejected']);

    return response()->json([
      'success' => true,
      'message' => 'Testimoni berhasil ditolak',
    ]);
  }

  public function destroy($id)
  {
    $testimonial = Testimonial::findOrFail($id);
    $testimonial->delete();

    return response()->json([
      'success' => true,
      'message' => 'Testimoni berhasil dihapus',
    ]);
  }
}
