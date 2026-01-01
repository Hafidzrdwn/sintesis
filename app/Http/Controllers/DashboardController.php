<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Internship;
use App\Models\Logbook;
use App\Models\Presence;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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

        if ($user->hasActiveInternship() && $user->onGoingInternship()) {
            $internship = $user->currentInternship();

            $tasks = Task::where('intern_id', $user->id)
                ->whereIn('status', ['pending', 'in_progress', 'review', 'completed'])
                ->orderByRaw("CASE 
                    WHEN status = 'in_progress' THEN 1 
                    WHEN status = 'pending' THEN 2 
                    WHEN status = 'review' THEN 3 
                    WHEN status = 'completed' THEN 4 
                    ELSE 5 END")
                ->orderBy('due_date')
                ->take(5)
                ->get()
                ->map(fn($task) => [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'due_date' => $task->due_date?->format('Y-m-d'),
                    'due_date_human' => $task->due_date?->translatedFormat('d M'),
                    'is_overdue' => $task->isOverdue(),
                    'days_until_due' => $task->getDaysUntilDue(),
                ]);

            $logbooks = Logbook::where('user_id', $user->id)
                ->orderBy('date', 'desc')
                ->take(3)
                ->get()
                ->map(fn($log) => [
                    'id' => $log->id,
                    'date' => $log->date->format('Y-m-d'),
                    'date_human' => $log->date->translatedFormat('l, d M'),
                    'activity' => $log->activity,
                    'description' => $log->description,
                    'status' => $log->status,
                ]);

            $todayPresence = Presence::where('user_id', $user->id)
                ->whereDate('date', Carbon::today())
                ->first();

            return Inertia::render('InternDashboard', [
                'internship' => $internship ? [
                    'id' => $internship->id,
                    'position' => $internship->custom_position ?? $internship->job?->title ?? '-',
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
                'tasks' => $tasks,
                'logbooks' => $logbooks,
                'todayPresence' => $todayPresence ? [
                    'id' => $todayPresence->id,
                    'check_in_time' => $todayPresence->check_in_time,
                    'check_out_time' => $todayPresence->check_out_time,
                    'status' => $todayPresence->status,
                    'attendance_mode' => $todayPresence->attendance_mode,
                ] : null,
            ]);
        }

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
