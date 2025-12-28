<script setup>
import InternLayout from '@/Layouts/InternLayout.vue';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

defineOptions({ layout: InternLayout });

const mapContainer = ref(null);
// ... existing onMounted ...
onMounted(() => {
    if (mapContainer.value) {
        const map = L.map(mapContainer.value).setView([-6.2088, 106.8456], 13); // Jakarta Coordinates
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        // Add a marker/circle for "Kantor Pusat"
        L.marker([-6.2088, 106.8456]).addTo(map)
            .bindPopup('Kantor Pusat, Menara A')
            .openPopup();
            
        L.circle([-6.2088, 106.8456], {
            color: 'blue',
            fillColor: '#0b5daf',
            fillOpacity: 0.1,
            radius: 500
        }).addTo(map);
    }
});
</script>

<template>
    <Head title="Dasbor Intern - Pusat Eksekusi" />

    <!-- Header -->
    <div class="flex flex-wrap justify-between items-end gap-4">
        <div class="flex flex-col gap-1">
            <p class="text-text-main text-3xl md:text-4xl font-black leading-tight tracking-tight">Selamat Pagi, {{ $page.props.auth.user.name.split(' ')[0] }}</p>
            <p class="text-text-secondary text-base font-medium">Kamis, 24 Okt • Jadikan hari ini bermakna</p>
        </div>
        <div class="hidden sm:flex items-center gap-2 bg-white rounded-full px-4 py-2 border border-slate-200 shadow-sm">
            <span class="relative flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-success"></span>
            </span>
            <span class="text-sm font-semibold text-slate-700">Online</span>
        </div>
    </div>

    <!-- Attendance Section -->
    <section class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold flex items-center gap-2 text-text-main">
                <span class="material-symbols-outlined text-primary">pin_drop</span>
                Absensi Kehadiran
            </h2>
            <span class="text-xs font-mono bg-slate-100 px-3 py-1 rounded-full text-text-secondary border border-slate-200">GPS: Akurasi ±5m</span>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Map -->
            <div class="relative group overflow-hidden rounded-xl h-64 lg:h-80 w-full bg-slate-100 border border-slate-200">
                <div ref="mapContainer" class="absolute inset-0 z-0"></div>
                
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent flex items-end p-4 pointer-events-none z-10">
                    <div class="bg-white/95 backdrop-blur-sm p-3 rounded-lg flex items-center gap-3 shadow-lg border border-slate-100 pointer-events-auto">
                        <span class="material-symbols-outlined text-primary">apartment</span>
                        <div>
                            <p class="text-[10px] font-bold uppercase text-text-secondary">Zona Saat Ini</p>
                            <p class="text-sm font-bold text-slate-900">Kantor Pusat, Menara A</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Camera / Action -->
            <div class="flex flex-col gap-4 h-full">
                <div class="relative flex-1 bg-slate-800 rounded-xl overflow-hidden min-h-[200px] flex items-center justify-center group border border-slate-200">
                        <!-- Camera Placeholder -->
                    <div class="absolute inset-0 bg-slate-700 flex items-center justify-center">
                            <span class="material-symbols-outlined text-slate-500 text-6xl">photo_camera</span>
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="border-2 border-white/40 w-24 h-24 rounded-full flex items-center justify-center backdrop-blur-sm bg-white/10">
                            <span class="material-symbols-outlined text-white text-4xl">photo_camera</span>
                        </div>
                    </div>
                    <div class="absolute top-4 right-4 bg-danger text-white text-[10px] font-bold px-2 py-1 rounded animate-pulse">LANGSUNG</div>
                </div>
                <button class="w-full py-4 bg-success hover:bg-[#059669] text-white rounded-xl font-bold text-lg shadow-lg shadow-success/20 transition-all active:scale-95 flex items-center justify-center gap-3 group/btn cursor-pointer">
                    <span class="bg-white/20 rounded-full p-1 group-hover/btn:rotate-12 transition-transform">
                        <span class="material-symbols-outlined block">fingerprint</span>
                    </span>
                    Masuk Sekarang - 08:58
                </button>
            </div>
        </div>
    </section>

    <!-- Tasks and Logbook Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        <!-- Task Summary -->
        <div class="xl:col-span-2 flex flex-col gap-4">
            <div class="flex items-center justify-between px-1">
                <h3 class="text-lg font-bold text-text-main">Ringkasan Tugas</h3>
                <div class="flex bg-slate-200 rounded-lg p-1">
                    <button class="px-4 py-1.5 rounded-md bg-white text-primary shadow-sm text-xs font-semibold transition-all">Semua</button>
                    <button class="px-4 py-1.5 rounded-md text-text-secondary hover:text-text-main hover:bg-white/50 text-xs font-medium transition-all">Tenggat Hari Ini</button>
                    <button class="px-4 py-1.5 rounded-md text-text-secondary hover:text-text-main hover:bg-white/50 text-xs font-medium transition-all">Terlewat</button>
                </div>
            </div>
            
            <div class="flex flex-col gap-3">
                <!-- Task Item 1 -->
                <div class="group flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 rounded-xl bg-white border border-slate-200 hover:border-primary/40 transition-colors shadow-sm">
                    <div class="flex items-start gap-4">
                        <div class="mt-1 p-2 rounded-full bg-blue-50 text-blue-600 border border-blue-100">
                            <span class="material-symbols-outlined text-xl">layers</span>
                        </div>
                        <div>
                            <h4 class="font-bold text-text-main leading-tight">Laporan Analisis Kompetitor</h4>
                            <p class="text-sm text-text-secondary mt-1">Penelitian & Pengembangan • Tenggat 17:00</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between sm:justify-end gap-4 w-full sm:w-auto">
                        <!-- Avatars placeholder -->
                        <div class="flex -space-x-2">
                            <div class="w-8 h-8 rounded-full border-2 border-white bg-slate-300 flex items-center justify-center text-[10px] font-bold text-slate-500">A</div>
                            <div class="w-8 h-8 rounded-full border-2 border-white bg-slate-300 flex items-center justify-center text-[10px] font-bold text-slate-500">B</div>
                        </div>
                        <span class="whitespace-nowrap px-3 py-1 rounded-full border border-blue-200 bg-blue-50 text-blue-700 text-xs font-bold uppercase tracking-wider">
                            Sedang Berjalan
                        </span>
                    </div>
                </div>

                <!-- Task Item 2 -->
                <div class="group flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 rounded-xl bg-white border border-slate-200 hover:border-primary/40 transition-colors shadow-sm">
                    <div class="flex items-start gap-4">
                        <div class="mt-1 p-2 rounded-full bg-purple-50 text-purple-600 border border-purple-100">
                            <span class="material-symbols-outlined text-xl">palette</span>
                        </div>
                        <div>
                            <h4 class="font-bold text-text-main leading-tight">Perbarui Komponen UI Kit</h4>
                            <p class="text-sm text-text-secondary mt-1">Sistem Desain • Tenggat Besok</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between sm:justify-end gap-4 w-full sm:w-auto">
                            <div class="flex -space-x-2">
                            <div class="w-8 h-8 rounded-full border-2 border-white bg-slate-300 flex items-center justify-center text-[10px] font-bold text-slate-500">C</div>
                        </div>
                        <span class="whitespace-nowrap px-3 py-1 rounded-full border border-slate-200 bg-slate-100 text-slate-600 text-xs font-bold uppercase tracking-wider">
                            Akan Dikerjakan
                        </span>
                    </div>
                </div>

                <!-- Task Item 3 (Completed) -->
                <div class="group flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 rounded-xl bg-white border border-slate-200 opacity-70 hover:opacity-100 transition-opacity shadow-sm">
                    <div class="flex items-start gap-4">
                        <div class="mt-1 p-2 rounded-full bg-green-50 text-green-600 border border-green-100">
                            <span class="material-symbols-outlined text-xl">check_circle</span>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800 leading-tight line-through decoration-slate-400">Tinjauan Analitik Bulanan</h4>
                            <p class="text-sm text-text-secondary mt-1">Pemasaran • Selesai 2 jam lalu</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between sm:justify-end gap-4 w-full sm:w-auto">
                        <span class="whitespace-nowrap px-3 py-1 rounded-full border border-green-200 bg-green-50 text-green-700 text-xs font-bold uppercase tracking-wider">
                            Selesai
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logbook Summary -->
        <div class="xl:col-span-1 flex flex-col gap-4">
            <div class="flex items-center justify-between px-1">
                <h3 class="text-lg font-bold text-text-main">Buku Log Terbaru</h3>
                <button class="text-primary hover:text-primary-hover text-sm font-semibold flex items-center gap-1 transition-colors">
                    Lihat Semua <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </button>
            </div>
            <div class="flex flex-col bg-white rounded-2xl p-5 shadow-sm border border-slate-200 h-full min-h-[300px]">
                <button class="w-full mb-6 py-3 border-2 border-dashed border-slate-300 rounded-xl text-text-secondary hover:border-primary hover:text-primary hover:bg-blue-50 transition-all flex items-center justify-center gap-2 group">
                    <span class="material-symbols-outlined group-hover:scale-110 transition-transform">edit_note</span>
                    <span class="font-medium">Tulis Refleksi Harian</span>
                </button>
                <div class="flex flex-col gap-6 relative">
                    <div class="absolute left-3 top-2 bottom-2 w-0.5 bg-slate-200 z-0"></div>
                    
                    <!-- Log Entry 1 -->
                    <div class="flex gap-4 relative z-10">
                        <div class="flex-shrink-0 w-6 h-6 rounded-full bg-primary border-4 border-white shadow-sm mt-1"></div>
                        <div class="flex flex-col gap-1">
                            <div class="flex justify-between items-baseline">
                                <span class="text-sm font-bold text-text-main">Kemarin</span>
                                <span class="text-[10px] text-text-secondary">23 Okt</span>
                            </div>
                            <p class="text-sm text-slate-600 leading-relaxed line-clamp-2">
                                Memperbaiki bug API yang menyebabkan latensi pada modul profil pengguna. Belajar banyak tentang optimalisasi query SQL.
                            </p>
                        </div>
                    </div>

                        <!-- Log Entry 2 -->
                    <div class="flex gap-4 relative z-10">
                        <div class="flex-shrink-0 w-6 h-6 rounded-full bg-slate-300 border-4 border-white shadow-sm mt-1"></div>
                        <div class="flex flex-col gap-1">
                            <div class="flex justify-between items-baseline">
                                <span class="text-sm font-bold text-text-main">Selasa</span>
                                <span class="text-[10px] text-text-secondary">22 Okt</span>
                            </div>
                            <p class="text-sm text-slate-600 leading-relaxed line-clamp-2">
                                Menghadiri sinkronisasi tim. Mulai menyusun analisis kompetitor. Perlu bertanya pada Sarah tentang sumber data.
                            </p>
                        </div>
                    </div>

                        <!-- Log Entry 3 -->
                    <div class="flex gap-4 relative z-10">
                        <div class="flex-shrink-0 w-6 h-6 rounded-full bg-slate-300 border-4 border-white shadow-sm mt-1"></div>
                        <div class="flex flex-col gap-1">
                            <div class="flex justify-between items-baseline">
                                <span class="text-sm font-bold text-text-main">Senin</span>
                                <span class="text-[10px] text-text-secondary">21 Okt</span>
                            </div>
                            <p class="text-sm text-slate-600 leading-relaxed line-clamp-2">
                                Hari pertama sprint 4. Menyiapkan lingkungan lokal dan menarik perubahan repo terbaru.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
/* Leaflet Map Height Fix */
.leaflet-container {
    width: 100%;
    height: 100%;
    z-index: 1;
}
</style>
