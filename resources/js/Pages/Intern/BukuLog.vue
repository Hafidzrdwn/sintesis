<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import {
    Plus,
    Search,
    FileText,
    CheckCircle2,
    XCircle,
    Clock,
    ChevronRight,
    Calendar,
    BookOpen,
    ChevronLeft,
    Edit3,
    Trash2,
    Send,
    X,
    AlertCircle
} from 'lucide-vue-next';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    logbooks: Object,
    stats: Object,
    tasks: Array,
    filters: Object,
});

const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const selectedMonth = ref(props.filters?.month || new Date().getMonth() + 1);
const selectedYear = ref(props.filters?.year || new Date().getFullYear());
const searchQuery = ref(props.filters?.search || '');
const activeStatus = ref(props.filters?.status || 'all');

const periodLabel = computed(() => `${months[selectedMonth.value - 1]} ${selectedYear.value}`);

const showFormModal = ref(false);
const showDetailModal = ref(false);
const selectedLog = ref(null);
const isEditing = ref(false);
const isSubmitting = ref(false);
const formError = ref(null);
const formSuccess = ref(null);

const formData = ref({
    date: new Date().toISOString().split('T')[0],
    activity: '',
    description: '',
    duration_hours: 1,
    task_id: '',
});

const statusTabs = [
    { key: 'all', label: 'Semua', count: props.stats?.all || 0 },
    { key: 'draft', label: 'Draft', count: props.stats?.draft || 0 },
    { key: 'submitted', label: 'Diajukan', count: props.stats?.submitted || 0 },
    { key: 'approved', label: 'Disetujui', count: props.stats?.approved || 0 },
    { key: 'rejected', label: 'Ditolak', count: props.stats?.rejected || 0 },
];

const applyFilters = () => {
    router.get(route('intern.logbook'), {
        month: selectedMonth.value,
        year: selectedYear.value,
        status: activeStatus.value,
        search: searchQuery.value,
    }, { preserveState: true, preserveScroll: true });
};

watch([selectedMonth, selectedYear, activeStatus], () => applyFilters());

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

const getStatusColor = (status) => {
    const colors = {
        'draft': 'bg-slate-50 text-slate-600 border-slate-200',
        'submitted': 'bg-amber-50 text-amber-600 border-amber-200',
        'approved': 'bg-emerald-50 text-emerald-600 border-emerald-200',
        'rejected': 'bg-red-50 text-red-600 border-red-200',
    };
    return colors[status] || 'bg-slate-50 text-slate-600 border-slate-200';
};

const getStatusLabel = (status) => {
    const labels = { 'draft': 'Draft', 'submitted': 'Menunggu Review', 'approved': 'Disetujui', 'rejected': 'Ditolak' };
    return labels[status] || status;
};

const getStatusIcon = (status) => {
    const icons = { 'draft': Edit3, 'submitted': Clock, 'approved': CheckCircle2, 'rejected': XCircle };
    return icons[status] || Clock;
};

const openCreateModal = () => {
    formData.value = { date: new Date().toISOString().split('T')[0], activity: '', description: '', duration_hours: 1, task_id: '' };
    isEditing.value = false;
    formError.value = null;
    formSuccess.value = null;
    showFormModal.value = true;
};

const openEditModal = (log) => {
    formData.value = {
        id: log.id,
        date: log.date,
        activity: log.activity,
        description: log.description || '',
        duration_hours: log.duration_hours,
        task_id: log.task_id || '',
    };
    isEditing.value = true;
    formError.value = null;
    formSuccess.value = null;
    showFormModal.value = true;
};

const openDetail = (log) => {
    selectedLog.value = log;
    showDetailModal.value = true;
};

const closeFormModal = () => { showFormModal.value = false; };
const closeDetailModal = () => { showDetailModal.value = false; selectedLog.value = null; };

const saveLogbook = async () => {
    if (!formData.value.activity || !formData.value.date) {
        formError.value = 'Tanggal dan aktivitas wajib diisi.';
        return;
    }
    isSubmitting.value = true;
    formError.value = null;

    try {
        const url = isEditing.value ? `/intern/logbook/${formData.value.id}` : '/intern/logbook';
        const method = isEditing.value ? 'put' : 'post';
        const response = await axios[method](url, formData.value);

        if (response.data.success) {
            formSuccess.value = response.data.message;
            setTimeout(() => { closeFormModal(); router.reload(); }, 1000);
        }
    } catch (error) {
        formError.value = error.response?.data?.message || 'Terjadi kesalahan.';
    } finally {
        isSubmitting.value = false;
    }
};

const deleteLogbook = async (id) => {
    if (!confirm('Yakin ingin menghapus jurnal ini?')) return;
    try {
        const response = await axios.delete(`/intern/logbook/${id}`);
        if (response.data.success) { router.reload(); }
    } catch (error) {
        alert(error.response?.data?.message || 'Gagal menghapus.');
    }
};

const submitLogbook = async (id) => {
    try {
        const response = await axios.post(`/intern/logbook/${id}/submit`);
        if (response.data.success) {
            closeDetailModal();
            router.reload();
        }
    } catch (error) {
        alert(error.response?.data?.message || 'Gagal submit.');
    }
};
</script>

<template>

    <Head title="Buku Log" />

    <div class="flex flex-col gap-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Buku Log</h1>
                <p class="text-slate-500 mt-1">Catat aktivitas harian dan progres belajar Anda.</p>
            </div>
            <div class="flex items-center gap-2">
                <button @click="openCreateModal"
                    class="flex items-center gap-2 px-4 py-2.5 bg-primary text-white rounded-xl font-semibold hover:bg-primary-hover shadow-lg shadow-primary/25 transition-all active:scale-95 cursor-pointer">
                    <Plus class="w-4 h-4" />
                    <span>Tambah Jurnal</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            <div class="bg-white p-4 rounded-xl border border-slate-200 text-center">
                <p class="text-2xl font-black text-slate-900">{{ stats?.all || 0 }}</p>
                <p class="text-xs text-slate-500">Total Jurnal</p>
            </div>
            <div class="bg-emerald-50 p-4 rounded-xl border border-emerald-200 text-center">
                <p class="text-2xl font-black text-emerald-600">{{ stats?.approved || 0 }}</p>
                <p class="text-xs text-emerald-600">Disetujui</p>
            </div>
            <div class="bg-amber-50 p-4 rounded-xl border border-amber-200 text-center">
                <p class="text-2xl font-black text-amber-600">{{ stats?.submitted || 0 }}</p>
                <p class="text-xs text-amber-600">Menunggu</p>
            </div>
            <div class="bg-red-50 p-4 rounded-xl border border-red-200 text-center">
                <p class="text-2xl font-black text-red-600">{{ stats?.rejected || 0 }}</p>
                <p class="text-xs text-red-600">Ditolak</p>
            </div>
            <div class="bg-blue-50 p-4 rounded-xl border border-blue-200 text-center">
                <p class="text-2xl font-black text-blue-600">{{ stats?.total_hours || 0 }}</p>
                <p class="text-xs text-blue-600">Total Jam</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-200 flex flex-col lg:flex-row justify-between gap-4 bg-slate-50/50">
                <div class="flex p-1 bg-slate-100 rounded-xl overflow-x-auto">
                    <button v-for="tab in statusTabs" :key="tab.key" @click="activeStatus = tab.key"
                        class="px-3 py-2 rounded-lg text-xs font-medium transition-all whitespace-nowrap flex items-center gap-1.5 cursor-pointer"
                        :class="activeStatus === tab.key ? 'bg-white text-primary shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                        {{ tab.label }}
                        <span class="text-[10px] px-1.5 py-0.5 rounded-full"
                            :class="activeStatus === tab.key ? 'bg-primary/10 text-primary' : 'bg-slate-200 text-slate-500'">{{
                            tab.count }}</span>
                    </button>
                </div>
                <div class="flex items-center gap-2">
                    <div class="relative w-full sm:w-64">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                        <input v-model="searchQuery" @input="handleSearch" type="text" placeholder="Cari aktivitas..."
                            class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all placeholder:text-slate-400">
                    </div>
                    <div class="flex items-center gap-1">
                        <button @click="goToPrevMonth"
                            class="p-2 border border-slate-200 rounded-lg hover:bg-slate-50 cursor-pointer">
                            <ChevronLeft class="w-4 h-4 text-slate-500" />
                        </button>
                        <div
                            class="px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium min-w-[120px] text-center">
                            {{ periodLabel }}</div>
                        <button @click="goToNextMonth"
                            class="p-2 border border-slate-200 rounded-lg hover:bg-slate-50 cursor-pointer">
                            <ChevronRight class="w-4 h-4 text-slate-500" />
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead
                        class="bg-slate-50 text-slate-500 font-semibold uppercase text-xs tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4">Tanggal</th>
                            <th class="px-6 py-4">Aktivitas</th>
                            <th class="px-6 py-4">Tugas Terkait</th>
                            <th class="px-6 py-4">Durasi</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="log in logbooks?.data" :key="log.id"
                            class="group hover:bg-blue-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-slate-600 font-medium">{{ log.date_formatted }}
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-slate-900 font-medium line-clamp-1 group-hover:text-primary transition-colors cursor-pointer"
                                    @click="openDetail(log)">{{ log.activity }}</p>
                                <p v-if="log.description" class="text-xs text-slate-400 line-clamp-1 mt-0.5">{{
                                    log.description }}</p>
                            </td>
                            <td class="px-6 py-4 text-slate-500 text-xs">{{ log.task_title || '-' }}</td>
                            <td class="px-6 py-4 text-slate-600 font-mono text-xs">{{ log.duration_hours }} jam</td>
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
                                    <button v-if="log.status === 'draft' || log.status === 'rejected'"
                                        @click="openEditModal(log)"
                                        class="p-1.5 text-slate-400 hover:text-primary transition-colors cursor-pointer">
                                        <Edit3 class="w-4 h-4" />
                                    </button>
                                    <button v-if="log.status === 'draft' || log.status === 'rejected'"
                                        @click="deleteLogbook(log.id)"
                                        class="p-1.5 text-slate-400 hover:text-red-500 transition-colors cursor-pointer">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                    <button @click="openDetail(log)"
                                        class="p-1.5 text-slate-400 hover:text-primary transition-colors cursor-pointer">
                                        <ChevronRight class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!logbooks?.data?.length">
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                <BookOpen class="w-12 h-12 mx-auto mb-3 text-slate-300" />
                                <p class="font-medium">Belum ada jurnal untuk periode ini.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="logbooks?.last_page > 1"
                class="p-4 border-t border-slate-200 flex items-center justify-between text-sm text-slate-500">
                <span>Halaman {{ logbooks.current_page }} dari {{ logbooks.last_page }}</span>
                <div class="flex gap-2">
                    <button @click="router.get(logbooks.prev_page_url)" :disabled="!logbooks.prev_page_url"
                        class="px-3 py-1 border border-slate-200 rounded-lg hover:bg-slate-50 disabled:opacity-50 cursor-pointer">Previous</button>
                    <button @click="router.get(logbooks.next_page_url)" :disabled="!logbooks.next_page_url"
                        class="px-3 py-1 border border-slate-200 rounded-lg hover:bg-slate-50 disabled:opacity-50 cursor-pointer">Next</button>
                </div>
            </div>
        </div>
    </div>

    <Teleport to="body">
        <div v-if="showFormModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeFormModal"></div>
            <div class="relative bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between p-5 border-b border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900">{{ isEditing ? 'Edit Jurnal' : 'Tambah Jurnal Baru' }}
                    </h3>
                    <button @click="closeFormModal" class="p-2 hover:bg-slate-100 rounded-lg cursor-pointer">
                        <X class="w-5 h-5 text-slate-400" />
                    </button>
                </div>
                <div class="p-5 flex flex-col gap-4">
                    <div v-if="formSuccess"
                        class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-sm flex items-center gap-2">
                        <CheckCircle2 class="w-4 h-4" />{{ formSuccess }}
                    </div>
                    <div v-if="formError"
                        class="p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm flex items-center gap-2">
                        <AlertCircle class="w-4 h-4" />{{ formError }}
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-medium text-slate-600">Tanggal</label>
                            <input type="date" v-model="formData.date" :disabled="isEditing"
                                class="px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary disabled:bg-slate-50" />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-medium text-slate-600">Durasi (jam)</label>
                            <input type="number" v-model="formData.duration_hours" min="0.5" max="24" step="0.5"
                                class="px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary" />
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-slate-600">Aktivitas</label>
                        <input type="text" v-model="formData.activity" placeholder="Apa yang Anda kerjakan hari ini?"
                            class="px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-slate-600">Deskripsi (opsional)</label>
                        <textarea v-model="formData.description" rows="3" placeholder="Detail aktivitas..."
                            class="px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary resize-none"></textarea>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-slate-600">Tugas Terkait (opsional)</label>
                        <select v-model="formData.task_id"
                            class="px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            <option value="">Tidak ada tugas terkait</option>
                            <option v-for="task in tasks" :key="task.id" :value="task.id">{{ task.title }}</option>
                        </select>
                    </div>
                </div>
                <div class="p-5 border-t border-slate-200 flex gap-3">
                    <button @click="closeFormModal"
                        class="flex-1 px-4 py-2.5 border border-slate-200 text-slate-600 rounded-xl font-semibold hover:bg-slate-50 cursor-pointer">Batal</button>
                    <button @click="saveLogbook" :disabled="isSubmitting"
                        class="flex-1 px-4 py-2.5 bg-primary text-white rounded-xl font-semibold hover:bg-primary-hover disabled:opacity-50 cursor-pointer">{{
                            isSubmitting ? 'Menyimpan...' : 'Simpan' }}</button>
                </div>
            </div>
        </div>
    </Teleport>

    <Teleport to="body">
        <div v-if="showDetailModal && selectedLog" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeDetailModal"></div>
            <div class="relative bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden">
                <div class="p-5 border-b border-slate-200 flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Detail Jurnal</h3>
                        <p class="text-slate-500 text-sm">{{ selectedLog.date_formatted }}</p>
                    </div>
                    <button @click="closeDetailModal" class="p-2 hover:bg-slate-100 rounded-lg cursor-pointer">
                        <X class="w-5 h-5 text-slate-400" />
                    </button>
                </div>
                <div class="p-5 flex flex-col gap-4">
                    <div>
                        <h4 class="text-xs font-bold text-slate-500 uppercase mb-1">Aktivitas</h4>
                        <p class="text-slate-900 font-medium">{{ selectedLog.activity }}</p>
                    </div>
                    <div v-if="selectedLog.description">
                        <h4 class="text-xs font-bold text-slate-500 uppercase mb-1">Deskripsi</h4>
                        <p class="text-slate-600 text-sm leading-relaxed">{{ selectedLog.description }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <h4 class="text-xs font-bold text-slate-500 uppercase mb-1">Durasi</h4>
                            <p class="text-slate-900 font-mono">{{ selectedLog.duration_hours }} jam</p>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-500 uppercase mb-1">Tugas Terkait</h4>
                            <p class="text-slate-600">{{ selectedLog.task_title || '-' }}</p>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-500 uppercase mb-1">Status</h4>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border"
                            :class="getStatusColor(selectedLog.status)">
                            <component :is="getStatusIcon(selectedLog.status)" class="w-3.5 h-3.5" />
                            {{ getStatusLabel(selectedLog.status) }}
                        </span>
                    </div>
                    <div v-if="selectedLog.mentor_notes" class="bg-blue-50 p-4 rounded-xl border border-blue-100">
                        <h4 class="text-xs font-bold text-blue-800 mb-1">Feedback Mentor</h4>
                        <p class="text-sm text-blue-700">{{ selectedLog.mentor_notes }}</p>
                        <p v-if="selectedLog.approved_by_name" class="text-xs text-blue-500 mt-2">- {{
                            selectedLog.approved_by_name }}, {{ selectedLog.approved_at }}</p>
                    </div>
                </div>
                <div class="p-5 border-t border-slate-200 flex gap-3">
                    <button @click="closeDetailModal"
                        class="flex-1 px-4 py-2.5 border border-slate-200 text-slate-600 rounded-xl font-semibold hover:bg-slate-50 cursor-pointer">Tutup</button>
                    <button v-if="selectedLog.status === 'draft' || selectedLog.status === 'rejected'"
                        @click="submitLogbook(selectedLog.id)"
                        class="flex-1 px-4 py-2.5 bg-primary text-white rounded-xl font-semibold hover:bg-primary-hover flex items-center justify-center gap-2 cursor-pointer">
                        <Send class="w-4 h-4" />Kirim untuk Review
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
