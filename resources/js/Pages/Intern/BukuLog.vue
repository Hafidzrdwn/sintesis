<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    Plus, 
    Search, 
    FileText, 
    ExternalLink, 
    CheckCircle2, 
    XCircle, 
    Clock, 
    ChevronRight,
    Calendar,
    BookOpen
} from 'lucide-vue-next';

defineOptions({ layout: AuthenticatedLayout });

const searchQuery = ref('');
const showDetailModal = ref(false);
const selectedLog = ref(null);

// Mock Data
const logs = ref([
    {
        id: 1,
        date: '2024-10-24',
        activity: 'Memperbaiki bug API pada modul User Profile dan optimasi query.',
        output: 'Pull Request #42',
        outputUrl: '#',
        status: 'Approved',
        feedback: 'Kerja bagus, query execution time berkurang drastis.'
    },
    {
        id: 2,
        date: '2024-10-23',
        activity: 'Reset Password Flow implementation dengan email token.',
        output: 'Flowchart & Code',
        outputUrl: '#',
        status: 'Pending',
        feedback: null
    },
    {
        id: 3,
        date: '2024-10-22',
        activity: 'Meeting dengan tim frontend untuk sinkronisasi komponen button.',
        output: 'Notulensi',
        outputUrl: '#',
        status: 'Rejected',
        feedback: 'Kurang detail pada bagian decision making. Tolong lengkapi.'
    },
     {
        id: 4,
        date: '2024-10-21',
        activity: 'Setup project awal dan instalasi dependencies.',
        output: 'Repo Initialized',
        outputUrl: '#',
        status: 'Approved',
        feedback: 'Good.'
    }
]);

const getStatusColor = (status) => {
    switch(status.toLowerCase()) {
        case 'approved': return 'bg-emerald-50 text-emerald-600 border-emerald-200';
        case 'rejected': return 'bg-red-50 text-red-600 border-red-200';
        default: return 'bg-amber-50 text-amber-600 border-amber-200';
    }
};

const getStatusIcon = (status) => {
    switch(status.toLowerCase()) {
        case 'approved': return CheckCircle2;
        case 'rejected': return XCircle;
        default: return Clock;
    }
};

const openDetail = (log) => {
    selectedLog.value = log;
    showDetailModal.value = true;
};

const filteredLogs = computed(() => {
    if (!searchQuery.value) return logs.value;
    return logs.value.filter(log => 
        log.activity.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        log.output.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});
</script>

<template>
    <Head title="Buku Log" />

    <div class="flex flex-col gap-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Buku Log</h1>
                <p class="text-slate-500 mt-1">Catat aktivitas harian dan progres belajar Anda.</p>
            </div>
            <button class="flex items-center gap-2 px-4 py-2.5 bg-primary text-white rounded-xl font-semibold hover:bg-primary-hover shadow-lg shadow-primary/25 transition-all active:scale-95">
                <Plus class="w-4 h-4" />
                <span>Tambah Jurnal</span>
            </button>
        </div>

        <!-- Main Content -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <!-- Toolbar -->
            <div class="p-4 border-b border-slate-200 flex flex-col sm:flex-row justify-between gap-4 bg-slate-50/50">
                <div class="relative w-full sm:w-72">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Cari aktivitas..." 
                        class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all placeholder:text-slate-400"
                    >
                </div>
                <!-- Date Filter Placeholder -->
                 <button class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-xl text-slate-600 text-sm font-medium hover:bg-slate-50 transition-all">
                    <Calendar class="w-4 h-4" />
                    <span>Oktober 2024</span>
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500 font-semibold uppercase text-xs tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 w-32">Tanggal</th>
                            <th class="px-6 py-4">Aktivitas</th>
                            <th class="px-6 py-4 w-48">Output</th>
                            <th class="px-6 py-4 w-32">Status</th>
                            <th class="px-6 py-4 w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr 
                            v-for="log in filteredLogs" 
                            :key="log.id" 
                            class="group hover:bg-blue-50/50 transition-colors cursor-pointer"
                            @click="openDetail(log)"
                        >
                            <td class="px-6 py-4 whitespace-nowrap text-slate-600 font-medium">
                                {{ log.date }}
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-slate-900 font-medium line-clamp-1 group-hover:text-primary transition-colors">
                                    {{ log.activity }}
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                <a 
                                    :href="log.outputUrl" 
                                    @click.stop 
                                    class="inline-flex items-center gap-1.5 text-primary hover:text-primary-hover font-medium hover:underline decoration-1 underline-offset-2"
                                >
                                    <FileText class="w-3.5 h-3.5" />
                                    {{ log.output }}
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border" :class="getStatusColor(log.status)">
                                    <component :is="getStatusIcon(log.status)" class="w-3.5 h-3.5" />
                                    {{ log.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <ChevronRight class="w-5 h-5 text-slate-300 group-hover:text-primary transition-colors" />
                            </td>
                        </tr>
                        
                        <!-- Empty State -->
                        <tr v-if="filteredLogs.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                        <BookOpen class="w-8 h-8" />
                                    </div>
                                    <p class="font-medium">Belum ada catatan jurnal.</p>
                                    <p class="text-sm text-slate-400 max-w-xs mx-auto">Mulai tulis aktivitas harianmu untuk mendapatkan feedback dari mentor.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Placeholder -->
            <div class="p-4 border-t border-slate-200 flex items-center justify-between text-sm text-slate-500">
                <span>Menampilkan 1-4 dari 4 data</span>
                <div class="flex gap-2">
                    <button class="px-3 py-1 border border-slate-200 rounded-lg hover:bg-slate-50 disabled:opacity-50" disabled>Previous</button>
                    <button class="px-3 py-1 border border-slate-200 rounded-lg hover:bg-slate-50 disabled:opacity-50" disabled>Next</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Modal (Mock) -->
    <div v-if="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="showDetailModal = false"></div>
        <div class="relative bg-white w-full max-w-lg rounded-2xl shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            <div class="p-6 border-b border-slate-100 flex justify-between items-start">
                <div>
                    <h3 class="text-xl font-bold text-slate-900">Detail Jurnal</h3>
                    <p class="text-slate-500 text-sm mt-1">{{ selectedLog?.date }}</p>
                </div>
                <button @click="showDetailModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <XCircle class="w-6 h-6" />
                </button>
            </div>
            
            <div class="p-6 flex flex-col gap-6">
                <div>
                    <h4 class="text-sm font-bold text-slate-900 mb-2">Aktivitas</h4>
                    <p class="text-slate-600 leading-relaxed">{{ selectedLog?.activity }}</p>
                </div>
                
                 <div>
                    <h4 class="text-sm font-bold text-slate-900 mb-2">Output</h4>
                    <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
                        <FileText class="w-8 h-8 text-primary" />
                        <div>
                            <p class="font-bold text-slate-700 text-sm">{{ selectedLog?.output }}</p>
                            <span class="text-xs text-slate-400">Klik untuk melihat file</span>
                        </div>
                        <ExternalLink class="w-4 h-4 text-slate-400 ml-auto" />
                    </div>
                </div>

                <div v-if="selectedLog?.feedback" class="bg-blue-50 p-4 rounded-xl border border-blue-100">
                     <h4 class="text-sm font-bold text-blue-800 mb-1 flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">chat</span> Feedback Mentor
                     </h4>
                     <p class="text-sm text-blue-700 leading-relaxed">{{ selectedLog?.feedback }}</p>
                </div>
            </div>

            <div class="p-6 border-t border-slate-100 bg-slate-50 flex justify-end">
                <button @click="showDetailModal = false" class="px-4 py-2 bg-white border border-slate-200 rounded-lg font-semibold text-slate-600 hover:bg-slate-100 transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</template>
