<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    LayoutList, 
    Filter, 
    Plus, 
    Calendar, 
    MoreHorizontal, 
    ChevronRight, 
    Search,
    Clock,
    CheckCircle2,
    AlertCircle,
    Paperclip
} from 'lucide-vue-next';

defineOptions({ layout: AuthenticatedLayout });

const activeTab = ref('Semua');
const tabs = ['Semua', 'Sedang Dikerjakan', 'Review', 'Selesai'];

// Mock Data
const tasks = [
    {
        id: 1,
        title: 'Analisis Kompetitor Q2',
        description: 'Buat laporan mendalam tentang strategi harga kompetitor utama untuk kuartal kedua.',
        deadline: '2024-10-25',
        priority: 'High',
        status: 'Sedang Dikerjakan',
        progress: 65,
        project: 'Marketing Research'
    },
    {
        id: 2,
        title: 'Redesign Landing Page',
        description: 'Usulkan mockup baru untuk halaman utama dengan fokus pada konversi user mobile.',
        deadline: '2024-10-28',
        priority: 'Medium',
        status: 'Review',
        progress: 90,
        project: 'UI/UX Design'
    },
    {
        id: 3,
        title: 'Implementasi API Auth',
        description: 'Integrasi endpoint login dan register dengan validasi yang ketat.',
        deadline: '2024-10-20',
        priority: 'High',
        status: 'Selesai',
        progress: 100,
        project: 'Backend Development'
    },
    {
        id: 4,
        title: 'Dokumentasi Sistem',
        description: 'Lengkapi dokumentasi teknis untuk modul pembayaran.',
        deadline: '2024-10-30',
        priority: 'Low',
        status: 'Sedang Dikerjakan',
        progress: 25,
        project: 'Documentation'
    },
];

const getPriorityColor = (priority) => {
    switch(priority.toLowerCase()) {
        case 'high': return 'bg-red-50 text-red-600 border-red-200';
        case 'medium': return 'bg-amber-50 text-amber-600 border-amber-200';
        case 'low': return 'bg-blue-50 text-blue-600 border-blue-200';
        default: return 'bg-slate-50 text-slate-600 border-slate-200';
    }
};

const getStatusColor = (status) => {
    switch(status.toLowerCase()) {
        case 'sdg dikerjakan': case 'sedang dikerjakan': return 'text-primary';
        case 'review': return 'text-amber-500';
        case 'selesai': return 'text-emerald-500';
        default: return 'text-slate-500';
    }
};
</script>

<template>
    <Head title="Tugas Saya" />

    <div class="flex flex-col gap-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Tugas Saya</h1>
                <p class="text-slate-500 mt-1">Kelola dan pantau progres pekerjaan Anda di sini.</p>
            </div>
            <div class="flex items-center gap-3">
                 <button class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-600 font-medium hover:bg-slate-50 hover:border-slate-300 transition-all shadow-sm">
                    <Filter class="w-4 h-4" />
                    <span>Filter</span>
                </button>
                <button class="flex items-center gap-2 px-4 py-2.5 bg-primary text-white rounded-xl font-semibold hover:bg-primary-hover shadow-lg shadow-primary/25 transition-all active:scale-95">
                    <Plus class="w-4 h-4" />
                    <span>Tugas Baru</span>
                </button>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-2 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex p-1 bg-slate-100 rounded-xl w-full sm:w-auto overflow-x-auto">
                <button 
                    v-for="tab in tabs" 
                    :key="tab"
                    @click="activeTab = tab"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-all whitespace-nowrap min-w-[80px]"
                    :class="activeTab === tab ? 'bg-white text-primary shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-200/50'"
                >
                    {{ tab }}
                </button>
            </div>
            <div class="relative w-full sm:w-64">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                <input 
                    type="text" 
                    placeholder="Cari tugas..." 
                    class="w-full pl-10 pr-4 py-2 bg-slate-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all placeholder:text-slate-400"
                >
            </div>
        </div>

        <!-- Task Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="task in tasks" :key="task.id" class="group bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:shadow-md hover:border-primary/30 transition-all flex flex-col h-full relative overflow-hidden">
                <!-- Top Decorator -->
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-primary/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>

                <div class="flex justify-between items-start mb-4">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border" :class="getPriorityColor(task.priority)">
                        {{ task.priority }}
                    </span>
                    <button class="text-slate-400 hover:text-primary transition-colors">
                        <MoreHorizontal class="w-5 h-5" />
                    </button>
                </div>

                <div class="mb-4 flex-1">
                    <p class="text-xs font-semibold text-primary mb-1">{{ task.project }}</p>
                    <h3 class="text-lg font-bold text-slate-900 leading-snug group-hover:text-primary transition-colors mb-2">{{ task.title }}</h3>
                    <p class="text-sm text-slate-500 line-clamp-2">{{ task.description }}</p>
                </div>

                <!-- Info Row -->
                <div class="flex items-center gap-4 text-xs text-slate-500 mb-6 font-medium">
                    <div class="flex items-center gap-1.5" :class="{'text-red-500': task.status === 'Review'}">
                        <Calendar class="w-3.5 h-3.5" />
                        {{ task.deadline }}
                    </div>
                </div>

                <!-- Progress & Footer -->
                <div class="mt-auto">
                    <div class="flex justify-between items-center text-xs font-semibold mb-2">
                        <span :class="getStatusColor(task.status)">{{ task.status }}</span>
                        <span class="text-slate-700">{{ task.progress }}%</span>
                    </div>
                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden mb-5">
                        <div 
                            class="h-full rounded-full transition-all duration-500 relative" 
                            :class="task.progress === 100 ? 'bg-emerald-500' : 'bg-primary'"
                            :style="{ width: `${task.progress}%` }"
                        ></div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <button class="flex items-center justify-center gap-2 px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold transition-all">
                            Lihat Detail
                        </button>
                         <button class="flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-all shadow-lg shadow-slate-900/20">
                            <Paperclip class="w-3.5 h-3.5" />
                            Kirim Tugas
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Add New Placeholder -->
            <button class="group flex flex-col items-center justify-center gap-4 min-h-[300px] border-2 border-dashed border-slate-200 rounded-2xl hover:border-primary/50 hover:bg-blue-50/50 transition-all">
                <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center group-hover:bg-white group-hover:scale-110 group-hover:shadow-lg transition-all text-slate-400 group-hover:text-primary">
                    <Plus class="w-8 h-8" />
                </div>
                <div class="text-center">
                    <h3 class="font-bold text-slate-700 group-hover:text-primary transition-colors">Buat Tugas Baru</h3>
                    <p class="text-xs text-slate-400 mt-1 px-8">Tambahkan tugas mandiri atau inisiatif baru</p>
                </div>
            </button>
        </div>
    </div>
</template>
