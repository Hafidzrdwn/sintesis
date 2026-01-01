<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import {
    Search, Plus, Filter, Calendar, Clock, AlertTriangle, CheckCircle2, XCircle,
    Edit3, Trash2, Eye, Star, Send, X, User, ListTodo, Loader2, RotateCcw, Paperclip, ExternalLink, FileText
} from 'lucide-vue-next';
import RichTextEditor from '@/Components/RichTextEditor.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    tasks: Array,
    stats: Object,
    mentees: Array,
    filters: Object,
});

const tabs = [
    { key: 'all', label: 'Semua' },
    { key: 'pending', label: 'Pending' },
    { key: 'in_progress', label: 'Dikerjakan' },
    { key: 'review', label: 'Review' },
    { key: 'completed', label: 'Selesai' },
];

const priorityOptions = [
    { value: 'urgent', label: 'Urgent', color: 'bg-red-100 text-red-700 border-red-200' },
    { value: 'high', label: 'High', color: 'bg-orange-50 text-orange-600 border-orange-200' },
    { value: 'medium', label: 'Medium', color: 'bg-amber-50 text-amber-600 border-amber-200' },
    { value: 'low', label: 'Low', color: 'bg-blue-50 text-blue-600 border-blue-200' },
];

const activeTab = ref(props.filters?.status || 'all');
const selectedPriority = ref(props.filters?.priority || '');
const selectedIntern = ref(props.filters?.intern_id || '');
const searchQuery = ref(props.filters?.search || '');

const showFormModal = ref(false);
const showReviewModal = ref(false);
const showDetailModal = ref(false);
const isEditing = ref(false);
const selectedTask = ref(null);
const isSubmitting = ref(false);

const form = ref({
    title: '',
    description: '',
    due_date: '',
    priority: 'medium',
    intern_id: '',
    estimated_hours: null,
});

const reviewForm = ref({
    feedback: '',
    rating: 5,
    actual_hours: null,
});

const applyFilters = () => {
    router.get(route('mentor.tasks'), {
        status: activeTab.value,
        priority: selectedPriority.value,
        intern_id: selectedIntern.value,
        search: searchQuery.value,
    }, { preserveState: true, preserveScroll: true });
};

watch([activeTab, selectedPriority, selectedIntern], () => applyFilters());

let searchTimeout;
const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 500);
};

const getPriorityClass = (priority) => {
    return priorityOptions.find(p => p.value === priority)?.color || 'bg-slate-100 text-slate-600';
};

const getStatusClass = (status) => {
    const classes = {
        'pending': 'bg-slate-100 text-slate-600',
        'in_progress': 'bg-blue-100 text-blue-600',
        'review': 'bg-amber-100 text-amber-600',
        'completed': 'bg-emerald-100 text-emerald-600',
        'cancelled': 'bg-red-100 text-red-600',
    };
    return classes[status] || 'bg-slate-100 text-slate-600';
};

const getStatusLabel = (status) => {
    const labels = {
        'pending': 'Pending',
        'in_progress': 'Dikerjakan',
        'review': 'Menunggu Review',
        'completed': 'Selesai',
        'cancelled': 'Dibatalkan',
    };
    return labels[status] || status;
};

const openCreateModal = () => {
    isEditing.value = false;
    form.value = { title: '', description: '', due_date: '', priority: 'medium', intern_id: '', estimated_hours: null };
    showFormModal.value = true;
};

const openEditModal = (task) => {
    isEditing.value = true;
    selectedTask.value = task;
    form.value = {
        title: task.title,
        description: task.description || '',
        due_date: task.due_date,
        priority: task.priority,
        intern_id: task.intern_id,
        estimated_hours: task.estimated_hours,
    };
    showFormModal.value = true;
};

const openReviewModal = (task) => {
    selectedTask.value = task;
    reviewForm.value = { feedback: '', rating: 5, actual_hours: task.estimated_hours };
    showReviewModal.value = true;
};

const openDetailModal = (task) => {
    selectedTask.value = task;
    showDetailModal.value = true;
};

const closeAllModals = () => {
    showFormModal.value = false;
    showReviewModal.value = false;
    showDetailModal.value = false;
    selectedTask.value = null;
};

const submitForm = async () => {
    isSubmitting.value = true;
    try {
        const url = isEditing.value
            ? route('mentor.tasks.update', selectedTask.value.id)
            : route('mentor.tasks.store');
        const method = isEditing.value ? 'put' : 'post';

        await axios[method](url, form.value);
        closeAllModals();
        router.reload({ only: ['tasks', 'stats'] });
    } catch (error) {
        alert(error.response?.data?.message || 'Terjadi kesalahan');
    } finally {
        isSubmitting.value = false;
    }
};

const approveTask = async () => {
    isSubmitting.value = true;
    try {
        await axios.post(route('mentor.tasks.approve', selectedTask.value.id), reviewForm.value);
        closeAllModals();
        router.reload({ only: ['tasks', 'stats'] });
    } catch (error) {
        alert(error.response?.data?.message || 'Terjadi kesalahan');
    } finally {
        isSubmitting.value = false;
    }
};

const rejectTask = async () => {
    if (!reviewForm.value.feedback) {
        alert('Feedback wajib diisi untuk reject');
        return;
    }
    isSubmitting.value = true;
    try {
        await axios.post(route('mentor.tasks.reject', selectedTask.value.id), { feedback: reviewForm.value.feedback });
        closeAllModals();
        router.reload({ only: ['tasks', 'stats'] });
    } catch (error) {
        alert(error.response?.data?.message || 'Terjadi kesalahan');
    } finally {
        isSubmitting.value = false;
    }
};

const deleteTask = async (task) => {
    confirmAction.value = {
        show: true,
        title: 'Hapus Tugas',
        message: `Yakin ingin menghapus tugas "${task.title}"?`,
        variant: 'danger',
        action: async () => {
            try {
                await axios.delete(route('mentor.tasks.destroy', task.id));
                router.reload({ only: ['tasks', 'stats'] });
            } catch (error) {
                alert(error.response?.data?.message || 'Terjadi kesalahan');
            }
        },
    };
};

const cancelTask = async (task) => {
    confirmAction.value = {
        show: true,
        title: 'Batalkan Tugas',
        message: `Yakin ingin membatalkan tugas "${task.title}"?`,
        variant: 'warning',
        action: async () => {
            try {
                await axios.post(route('mentor.tasks.cancel', task.id));
                router.reload({ only: ['tasks', 'stats'] });
            } catch (error) {
                alert(error.response?.data?.message || 'Terjadi kesalahan');
            }
        },
    };
};

// Confirm Modal State
const confirmAction = ref({ show: false, title: '', message: '', variant: 'danger', action: null });
const isConfirmLoading = ref(false);

const executeConfirm = async () => {
    if (!confirmAction.value.action) return;
    isConfirmLoading.value = true;
    try {
        await confirmAction.value.action();
    } finally {
        isConfirmLoading.value = false;
        confirmAction.value.show = false;
    }
};
</script>

<template>

    <Head title="Manajemen Tugas" />

    <div class="flex flex-col gap-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Manajemen Tugas</h1>
                <p class="text-slate-500 mt-1">Kelola tugas untuk mentee Anda.</p>
            </div>
            <button @click="openCreateModal"
                class="flex items-center gap-2 px-4 py-2.5 bg-primary hover:bg-primary-hover text-white rounded-xl font-semibold transition-colors cursor-pointer">
                <Plus class="w-4 h-4" />
                Buat Tugas
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="bg-white p-4 rounded-xl border border-slate-200 text-center">
                <ListTodo class="w-6 h-6 mx-auto mb-2 text-primary" />
                <p class="text-2xl font-black text-slate-900">{{ stats?.all || 0 }}</p>
                <p class="text-xs text-slate-500">Total Tugas</p>
            </div>
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 text-center">
                <Clock class="w-6 h-6 mx-auto mb-2 text-slate-600" />
                <p class="text-2xl font-black text-slate-700">{{ stats?.pending || 0 }}</p>
                <p class="text-xs text-slate-500">Pending</p>
            </div>
            <div class="bg-blue-50 p-4 rounded-xl border border-blue-200 text-center">
                <Loader2 class="w-6 h-6 mx-auto mb-2 text-blue-600" />
                <p class="text-2xl font-black text-blue-600">{{ stats?.in_progress || 0 }}</p>
                <p class="text-xs text-blue-600">Dikerjakan</p>
            </div>
            <div class="bg-amber-50 p-4 rounded-xl border border-amber-200 text-center">
                <Eye class="w-6 h-6 mx-auto mb-2 text-amber-600" />
                <p class="text-2xl font-black text-amber-600">{{ stats?.review || 0 }}</p>
                <p class="text-xs text-amber-600">Menunggu Review</p>
            </div>
            <div class="bg-emerald-50 p-4 rounded-xl border border-emerald-200 text-center">
                <CheckCircle2 class="w-6 h-6 mx-auto mb-2 text-emerald-600" />
                <p class="text-2xl font-black text-emerald-600">{{ stats?.completed || 0 }}</p>
                <p class="text-xs text-emerald-600">Selesai</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="p-4 border-b border-slate-200 flex flex-wrap items-center gap-4">
                <!-- Tabs -->
                <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl">
                    <button v-for="tab in tabs" :key="tab.key" @click="activeTab = tab.key"
                        class="px-3 py-1.5 text-sm rounded-lg cursor-pointer transition-all"
                        :class="activeTab === tab.key ? 'bg-white text-primary font-bold shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                        {{ tab.label }}
                        <span v-if="stats?.[tab.key]" class="ml-1 text-xs opacity-70">({{ stats[tab.key] }})</span>
                    </button>
                </div>

                <div class="flex-1"></div>

                <select v-model="selectedPriority"
                    class="px-3 py-2 border border-slate-200 rounded-xl text-sm cursor-pointer focus:ring-2 focus:ring-primary/20">
                    <option value="">Semua Priority</option>
                    <option v-for="p in priorityOptions" :key="p.value" :value="p.value">{{ p.label }}</option>
                </select>

                <select v-model="selectedIntern"
                    class="px-3 py-2 border border-slate-200 rounded-xl text-sm cursor-pointer focus:ring-2 focus:ring-primary/20">
                    <option value="">Semua Mentee</option>
                    <option v-for="m in mentees" :key="m.id" :value="m.id">{{ m.name }}</option>
                </select>

                <div class="relative">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                    <input v-model="searchQuery" @input="handleSearch" type="text" placeholder="Cari tugas..."
                        class="pl-10 pr-4 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 w-48">
                </div>
            </div>

            <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="task in tasks" :key="task.id"
                    class="bg-white rounded-xl border border-slate-200 p-4 hover:shadow-md hover:border-primary/30 transition-all"
                    :class="{ 'border-l-4 border-l-red-500': task.is_overdue && task.status !== 'completed' }">

                    <div class="flex items-start justify-between mb-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold"
                            :class="getPriorityClass(task.priority)">
                            {{ task.priority.toUpperCase() }}
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold" :class="getStatusClass(task.status)">
                            {{ getStatusLabel(task.status) }}
                        </span>
                    </div>

                    <h3 class="font-bold text-slate-900 mb-1 line-clamp-2">{{ task.title }}</h3>
                    <div v-if="task.description" class="rich-content text-sm text-slate-500 mb-3 line-clamp-2"
                        v-html="task.description"></div>

                    <div class="flex items-center gap-2 mb-3 text-xs text-slate-400">
                        <User class="w-3.5 h-3.5" />
                        <span>{{ task.intern_name }}</span>
                    </div>

                    <div class="flex items-center gap-4 mb-3 text-xs">
                        <span class="flex items-center gap-1"
                            :class="task.is_overdue && task.status !== 'completed' ? 'text-red-500 font-bold' : 'text-slate-500'">
                            <Calendar class="w-3.5 h-3.5" />
                            {{ task.due_date_formatted }}
                        </span>
                        <span v-if="task.estimated_hours" class="flex items-center gap-1 text-slate-500">
                            <Clock class="w-3.5 h-3.5" />
                            {{ task.estimated_hours }} jam
                        </span>
                    </div>

                    <div v-if="task.rating" class="flex items-center gap-1 mb-3">
                        <Star v-for="i in 5" :key="i" class="w-4 h-4"
                            :class="i <= task.rating ? 'text-amber-400 fill-amber-400' : 'text-slate-200'" />
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2 pt-3 border-t border-slate-100">
                        <button @click="openDetailModal(task)"
                            class="p-2 hover:bg-slate-100 rounded-lg text-slate-400 hover:text-primary cursor-pointer">
                            <Eye class="w-4 h-4" />
                        </button>
                        <button v-if="task.status === 'pending'" @click="openEditModal(task)"
                            class="p-2 hover:bg-slate-100 rounded-lg text-slate-400 hover:text-blue-500 cursor-pointer">
                            <Edit3 class="w-4 h-4" />
                        </button>
                        <button v-if="task.status === 'pending'" @click="deleteTask(task)"
                            class="p-2 hover:bg-slate-100 rounded-lg text-slate-400 hover:text-red-500 cursor-pointer">
                            <Trash2 class="w-4 h-4" />
                        </button>
                        <button v-if="task.status === 'review'" @click="openReviewModal(task)"
                            class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-bold cursor-pointer">
                            <CheckCircle2 class="w-4 h-4" /> Review
                        </button>
                        <button v-if="['pending', 'in_progress'].includes(task.status)" @click="cancelTask(task)"
                            class="p-2 hover:bg-red-50 rounded-lg text-slate-400 hover:text-red-500 cursor-pointer"
                            title="Batalkan">
                            <XCircle class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="!tasks?.length" class="col-span-full text-center py-12 text-slate-400">
                    <ListTodo class="w-16 h-16 mx-auto mb-4 text-slate-300" />
                    <p>Tidak ada tugas yang ditemukan.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <Teleport to="body">
        <div v-if="showFormModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeAllModals"></div>
            <div
                class="relative bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
                <div class="flex items-center justify-between p-5 border-b border-slate-200 shrink-0">
                    <h3 class="text-lg font-bold text-slate-900">{{ isEditing ? 'Edit Tugas' : 'Buat Tugas Baru' }}</h3>
                    <button @click="closeAllModals" class="p-2 hover:bg-slate-100 rounded-lg cursor-pointer">
                        <X class="w-5 h-5 text-slate-400" />
                    </button>
                </div>
                <form @submit.prevent="submitForm" class="p-5 flex flex-col gap-4 overflow-y-auto">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Judul Tugas *</label>
                        <input v-model="form.title" type="text" required
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20"
                            placeholder="Masukkan judul tugas">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Deskripsi</label>
                        <RichTextEditor v-model="form.description" placeholder="Deskripsi tugas..." minHeight="100px" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Mentee *</label>
                            <select v-model="form.intern_id" required :disabled="isEditing"
                                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20 disabled:bg-slate-50">
                                <option value="">Pilih Mentee</option>
                                <option v-for="m in mentees" :key="m.id" :value="m.id">{{ m.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Deadline *</label>
                            <input v-model="form.due_date" type="date" required
                                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Prioritas *</label>
                            <select v-model="form.priority" required
                                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20">
                                <option v-for="p in priorityOptions" :key="p.value" :value="p.value">{{ p.label }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Estimasi Jam</label>
                            <input v-model="form.estimated_hours" type="number" step="0.5" min="0.5"
                                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20"
                                placeholder="0">
                        </div>
                    </div>
                    <div class="flex gap-3 pt-4 border-t border-slate-200">
                        <button type="button" @click="closeAllModals"
                            class="flex-1 px-4 py-2.5 bg-slate-100 text-slate-600 rounded-xl font-semibold hover:bg-slate-200 cursor-pointer">Batal</button>
                        <button type="submit" :disabled="isSubmitting"
                            class="flex-1 px-4 py-2.5 bg-primary text-white rounded-xl font-semibold hover:bg-primary-hover disabled:opacity-50 cursor-pointer">
                            {{ isSubmitting ? 'Menyimpan...' : (isEditing ? 'Simpan' : 'Buat Tugas') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>

    <!-- Review Modal -->
    <Teleport to="body">
        <div v-if="showReviewModal && selectedTask" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeAllModals"></div>
            <div class="relative bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between p-5 border-b border-slate-200">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Review Tugas</h3>
                        <p class="text-sm text-slate-500">{{ selectedTask.title }}</p>
                    </div>
                    <button @click="closeAllModals" class="p-2 hover:bg-slate-100 rounded-lg cursor-pointer">
                        <X class="w-5 h-5 text-slate-400" />
                    </button>
                </div>
                <div class="p-5 flex flex-col gap-4 max-h-[60vh] overflow-y-auto">
                    <div class="bg-slate-50 p-4 rounded-xl">
                        <p class="text-xs text-slate-500 mb-1">Dikirim oleh</p>
                        <p class="font-semibold text-slate-900">{{ selectedTask.intern_name }}</p>
                    </div>

                    <!-- Submission Notes -->
                    <div v-if="selectedTask.submission_notes" class="bg-blue-50 border border-blue-200 p-4 rounded-xl">
                        <div class="flex items-center gap-2 mb-2">
                            <FileText class="w-4 h-4 text-blue-600" />
                            <span class="text-xs font-semibold text-blue-600">Catatan dari Intern</span>
                        </div>
                        <div class="text-sm text-blue-800 rich-content" v-html="selectedTask.submission_notes"></div>
                    </div>

                    <!-- Submission Files -->
                    <div v-if="selectedTask.submission_files?.length" class="border border-slate-200 rounded-xl p-4">
                        <p class="text-xs text-slate-500 font-semibold mb-2">File Submission ({{
                            selectedTask.submission_files.length }})</p>
                        <div class="flex flex-col gap-2">
                            <a v-for="(file, i) in selectedTask.submission_files" :key="i" :href="file.path"
                                target="_blank"
                                class="flex items-center gap-2 p-2 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors">
                                <Paperclip class="w-4 h-4 text-slate-400 shrink-0" />
                                <span class="text-sm text-primary truncate flex-1">{{ file.name }}</span>
                                <ExternalLink class="w-4 h-4 text-slate-400 shrink-0" />
                            </a>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Rating *</label>
                        <div class="flex gap-2">
                            <button v-for="i in 5" :key="i" type="button" @click="reviewForm.rating = i"
                                class="cursor-pointer">
                                <Star class="w-8 h-8"
                                    :class="i <= reviewForm.rating ? 'text-amber-400 fill-amber-400' : 'text-slate-200 hover:text-amber-200'" />
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Feedback</label>
                        <RichTextEditor v-model="reviewForm.feedback" placeholder="Berikan feedback untuk intern..."
                            minHeight="80px" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Actual Hours</label>
                        <input v-model="reviewForm.actual_hours" type="number" step="0.5" min="0.5"
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20"
                            placeholder="Jam kerja aktual">
                    </div>
                    <div class="flex gap-3 pt-4 border-t border-slate-200">
                        <button @click="rejectTask" :disabled="isSubmitting"
                            class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-red-100 text-red-600 rounded-xl font-semibold hover:bg-red-200 disabled:opacity-50 cursor-pointer">
                            <RotateCcw class="w-4 h-4" /> Revisi
                        </button>
                        <button @click="approveTask" :disabled="isSubmitting"
                            class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-500 text-white rounded-xl font-semibold hover:bg-emerald-600 disabled:opacity-50 cursor-pointer">
                            <CheckCircle2 class="w-4 h-4" /> Approve
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>

    <!-- Detail Modal -->
    <Teleport to="body">
        <div v-if="showDetailModal && selectedTask" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeAllModals"></div>
            <div
                class="relative bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between p-5 border-b border-slate-200 sticky top-0 bg-white">
                    <h3 class="text-lg font-bold text-slate-900">Detail Tugas</h3>
                    <button @click="closeAllModals" class="p-2 hover:bg-slate-100 rounded-lg cursor-pointer">
                        <X class="w-5 h-5 text-slate-400" />
                    </button>
                </div>
                <div class="p-5 flex flex-col gap-4">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold"
                            :class="getPriorityClass(selectedTask.priority)">{{ selectedTask.priority.toUpperCase()
                            }}</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold"
                            :class="getStatusClass(selectedTask.status)">{{ getStatusLabel(selectedTask.status)
                            }}</span>
                    </div>
                    <h2 class="text-xl font-bold text-slate-900">{{ selectedTask.title }}</h2>
                    <div v-if="selectedTask.description" class="rich-content text-slate-600"
                        v-html="selectedTask.description"></div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-slate-50 p-3 rounded-xl">
                            <p class="text-xs text-slate-500">Mentee</p>
                            <p class="font-semibold text-slate-900">{{ selectedTask.intern_name }}</p>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-xl">
                            <p class="text-xs text-slate-500">Deadline</p>
                            <p class="font-semibold text-slate-900"
                                :class="{ 'text-red-600': selectedTask.is_overdue }">{{ selectedTask.due_date_formatted
                                }}</p>
                        </div>
                        <div v-if="selectedTask.estimated_hours" class="bg-slate-50 p-3 rounded-xl">
                            <p class="text-xs text-slate-500">Estimasi</p>
                            <p class="font-semibold text-slate-900">{{ selectedTask.estimated_hours }} jam</p>
                        </div>
                        <div v-if="selectedTask.actual_hours" class="bg-slate-50 p-3 rounded-xl">
                            <p class="text-xs text-slate-500">Aktual</p>
                            <p class="font-semibold text-slate-900">{{ selectedTask.actual_hours }} jam</p>
                        </div>
                        <div v-if="selectedTask.started_at" class="bg-slate-50 p-3 rounded-xl">
                            <p class="text-xs text-slate-500">Mulai Dikerjakan</p>
                            <p class="font-semibold text-slate-900">{{ selectedTask.started_at }}</p>
                        </div>
                        <div v-if="selectedTask.completed_at" class="bg-slate-50 p-3 rounded-xl">
                            <p class="text-xs text-slate-500">Selesai</p>
                            <p class="font-semibold text-slate-900">{{ selectedTask.completed_at }}</p>
                        </div>
                    </div>

                    <div v-if="selectedTask.rating" class="flex items-center gap-2">
                        <p class="text-sm text-slate-500">Rating:</p>
                        <Star v-for="i in 5" :key="i" class="w-5 h-5"
                            :class="i <= selectedTask.rating ? 'text-amber-400 fill-amber-400' : 'text-slate-200'" />
                    </div>

                    <div v-if="selectedTask.feedback" class="bg-blue-50 border border-blue-200 p-4 rounded-xl">
                        <p class="text-xs text-blue-600 font-semibold mb-1">Feedback Mentor</p>
                        <div class="rich-content text-sm text-blue-800" v-html="selectedTask.feedback"></div>
                    </div>
                </div>
                <div class="p-5 border-t border-slate-200 bg-slate-50">
                    <button @click="closeAllModals"
                        class="w-full px-4 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-semibold hover:bg-slate-100 cursor-pointer">Tutup</button>
                </div>
            </div>
        </div>
    </Teleport>

    <!-- Confirm Modal -->
    <ConfirmModal :show="confirmAction.show" :title="confirmAction.title" :message="confirmAction.message"
        :variant="confirmAction.variant" :loading="isConfirmLoading" @confirm="executeConfirm"
        @cancel="confirmAction.show = false" />
</template>