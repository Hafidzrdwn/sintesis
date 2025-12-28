<?php

namespace Database\Seeders;

use App\Models\Internship;
use App\Models\User;
use Illuminate\Database\Seeder;

class InternshipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Creates internship records linking interns to mentors
     */
    public function run(): void
    {
        $mentors = User::where('role', 'mentor')->get();
        $interns = User::where('role', 'intern')->get();

        if ($mentors->isEmpty() || $interns->isEmpty()) {
            $this->command->warn('No mentors or interns found. Please run UserSeeder first.');
            return;
        }

        $positions = [
            'Frontend Developer',
            'Backend Developer',
            'Full Stack Developer',
            'UI/UX Designer',
            'Data Analyst',
        ];

        $departments = [
            'Engineering',
            'Product',
            'Design',
            'Data Science',
        ];

        foreach ($interns as $index => $intern) {
            // Assign mentors in rotation
            $mentor = $mentors[$index % $mentors->count()];

            Internship::firstOrCreate(
                ['intern_id' => $intern->id],
                [
                    'intern_id' => $intern->id,
                    'mentor_id' => $mentor->id,
                    'position' => $positions[$index % count($positions)],
                    'department' => $departments[$index % count($departments)],
                    'start_date' => now()->subMonths(1),
                    'end_date' => now()->addMonths(2),
                    'status' => 'active',
                    'notes' => null,
                ]
            );
        }

        $this->command->info(count($interns) . ' internship records created');
    }
}
