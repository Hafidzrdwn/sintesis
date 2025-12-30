<?php

namespace Database\Seeders;

use App\Models\Institution;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InstitutionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            // --- JAWA TIMUR (SURABAYA & MALANG) ---
            'Universitas Airlangga (UNAIR)',
            'Institut Teknologi Sepuluh Nopember (ITS)',
            'Universitas Negeri Surabaya (UNESA)',
            'Universitas Pembangunan Nasional (UPN) "Veteran" Jawa Timur',
            'Universitas Islam Negeri Sunan Ampel (UINSA)',
            'Universitas Kristen Petra',
            'Universitas Surabaya (UBAYA)',
            'Universitas Katolik Widya Mandala Surabaya',
            'Universitas Ciputra Surabaya',
            'Universitas Brawijaya (UB)',
            'Universitas Negeri Malang (UM)',
            'Universitas Muhammadiyah Malang (UMM)',
            'Universitas Islam Malang (UNISMA)',
            'SMK Negeri 1 Surabaya',
            'SMK Negeri 2 Surabaya',
            'SMK Negeri 3 Surabaya',
            'SMK Negeri 4 Surabaya',
            'SMK Negeri 5 Surabaya',
            'SMK Negeri 6 Surabaya',
            'SMK Negeri 7 Surabaya',
            'SMK Negeri 8 Surabaya',
            'SMK Negeri 10 Surabaya',
            'SMK Negeri 12 Surabaya',
            'SMK Negeri 1 Malang',
            'SMK Negeri 2 Malang',
            'SMK Negeri 4 Malang',
            'SMK Telkom Sidoarjo',
            'SMK Telkom Malang',

            // --- DKI JAKARTA ---
            'Universitas Indonesia (UI)',
            'Universitas Pembangunan Nasional (UPN) "Veteran" Jakarta',
            'Universitas Negeri Jakarta (UNJ)',
            'Universitas Bina Nusantara (BINUS)',
            'Universitas Trisakti',
            'Universitas Tarumanagara (UNTAR)',
            'Universitas Katolik Indonesia Atma Jaya',
            'Universitas Esa Unggul',
            'Universitas Muhammadiyah Jakarta',
            'Universitas Mercu Buana',
            'Universitas Islam Negeri Syarif Hidayatullah',
            'SMK Negeri 1 Jakarta',
            'SMK Negeri 26 Jakarta',
            'SMK Negeri 57 Jakarta',
            'SMKS Wikrama Jakarta',

            // --- JAWA BARAT (BANDUNG) ---
            'Institut Teknologi Bandung (ITB)',
            'Universitas Padjadjaran (UNPAD)',
            'Universitas Pendidikan Indonesia (UPI)',
            'Universitas Telkom (Telkom University)',
            'Universitas Katolik Parahyangan (UNPAR)',
            'Universitas Komputer Indonesia (UNIKOM)',
            'Universitas Islam Negeri Sunan Gunung Djati',
            'SMK Negeri 1 Bandung',
            'SMK Negeri 4 Bandung',
            'SMK Negeri 7 Bandung',

            // --- D.I. YOGYAKARTA ---
            'Universitas Gadjah Mada (UGM)',
            'Universitas Pembangunan Nasional (UPN) "Veteran" Yogyakarta',
            'Universitas Negeri Yogyakarta (UNY)',
            'Universitas Islam Indonesia (UII)',
            'Universitas Muhammadiyah Yogyakarta (UMY)',
            'Universitas Atma Jaya Yogyakarta',
            'Universitas Sanata Dharma',
            'Universitas Amikom Yogyakarta',
            'SMK Negeri 2 Yogyakarta',
            'SMK Negeri 3 Yogyakarta',

            // --- JAWA TENGAH (SEMARANG & SURAKARTA) ---
            'Universitas Diponegoro (UNDIP)',
            'Universitas Negeri Semarang (UNNES)',
            'Universitas Sebelas Maret (UNS)',
            'Universitas Muhammadiyah Surakarta (UMS)',
            'Universitas Dian Nuswantoro (UDINUS)',
            'SMK Negeri 7 Semarang',
            'SMK Negeri 2 Surakarta',

            // --- SUMATERA (MEDAN) ---
            'Universitas Sumatera Utara (USU)',
            'Universitas Negeri Medan (UNIMED)',
            'Universitas Muhammadiyah Sumatera Utara (UMSU)',
            'Universitas Medan Area (UMA)',
            'SMK Negeri 1 Medan',
            'SMK Negeri 9 Medan',

            // --- SULAWESI (MAKASSAR) ---
            'Universitas Hasanuddin (UNHAS)',
            'Universitas Negeri Makassar (UNM)',
            'Universitas Muslim Indonesia (UMI)',
            'Universitas Islam Negeri Alauddin',
            'SMK Negeri 1 Makassar',
            'SMK Negeri 2 Makassar',
        ];

        foreach ($data as $name) {
            Institution::firstOrCreate([
                'name' => $name,
            ]);
        }
    }
}
