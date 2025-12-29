<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Job;
use App\Models\User;
use App\Models\Internship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RecruitmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Applicant::with(['job', 'reviewer', 'user'])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhereRelation('user', 'email', 'like', "%{$search}%")
                  ->orWhere('university', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('job')) {
            $query->whereRelation('job', 'slug', $request->job);
        }

        $applicants = $query->paginate(10)->withQueryString();

        $stats = [
            'total' => Applicant::count(),
            'pending' => Applicant::where('status', 'pending')->count(),
            'reviewed' => Applicant::where('status', 'reviewed')->count(),
            'interview' => Applicant::where('status', 'interview')->count(),
            'accepted' => Applicant::where('status', 'accepted')->count(),
            'rejected' => Applicant::where('status', 'rejected')->count(),
        ];

        $jobs = Job::select('id', 'title', 'slug')->orderBy('title')->get();

        $mentors = User::where('role', 'mentor')
            ->where('status', 'active')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

            // return $applicants;

        return Inertia::render('Admin/Recruitment', [
            'applicants' => $applicants,
            'stats' => $stats,
            'jobs' => $jobs,
            'mentors' => $mentors,
            'filters' => $request->only(['search', 'status', 'job']),
        ]);
    }

    public function show(Applicant $applicant)
    {
        $applicant->load(['job', 'reviewer']);

        return response()->json([
            'applicant' => $applicant,
        ]);
    }

    public function updateStatus(Request $request, Applicant $applicant)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,reviewed,interview,accepted,rejected',
            'notes' => 'nullable|string|max:1000',
            'mentor_id' => 'nullable|exists:users,id',
        ]);

        $applicant->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? $applicant->notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        if ($validated['status'] === 'accepted') {
            $this->processAcceptedApplicant($applicant, $validated['mentor_id'] ?? null);
        }

        return back()->with('success', 'Status lamaran berhasil diperbarui.');
    }

    private function processAcceptedApplicant(Applicant $applicant, ?string $mentorId)
    {   
        $user = User::where('email', $applicant->user->email)->first();
        
        Internship::create([
            'applicant_id' => $applicant->id,
            'intern_id' => $user->id,
            'mentor_id' => $mentorId,
            'job_id' => $applicant->job_id,
            'start_date' => $applicant->start_date ?? now(),
            'end_date' => $applicant->end_date ?? now()->addMonths(3),
            'status' => 'active',
        ]);
    }
}
