<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

defineOptions({ layout: AuthenticatedLayout }); 
   
import { 
    Clock, 
    MapPin, 
    CheckCircle2, 
    XCircle, 
    AlertTriangle, 
    Filter,
    ChevronLeft,
    ChevronRight,
    Search,
    Calendar
} from 'lucide-vue-next';


// Mock Statistics
const stats = [
    {
        label: 'Total Hadir',
        value: '18',
        unit: 'Hari',
        color: 'bg-emerald-50 text-emerald-600',
        icon: CheckCircle2
    },
    {
        label: 'Terlambat',
        value: '2',
        unit: 'Hari',
        color: 'bg-amber-50 text-amber-600',
        icon: AlertTriangle
    },
    {
        label: 'Izin / Sakit',
        value: '1',
        unit: 'Hari',
        color: 'bg-blue-50 text-blue-600',
        icon: Calendar
    },
    {
        label: 'Alpha',
        value: '0',
        unit: 'Hari',
        color: 'bg-red-50 text-red-600',
        icon: XCircle
    }
];

// Mock Attendance Data
const attendanceHistory = ref([
    {
        id: 1,
        date: '2024-10-24',
        checkIn: '07:55',
        checkOut: '17:05',
        location: 'Kantor Pusat',
        status: 'Hadir',
        statusColor: 'text-emerald-600 bg-emerald-50 border-emerald-200',
        notes: '-'
    },
    {
        id: 2,
        date: '2024-10-23',
        checkIn: '08:15',
        checkOut: '17:10',
        location: 'Kantor Pusat',
        status: 'Terlambat',
        statusColor: 'text-amber-600 bg-amber-50 border-amber-200',
        notes: 'Macet di tol dalam kota'
    },
    {
        id: 3,
        date: '2024-10-22',
        checkIn: '07:50',
        checkOut: '17:00',
        location: 'WFH',
        status: 'Hadir',
        statusColor: 'text-emerald-600 bg-emerald-50 border-emerald-200',
        notes: 'Izin WFH Approved'
    },
    {
        id: 4,
        date: '2024-10-21',
        checkIn: '-',
        checkOut: '-',
        location: '-',
        status: 'Sakit',
        statusColor: 'text-blue-600 bg-blue-50 border-blue-200',
        notes: 'Surat dokter terlampir'
    },
    {
        id: 5,
        date: '2024-10-18',
        checkIn: '07:58',
        checkOut: '17:02',
        location: 'Kantor Pusat',
        status: 'Hadir',
        statusColor: 'text-emerald-600 bg-emerald-50 border-emerald-200',
        notes: '-'
    },
]);

const selectedPeriod = ref('Oktober 2024');
</script>

<template>
    <Head title="Riwayat Absensi" />

    <div class="flex flex-col gap-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Riwayat Absensi</h1>
                <p class="text-slate-500 mt-1">Pantau kehadiran dan kedisiplinan Anda.</p>
            </div>
            <div class="flex items-center gap-3">
                 <button class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-600 font-medium hover:bg-slate-50 hover:border-slate-300 transition-all shadow-sm">
                    <Calendar class="w-4 h-4" />
                    <span>{{ selectedPeriod }}</span>
                </button>
                <button class="flex items-center gap-2 px-4 py-2.5 bg-primary text-white rounded-xl font-semibold hover:bg-primary-hover shadow-lg shadow-primary/25 transition-all active:scale-95">
                    <Filter class="w-4 h-4" />
                    <span>Filter</span>
                </button>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div v-for="(stat, index) in stats" :key="index" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col gap-3 relative overflow-hidden group hover:shadow-md transition-all">
                <div class="flex items-center justify-between z-10">
                    <span class="text-sm font-medium text-slate-500">{{ stat.label }}</span>
                    <div class="p-2 rounded-lg" :class="stat.color">
                        <component :is="stat.icon" class="w-4 h-4" />
                    </div>
                </div>
                <div class="flex items-baseline gap-1 z-10">
                    <h2 class="text-3xl font-bold text-slate-900">{{ stat.value }}</h2>
                    <span class="text-xs text-slate-400 font-medium">{{ stat.unit }}</span>
                </div>
                 <!-- Decorative BG -->
                <div class="absolute -right-4 -bottom-4 w-20 h-20 rounded-full opacity-5 transition-transform group-hover:scale-110" :class="stat.color.replace('text-', 'bg-').replace('bg-', 'bg-')"></div>
            </div>
        </div>

        <!-- Attendance Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
             <!-- Toolbar -->
            <div class="p-4 border-b border-slate-200 flex flex-col sm:flex-row justify-between gap-4 bg-slate-50/50">
                <div class="relative w-full sm:w-72">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                    <input 
                        type="text" 
                        placeholder="Cari berdasarkan tanggal..." 
                        class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all placeholder:text-slate-400"
                    >
                </div>
                <!-- Pagination Buttons Placeholder -->
                 <div class="flex gap-2">
                    <button class="p-2 border border-slate-200 rounded-lg hover:bg-slate-50 text-slate-500 disabled:opacity-50 transition-colors">
                        <ChevronLeft class="w-4 h-4" />
                    </button>
                    <button class="p-2 border border-slate-200 rounded-lg hover:bg-slate-50 text-slate-500 disabled:opacity-50 transition-colors">
                        <ChevronRight class="w-4 h-4" />
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500 font-semibold uppercase text-xs tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4">Tanggal</th>
                            <th class="px-6 py-4">Jam Masuk</th>
                            <th class="px-6 py-4">Jam Pulang</th>
                            <th class="px-6 py-4">Lokasi</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="item in attendanceHistory" :key="item.id" class="group hover:bg-blue-50/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-slate-900">
                                {{ item.date }}
                            </td>
                            <td class="px-6 py-4 text-slate-600 font-mono">
                                {{ item.checkIn }}
                            </td>
                            <td class="px-6 py-4 text-slate-600 font-mono">
                                {{ item.checkOut }}
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                <div class="flex items-center gap-1.5">
                                    <MapPin class="w-3.5 h-3.5 text-slate-400" />
                                    {{ item.location }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border" :class="item.statusColor">
                                    {{ item.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500 italic truncate max-w-[200px]">
                                {{ item.notes }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
             <div class="p-4 border-t border-slate-200 text-center text-xs text-slate-400">
                Menampilkan 5 data terakhir
            </div>
        </div>
    </div>
</template>
