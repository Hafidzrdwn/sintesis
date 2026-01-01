<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import {
    Calendar, Search, Clock, CheckCircle2, AlertCircle, Play, Send, Eye, Star,
    X, FileText, Upload, Paperclip, Trash2, Download, ExternalLink
} from 'lucide-vue-next';
import RichTextEditor from '@/Components/RichTextEditor.vue';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    tasks: Array,
    stats: Object,
    filters: Object,
});

const activeTab = ref(props.filters?.status || 'all');
const searchQuery = ref(props.filters?.search || '');
const isSubmitting = ref({});
const actionMessage = ref({ type: '', text: '' });

const showDetailModal = ref(false);
const showSubmitModal = ref(false);
const selectedTask = ref(null);

const submitForm = ref({ notes: '', files: [] });
const fileInput = ref(null);

const tabs = computed(() => [
    { key: 'all', label: 'Semua', count: props.stats?.all || 0 },
    { key: 'pending', label: 'Pending', count: props.stats?.pending || 0 },
    { key: 'in_progress', label: 'Dikerjakan', count: props.stats?.in_progress || 0 },
    { key: 'review', label: 'Review', count: props.stats?.review || 0 },
    { key: 'completed', label: 'Selesai', count: props.stats?.completed || 0 },
]);

const applyFilters = () => {
    router.get(route('intern.tasks'), {
        status: activeTab.value,
        search: searchQuery.value,
    }, { preserveState: true, preserveScroll: true });
};

watch(activeTab, () => applyFilters());

let searchTimeout;
const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 500);
};

const getPriorityColor = (priority) => {
    const colors = {
        'urgent': 'bg-red-100 text-red-700 border-red-200',
        'high': 'bg-orange-50 text-orange-600 border-orange-200',
        'medium': 'bg-amber-50 text-amber-600 border-amber-200',
        'low': 'bg-blue-50 text-blue-600 border-blue-200',
    };
    return colors[priority] || 'bg-slate-50 text-slate-600 border-slate-200';
};

const getStatusColor = (status) => {
    const colors = {
        'pending': 'bg-slate-100 text-slate-600',
        'in_progress': 'bg-blue-100 text-blue-600',
        'review': 'bg-amber-100 text-amber-600',
        'completed': 'bg-emerald-100 text-emerald-600',
    };
    return colors[status] || 'bg-slate-100 text-slate-600';
};

const getStatusLabel = (status) => {
    const labels = {
        'pending': 'Pending',
        'in_progress': 'Dikerjakan',
        'review': 'Menunggu Review',
        'completed': 'Selesai',
    };
    return labels[status] || status;
};

const openDetailModal = (task) => {
    selectedTask.value = task;
    showDetailModal.value = true;
};

const openSubmitModal = (task) => {
    selectedTask.value = task;
    submitForm.value = { notes: '', files: [] };
    showSubmitModal.value = true;
};

const closeModals = () => {
    showDetailModal.value = false;
    showSubmitModal.value = false;
    selectedTask.value = null;
};

const showMessage = (type, text) => {
    actionMessage.value = { type, text };
    setTimeout(() => { actionMessage.value = { type: '', text: '' }; }, 3000);
};

const reloadData = () => {
    router.reload({ only: ['tasks', 'stats'] });
};

const startTask = async (taskId) => {
    isSubmitting.value[taskId] = true;
    try {
        const response = await axios.post(`/intern/tasks/${taskId}/start`);
        if (response.data.success) {
            showMessage('success', response.data.message);
            closeModals();
            reloadData();
        }
    } catch (error) {
        showMessage('error', error.response?.data?.message || 'Terjadi kesalahan');
    } finally {
        isSubmitting.value[taskId] = false;
    }
};

const handleFileSelect = (event) => {
    const files = Array.from(event.target.files);
    const maxSize = 10 * 1024 * 1024;

    files.forEach(file => {
        if (submitForm.value.files.length >= 5) {
            showMessage('error', 'Maksimal 5 file');
            return;
        }
        if (file.size > maxSize) {
            showMessage('error', `File ${file.name} terlalu besar (max 10MB)`);
            return;
        }
        submitForm.value.files.push(file);
    });

    if (fileInput.value) fileInput.value.value = '';
};

const removeFile = (index) => {
    submitForm.value.files.splice(index, 1);
};

const formatFileSize = (bytes) => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
};

const submitTaskForReview = async () => {
    if (!selectedTask.value) return;

    isSubmitting.value[selectedTask.value.id] = true;
    try {
        const formData = new FormData();
        formData.append('notes', submitForm.value.notes || '');
        submitForm.value.files.forEach((file, i) => {
            formData.append(`files[${i}]`, file);
        });

        const response = await axios.post(`/intern/tasks/${selectedTask.value.id}/submit`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        if (response.data.success) {
            showMessage('success', response.data.message);
            closeModals();
            reloadData();
        }
    } catch (error) {
        showMessage('error', error.response?.data?.message || 'Terjadi kesalahan');
    } finally {
        isSubmitting.value[selectedTask.value.id] = false;
    }
};
</script>

<template>

    <Head title="Tugas Saya" />

    <div class="flex flex-col gap-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Tugas Saya</h1>
                <p class="text-slate-500 mt-1">Kelola dan pantau progres pekerjaan Anda.</p>
            </div>
        </div>

        <!-- Flash Message -->
        <div v-if="actionMessage.text" class="p-4 rounded-xl flex items-center gap-2"
            :class="actionMessage.type === 'success' ? 'bg-emerald-50 border border-emerald-200 text-emerald-700' : 'bg-red-50 border border-red-200 text-red-700'">
            <component :is="actionMessage.type === 'success' ? CheckCircle2 : AlertCircle" class="w-5 h-5 shrink-0" />
            <span class="text-sm font-medium">{{ actionMessage.text }}</span>
        </div>

        <!-- Filters -->
        <div
            class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-2 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex p-1 bg-slate-100 rounded-xl w-full sm:w-auto overflow-x-auto">
                <button v-for="tab in tabs" :key="tab.key" @click="activeTab = tab.key"
                    class="px-3 py-2 rounded-lg text-sm font-medium transition-all whitespace-nowrap flex items-center gap-2 cursor-pointer"
                    :class="activeTab === tab.key ? 'bg-white text-primary shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-200/50'">
                    {{ tab.label }}
                    <span class="text-[10px] px-1.5 py-0.5 rounded-full"
                        :class="activeTab === tab.key ? 'bg-primary/10 text-primary' : 'bg-slate-200 text-slate-500'">
                        {{ tab.count }}
                    </span>
                </button>
            </div>
            <div class="relative w-full sm:w-64">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                <input v-model="searchQuery" @input="handleSearch" type="text" placeholder="Cari tugas..."
                    class="w-full pl-10 pr-4 py-2 bg-slate-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:bg-white">
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!tasks?.length" class="bg-white p-12 rounded-2xl border border-slate-200 text-center">
            <Clock class="w-12 h-12 mx-auto mb-4 text-slate-300" />
            <h3 class="font-bold text-slate-700 mb-1">Tidak ada tugas</h3>
            <p class="text-sm text-slate-400">Belum ada tugas yang diberikan untuk Anda.</p>
        </div>

        <!-- Task Cards -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <div v-for="task in tasks" :key="task.id" @click="openDetailModal(task)"
                class="group bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:shadow-md hover:border-primary/30 transition-all cursor-pointer"
                :class="{ 'border-l-4 border-l-red-500': task.is_overdue && task.status !== 'completed' }">

                <div class="flex justify-between items-start mb-3">
                    <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border"
                        :class="getPriorityColor(task.priority)">
                        {{ task.priority }}
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold" :class="getStatusColor(task.status)">
                        {{ getStatusLabel(task.status) }}
                    </span>
                </div>

                <h3
                    class="text-base font-bold text-slate-900 mb-2 line-clamp-2 group-hover:text-primary transition-colors">
                    {{ task.title }}</h3>
                <div v-if="task.description" class="rich-content text-sm text-slate-500 line-clamp-2 mb-3"
                    v-html="task.description"></div>

                <div class="flex items-center justify-between text-xs text-slate-500 mb-4">
                    <div class="flex items-center gap-1.5"
                        :class="task.is_overdue && task.status !== 'completed' ? 'text-red-500 font-semibold' : ''">
                        <Calendar class="w-3.5 h-3.5" />
                        {{ task.due_date_formatted }}
                    </div>
                    <div v-if="task.mentor_name" class="text-slate-400">{{ task.mentor_name }}</div>
                </div>

                <div v-if="task.rating" class="flex items-center gap-1 mb-3">
                    <Star v-for="i in 5" :key="i" class="w-4 h-4"
                        :class="i <= task.rating ? 'text-amber-400 fill-amber-400' : 'text-slate-200'" />
                </div>

                <!-- Quick Status Badge -->
                <div class="text-center text-xs font-medium py-2 rounded-xl" :class="{
                    'bg-slate-50 text-slate-500': task.status === 'pending',
                    'bg-blue-50 text-blue-600': task.status === 'in_progress',
                    'bg-amber-50 text-amber-600': task.status === 'review',
                    'bg-emerald-50 text-emerald-600': task.status === 'completed',
                }">
                    <span v-if="task.status === 'pending'">Klik untuk lihat detail</span>
                    <span v-else-if="task.status === 'in_progress'">Sedang dikerjakan</span>
                    <span v-else-if="task.status === 'review'">Menunggu review mentor</span>
                    <span v-else-if="task.status === 'completed'">Selesai ⭐ {{ task.rating }}/5</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <Teleport to="body">
        <div v-if="showDetailModal && selectedTask" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModals"></div>
            <div
                class="relative bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between p-5 border-b border-slate-200 sticky top-0 bg-white z-10">
                    <h3 class="text-lg font-bold text-slate-900">Detail Tugas</h3>
                    <button @click="closeModals" class="p-2 hover:bg-slate-100 rounded-lg cursor-pointer">
                        <X class="w-5 h-5 text-slate-400" />
                    </button>
                </div>

                <div class="p-5 flex flex-col gap-4">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold border"
                            :class="getPriorityColor(selectedTask.priority)">{{ selectedTask.priority.toUpperCase()
                            }}</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold"
                            :class="getStatusColor(selectedTask.status)">{{ getStatusLabel(selectedTask.status)
                            }}</span>
                    </div>

                    <h2 class="text-xl font-bold text-slate-900">{{ selectedTask.title }}</h2>
                    <div v-if="selectedTask.description" class="rich-content text-slate-600"
                        v-html="selectedTask.description"></div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-slate-50 p-3 rounded-xl">
                            <p class="text-xs text-slate-500">Mentor</p>
                            <p class="font-semibold text-slate-900">{{ selectedTask.mentor_name }}</p>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-xl">
                            <p class="text-xs text-slate-500">Deadline</p>
                            <p class="font-semibold"
                                :class="selectedTask.is_overdue ? 'text-red-600' : 'text-slate-900'">{{
                                    selectedTask.due_date_formatted }}</p>
                        </div>
                        <div v-if="selectedTask.estimated_hours" class="bg-slate-50 p-3 rounded-xl">
                            <p class="text-xs text-slate-500">Estimasi Waktu</p>
                            <p class="font-semibold text-slate-900">{{ selectedTask.estimated_hours }} jam</p>
                        </div>
                        <div v-if="selectedTask.started_at" class="bg-slate-50 p-3 rounded-xl">
                            <p class="text-xs text-slate-500">Mulai Dikerjakan</p>
                            <p class="font-semibold text-slate-900">{{ selectedTask.started_at }}</p>
                        </div>
                    </div>

                    <!-- Submission Info -->
                    <div v-if="selectedTask.submission_notes" class="bg-blue-50 border border-blue-200 p-4 rounded-xl">
                        <p class="text-xs text-blue-600 font-semibold mb-1">Catatan Submission</p>
                        <div class="text-sm text-blue-800 rich-content" v-html="selectedTask.submission_notes"></div>
                    </div>

                    <div v-if="selectedTask.submission_files?.length" class="border border-slate-200 rounded-xl p-4">
                        <p class="text-xs text-slate-500 font-semibold mb-2">File Submission</p>
                        <div class="flex flex-col gap-2">
                            <a v-for="(file, i) in selectedTask.submission_files" :key="i" :href="file.path"
                                target="_blank"
                                class="flex items-center gap-2 p-2 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors">
                                <Paperclip class="w-4 h-4 text-slate-400" />
                                <span class="text-sm text-primary truncate flex-1">{{ file.name }}</span>
                                <ExternalLink class="w-4 h-4 text-slate-400" />
                            </a>
                        </div>
                    </div>

                    <!-- Rating & Feedback -->
                    <div v-if="selectedTask.rating" class="flex items-center gap-2">
                        <p class="text-sm text-slate-500">Rating:</p>
                        <Star v-for="i in 5" :key="i" class="w-5 h-5"
                            :class="i <= selectedTask.rating ? 'text-amber-400 fill-amber-400' : 'text-slate-200'" />
                    </div>

                    <div v-if="selectedTask.feedback" class="bg-emerald-50 border border-emerald-200 p-4 rounded-xl">
                        <p class="text-xs text-emerald-600 font-semibold mb-1">Feedback Mentor</p>
                        <div class="rich-content text-sm text-emerald-800" v-html="selectedTask.feedback"></div>
                    </div>
                </div>

                <div class="p-5 border-t border-slate-200 bg-slate-50 flex gap-3">
                    <button v-if="selectedTask.status === 'pending'" @click="startTask(selectedTask.id)"
                        :disabled="isSubmitting[selectedTask.id]"
                        class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-primary text-white rounded-xl font-semibold hover:bg-primary-hover disabled:opacity-50 cursor-pointer">
                        <Play class="w-4 h-4" />
                        {{ isSubmitting[selectedTask.id] ? 'Loading...' : 'Mulai Kerjakan' }}
                    </button>
                    <button v-else-if="selectedTask.status === 'in_progress'"
                        @click="showDetailModal = false; openSubmitModal(selectedTask)"
                        class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-amber-500 text-white rounded-xl font-semibold hover:bg-amber-600 cursor-pointer">
                        <Send class="w-4 h-4" />
                        Kirim untuk Review
                    </button>
                    <button v-else @click="closeModals"
                        class="flex-1 px-4 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-semibold hover:bg-slate-100 cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </Teleport>

    <!-- Submit Modal -->
    <Teleport to="body">
        <div v-if="showSubmitModal && selectedTask" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModals"></div>
            <div class="relative bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between p-5 border-b border-slate-200">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Kirim untuk Review</h3>
                        <p class="text-sm text-slate-500">{{ selectedTask.title }}</p>
                    </div>
                    <button @click="closeModals" class="p-2 hover:bg-slate-100 rounded-lg cursor-pointer">
                        <X class="w-5 h-5 text-slate-400" />
                    </button>
                </div>

                <div class="p-5 flex flex-col gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Catatan untuk Mentor</label>
                        <RichTextEditor v-model="submitForm.notes"
                            placeholder="Jelaskan apa yang sudah dikerjakan, link PR, atau catatan lainnya..."
                            minHeight="100px" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Upload File (Opsional)</label>
                        <div
                            class="border-2 border-dashed border-slate-200 rounded-xl p-4 text-center hover:border-primary/50 transition-colors">
                            <input ref="fileInput" type="file" multiple
                                accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.zip" @change="handleFileSelect"
                                class="hidden" id="file-upload">
                            <label for="file-upload" class="cursor-pointer">
                                <Upload class="w-8 h-8 mx-auto mb-2 text-slate-400" />
                                <p class="text-sm text-slate-500">Klik untuk upload file</p>
                                <p class="text-xs text-slate-400 mt-1">Max 5 files, 10MB each (jpg, png, pdf, doc, zip)
                                </p>
                            </label>
                        </div>

                        <div v-if="submitForm.files.length" class="mt-3 flex flex-col gap-2">
                            <div v-for="(file, i) in submitForm.files" :key="i"
                                class="flex items-center gap-2 p-2 bg-slate-50 rounded-lg">
                                <Paperclip class="w-4 h-4 text-slate-400 shrink-0" />
                                <span class="text-sm text-slate-600 truncate flex-1">{{ file.name }}</span>
                                <span class="text-xs text-slate-400">{{ formatFileSize(file.size) }}</span>
                                <button @click="removeFile(i)"
                                    class="p-1 hover:bg-red-100 rounded text-slate-400 hover:text-red-500 cursor-pointer">
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-5 border-t border-slate-200 bg-slate-50 flex gap-3">
                    <button @click="closeModals"
                        class="flex-1 px-4 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-semibold hover:bg-slate-100 cursor-pointer">
                        Batal
                    </button>
                    <button @click="submitTaskForReview" :disabled="isSubmitting[selectedTask.id]"
                        class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-primary text-white rounded-xl font-semibold hover:bg-primary-hover disabled:opacity-50 cursor-pointer">
                        <Send class="w-4 h-4" />
                        {{ isSubmitting[selectedTask.id] ? 'Mengirim...' : 'Kirim Review' }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
