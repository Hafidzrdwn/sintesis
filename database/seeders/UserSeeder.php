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
                'username' => 'admin',
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
                'username' => 'budisantoso',
                'email' => 'budi.santoso@inosoft.com',
                'phone' => '081234567891',
            ],
            [
                'name' => 'Ir. Siti Rahayu',
                'username' => 'sitirahayu',
                'email' => 'siti.rahayu@inosoft.com',
                'phone' => '081234567892',
            ],
            [
                'name' => 'Ahmad Fauzi, M.Kom',
                'username' => 'ahmadfauzi',
                'email' => 'ahmadfauzi@inosoft.com',
                'phone' => '081234567893',
            ],
            [
                'name' => 'Rizky Wisnu Adji, S.Kom',
                'username' => 'rizkyadji',
                'email' => 'rizkyadji@inosoft.com',
                'phone' => '081234567894',
            ],
            [
                'name' => 'Dewi Suksma Wati, M.T',
                'username' => 'dewisw',
                'email' => 'dewisw@inosoft.com',
                'phone' => '081234567895',
            ],
            [
                'name' => 'Fajar Nugroho',
                'username' => 'fajarnugroho',
                'email' => 'fajar.nugroho@inosoft.com',
                'phone' => '081234567896',
            ],
            [
                'name' => 'Putri Wulandari, S.T',
                'username' => 'putriw',
                'email' => 'putri.w@inosoft.com',
                'phone' => '081234567897',
            ],
            [
                'name' => 'Andi Kurniawan, M.Sc',
                'username' => 'andikurniawan',
                'email' => 'andi.kurniawan@inosoft.com',
                'phone' => '081234567898',
            ]
        ];

        foreach ($mentors as $mentorData) {
            $mentor = User::firstOrCreate(
                ['email' => $mentorData['email']],
                [
                    'name' => $mentorData['name'],
                    'username' => $mentorData['username'],
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
        $this->command->info('8 Mentors created');

        // Create Interns
        $interns = [
            [
                'name' => 'Dewi Lestari',
                'username' => 'dewilestari',
                'email' => 'dewi.lestari@gmail.com',
                'phone' => '081234567894',
            ],
            [
                'name' => 'Rizki Pratama',
                'username' => 'rizkypratama',
                'email' => 'rizki.pratama@gmail.com',
                'phone' => '081234567895',
            ],
            [
                'name' => 'Nurul Hidayah',
                'username' => 'nurulhidayah',
                'email' => 'nurul.hidayah@gmail.com',
                'phone' => '081234567896',
            ],
            [
                'name' => 'Andi Wijaya',
                'username' => 'andiwijaya',
                'email' => 'andi.wijaya@gmail.com',
                'phone' => '081234567897',
            ],
            [
                'name' => 'Putri Ayu',
                'username' => 'putriayu',
                'email' => 'putri.ayu@gmail.com',
                'phone' => '081234567898',
            ],
        ];

        foreach ($interns as $internData) {
            $intern = User::firstOrCreate(
                ['email' => $internData['email']],
                [
                    'name' => $internData['name'],
                    'username' => $internData['username'],
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
