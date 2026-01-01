<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Internship;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Handle dashboard routing based on user's intern status
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $status = $user->getApplicationStatus();

        // Check for active/ongoing internship -> go to InternDashboard
        if ($user->hasActiveInternship() && $user->onGoingInternship()) {
            $internship = $user->currentInternship();

            return Inertia::render('InternDashboard', [
                'internship' => $internship ? [
                    'id' => $internship->id,
                    'position' => $internship->position,
                    'department' => $internship->department,
                    'start_date' => $internship->start_date->format('Y-m-d'),
                    'end_date' => $internship->end_date->format('Y-m-d'),
                    'mentor' => $internship->mentor ? [
                        'id' => $internship->mentor->id,
                        'name' => $internship->mentor->name,
                        'avatar' => $internship->mentor->avatar,
                    ] : null,
                    'progress_percentage' => $internship->getProgressPercentage(),
                    'remaining_days' => $internship->getRemainingDays(),
                ] : null,
            ]);
        }

        // Check for terminated or completed internship
        $pastInternship = Internship::where('intern_id', $user->id)
            ->whereIn('status', ['terminated', 'completed'])
            ->with(['job:id,title', 'mentor:id,name'])
            ->orderBy('updated_at', 'desc')
            ->first();

        $applicant = $user->applicant();

        return Inertia::render('CandidateDashboard', [
            'applicationStatus' => $status,
            'application' => $applicant ? [
                'id' => $applicant->id,
                'position_applied' => $applicant->job?->title,
                'status' => $applicant->status,
                'created_at' => $applicant->created_at,
                'reviewed_at' => $applicant->reviewed_at,
                'notes' => $applicant->notes,
                'start_date' => $applicant->start_date,
                'end_date' => $applicant->end_date,
                'institution' => $applicant->institution->name,
            ] : null,
            'internship' => $pastInternship ? [
                'id' => $pastInternship->id,
                'status' => $pastInternship->status,
                'position' => $pastInternship->custom_position ?? $pastInternship->job?->title ?? '-',
                'mentor_name' => $pastInternship->mentor?->name,
                'start_date' => $pastInternship->start_date->format('Y-m-d'),
                'end_date' => $pastInternship->end_date->format('Y-m-d'),
                'notes' => $pastInternship->notes,
                'certificate_url' => $pastInternship->certificate_url,
            ] : null,
        ]);
    }
}
