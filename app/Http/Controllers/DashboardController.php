<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
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

        if ($status === 'active_intern') {
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

        $applicant = $user->applicant();

        return Inertia::render('CandidateDashboard', [
            'applicationStatus' => $status, // 'none', 'pending', 'accepted', 'rejected'
            'application' => $applicant ? [
                'id' => $applicant->id,
                'position_applied' => $applicant->position_applied,
                'status' => $applicant->status,
                'created_at' => $applicant->created_at,
                'reviewed_at' => $applicant->reviewed_at,
                'notes' => $applicant->notes,
                'start_date' => $applicant->start_date,
                'end_date' => $applicant->end_date,
                'university' => $applicant->university,
            ] : null,
        ]);
    }
}
