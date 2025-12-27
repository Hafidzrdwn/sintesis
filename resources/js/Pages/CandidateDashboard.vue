<script setup>
import AuthenticatedUserLayout from '@/Layouts/AuthenticatedUserLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dasbor Calon Intern" />

    <AuthenticatedUserLayout>
        <!-- Header -->
        <header class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Dasbor Calon Intern</h1>
                <p class="mt-1 text-sm text-gray-500">Pantau status lamaran aktif Anda dan langkah selanjutnya.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                    <span class="mr-1.5 flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    Akun Aktif
                </span>
            </div>
        </header>

        <div class="flex flex-col gap-6">
            <!-- Application Status Card -->
            <section class="bg-white rounded-xl border border-gray-200 shadow-[0_2px_8px_rgba(0,0,0,0.04)] overflow-hidden">
                <div class="grid lg:grid-cols-5 h-full">
                    <!-- Left Column: Job Info -->
                    <div class="lg:col-span-3 p-6 sm:p-8 flex flex-col border-b lg:border-b-0 lg:border-r border-gray-200 bg-gray-50/50">
                        <div class="flex-1">
                            <div class="flex items-start gap-5 mb-6">
                                <div class="h-16 w-16 min-w-[4rem] rounded-xl bg-white border border-gray-200 p-3 shadow-sm flex items-center justify-center overflow-hidden">
                                     <!-- Placeholder for Job Image/Icon -->
                                    <div class="w-full h-full bg-contain bg-center bg-no-repeat flex items-center justify-center text-gray-400">
                                         <span class="material-symbols-outlined text-[32px]">work</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide bg-blue-100 text-blue-700 border border-blue-200">Internship</span>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide bg-purple-100 text-purple-700 border border-purple-200">Remote</span>
                                    </div>
                                    <h2 class="text-xl font-bold text-gray-900 leading-tight">UI/UX Designer Intern</h2>
                                    <p class="text-sm text-gray-500 mt-1 font-mono">#REQ-2023-001</p>
                                </div>
                            </div>

                            <div class="bg-amber-50 border-l-4 border-amber-400 rounded-r-lg p-4 mb-6">
                                <h3 class="text-sm font-bold text-amber-900 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[18px]">hourglass_top</span>
                                    Lamaran Sedang Ditinjau
                                </h3>
                                <p class="text-xs text-amber-800 mt-1.5 leading-relaxed font-body">
                                    Tim HRD kami sedang meninjau berkas lamaran Anda. Proses ini biasanya memakan waktu 3-5 hari kerja. Hasil seleksi akan diumumkan melalui email dan dasbor ini.
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-y-4 gap-x-8 text-sm border-t border-gray-200 pt-5 mt-auto">
                                <div>
                                    <p class="text-gray-500 text-xs uppercase font-bold tracking-wider mb-1">Tanggal Melamar</p>
                                    <p class="font-semibold text-gray-900">12 Oktober 2023</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 text-xs uppercase font-bold tracking-wider mb-1">Departemen</p>
                                    <p class="font-semibold text-gray-900">Product Design</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-6 pt-2">
                            <button class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg bg-white border border-gray-300 px-5 py-2.5 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50 hover:text-primary hover:border-primary/30 transition-all focus:ring-2 focus:ring-primary/20 cursor-pointer">
                                <span class="material-symbols-outlined text-[18px]">visibility</span>
                                Lihat Detail Lamaran
                            </button>
                        </div>
                    </div>

                    <!-- Right Column: Timeline -->
                    <div class="lg:col-span-2 p-6 sm:p-8 bg-white flex flex-col">
                        <h3 class="font-bold text-gray-900 mb-6 flex items-center gap-2 pb-4 border-b border-gray-100">
                            <span class="material-symbols-outlined text-primary">history_edu</span>
                            Riwayat Progres
                        </h3>
                        <div class="relative pl-1 flex-1">
                            <!-- Step 1: Completed -->
                            <div class="relative flex gap-4 pb-8 group">
                                <div class="flex flex-col items-center relative z-10">
                                    <div class="h-8 w-8 rounded-full bg-primary flex items-center justify-center shadow-md shadow-primary/20 transition-transform group-hover:scale-110">
                                        <span class="material-symbols-outlined text-white text-[16px] font-bold">check</span>
                                    </div>
                                    <div class="w-0.5 h-full bg-primary/20 absolute top-8"></div>
                                </div>
                                <div class="pt-1.5">
                                    <p class="text-sm font-bold text-gray-900">Registrasi Berhasil</p>
                                    <p class="text-xs text-gray-500 mt-0.5 font-medium">10 Okt 2023, 09:00 WIB</p>
                                </div>
                            </div>

                            <!-- Step 2: Completed -->
                            <div class="relative flex gap-4 pb-8 group">
                                <div class="flex flex-col items-center relative z-10">
                                    <div class="h-8 w-8 rounded-full bg-primary flex items-center justify-center shadow-md shadow-primary/20 transition-transform group-hover:scale-110">
                                        <span class="material-symbols-outlined text-white text-[16px] font-bold">mark_email_read</span>
                                    </div>
                                    <div class="w-0.5 h-full bg-primary/20 absolute top-8"></div>
                                </div>
                                <div class="pt-1.5">
                                    <p class="text-sm font-bold text-gray-900">Lamaran Terkirim</p>
                                    <p class="text-xs text-gray-500 mt-0.5 font-medium">12 Okt 2023, 14:30 WIB</p>
                                </div>
                            </div>

                            <!-- Step 3: Active (Pulse) -->
                            <div class="relative flex gap-4 pb-8">
                                <div class="flex flex-col items-center relative z-10">
                                    <div class="h-8 w-8 rounded-full bg-white border-2 border-amber-400 flex items-center justify-center shadow-sm relative">
                                        <div class="h-2.5 w-2.5 rounded-full bg-amber-400 animate-pulse"></div>
                                    </div>
                                    <div class="w-0.5 h-full bg-gray-100 absolute top-8"></div>
                                </div>
                                <div class="pt-1.5">
                                    <p class="text-sm font-bold text-amber-600">Seleksi Administrasi</p>
                                    <span class="inline-flex mt-1 items-center px-2 py-0.5 rounded text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-100">
                                        Sedang Berlangsung
                                    </span>
                                </div>
                            </div>

                            <!-- Step 4: Pending -->
                            <div class="relative flex gap-4">
                                <div class="flex flex-col items-center relative z-10">
                                    <div class="h-8 w-8 rounded-full bg-gray-50 border border-gray-200 flex items-center justify-center text-gray-300">
                                        <span class="material-symbols-outlined text-[18px]">groups</span>
                                    </div>
                                </div>
                                <div class="pt-1.5">
                                    <p class="text-sm font-medium text-gray-400">Wawancara User</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Menunggu jadwal</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Bottom Section -->
            <section class="grid md:grid-cols-3 gap-6">
                <!-- Promo Card -->
                <div class="md:col-span-2 bg-gradient-to-br from-white to-blue-50 rounded-xl border border-gray-200 p-6 flex flex-col-reverse sm:flex-row items-center gap-6 relative overflow-hidden shadow-[0_2px_8px_rgba(0,0,0,0.04)] group">
                    <div class="absolute top-0 right-0 w-40 h-40 bg-primary/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>
                    <div class="flex-1 z-10 relative">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white border border-blue-100 text-[10px] font-bold uppercase text-primary mb-3 shadow-sm">
                            <span class="material-symbols-outlined text-[14px]">stars</span>
                            <span>Peluang Baru</span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Masih mencari posisi yang lebih cocok?</h3>
                        <p class="text-sm text-gray-600 mb-5 leading-relaxed font-body">
                            Jangan lewatkan kesempatan emas lainnya. Kami membuka lowongan baru setiap minggunya di berbagai divisi.
                        </p>
                        <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary text-white px-5 py-2.5 text-sm font-bold shadow-md shadow-primary/20 hover:bg-primary-hover hover:shadow-lg hover:shadow-primary/30 transition-all duration-200 cursor-pointer">
                            <span>Jelajahi Lowongan</span>
                            <span class="material-symbols-outlined text-[18px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
                        </button>
                    </div>
                    <!-- Using a div for background image if no actual image available, or use placeholder -->
                    <div class="w-full sm:w-1/3 min-h-[160px] rounded-lg bg-cover bg-center shadow-inner border border-white/50 relative overflow-hidden bg-slate-200 flex items-center justify-center">
                         <span class="material-symbols-outlined text-4xl text-slate-300">image</span>
                         <div class="absolute inset-0 bg-primary/10 group-hover:bg-primary/0 transition-colors duration-500"></div>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="md:col-span-1 bg-white rounded-xl border border-gray-200 p-6 shadow-[0_2px_8px_rgba(0,0,0,0.04)] flex flex-col justify-center">
                    <h3 class="text-base font-bold text-gray-900 mb-4">Butuh Bantuan?</h3>
                    <ul class="space-y-3">
                        <li>
                            <a href="#" class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 border border-transparent hover:bg-white hover:border-gray-200 hover:shadow-sm transition-all group">
                                <div class="h-8 w-8 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-400 group-hover:text-primary transition-colors">
                                    <span class="material-symbols-outlined text-[18px]">help_center</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-bold text-gray-700 group-hover:text-primary transition-colors">Pusat Bantuan</p>
                                    <p class="text-[10px] text-gray-500">FAQ & Panduan</p>
                                </div>
                                <span class="material-symbols-outlined text-gray-300 text-[18px] group-hover:text-primary">chevron_right</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 border border-transparent hover:bg-white hover:border-gray-200 hover:shadow-sm transition-all group">
                                <div class="h-8 w-8 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-400 group-hover:text-primary transition-colors">
                                    <span class="material-symbols-outlined text-[18px]">support_agent</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-bold text-gray-700 group-hover:text-primary transition-colors">Hubungi HR</p>
                                    <p class="text-[10px] text-gray-500">Senin - Jumat, 09:00 - 17:00</p>
                                </div>
                                <span class="material-symbols-outlined text-gray-300 text-[18px] group-hover:text-primary">chevron_right</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </section>
        </div>
    </AuthenticatedUserLayout>
</template>
