<?php

namespace Database\Seeders;

use App\Models\Applicant;
use App\Models\Institution;
use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ApplicantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $skill_list = collect(['JavaScript / TS', 'PHP', 'Java', 'Python', 'Go (Golang)', 'C# / .NET', 'Dart / Flutter', 'Kotlin / Swift']);
        $db_list = collect(['MySQL / MariaDB', 'PostgreSQL', 'SQL Server', 'MongoDB']);
        $os_list = collect(['Windows', 'Linux (Ubuntu/etc)', 'macOS']);

        $interns = User::where('role', 'intern')->get();
        $admin = User::where('role', 'admin')->first();
        
        $jobs = Job::where('status', 'open')->get();
        $institutions = Institution::all();

        if($interns->isEmpty() || $jobs->isEmpty() || $institutions->isEmpty()) {
            $this->command->warn('No interns, jobs, or institutions found. Please run UserSeeder, JobSeeder, and InstitutionSeeder first.');
            return;
        }

        foreach ($interns as $index => $intern) {
            $job = $jobs[$index % $jobs->count()];
            $institution = $institutions[$index % $institutions->count()];

            $skillCounts = [1, 2, 3, $skill_list->count()];
            $randomSkillCount = $skillCounts[array_rand($skillCounts)];
            $randomDbCount = rand(1, 3);
            $randomOsCount = rand(1, 2);

            $status = ['pending', 'reviewed', 'interview', 'accepted', 'rejected'][rand(0, 4)];

            Applicant::create([
                'user_id' => $intern->id,
                'job_id' => $job->id,
                'institution_id' => $institution->id,
                'full_name' => $intern->name,
                'phone' => $intern->phone,
                'referral' => ['Evy Juniandari', 'Agus Winata'][rand(0, 1)],
                'skills' => $skill_list->random($randomSkillCount)->toArray(),
                'other_skills' => null,
                'databases' => $db_list->random($randomDbCount)->toArray(),
                'other_databases' => null,
                'operating_systems' => $os_list->random($randomOsCount)->toArray(),
                'other_os' => null,
                'other_interest' => ['UI/UX', 'System Analyst', 'QA/QC', 'Project Management'][rand(0, 3)],
                'demo_required' => [true, false][rand(0, 1)],
                'self_description' => fake()->paragraph(1),
                'portfolio_url' => fake()->url(),
                'start_date' => now()->subMonths(rand(0, 2)),
                'end_date' => now()->addMonths(rand(2, 4)),
                'letter_path' => 'https://morth.nic.in/sites/default/files/dd12-13_0.pdf',
                'id_card_path' => 'https://picsum.photos/200/300',
                'cv_path' => 'https://morth.nic.in/sites/default/files/dd12-13_0.pdf',
                'status' => $status,
                'notes' => null,
                'reviewed_by' => in_array($status, ['reviewed', 'interview', 'accepted', 'rejected']) ? $admin->id : null,
                'reviewed_at' => in_array($status, ['reviewed', 'interview', 'accepted', 'rejected']) ? now() : null,
            ]);
        }
    }
}
