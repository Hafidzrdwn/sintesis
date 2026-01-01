<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import {
    Filter,
    Calendar,
    Search,
    Clock,
    CheckCircle2,
    AlertCircle,
    Play,
    Send,
    Eye,
    AlertTriangle
} from 'lucide-vue-next';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    tasks: Array,
    stats: Object,
    filters: Object,
});

const activeTab = ref(props.filters?.status || 'all');
const searchQuery = ref(props.filters?.search || '');
const isSubmitting = ref({});
const actionSuccess = ref(null);
const actionError = ref(null);

const tabs = [
    { key: 'all', label: 'Semua', count: props.stats?.all || 0 },
    { key: 'pending', label: 'Pending', count: props.stats?.pending || 0 },
    { key: 'in_progress', label: 'Dikerjakan', count: props.stats?.in_progress || 0 },
    { key: 'review', label: 'Review', count: props.stats?.review || 0 },
    { key: 'completed', label: 'Selesai', count: props.stats?.completed || 0 },
];

const applyFilters = () => {
    router.get(route('intern.tasks'), {
        status: activeTab.value,
        search: searchQuery.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

watch(activeTab, () => {
    applyFilters();
});

let searchTimeout;
const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
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

const getPriorityLabel = (priority) => {
    const labels = { 'urgent': 'Urgent', 'high': 'High', 'medium': 'Medium', 'low': 'Low' };
    return labels[priority] || priority;
};

const getStatusColor = (status) => {
    const colors = {
        'pending': 'text-slate-500',
        'in_progress': 'text-primary',
        'review': 'text-amber-500',
        'completed': 'text-emerald-500',
    };
    return colors[status] || 'text-slate-500';
};

const getStatusLabel = (status) => {
    const labels = {
        'pending': 'Pending',
        'in_progress': 'Sedang Dikerjakan',
        'review': 'Menunggu Review',
        'completed': 'Selesai',
    };
    return labels[status] || status;
};

const getStatusIcon = (status) => {
    const icons = {
        'pending': Clock,
        'in_progress': Play,
        'review': Eye,
        'completed': CheckCircle2,
    };
    return icons[status] || Clock;
};

const startTask = async (taskId) => {
    isSubmitting.value[taskId] = true;
    try {
        const response = await axios.post(`/intern/tasks/${taskId}/start`);
        if (response.data.success) {
            actionSuccess.value = response.data.message;
            router.reload();
        }
    } catch (error) {
        actionError.value = error.response?.data?.message || 'Terjadi kesalahan';
    } finally {
        isSubmitting.value[taskId] = false;
        setTimeout(() => { actionSuccess.value = null; actionError.value = null; }, 3000);
    }
};

const submitTask = async (taskId) => {
    isSubmitting.value[taskId] = true;
    try {
        const response = await axios.post(`/intern/tasks/${taskId}/submit`);
        if (response.data.success) {
            actionSuccess.value = response.data.message;
            router.reload();
        }
    } catch (error) {
        actionError.value = error.response?.data?.message || 'Terjadi kesalahan';
    } finally {
        isSubmitting.value[taskId] = false;
        setTimeout(() => { actionSuccess.value = null; actionError.value = null; }, 3000);
    }
};

const filteredTasks = computed(() => props.tasks || []);
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

        <div v-if="actionSuccess"
            class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 flex items-center gap-2">
            <CheckCircle2 class="w-5 h-5 shrink-0" />
            <span class="text-sm font-medium">{{ actionSuccess }}</span>
        </div>
        <div v-if="actionError"
            class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 flex items-center gap-2">
            <AlertCircle class="w-5 h-5 shrink-0" />
            <span class="text-sm font-medium">{{ actionError }}</span>
        </div>

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
                    class="w-full pl-10 pr-4 py-2 bg-slate-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all placeholder:text-slate-400">
            </div>
        </div>

        <div v-if="filteredTasks.length === 0" class="bg-white p-12 rounded-2xl border border-slate-200 text-center">
            <Clock class="w-12 h-12 mx-auto mb-4 text-slate-300" />
            <h3 class="font-bold text-slate-700 mb-1">Tidak ada tugas</h3>
            <p class="text-sm text-slate-400">Belum ada tugas yang diberikan untuk Anda.</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <div v-for="task in filteredTasks" :key="task.id"
                class="group bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:shadow-md hover:border-primary/30 transition-all flex flex-col h-full relative overflow-hidden">
                <div
                    class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-primary/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                </div>

                <div class="flex justify-between items-start mb-3">
                    <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border"
                        :class="getPriorityColor(task.priority)">
                        {{ getPriorityLabel(task.priority) }}
                    </span>
                    <div class="flex items-center gap-1.5 text-xs font-medium" :class="getStatusColor(task.status)">
                        <component :is="getStatusIcon(task.status)" class="w-3.5 h-3.5" />
                        {{ getStatusLabel(task.status) }}
                    </div>
                </div>

                <div class="mb-4 flex-1">
                    <h3
                        class="text-base font-bold text-slate-900 leading-snug group-hover:text-primary transition-colors mb-2 line-clamp-2">
                        {{ task.title }}</h3>
                    <p class="text-sm text-slate-500 line-clamp-2">{{ task.description }}</p>
                </div>

                <div class="flex items-center justify-between text-xs text-slate-500 mb-4">
                    <div class="flex items-center gap-1.5" :class="task.is_overdue ? 'text-red-500 font-semibold' : ''">
                        <Calendar class="w-3.5 h-3.5" />
                        {{ task.due_date_formatted }}
                        <span v-if="task.is_overdue" class="text-red-500">(Terlambat)</span>
                    </div>
                    <div v-if="task.mentor_name" class="text-slate-400">
                        {{ task.mentor_name }}
                    </div>
                </div>

                <div class="grid gap-2 mt-auto">
                    <button v-if="task.status === 'pending'" @click="startTask(task.id)"
                        :disabled="isSubmitting[task.id]"
                        class="flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-bold transition-all cursor-pointer disabled:opacity-50">
                        <Play class="w-4 h-4" />
                        {{ isSubmitting[task.id] ? 'Loading...' : 'Mulai Kerjakan' }}
                    </button>
                    <button v-else-if="task.status === 'in_progress'" @click="submitTask(task.id)"
                        :disabled="isSubmitting[task.id]"
                        class="flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition-all cursor-pointer disabled:opacity-50">
                        <Send class="w-4 h-4" />
                        {{ isSubmitting[task.id] ? 'Loading...' : 'Kirim untuk Review' }}
                    </button>
                    <div v-else-if="task.status === 'review'"
                        class="flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-600 text-xs font-bold">
                        <Eye class="w-4 h-4" />
                        Menunggu Review Mentor
                    </div>
                    <div v-else-if="task.status === 'completed'"
                        class="flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 text-xs font-bold">
                        <CheckCircle2 class="w-4 h-4" />
                        Selesai
                        <span v-if="task.rating" class="ml-1">⭐ {{ task.rating }}/5</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
