<?php

namespace Database\Seeders;

use App\Models\Job;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JobSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jobs = [
            [
                'title' => 'Backend Developer Intern',
                'type' => 'Hybrid',
                'status' => 'open',
                'location' => 'Surabaya',
                'description' => 'Bergabunglah dengan tim engineering kami untuk membangun API yang skalabel dan efisien menggunakan Laravel.',
                'responsibilities' => [
                    'Membangun dan memelihara fitur sisi server menggunakan Laravel',
                    'Mengoptimalkan database MySQL untuk performa maksimal',
                    'Bekerja sama dengan tim frontend untuk integrasi API'
                ],
                'requirements' => [
                    'Mahasiswa tingkat akhir Jurusan Sistem Informasi atau Teknik Informatika',
                    'Memahami konsep dasar PHP dan framework Laravel',
                    'Paham mengenai konsep RESTful API'
                ],
                'benefits' => [
                    'Sertifikat Magang Resmi',
                    'Uang Saku Bulanan',
                    'Mentoring intensif dari Senior Developer'
                ],
                'duration' => '3 - 6 Bulan',
                'deadline' => Carbon::parse('2026-02-28'),
            ],
            [
                'title' => 'Frontend Developer Intern',
                'type' => 'On-site',
                'status' => 'open',
                'location' => 'Surabaya',
                'description' => 'Bantu kami menciptakan antarmuka pengguna yang interaktif dan responsif menggunakan Vue.js dan Tailwind CSS.',
                'responsibilities' => [
                    'Menerjemahkan desain Figma menjadi kode Vue.js yang bersih',
                    'Memastikan responsivitas aplikasi di berbagai perangkat',
                    'Melakukan integrasi API dengan state management (Pinia/Vuex)'
                ],
                'requirements' => [
                    'Menguasai HTML, CSS, dan JavaScript modern',
                    'Pernah menggunakan Vue.js atau React JS',
                    'Familiar dengan Tailwind CSS'
                ],
                'benefits' => [
                    'Akses ke kursus premium frontend',
                    'Pengalaman mengerjakan project real',
                    'Lingkungan kerja yang suportif'
                ],
                'duration' => '3 Bulan',
                'deadline' => Carbon::parse('2026-01-15'),
            ],
            [
                'title' => 'UI/UX Designer Intern',
                'type' => 'Hybrid',
                'status' => 'open',
                'location' => 'Surabaya',
                'description' => 'Rancang pengalaman pengguna yang bermakna dan estetis untuk berbagai produk digital inovatif kami.',
                'responsibilities' => [
                    'Melakukan user research dan membuat user flow',
                    'Membuat wireframe dan desain high-fidelity',
                    'Menyusun prototype interaktif di Figma'
                ],
                'requirements' => [
                    'Memiliki portfolio desain UI/UX yang menarik',
                    'Mahir menggunakan Figma atau Adobe XD',
                    'Memiliki pemahaman dasar tentang Design Thinking'
                ],
                'benefits' => [
                    'Review portfolio oleh Product Designer profesional',
                    'Fleksibilitas kerja hybrid',
                    'Networking luas'
                ],
                'duration' => '4 Bulan',
                'deadline' => Carbon::parse('2025-12-30'),
            ],
            [
                'title' => 'Mobile Developer Intern (Flutter)',
                'type' => 'Remote',
                'status' => 'open',
                'location' => 'Surabaya',
                'description' => 'Kembangkan aplikasi mobile cross-platform yang handal menggunakan framework Flutter.',
                'responsibilities' => [
                    'Mengembangkan fitur baru pada aplikasi mobile Android/iOS',
                    'Memperbaiki bug dan meningkatkan performa aplikasi',
                    'Menerapkan UI desain yang kompleks ke dalam widget Flutter'
                ],
                'requirements' => [
                    'Paham bahasa pemrograman Dart',
                    'Memiliki pengalaman dasar dengan Flutter SDK',
                    'Mampu bekerja secara mandiri secara remote'
                ],
                'benefits' => [
                    'Waktu kerja fleksibel',
                    'Kesempatan menjadi karyawan tetap',
                    'Tunjangan internet'
                ],
                'duration' => '6 Bulan',
                'deadline' => Carbon::parse('2026-03-20'),
            ],
            [
                'title' => 'Quality Assurance Intern',
                'type' => 'On-site',
                'status' => 'open',
                'location' => 'Surabaya',
                'description' => 'Pastikan kualitas produk software kami terjaga melalui pengujian manual dan otomatis.',
                'responsibilities' => [
                    'Membuat test case berdasarkan kebutuhan fitur',
                    'Melakukan pengujian fungsional dan pelaporan bug',
                    'Belajar membuat automated testing sederhana'
                ],
                'requirements' => [
                    'Teliti dan memiliki perhatian detail yang tinggi',
                    'Mampu berkomunikasi dengan baik antara tim dev dan user',
                    'Memahami siklus SDLC'
                ],
                'benefits' => [
                    'Pelatihan Automated Testing',
                    'Gratis makan siang di kantor',
                    'Akses fasilitas kantor'
                ],
                'duration' => '3 Bulan',
                'deadline' => Carbon::parse('2026-01-30'),
            ],
            [
                'title' => 'HR Operations Intern',
                'type' => 'On-site',
                'status' => 'open',
                'location' => 'Surabaya',
                'description' => 'Bantu tim HR dalam mengelola administrasi dan proses rekrutmen peserta magang.',
                'responsibilities' => [
                    'Melakukan screening awal dokumen pendaftar magang',
                    'Menjadwalkan sesi interview antara mentor dan kandidat',
                    'Mengelola data absensi dan logbook mingguan intern'
                ],
                'requirements' => [
                    'Mahasiswa Psikologi, Manajemen, atau Administrasi Bisnis',
                    'Memiliki kemampuan organisasi yang baik',
                    'Mampu menjaga kerahasiaan data perusahaan'
                ],
                'benefits' => [
                    'Pengalaman HR di industri teknologi',
                    'Sertifikat pengalaman kerja',
                    'Bonus performa'
                ],
                'duration' => '6 Bulan',
                'deadline' => Carbon::parse('2026-02-10'),
            ],
        ];

        foreach ($jobs as $jobData) {
            Job::firstOrCreate(
                ['title' => $jobData['title']],
                $jobData
            );
        }
    }
}
