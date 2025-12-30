<?php

namespace Database\Seeders;

use App\Models\Applicant;
use App\Models\Institution;
use App\Models\Internship;
use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Seeder;

class InternshipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Creates internship records linking interns to mentors with job positions
     */
    public function run(): void
    {
        $mentors = User::where('role', 'mentor')->get();
        $interns = User::where('role', 'intern')->get();
        $jobs = Job::where('status', 'open')->get();

        if ($mentors->isEmpty() || $interns->isEmpty()) {
            $this->command->warn('No mentors or interns found. Please run UserSeeder first.');
            return;
        }

        if ($jobs->isEmpty()) {
            $this->command->warn('No jobs found. Please run JobSeeder first.');
            return;
        }

        foreach ($interns as $index => $intern) {
            // Assign mentors in rotation
            $mentor = $mentors[$index % $mentors->count()];
            
            // Assign jobs in rotation
            $job = $jobs[$index % $jobs->count()];

            $applicant = Applicant::where('user_id', $intern->id)->first();

            if($applicant->status === 'accepted') {
                Internship::firstOrCreate(
                    ['intern_id' => $intern->id],
                    [
                        'intern_id' => $intern->id,
                        'mentor_id' => $mentor->id,
                        'applicant_id' => $applicant->id,
                        'job_id' => $job->id,
                        'custom_position' => null,
                        'start_date' => $applicant->start_date,
                        'end_date' => $applicant->end_date,
                        'status' => 'active',
                        'notes' => 'Selamat & Semangat Magangnya!!',
                    ]
                );
            }
        }

        $this->command->info(count($interns) . ' internship records created/updated');
    }
}
