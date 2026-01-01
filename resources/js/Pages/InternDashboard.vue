<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AttendanceCard from '@/Components/AttendanceCard.vue';
import { formatDate } from '@/utils/helpers';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    internship: Object,
    tasks: Array,
    logbooks: Array,
    todayPresence: Object,
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Selamat Pagi';
    if (hour < 15) return 'Selamat Siang';
    if (hour < 18) return 'Selamat Sore';
    return 'Selamat Malam';
});

const todayDate = computed(() => {
    return new Date().toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
});

const taskFilter = ref('all');

const filteredTasks = computed(() => {
    if (!props.tasks) return [];

    switch (taskFilter.value) {
        case 'today':
            return props.tasks.filter(t => t.days_until_due === 0 && t.status !== 'completed');
        case 'overdue':
            return props.tasks.filter(t => t.is_overdue);
        default:
            return props.tasks;
    }
});

const getTaskStatusStyle = (status) => {
    const styles = {
        'pending': { bg: 'bg-slate-100', text: 'text-slate-600', border: 'border-slate-200', label: 'Akan Dikerjakan' },
        'in_progress': { bg: 'bg-blue-50', text: 'text-blue-700', border: 'border-blue-200', label: 'Sedang Berjalan' },
        'review': { bg: 'bg-purple-50', text: 'text-purple-700', border: 'border-purple-200', label: 'Review' },
        'completed': { bg: 'bg-green-50', text: 'text-green-700', border: 'border-green-200', label: 'Selesai' },
    };
    return styles[status] || styles.pending;
};

const getPriorityIcon = (priority) => {
    const icons = {
        'urgent': 'priority_high',
        'high': 'arrow_upward',
        'medium': 'remove',
        'low': 'arrow_downward',
    };
    return icons[priority] || 'remove';
};

const getPriorityColor = (priority) => {
    const colors = {
        'urgent': 'text-red-600 bg-red-50 border-red-100',
        'high': 'text-orange-600 bg-orange-50 border-orange-100',
        'medium': 'text-blue-600 bg-blue-50 border-blue-100',
        'low': 'text-slate-500 bg-slate-50 border-slate-100',
    };
    return colors[priority] || colors.medium;
};
</script>

<template>

    <Head title="Dasbor Intern" />

    <div class="flex flex-wrap justify-between items-end gap-4">
        <div class="flex flex-col gap-1">
            <p class="text-text-main text-3xl md:text-4xl font-black leading-tight tracking-tight">
                {{ greeting }}, {{ user.name.split(' ')[0] }}
            </p>
            <p class="text-text-secondary text-base font-medium">{{ todayDate }} • Jadikan hari ini bermakna</p>
        </div>
        <div
            class="hidden sm:flex items-center gap-2 bg-white rounded-full px-4 py-2 border border-slate-200 shadow-sm">
            <span class="relative flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-success"></span>
            </span>
            <span class="text-sm font-semibold text-slate-700">Online</span>
        </div>
    </div>

    <AttendanceCard office-name="PT Inosoft Trans Sistem" :office-coordinates="[-7.3172337, 112.7888917]"
        :max-distance="100" :today-presence="todayPresence" />

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        <div class="xl:col-span-2 flex flex-col gap-4">
            <div class="flex items-center justify-between px-1">
                <h3 class="text-lg font-bold text-text-main">Ringkasan Tugas</h3>
                <div class="flex bg-slate-200 rounded-lg p-1">
                    <button @click="taskFilter = 'all'"
                        class="px-4 py-1.5 rounded-md text-xs font-semibold transition-all"
                        :class="taskFilter === 'all' ? 'bg-white text-primary shadow-sm' : 'text-text-secondary hover:text-text-main hover:bg-white/50'">Semua</button>
                    <button @click="taskFilter = 'today'"
                        class="px-4 py-1.5 rounded-md text-xs font-medium transition-all"
                        :class="taskFilter === 'today' ? 'bg-white text-primary shadow-sm' : 'text-text-secondary hover:text-text-main hover:bg-white/50'">Tenggat
                        Hari Ini</button>
                    <button @click="taskFilter = 'overdue'"
                        class="px-4 py-1.5 rounded-md text-xs font-medium transition-all"
                        :class="taskFilter === 'overdue' ? 'bg-white text-primary shadow-sm' : 'text-text-secondary hover:text-text-main hover:bg-white/50'">Terlewat</button>
                </div>
            </div>

            <div class="flex flex-col gap-3">
                <div v-for="task in filteredTasks" :key="task.id"
                    class="group flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 rounded-xl bg-white border border-slate-200 hover:border-primary/40 transition-colors shadow-sm"
                    :class="{ 'opacity-70': task.status === 'completed' }">
                    <div class="flex items-start gap-4">
                        <div class="mt-1 p-2 rounded-full border" :class="getPriorityColor(task.priority)">
                            <span class="material-symbols-outlined text-xl">{{ getPriorityIcon(task.priority) }}</span>
                        </div>
                        <div>
                            <h4 class="font-bold text-text-main leading-tight"
                                :class="{ 'line-through decoration-slate-400': task.status === 'completed' }">
                                {{ task.title }}
                            </h4>
                            <p class="text-sm text-text-secondary mt-1">
                                {{ task.description?.substring(0, 50) }}{{ task.description?.length > 50 ? '...' : '' }}
                                <span v-if="task.due_date_human" class="ml-2">• Tenggat {{ task.due_date_human }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between sm:justify-end gap-4 w-full sm:w-auto">
                        <span v-if="task.is_overdue"
                            class="px-2 py-0.5 rounded-full bg-red-100 text-red-700 text-[10px] font-bold">
                            TERLAMBAT
                        </span>
                        <span
                            class="whitespace-nowrap px-3 py-1 rounded-full border text-xs font-bold uppercase tracking-wider"
                            :class="[getTaskStatusStyle(task.status).bg, getTaskStatusStyle(task.status).text, getTaskStatusStyle(task.status).border]">
                            {{ getTaskStatusStyle(task.status).label }}
                        </span>
                    </div>
                </div>

                <div v-if="filteredTasks.length === 0"
                    class="p-8 text-center bg-white rounded-xl border border-slate-200">
                    <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">assignment</span>
                    <p class="text-slate-500">
                        {{ taskFilter === 'all' ? 'Belum ada tugas' : taskFilter === 'today' ? 'Tidak ada tugas jatuh tempo hari ini' : 'Tidak ada tugas terlewat' }}
                    </p>
                </div>
            </div>

            <Link :href="route('intern.tasks')"
                class="text-primary hover:text-primary-hover text-sm font-semibold flex items-center justify-center gap-1 transition-colors py-2">
                Lihat Semua Tugas <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </Link>
        </div>

        <div class="xl:col-span-1 flex flex-col gap-4">
            <div class="flex items-center justify-between px-1">
                <h3 class="text-lg font-bold text-text-main">Buku Log Terbaru</h3>
                <Link :href="route('intern.logbook')"
                    class="text-primary hover:text-primary-hover text-sm font-semibold flex items-center gap-1 transition-colors">
                    Lihat Semua <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </Link>
            </div>
            <div class="flex flex-col bg-white rounded-2xl p-5 shadow-sm border border-slate-200 h-full min-h-[300px]">
                <Link :href="route('intern.logbook')"
                    class="w-full mb-6 py-3 border-2 border-dashed border-slate-300 rounded-xl text-text-secondary hover:border-primary hover:text-primary hover:bg-blue-50 transition-all flex items-center justify-center gap-2 group">
                    <span class="material-symbols-outlined group-hover:scale-110 transition-transform">edit_note</span>
                    <span class="font-medium">Tulis Refleksi Harian</span>
                </Link>
                <div class="flex flex-col gap-6 relative">
                    <div v-if="logbooks?.length" class="absolute left-3 top-2 bottom-2 w-0.5 bg-slate-200 z-0"></div>

                    <div v-for="(log, index) in logbooks" :key="log.id" class="flex gap-4 relative z-10">
                        <div class="shrink-0 w-6 h-6 rounded-full border-4 border-white shadow-sm mt-1"
                            :class="index === 0 ? 'bg-primary' : 'bg-slate-300'"></div>
                        <div class="flex flex-col gap-1">
                            <div class="flex justify-between items-baseline">
                                <span class="text-sm font-bold text-text-main">{{ log.date_human }}</span>
                            </div>
                            <p class="text-sm text-slate-600 leading-relaxed line-clamp-2">
                                {{ log.activity || log.description }}
                            </p>
                        </div>
                    </div>

                    <div v-if="!logbooks?.length" class="text-center py-6">
                        <span class="material-symbols-outlined text-3xl text-slate-300 mb-2">book</span>
                        <p class="text-sm text-slate-500">Belum ada catatan logbook</p>
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
