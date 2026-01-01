<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Institution;
use App\Models\Internship;
use App\Models\Job;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MonitoringController extends Controller
{
    public function index()
    {
        $universityData = $this->getUniversityDistribution();
        $mentorData = $this->getMentorCapacity();
        $historicalData = $this->getHistoricalData();
        $statusDistribution = $this->getStatusDistribution();
        $skillDistribution = $this->getSkillDistribution();
        $monthlyByUniversity = $this->getMonthlyByUniversity();

        // return response()->json($historicalData, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return Inertia::render('Admin/GlobalMonitoring', [
            'universityData' => $universityData,
            'mentorData' => $mentorData,
            'historicalData' => $historicalData,
            'statusDistribution' => $statusDistribution,
            'skillDistribution' => $skillDistribution,
            'monthlyByUniversity' => $monthlyByUniversity,
        ]);
    }

    private function getUniversityDistribution()
    {
        $allInstitutions = Institution::select('id', 'name')
            ->withCount('applicants as total_applications')
            ->addSelect([
                'total_positions' => Applicant::selectRaw('count(distinct job_id)')
                    ->whereColumn('institution_id', 'institutions.id')
            ])
            ->having('total_applications', '>', 0)
            ->orderByDesc('total_applications')
            ->get();

        $topInstitutions = $allInstitutions->take(10)->map(function ($institution) {

            $applications = Applicant::where('institution_id', $institution->id)
                ->with(['user:id,name,email', 'job:id,title,slug'])
                ->orderByDesc('id')
                ->get()
                ->map(function ($app) {
                    $app->position_name =
                        $app->job?->title
                        ?? $app->custom_position
                        ?? 'Tidak Diketahui';
                    return $app;
                });

            $repeatUserIds = $applications
                ->groupBy(function ($app) {
                    return $app->user_id . '|' . $app->position_name;
                })
                ->filter(fn($group) => $group->count() > 1)
                ->map(fn($group) => $group->first()->user_id)
                ->unique()
                ->values()
                ->toArray();

            $positions = $applications
                ->groupBy('position_name')
                ->map(function ($apps, $positionName) use ($repeatUserIds) {
                    return [
                        'name' => $positionName ?: 'Posisi Tidak Diketahui',
                        'slug' => $apps->first()->job?->slug,
                        'total_applications' => $apps->count(),
                        'total_applicants' => $apps->unique('user_id')->count(),
                        'applicants' => $apps->map(function ($app) use ($repeatUserIds) {
                            return [
                                'id' => $app->id,
                                'name' => $app->full_name ?? $app->user?->name ?? 'Unknown',
                                'email' => $app->user?->email ?? '',
                                'status' => $app->status,
                                'applied_at' => $app->created_at?->format('d M Y'),
                                'is_repeat' => in_array($app->user_id, $repeatUserIds),
                            ];
                        })->values(),
                    ];
                })
                ->values();

            return [
                'id' => $institution->id,
                'name' => $institution->name,
                'total_applications' => $institution->total_applications,
                'total_positions' => $positions->count(),
                'positions' => $positions,
            ];
        });

        return [
            'all' => $allInstitutions,
            'top' => $topInstitutions,
        ];
    }

    private function getMentorCapacity()
    {
        $mentorCount = User::where('role', 'mentor')->where('status', 'active')->count();
        $activeInterns = Internship::whereIn('status', ['active', 'extended'])->count();

        $avgDuration = Internship::whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->selectRaw('AVG(DATEDIFF(end_date, start_date)) as avg_days')
            ->value('avg_days');

        $averageInternshipDays = $avgDuration ? round($avgDuration) : 90;

        return [
            'mentorCount' => $mentorCount,
            'activeInterns' => $activeInterns,
            'ratio' => $mentorCount > 0 ? round($activeInterns / $mentorCount, 1) : 0,
            'maxRatio' => 5,
            'capacityUsed' => $mentorCount > 0 ? round(($activeInterns / ($mentorCount * 5)) * 100) : 0,
            'averageInternshipDays' => $averageInternshipDays,
        ];
    }

    private function getHistoricalData()
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthLabel = $date->translatedFormat('F Y');

            $applicantCount = Applicant::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            $internCount = Internship::whereYear('start_date', $date->year)
                ->whereMonth('start_date', $date->month)
                ->whereIn('status', ['active', 'extended'])
                ->count();

            $months[] = [
                'month' => $monthLabel,
                'applicants' => $applicantCount,
                'interns' => $internCount,
            ];
        }

        return $months;
    }

    private function getStatusDistribution()
    {
        $statuses = Applicant::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($item) => [$item->status => $item->total]);

        $statusLabels = [
            'pending' => 'Pending',
            'reviewed' => 'Direview',
            'interview' => 'Interview',
            'accepted' => 'Diterima',
            'rejected' => 'Ditolak',
        ];

        $result = [];
        foreach ($statusLabels as $key => $label) {
            $result[] = [
                'status' => $key,
                'label' => $label,
                'total' => $statuses[$key] ?? 0,
            ];
        }

        return $result;
    }

    private function getSkillDistribution()
    {
        $counts = [
            'combined' => [],
            'programming' => [],
            'database' => []
        ];

        $applicants = Applicant::select([
            'skills',
            'other_skills',
            'databases',
            'other_databases'
        ])->get();

        foreach ($applicants as $app) {
            $this->aggregateSkills($app->skills, $app->other_skills, 'programming', $counts);
            $this->aggregateSkills($app->databases, $app->other_databases, 'database', $counts);
        }

        $formattedSkills = [];

        foreach ($counts as $category => $data) {
            arsort($data); 

            $formatted = collect($data)->take(10)->map(fn($total, $skill) => [
                'skill' => $skill,
                'total' => $total
            ])->values()->toArray();

            $formattedSkills[$category] = $formatted;         
        }

        return $formattedSkills;
    }

    private function aggregateSkills($skillsArray, $otherString, $category, &$counts)
    {
        $items = is_array($skillsArray) ? $skillsArray : [];

        if (!empty($otherString)) {
            $others = preg_split('/[,;]+/', $otherString);
            foreach ($others as $o) {
                $val = trim($o);
                if ($val) $items[] = $val;
            }
        }

        foreach ($items as $skill) {
            $key = strtolower(trim($skill));
            $displayLabel = $this->formatSkillLabel($key);
            $counts[$category][$displayLabel] = ($counts[$category][$displayLabel] ?? 0) + 1;
            $counts['combined'][$displayLabel] = ($counts['combined'][$displayLabel] ?? 0) + 1;
        }
    }

    private function formatSkillLabel($skill)
    {
        $dictionary = [
            'sqlite'     => 'SQLite',
            'oracle'     => 'Oracle SQL',
            'oraclesql'  => 'Oracle SQL',
            'oracle sql' => 'Oracle SQL',
            'sqloracle' => 'Oracle SQL',
            'sql oracle' => 'Oracle SQL',
            'mariadb'    => 'MariaDB',
            'vuejs'      => 'Vue.js',
            'reactjs'    => 'React JS',
            'react js'   => 'React JS',
            'react.js'   => 'React JS',
            'nodejs'     => 'Node.js',
            'node js'    => 'Node.js',
            'node.js'    => 'Node.js',
        ];

        $caps = ['php', 'sql', 'js'];
        
        if (isset($dictionary[$skill])) {
            return $dictionary[$skill];
        }

        if (in_array($skill, $caps)) {
            return strtoupper($skill);
        }

        return ucwords($skill);
    }

    private function getMonthlyByUniversity()
    {
        $months = [];

        // Get top 5 institutions by applicant count
        $topInstitutions = Institution::withCount('applicants')
            ->having('applicants_count', '>', 0)
            ->orderByDesc('applicants_count')
            ->limit(5)
            ->get();

        $institutionNames = $topInstitutions->pluck('name')->toArray();
        $institutionIds = $topInstitutions->pluck('id', 'name')->toArray();

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthLabel = $date->translatedFormat('F Y');

            $monthData = ['month' => $monthLabel];

            foreach ($institutionNames as $name) {
                if (isset($institutionIds[$name])) {
                    $count = Applicant::where('institution_id', $institutionIds[$name])
                        ->whereYear('created_at', $date->year)
                        ->whereMonth('created_at', $date->month)
                        ->count();
                    $monthData[$name] = $count ?: null;
                }
            }

            $months[] = $monthData;
        }

        return [
            'universities' => $institutionNames,
            'data' => $months,
        ];
    }
}
