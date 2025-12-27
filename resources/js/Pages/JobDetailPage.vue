<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import LandingLayout from '@/Layouts/LandingLayout.vue';
import ProcessStepCard from '@/Components/Landing/ProcessStepCard.vue';
import JobStatCard from '@/Components/Landing/JobStatCard.vue';
import BaseButton from '@/Components/BaseButton.vue';

const activeTab = ref('Tentang');
const tabs = ['Tentang', 'Persyaratan', 'Benefit', 'Proses Seleksi'];

// Dummy Data
const selectionSteps = [
    {
        number: 1,
        title: 'Pilih Posisi',
        description: "Cari lowongan yang berstatus 'Terbuka' dan sesuai dengan keahlian teknis Anda.",
        icon: 'search'
    },
    {
        number: 2,
        title: 'Registrasi',
        description: "Lengkapi profil kandidat dan unggah portofolio pada halaman detail lowongan.",
        icon: 'app_registration'
    },
    {
        number: 3,
        title: 'Seleksi',
        description: "Tim HR Inosoft akan melakukan screening CV dan wawancara kompetensi.",
        icon: 'assignment_turned_in'
    },
    {
        number: 4,
        title: 'Onboarding',
        description: "Mulai periode magang dengan akses penuh ke tools dan ekosistem SINTESIS.",
        icon: 'rocket_launch'
    }
];

const stats = [
    { label: 'Total', value: 128, subLabel: 'Pelamar', icon: 'group', color: 'blue' },
    { label: 'Menunggu', value: 45, subLabel: 'Belum review', icon: 'hourglass_top', color: 'yellow' },
    { label: 'Ditinjau', value: 23, subLabel: 'Screening', icon: 'find_in_page', color: 'indigo' },
    { label: 'Ditolak', value: 54, subLabel: 'Tidak lolos', icon: 'close', color: 'red' },
    { label: 'Diterima', value: 6, subLabel: 'Onboarding', icon: 'check', color: 'green' },
];
</script>

<template>
    <LandingLayout mainClass="bg-slate-50">
        <Head title="Detail Lowongan" />
        <div class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 bg-slate-50 min-h-screen">
            <!-- Breadcrumb & Back -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <Link href="/" class="hover:text-primary transition-colors">Lowongan</Link>
                    <span class="material-symbols-outlined text-base">chevron_right</span>
                    <span class="text-slate-900 font-medium">Frontend Developer Intern</span>
                </div>
                <Link href="/" class="hidden sm:flex items-center gap-1 text-sm text-slate-500 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-lg">arrow_back</span>
                    Kembali
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
                <!-- Left Column -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 md:p-8">
                        <!-- Header -->
                        <div class="flex flex-col sm:flex-row gap-6 items-start mb-8">
                            <div class="bg-gradient-to-br from-primary to-blue-700 size-16 sm:size-20 rounded-xl flex items-center justify-center text-white shrink-0 shadow-lg">
                                <span class="material-symbols-outlined text-4xl">code</span>
                            </div>
                            <div class="flex-1 w-full">
                                <div class="flex flex-col sm:flex-row justify-between items-start gap-2">
                                    <div>
                                        <div class="flex items-center gap-3 mb-2">
                                            <span class="bg-green-50 text-green-700 text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded border border-green-100">Terbuka</span>
                                        </div>
                                        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 leading-tight mb-2">Frontend Developer Intern</h1>
                                        <p class="text-slate-500 font-medium flex items-center gap-1">
                                            Inosoft Trans Sistem
                                            <span class="text-slate-300">•</span>
                                            <span class="text-primary text-sm">Internship</span>
                                        </p>
                                    </div>
                                    <div class="flex flex-col items-end gap-1 mt-2 sm:mt-0">
                                        <button class="text-slate-400 hover:text-primary transition-colors p-1 cursor-pointer">
                                            <span class="material-symbols-outlined">bookmark</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabs -->
                        <div class="border-b border-slate-200 mb-6">
                            <div class="flex gap-2 overflow-x-auto no-scrollbar">
                                <button 
                                    v-for="tab in tabs" 
                                    :key="tab"
                                    @click="activeTab = tab"
                                    class="px-4 py-3 text-sm font-medium border-b-2 transition-all cursor-pointer whitespace-nowrap"
                                    :class="[
                                        activeTab === tab 
                                            ? 'text-primary border-primary' 
                                            : 'text-slate-500 border-transparent hover:text-primary hover:border-primary/30'
                                    ]"
                                >
                                    {{ tab }}
                                </button>
                            </div>
                        </div>

                        <!-- Tab Content -->
                        <div class="min-h-[300px]">
                            <!-- Tentang -->
                            <div v-if="activeTab === 'Tentang'" class="prose prose-slate prose-sm max-w-none text-slate-600 leading-relaxed space-y-8">
                                <section>
                                    <h3 class="text-lg font-bold text-slate-900 mb-4">Deskripsi Pekerjaan</h3>
                                    <p>
                                        Inosoft Trans Sistem membuka kesempatan magang bagi mahasiswa tingkat akhir yang memiliki passion di bidang pengembangan web frontend. Posisi ini bertujuan untuk memberikan pengalaman praktis dalam membangun antarmuka aplikasi web modern yang responsif dan user-friendly menggunakan teknologi terkini.
                                    </p>
                                    <p class="mt-4">
                                        Anda akan bekerja secara kolaboratif dalam lingkungan agile, didampingi oleh mentor profesional yang akan membantu mengembangkan skill teknis dan soft skill Anda.
                                    </p>
                                </section>
                                <section>
                                    <h3 class="text-lg font-bold text-slate-900 mb-4">Tanggung Jawab</h3>
                                    <ul class="list-disc pl-5 space-y-2 marker:text-slate-400">
                                        <li>Mengimplementasikan desain UI/UX dari Figma menjadi kode frontend yang bersih dan efisien.</li>
                                        <li>Mengoptimalkan performa aplikasi untuk kecepatan maksimal dan skalabilitas.</li>
                                        <li>Berkolaborasi dengan tim Backend untuk integrasi API dan manajemen state aplikasi.</li>
                                        <li>Melakukan debugging, troubleshooting, dan memastikan kompatibilitas lintas browser.</li>
                                        <li>Berpartisipasi aktif dalam code review dan daily stand-up meeting.</li>
                                    </ul>
                                </section>
                            </div>

                            <!-- Persyaratan -->
                            <div v-if="activeTab === 'Persyaratan'" class="prose prose-slate prose-sm max-w-none text-slate-600 leading-relaxed">
                                <section>
                                    <h3 class="text-lg font-bold text-slate-900 mb-4">Kualifikasi Minimum</h3>
                                    <ul class="list-disc pl-5 space-y-2 marker:text-slate-400">
                                        <li>Mahasiswa aktif semester akhir atau fresh graduate jurusan TI/SI.</li>
                                        <li>Memahami dasar-dasar HTML5, CSS3, dan JavaScript (ES6+).</li>
                                        <li>Familiar dengan framework React.js atau Vue.js adalah nilai plus.</li>
                                        <li>Memiliki kemampuan problem solving yang baik dan eager to learn.</li>
                                    </ul>
                                </section>
                            </div>

                             <!-- Benefit -->
                             <div v-if="activeTab === 'Benefit'" class="prose prose-slate prose-sm max-w-none text-slate-600 leading-relaxed">
                                <p>Benefit details placeholder...</p>
                            </div>

                            <!-- Proses Seleksi -->
                            <div v-if="activeTab === 'Proses Seleksi'">
                                <div class="grid gap-6">
                                     <ProcessStepCard 
                                        v-for="step in selectionSteps" 
                                        :key="step.number"
                                        v-bind="step"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column (Sidebar) -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 sticky top-24">
                        <h3 class="font-bold text-slate-900 mb-5 pb-4 border-b border-slate-100">Informasi Lowongan</h3>
                        
                        <div class="space-y-5 mb-8">
                            <div class="flex items-start gap-3.5">
                                <div class="bg-slate-50 p-2 rounded-lg text-slate-400 shrink-0">
                                    <span class="material-symbols-outlined text-[20px]">location_on</span>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 font-medium mb-0.5">Lokasi</p>
                                    <p class="text-sm font-semibold text-slate-800">Surabaya (On-site)</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3.5">
                                <div class="bg-slate-50 p-2 rounded-lg text-slate-400 shrink-0">
                                    <span class="material-symbols-outlined text-[20px]">payments</span>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 font-medium mb-0.5">Uang Saku</p>
                                    <p class="text-sm font-semibold text-slate-800">Kompetitif</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3.5">
                                <div class="bg-slate-50 p-2 rounded-lg text-slate-400 shrink-0">
                                    <span class="material-symbols-outlined text-[20px]">calendar_month</span>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 font-medium mb-0.5">Durasi</p>
                                    <p class="text-sm font-semibold text-slate-800">3 - 6 Bulan</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3.5">
                                <div class="bg-slate-50 p-2 rounded-lg text-slate-400 shrink-0">
                                    <span class="material-symbols-outlined text-[20px]">event_busy</span>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 font-medium mb-0.5">Batas Daftar</p>
                                    <p class="text-sm font-semibold text-slate-800">25 Agustus 2023</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3">
                            <BaseButton variant="primary" size="lg" full>
                                Daftar Sekarang
                            </BaseButton>
                            <BaseButton variant="outline" size="lg" full>
                                <span class="material-symbols-outlined text-[20px]">share</span>
                                Bagikan
                            </BaseButton>
                        </div>
                        <div class="mt-4 text-center">
                            <p class="text-sm text-slate-400">
                                Login untuk melamar posisi ini.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Section -->
            <div class="pt-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-slate-900">Status & Statistik Pelamar</h3>
                    <span class="hidden sm:inline-block text-xs font-medium text-slate-500 bg-white border border-slate-200 px-3 py-1 rounded-full">
                        Data Real-time
                    </span>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <JobStatCard 
                        v-for="stat in stats"
                        :key="stat.label"
                        v-bind="stat"
                    />
                </div>
            </div>
        </div>
    </LandingLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
