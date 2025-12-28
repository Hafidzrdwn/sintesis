<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Creates:
     * - 1 Super Admin (admin@inosoft.com)
     * - 3 Mentors
     * - 5 Interns
     */
    public function run(): void
    {
        // Create Super Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@inosoft.com'],
            [
                'name' => 'Super Admin',
                'email' => 'admin@inosoft.com',
                'phone' => '081234567890',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');
        $this->command->info('Super Admin created: admin@inosoft.com');

        // Create Mentors
        $mentors = [
            [
                'name' => 'Dr. Budi Santoso',
                'email' => 'budi.santoso@inosoft.com',
                'phone' => '081234567891',
            ],
            [
                'name' => 'Ir. Siti Rahayu',
                'email' => 'siti.rahayu@inosoft.com',
                'phone' => '081234567892',
            ],
            [
                'name' => 'Ahmad Fauzi, M.Kom',
                'email' => 'ahmad.fauzi@inosoft.com',
                'phone' => '081234567893',
            ],
        ];

        foreach ($mentors as $mentorData) {
            $mentor = User::firstOrCreate(
                ['email' => $mentorData['email']],
                [
                    'name' => $mentorData['name'],
                    'email' => $mentorData['email'],
                    'phone' => $mentorData['phone'],
                    'password' => Hash::make('password'),
                    'role' => 'mentor',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );
            $mentor->assignRole('mentor');
        }
        $this->command->info('3 Mentors created');

        // Create Interns
        $interns = [
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi.lestari@student.university.ac.id',
                'phone' => '081234567894',
            ],
            [
                'name' => 'Rizki Pratama',
                'email' => 'rizki.pratama@student.university.ac.id',
                'phone' => '081234567895',
            ],
            [
                'name' => 'Nurul Hidayah',
                'email' => 'nurul.hidayah@student.university.ac.id',
                'phone' => '081234567896',
            ],
            [
                'name' => 'Andi Wijaya',
                'email' => 'andi.wijaya@student.university.ac.id',
                'phone' => '081234567897',
            ],
            [
                'name' => 'Putri Ayu',
                'email' => 'putri.ayu@student.university.ac.id',
                'phone' => '081234567898',
            ],
        ];

        foreach ($interns as $internData) {
            $intern = User::firstOrCreate(
                ['email' => $internData['email']],
                [
                    'name' => $internData['name'],
                    'email' => $internData['email'],
                    'phone' => $internData['phone'],
                    'password' => Hash::make('password'),
                    'role' => 'intern',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );
            $intern->assignRole('intern');
        }
        $this->command->info('5 Interns created');

        $this->command->info('');
        $this->command->info('=================================');
        $this->command->info('Default password for all users: password');
        $this->command->info('=================================');
    }
}
