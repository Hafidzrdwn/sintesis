<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import {
    Clock,
    MapPin,
    CheckCircle2,
    XCircle,
    AlertTriangle,
    ChevronLeft,
    ChevronRight,
    Search,
    Calendar,
    Building2,
    Home,
    Plus,
    X
} from 'lucide-vue-next';
import axios from 'axios';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    attendances: Object,
    stats: Object,
    filters: Object,
});

const months = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

const selectedMonth = ref(props.filters.month);
const selectedYear = ref(props.filters.year);
const searchQuery = ref(props.filters.search);

const currentYear = new Date().getFullYear();
const years = Array.from({ length: 5 }, (_, i) => currentYear - i);

const periodLabel = computed(() => {
    return `${months[selectedMonth.value - 1]} ${selectedYear.value}`;
});

const applyFilters = () => {
    router.get(route('intern.attendance'), {
        month: selectedMonth.value,
        year: selectedYear.value,
        search: searchQuery.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

watch([selectedMonth, selectedYear], () => {
    applyFilters();
});

let searchTimeout;
const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
};

const goToPrevMonth = () => {
    if (selectedMonth.value === 1) {
        selectedMonth.value = 12;
        selectedYear.value--;
    } else {
        selectedMonth.value--;
    }
};

const goToNextMonth = () => {
    if (selectedMonth.value === 12) {
        selectedMonth.value = 1;
        selectedYear.value++;
    } else {
        selectedMonth.value++;
    }
};

const statsConfig = computed(() => [
    {
        label: 'Total Hadir',
        value: props.stats?.present || 0,
        unit: 'Hari',
        color: 'bg-emerald-50 text-emerald-600',
        icon: CheckCircle2
    },
    {
        label: 'Terlambat',
        value: props.stats?.late || 0,
        unit: 'Hari',
        color: 'bg-amber-50 text-amber-600',
        icon: AlertTriangle
    },
    {
        label: 'Izin / Sakit',
        value: props.stats?.leave || 0,
        unit: 'Hari',
        color: 'bg-blue-50 text-blue-600',
        icon: Calendar
    },
    {
        label: 'Alpha',
        value: props.stats?.absent || 0,
        unit: 'Hari',
        color: 'bg-red-50 text-red-600',
        icon: XCircle
    }
]);

const getStatusStyle = (status) => {
    const styles = {
        'present': 'text-emerald-600 bg-emerald-50 border-emerald-200',
        'late': 'text-amber-600 bg-amber-50 border-amber-200',
        'leave': 'text-blue-600 bg-blue-50 border-blue-200',
        'sick': 'text-blue-600 bg-blue-50 border-blue-200',
        'permit': 'text-purple-600 bg-purple-50 border-purple-200',
        'absent': 'text-red-600 bg-red-50 border-red-200',
    };
    return styles[status] || 'text-slate-600 bg-slate-50 border-slate-200';
};

const getStatusLabel = (status) => {
    const labels = {
        'present': 'Hadir',
        'late': 'Terlambat',
        'leave': 'Cuti',
        'sick': 'Sakit',
        'permit': 'Izin',
        'absent': 'Alpha',
    };
    return labels[status] || status;
};

const getModeIcon = (mode) => {
    return mode === 'WFO' ? Building2 : Home;
};

const formatWorkingHours = (hours) => {
    if (!hours) return '-';
    return `${hours.toFixed(1)} jam`;
};

const showLeaveModal = ref(false);
const leaveForm = ref({
    date: '',
    type: 'sick',
    notes: '',
});
const isSubmittingLeave = ref(false);
const leaveError = ref(null);
const leaveSuccess = ref(null);

const leaveTypes = [
    { value: 'sick', label: 'Sakit', icon: 'medical_services' },
    { value: 'leave', label: 'Cuti', icon: 'beach_access' },
    { value: 'permit', label: 'Izin', icon: 'event_busy' },
];

const openLeaveModal = () => {
    leaveForm.value = { date: '', type: 'sick', notes: '' };
    leaveError.value = null;
    leaveSuccess.value = null;
    showLeaveModal.value = true;
};

const closeLeaveModal = () => {
    showLeaveModal.value = false;
};

const submitLeaveRequest = async () => {
    if (!leaveForm.value.date || !leaveForm.value.notes) {
        leaveError.value = 'Tanggal dan alasan wajib diisi.';
        return;
    }

    isSubmittingLeave.value = true;
    leaveError.value = null;

    try {
        const response = await axios.post('/intern/presence/leave', leaveForm.value);

        if (response.data.success) {
            leaveSuccess.value = response.data.message;
            setTimeout(() => {
                closeLeaveModal();
                router.reload();
            }, 1500);
        }
    } catch (error) {
        leaveError.value = error.response?.data?.message || 'Terjadi kesalahan.';
    } finally {
        isSubmittingLeave.value = false;
    }
};
</script>

<template>

    <Head title="Riwayat Absensi" />

    <div class="flex flex-col gap-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Riwayat Absensi</h1>
                <p class="text-slate-500 mt-1">Pantau kehadiran dan kedisiplinan Anda.</p>
            </div>
            <div class="flex items-center gap-2">
                <button @click="openLeaveModal"
                    class="flex items-center cursor-pointer gap-2 px-4 py-2 bg-primary text-white rounded-xl font-semibold hover:bg-primary-hover shadow-lg shadow-primary/25 transition-all active:scale-95">
                    <Plus class="w-4 h-4" />
                    <span>Ajukan Izin</span>
                </button>
                <button @click="goToPrevMonth"
                    class="p-2 border cursor-pointer border-slate-200 rounded-lg hover:bg-slate-50 text-slate-500 transition-colors">
                    <ChevronLeft class="w-4 h-4" />
                </button>
                <div
                    class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-xl text-slate-600 font-medium shadow-sm min-w-[180px] justify-center">
                    <Calendar class="w-4 h-4" />
                    <span>{{ periodLabel }}</span>
                </div>
                <button @click="goToNextMonth"
                    class="p-2 border cursor-pointer border-slate-200 rounded-lg hover:bg-slate-50 text-slate-500 transition-colors">
                    <ChevronRight class="w-4 h-4" />
                </button>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div v-for="(stat, index) in statsConfig" :key="index"
                class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col gap-3 relative overflow-hidden group hover:shadow-md transition-all">
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
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-200 flex flex-col sm:flex-row justify-between gap-4 bg-slate-50/50">
                <div class="relative w-full sm:w-72">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                    <input v-model="searchQuery" @input="handleSearch" type="text"
                        placeholder="Cari berdasarkan catatan..."
                        class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all placeholder:text-slate-400">
                </div>
                <div class="text-sm text-slate-500 flex items-center">
                    Total: {{ attendances?.total || 0 }} data
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead
                        class="bg-slate-50 text-slate-500 font-semibold uppercase text-xs tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4">Tanggal</th>
                            <th class="px-6 py-4">Jam Masuk</th>
                            <th class="px-6 py-4">Jam Pulang</th>
                            <th class="px-6 py-4">Durasi</th>
                            <th class="px-6 py-4">Mode</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="item in attendances?.data" :key="item.id"
                            class="group hover:bg-blue-50/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-slate-900">{{ item.date_formatted }}</div>
                            </td>
                            <td class="px-6 py-4 text-slate-600 font-mono">
                                {{ item.check_in_time }}
                            </td>
                            <td class="px-6 py-4 text-slate-600 font-mono">
                                {{ item.check_out_time }}
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                {{ formatWorkingHours(item.working_hours) }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1.5 text-slate-600">
                                    <component :is="getModeIcon(item.attendance_mode)"
                                        class="w-3.5 h-3.5 text-slate-400" />
                                    {{ item.attendance_mode }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border"
                                    :class="getStatusStyle(item.status)">
                                    {{ getStatusLabel(item.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500 italic truncate max-w-[200px]">
                                {{ item.notes }}
                            </td>
                        </tr>
                        <tr v-if="!attendances?.data?.length">
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                <Calendar class="w-12 h-12 mx-auto mb-3 opacity-50" />
                                <p>Belum ada data absensi untuk periode ini</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="attendances?.last_page > 1"
                class="p-4 border-t border-slate-200 flex justify-between items-center">
                <span class="text-sm text-slate-500">
                    Halaman {{ attendances.current_page }} dari {{ attendances.last_page }}
                </span>
                <div class="flex gap-2">
                    <button @click="router.get(attendances.prev_page_url)" :disabled="!attendances.prev_page_url"
                        class="px-4 py-2 border border-slate-200 rounded-lg hover:bg-slate-50 text-slate-600 text-sm disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        Sebelumnya
                    </button>
                    <button @click="router.get(attendances.next_page_url)" :disabled="!attendances.next_page_url"
                        class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover text-sm disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        Selanjutnya
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Request Modal -->
    <Teleport to="body">
        <div v-if="showLeaveModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeLeaveModal"></div>
            <div
                class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between p-5 border-b border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900">Ajukan Izin / Cuti</h3>
                    <button @click="closeLeaveModal" class="p-2 hover:bg-slate-100 rounded-lg transition-colors">
                        <X class="w-5 h-5 text-slate-400" />
                    </button>
                </div>
                <div class="p-5 flex flex-col gap-5">
                    <div v-if="leaveSuccess"
                        class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-sm font-medium flex items-center gap-2">
                        <CheckCircle2 class="w-5 h-5 shrink-0" />
                        {{ leaveSuccess }}
                    </div>
                    <div v-if="leaveError"
                        class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm font-medium flex items-center gap-2">
                        <XCircle class="w-5 h-5 shrink-0" />
                        {{ leaveError }}
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-slate-700">Jenis Pengajuan</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button v-for="lt in leaveTypes" :key="lt.value" @click="leaveForm.type = lt.value"
                                class="flex flex-col cursor-pointer items-center gap-2 p-3 rounded-xl border-2 transition-all"
                                :class="leaveForm.type === lt.value ? 'border-primary bg-primary/5 text-primary' : 'border-slate-200 text-slate-500 hover:border-slate-300'">
                                <span class="material-symbols-outlined">{{ lt.icon }}</span>
                                <span class="text-xs font-semibold">{{ lt.label }}</span>
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-slate-700">Tanggal</label>
                        <input type="date" v-model="leaveForm.date" :min="new Date().toISOString().split('T')[0]"
                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-slate-700">Alasan</label>
                        <textarea v-model="leaveForm.notes" rows="3" placeholder="Jelaskan alasan pengajuan..."
                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all resize-none placeholder:text-slate-400"></textarea>
                    </div>
                </div>
                <div class="p-5 border-t border-slate-200 flex gap-3">
                    <button @click="closeLeaveModal"
                        class="flex-1 px-4 py-3 border border-slate-200 cursor-pointer text-slate-600 rounded-xl font-semibold hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button @click="submitLeaveRequest" :disabled="isSubmittingLeave"
                        class="flex-1 px-4 py-3 bg-primary cursor-pointer text-white rounded-xl font-semibold hover:bg-primary-hover transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                        <span v-if="isSubmittingLeave"
                            class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        {{ isSubmittingLeave ? 'Mengajukan...' : 'Ajukan' }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
