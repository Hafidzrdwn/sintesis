<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import {
    Search, CheckCircle2, XCircle, Clock, ChevronLeft, ChevronRight, Eye, X, User, Calendar, FileText
} from 'lucide-vue-next';
import RichTextEditor from '@/Components/RichTextEditor.vue';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    logbooks: Object,
    stats: Object,
    mentees: Array,
    filters: Object,
});

const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const selectedMonth = ref(props.filters?.month || new Date().getMonth() + 1);
const selectedYear = ref(props.filters?.year || new Date().getFullYear());
const activeStatus = ref(props.filters?.status || 'all');
const selectedMentee = ref(props.filters?.mentee_id || '');
const searchQuery = ref(props.filters?.search || '');

const periodLabel = computed(() => `${months[selectedMonth.value - 1]} ${selectedYear.value}`);

const statusTabs = computed(() => [
    { key: 'all', label: 'Semua', count: props.stats?.all || 0 },
    { key: 'submitted', label: 'Menunggu Review', count: props.stats?.submitted || 0 },
    { key: 'approved', label: 'Disetujui', count: props.stats?.approved || 0 },
    { key: 'rejected', label: 'Ditolak', count: props.stats?.rejected || 0 },
]);

const showDetailModal = ref(false);
const showReviewModal = ref(false);
const selectedLog = ref(null);
const isSubmitting = ref(false);
const reviewNotes = ref('');
const actionMessage = ref(null);

const applyFilters = () => {
    router.get(route('mentor.logbook'), {
        month: selectedMonth.value,
        year: selectedYear.value,
        status: activeStatus.value,
        mentee_id: selectedMentee.value,
        search: searchQuery.value,
    }, { preserveState: true, preserveScroll: true });
};

watch([selectedMonth, selectedYear, activeStatus, selectedMentee], () => applyFilters());

let searchTimeout;
const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 500);
};

const goToPrevMonth = () => {
    if (selectedMonth.value === 1) { selectedMonth.value = 12; selectedYear.value--; }
    else { selectedMonth.value--; }
};

const goToNextMonth = () => {
    if (selectedMonth.value === 12) { selectedMonth.value = 1; selectedYear.value++; }
    else { selectedMonth.value++; }
};

const getStatusColor = (status) => ({
    'submitted': 'bg-amber-50 text-amber-600 border-amber-200',
    'approved': 'bg-emerald-50 text-emerald-600 border-emerald-200',
    'rejected': 'bg-red-50 text-red-600 border-red-200',
}[status] || 'bg-slate-50 text-slate-600 border-slate-200');

const getStatusLabel = (status) => ({
    'submitted': 'Menunggu Review',
    'approved': 'Disetujui',
    'rejected': 'Ditolak',
}[status] || status);

const getStatusIcon = (status) => ({
    'submitted': Clock,
    'approved': CheckCircle2,
    'rejected': XCircle,
}[status] || Clock);

const formatDuration = (decimalHours) => {
    const hours = Math.floor(decimalHours);
    const minutes = Math.round((decimalHours - hours) * 60);
    if (minutes === 0) return `${hours} jam`;
    if (hours === 0) return `${minutes} menit`;
    return `${hours} jam ${minutes} menit`;
};

const openDetail = (log) => {
    selectedLog.value = log;
    showDetailModal.value = true;
};

const openReviewModal = (log) => {
    selectedLog.value = log;
    reviewNotes.value = '';
    showReviewModal.value = true;
};

const closeModals = () => {
    showDetailModal.value = false;
    showReviewModal.value = false;
    selectedLog.value = null;
};

const approveLogbook = async () => {
    isSubmitting.value = true;
    try {
        const response = await axios.post(route('mentor.logbook.approve', selectedLog.value.id), { notes: reviewNotes.value });
        if (response.data.success) {
            actionMessage.value = { type: 'success', text: response.data.message };
            closeModals();
            router.reload({ only: ['logbooks', 'stats'] });
        }
    } catch (error) {
        actionMessage.value = { type: 'error', text: error.response?.data?.message || 'Terjadi kesalahan' };
    } finally {
        isSubmitting.value = false;
    }
};

const rejectLogbook = async () => {
    if (!reviewNotes.value.trim()) {
        actionMessage.value = { type: 'error', text: 'Alasan penolakan wajib diisi' };
        return;
    }
    isSubmitting.value = true;
    try {
        const response = await axios.post(route('mentor.logbook.reject', selectedLog.value.id), { notes: reviewNotes.value });
        if (response.data.success) {
            actionMessage.value = { type: 'success', text: response.data.message };
            closeModals();
            router.reload({ only: ['logbooks', 'stats'] });
        }
    } catch (error) {
        actionMessage.value = { type: 'error', text: error.response?.data?.message || 'Terjadi kesalahan' };
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<template>

    <Head title="Review Logbook" />

    <div class="flex flex-col gap-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Review Logbook</h1>
                <p class="text-slate-500 mt-1">Review dan setujui jurnal harian dari mentee Anda.</p>
            </div>
        </div>

        <!-- Action Message -->
        <div v-if="actionMessage" class="p-4 rounded-xl flex items-center gap-2"
            :class="actionMessage.type === 'success' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'">
            <CheckCircle2 v-if="actionMessage.type === 'success'" class="w-5 h-5" />
            <XCircle v-else class="w-5 h-5" />
            {{ actionMessage.text }}
            <button @click="actionMessage = null" class="ml-auto cursor-pointer">
                <X class="w-4 h-4" />
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-amber-50 p-4 rounded-xl border border-amber-200 text-center">
                <p class="text-2xl font-black text-amber-600">{{ stats?.submitted || 0 }}</p>
                <p class="text-xs text-amber-600">Menunggu Review</p>
            </div>
            <div class="bg-emerald-50 p-4 rounded-xl border border-emerald-200 text-center">
                <p class="text-2xl font-black text-emerald-600">{{ stats?.approved || 0 }}</p>
                <p class="text-xs text-emerald-600">Disetujui</p>
            </div>
            <div class="bg-red-50 p-4 rounded-xl border border-red-200 text-center">
                <p class="text-2xl font-black text-red-600">{{ stats?.rejected || 0 }}</p>
                <p class="text-xs text-red-600">Ditolak</p>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 text-center">
                <p class="text-2xl font-black text-slate-900">{{ stats?.all || 0 }}</p>
                <p class="text-xs text-slate-500">Total Jurnal</p>
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-200 flex flex-col lg:flex-row justify-between gap-4 bg-slate-50/50">
                <div class="flex p-1 bg-slate-100 rounded-xl overflow-x-auto">
                    <button v-for="tab in statusTabs" :key="tab.key" @click="activeStatus = tab.key"
                        class="px-4 py-2 text-sm rounded-lg font-medium whitespace-nowrap transition-all cursor-pointer"
                        :class="activeStatus === tab.key ? 'bg-white text-primary shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                        {{ tab.label }} <span class="ml-1 text-xs">({{ tab.count }})</span>
                    </button>
                </div>

                <div class="flex items-center justify-end gap-2 flex-wrap">
                    <div class="flex flex-1 items-center gap-2">
                        <button @click="goToPrevMonth"
                            class="p-2 border border-slate-200 rounded-lg hover:bg-slate-50 cursor-pointer">
                            <ChevronLeft class="w-4 h-4 text-slate-500" />
                        </button>
                        <div
                            class="px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium min-w-[120px] text-center">
                            {{ periodLabel }}
                        </div>
                        <button @click="goToNextMonth"
                            class="p-2 border border-slate-200 rounded-lg hover:bg-slate-50 cursor-pointer">
                            <ChevronRight class="w-4 h-4 text-slate-500" />
                        </button>
                    </div>

                    <select v-model="selectedMentee"
                        class="px-3 py-2 flex-1 border border-slate-200 rounded-xl text-sm cursor-pointer focus:ring-2 focus:ring-primary/20">
                        <option value="">Semua Mentee</option>
                        <option v-for="m in mentees" :key="m.id" :value="m.id">{{ m.name }}</option>
                    </select>

                    <div class="relative w-full">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                        <input v-model="searchQuery" @input="handleSearch" type="text" placeholder="Cari aktivitas..."
                            class="pl-9 pr-4 py-2 w-full border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20" />
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead
                        class="bg-slate-50 text-slate-500 font-semibold uppercase text-xs tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4">Mentee</th>
                            <th class="px-6 py-4">Tanggal</th>
                            <th class="px-6 py-4">Aktivitas</th>
                            <th class="px-6 py-4">Durasi</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="log in logbooks?.data" :key="log.id"
                            class="group hover:bg-blue-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="text-slate-900 font-medium text-sm">{{ log.intern_name }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-600 font-medium">{{ log.date_formatted }}
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-slate-900 font-medium line-clamp-1 group-hover:text-primary transition-colors cursor-pointer"
                                    @click="openDetail(log)">{{ log.activity }}</p>
                                <p v-if="log.task_title" class="text-xs text-slate-400 line-clamp-1 mt-0.5">{{
                                    log.task_title }}</p>
                            </td>
                            <td class="px-6 py-4 text-slate-600 text-xs">{{ formatDuration(log.duration_hours) }}</td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border"
                                    :class="getStatusColor(log.status)">
                                    <component :is="getStatusIcon(log.status)" class="w-3.5 h-3.5" />
                                    {{ getStatusLabel(log.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1">
                                    <button @click="openDetail(log)"
                                        class="p-2 hover:bg-slate-100 rounded-lg text-slate-400 hover:text-primary cursor-pointer">
                                        <Eye class="w-4 h-4" />
                                    </button>
                                    <button v-if="log.status === 'submitted'" @click="openReviewModal(log)"
                                        class="p-2 hover:bg-emerald-50 rounded-lg text-emerald-500 hover:text-emerald-600 cursor-pointer">
                                        <CheckCircle2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!logbooks?.data?.length">
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <FileText class="w-12 h-12 mx-auto mb-3 opacity-50" />
                                <p>Tidak ada jurnal untuk ditampilkan.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <Teleport to="body">
        <div v-if="showDetailModal && selectedLog" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModals"></div>
            <div
                class="relative bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
                <div class="p-5 border-b border-slate-200 flex justify-between items-start shrink-0">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Detail Jurnal</h3>
                        <p class="text-sm text-slate-500">{{ selectedLog.date_formatted }}</p>
                    </div>
                    <button @click="closeModals" class="p-2 hover:bg-slate-100 rounded-lg cursor-pointer">
                        <X class="w-5 h-5 text-slate-400" />
                    </button>
                </div>
                <div class="p-5 flex flex-col gap-4 overflow-y-auto">
                    <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl">
                        <div
                            class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                            {{ selectedLog.intern_name?.charAt(0) }}
                        </div>
                        <div>
                            <p class="font-semibold text-slate-900">{{ selectedLog.intern_name }}</p>
                            <p class="text-xs text-slate-500">{{ formatDuration(selectedLog.duration_hours) }}</p>
                        </div>
                        <span class="ml-auto px-2.5 py-1 rounded-full text-xs font-bold border"
                            :class="getStatusColor(selectedLog.status)">
                            {{ getStatusLabel(selectedLog.status) }}
                        </span>
                    </div>

                    <div>
                        <p class="text-xs text-slate-500 mb-1">Aktivitas</p>
                        <p class="font-semibold text-slate-900">{{ selectedLog.activity }}</p>
                    </div>

                    <div v-if="selectedLog.description">
                        <p class="text-xs text-slate-500 mb-1">Deskripsi</p>
                        <div class="rich-content text-sm text-slate-600" v-html="selectedLog.description"></div>
                    </div>

                    <div v-if="selectedLog.task_title" class="flex items-center gap-2 p-3 bg-blue-50 rounded-xl">
                        <FileText class="w-4 h-4 text-blue-600" />
                        <span class="text-sm text-blue-700">{{ selectedLog.task_title }}</span>
                    </div>

                    <div v-if="selectedLog.mentor_notes" class="p-4 rounded-xl"
                        :class="selectedLog.status === 'approved' ? 'bg-emerald-50 border border-emerald-200' : 'bg-red-50 border border-red-200'">
                        <p class="text-xs font-semibold mb-1"
                            :class="selectedLog.status === 'approved' ? 'text-emerald-600' : 'text-red-600'">
                            Catatan Mentor
                        </p>
                        <div class="rich-content text-sm"
                            :class="selectedLog.status === 'approved' ? 'text-emerald-800' : 'text-red-800'"
                            v-html="selectedLog.mentor_notes"></div>
                    </div>

                    <div v-if="selectedLog.approved_at" class="text-xs text-slate-400 text-center">
                        Di-review oleh {{ selectedLog.approved_by_name }} pada {{ selectedLog.approved_at }}
                    </div>
                </div>
                <div class="p-5 border-t border-slate-200 flex gap-3 shrink-0">
                    <button v-if="selectedLog.status === 'submitted'"
                        @click="showDetailModal = false; openReviewModal(selectedLog)"
                        class="flex-1 px-4 py-2.5 bg-primary text-white rounded-xl font-semibold hover:bg-primary-hover cursor-pointer">
                        Review Jurnal
                    </button>
                    <button @click="closeModals"
                        class="flex-1 px-4 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-semibold hover:bg-slate-50 cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </Teleport>

    <!-- Review Modal -->
    <Teleport to="body">
        <div v-if="showReviewModal && selectedLog" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModals"></div>
            <div
                class="relative bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
                <div class="p-5 border-b border-slate-200 flex justify-between items-start shrink-0">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Review Jurnal</h3>
                        <p class="text-sm text-slate-500">{{ selectedLog.intern_name }} - {{ selectedLog.date_formatted
                            }}</p>
                    </div>
                    <button @click="closeModals" class="p-2 hover:bg-slate-100 rounded-lg cursor-pointer">
                        <X class="w-5 h-5 text-slate-400" />
                    </button>
                </div>
                <div class="p-5 flex flex-col gap-4 overflow-y-auto">
                    <div class="bg-slate-50 p-4 rounded-xl">
                        <p class="font-semibold text-slate-900 mb-1">{{ selectedLog.activity }}</p>
                        <p class="text-sm text-slate-500">{{ formatDuration(selectedLog.duration_hours) }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Catatan Mentor (opsional untuk
                            approve, wajib untuk reject)</label>
                        <RichTextEditor v-model="reviewNotes" placeholder="Berikan catatan atau alasan penolakan..."
                            minHeight="100px" />
                    </div>
                </div>
                <div class="p-5 border-t border-slate-200 flex gap-3 shrink-0">
                    <button @click="rejectLogbook" :disabled="isSubmitting"
                        class="flex-1 px-4 py-2.5 bg-red-500 text-white rounded-xl font-semibold hover:bg-red-600 disabled:opacity-50 cursor-pointer">
                        {{ isSubmitting  ? 'Memproses...' : 'Tolak' }}
                    </button>
                    <button @click="approveLogbook" :disabled="isSubmitting"
                        class="flex-1 px-4 py-2.5 bg-emerald-500 text-white rounded-xl font-semibold hover:bg-emerald-600 disabled:opacity-50 cursor-pointer">
                        {{ isSubmitting ? 'Memproses...' : 'Setujui' }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
