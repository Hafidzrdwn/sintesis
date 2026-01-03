<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\Job;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JobController extends Controller
{
    /**
     * Get all open jobs for landing page
     */
    public function landing(): Response
    {
        $jobs = Job::select([
            'id',
            'title',
            'slug',
            'type',
            'description',
            'location',
            'duration',
            'image',
            'updated_at'
        ])
            ->where('status', 'open')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($job) {
                return [
                    'id' => $job->id,
                    'title' => $job->title,
                    'slug' => $job->slug,
                    'type' => $job->type,
                    'description' => $job->description,
                    'location' => $job->location,
                    'duration' => $job->duration,
                    'image' => $job->image,
                    'updatedAt' => $job->updated_at,
                    'href' => '/lowongan/' . $job->slug,
                ];
            });

        return Inertia::render('LandingPage', [
            'jobs' => $jobs,
        ]);
    }

    /**
     * Show job detail page
     */
    public function show(string $slug): Response
    {
        $job = Job::where('slug', $slug)->firstOrFail();

        $stats = [
            'total' => $job->applicants()->count(),
            'pending' => $job->applicants()->where('status', 'pending')->count(),
            'reviewed' => $job->applicants()->whereIn('status', ['reviewed', 'interview'])->count(),
            'rejected' => $job->applicants()->where('status', 'rejected')->count(),
            'accepted' => $job->applicants()->where('status', 'accepted')->count(),
        ];

        $user = (auth()->check()) ? auth()->user() : null;

        return Inertia::render('JobDetailPage', [
            'job' => [
                'id' => $job->id,
                'title' => $job->title,
                'slug' => $job->slug,
                'type' => $job->type,
                'status' => $job->status,
                'statusLabel' => $job->status_label,
                'location' => $job->location,
                'description' => $job->description,
                'responsibilities' => $job->responsibilities ?? [],
                'requirements' => $job->requirements ?? [],
                'benefits' => $job->benefits ?? [],
                'duration' => $job->duration,
                'deadline' => $job->deadline,
                'image' => $job->image,
            ],
            'user_active_internship' => $user?->hasActiveInternship(),
            'stats' => $stats,
            'canApply' => auth()->check() ? auth()->user()->canApply() : true,
        ]);
    }

    /**
     * Get open jobs for application form
     */
    public function openJobs()
    {
        return Job::open()
            ->select('id', 'title', 'slug', 'type', 'location')
            ->orderBy('title')
            ->get();
    }

    /**
     * Show internship application form
     */
    public function apply(?string $slug = null): Response|RedirectResponse
    {
        $user = request()->user();

        if (!$user->canApply()) {
            return redirect()->route('dashboard');
        }

        $openJobs = Job::open()
            ->select('id', 'title', 'slug', 'type', 'location')
            ->orderBy('title')
            ->get()
            ->map(fn($job) => [
                'id' => $job->id,
                'title' => $job->title,
                'slug' => $job->slug,
                'type' => $job->type,
                'location' => $job->location,
            ]);

        $selectedJob = null;
        if ($slug) {
            $job = Job::where('slug', $slug)->open()->first();
            if ($job) {
                $selectedJob = [
                    'id' => $job->id,
                    'title' => $job->title,
                    'slug' => $job->slug,
                    'type' => $job->type,
                    'location' => $job->location,
                ];
            }
        }

        $institutions = Institution::orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('InternshipApplication', [
            'openJobs' => $openJobs,
            'selectedJob' => $selectedJob,
            'institutions' => $institutions,
        ]);
    }
}
