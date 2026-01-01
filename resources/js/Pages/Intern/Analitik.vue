<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    BarChart3,
    PieChart,
    TrendingUp,
    Award,
    CalendarCheck,
    Clock,
    AlertTriangle,
    ArrowUpRight,
    ArrowDownRight,
    Target,
    CheckCircle2
} from 'lucide-vue-next';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend,
    ArcElement
} from 'chart.js';
import { Bar, Doughnut } from 'vue-chartjs';

defineOptions({ layout: AuthenticatedLayout });

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend,
    ArcElement
);

const props = defineProps({
    stats: Object,
    weeklyActivity: Array,
    taskDistribution: Object,
    internshipProgress: Object,
});

const weeklyActivityData = computed(() => ({
    labels: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'],
    datasets: [
        {
            label: 'Log Harian',
            backgroundColor: '#0b5cad',
            borderRadius: 6,
            data: props.weeklyActivity || [0, 0, 0, 0, 0]
        }
    ]
}));

const weeklyActivityOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#1e293b',
            padding: 10,
            cornerRadius: 8,
            displayColors: false,
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: { stepSize: 1 },
            grid: { color: '#f1f5f9' }
        },
        x: {
            grid: { display: false }
        }
    }
};

const taskStatusData = computed(() => ({
    labels: ['Selesai', 'Proses', 'Review', 'Pending'],
    datasets: [
        {
            backgroundColor: ['#10b981', '#0b5cad', '#f59e0b', '#94a3b8'],
            borderWidth: 0,
            data: [
                props.taskDistribution?.completed || 0,
                props.taskDistribution?.in_progress || 0,
                props.taskDistribution?.review || 0,
                props.taskDistribution?.pending || 0,
            ]
        }
    ]
}));

const taskStatusOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                usePointStyle: true,
                padding: 15,
                font: { family: 'inter', size: 11 }
            }
        }
    },
    cutout: '70%'
};

const totalTasks = computed(() => props.stats?.total_tasks || 0);

const statsConfig = computed(() => [
    {
        title: 'Tingkat Kehadiran',
        value: `${props.stats?.attendance_rate || 0}%`,
        trend: props.stats?.attendance_trend > 0
            ? `+${props.stats.attendance_trend}%`
            : `${props.stats?.attendance_trend || 0}%`,
        trendUp: (props.stats?.attendance_trend || 0) >= 0,
        icon: CalendarCheck,
        color: 'bg-emerald-50 text-emerald-600'
    },
    {
        title: 'Tugas Selesai',
        value: props.stats?.completed_tasks || 0,
        sub: `dari ${props.stats?.total_tasks || 0} tugas`,
        trend: props.stats?.completed_tasks === props.stats?.total_tasks ? 'Semua Selesai' : 'Berjalan',
        trendUp: true,
        icon: BarChart3,
        color: 'bg-blue-50 text-blue-600'
    },
    {
        title: 'Rata-rata Jam Kerja',
        value: `${props.stats?.avg_working_hours || 0}`,
        sub: 'jam/hari',
        trend: (props.stats?.avg_working_hours || 0) >= 8 ? 'Baik' : 'Kurang',
        trendUp: (props.stats?.avg_working_hours || 0) >= 7,
        icon: Clock,
        color: 'bg-purple-50 text-purple-600'
    },
    {
        title: 'Ketepatan Waktu',
        value: `${props.stats?.on_time_rate || 0}%`,
        sub: `${props.stats?.late_days || 0} hari terlambat`,
        trend: (props.stats?.on_time_rate || 0) >= 90 ? 'Excellent' : 'Perlu Perbaikan',
        trendUp: (props.stats?.on_time_rate || 0) >= 80,
        icon: Target,
        color: 'bg-amber-50 text-amber-600'
    }
]);
</script>

<template>

    <Head title="Analitik Performa" />

    <div class="flex flex-col gap-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Analitik Performa</h1>
                <p class="text-slate-500 mt-1">Ringkasan statistik dan perkembangan magang Anda.</p>
            </div>
        </div>

        <div v-if="internshipProgress" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">trending_up</span>
                        Progress Magang
                    </h3>
                    <p class="text-slate-500 text-sm mt-1">Periode: {{ internshipProgress.start_date }} s/d {{
                        internshipProgress.end_date }}</p>
                </div>
                <div class="flex items-center gap-6">
                    <div class="text-center">
                        <span class="text-2xl font-black text-primary">{{ internshipProgress.elapsed_days }}</span>
                        <span class="text-xs text-slate-500 block">Hari Berjalan</span>
                    </div>
                    <div class="w-px h-10 bg-slate-200"></div>
                    <div class="text-center">
                        <span class="text-2xl font-black text-emerald-600">{{ internshipProgress.remaining_days
                            }}</span>
                        <span class="text-xs text-slate-500 block">Hari Tersisa</span>
                    </div>
                    <div class="w-px h-10 bg-slate-200"></div>
                    <div class="text-center">
                        <span class="text-2xl font-black text-slate-700">{{ internshipProgress.total_days }}</span>
                        <span class="text-xs text-slate-500 block">Total Hari</span>
                    </div>
                </div>
            </div>
            <div class="relative">
                <div class="w-full bg-slate-100 rounded-full h-4 overflow-hidden">
                    <div class="bg-gradient-to-r from-primary to-blue-500 h-full rounded-full transition-all duration-500 relative"
                        :style="{ width: `${internshipProgress.progress}%` }">
                    </div>
                </div>
                <div class="flex justify-between mt-3">
                    <span class="text-xs text-slate-500">Mulai</span>
                    <span class="text-sm font-bold text-primary">{{ internshipProgress.progress }}% Tercapai</span>
                    <span class="text-xs text-slate-500">Selesai</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div v-for="(stat, index) in statsConfig" :key="index"
                class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col gap-3 relative overflow-hidden group hover:shadow-md transition-shadow">
                <div class="absolute -right-4 -bottom-4 w-20 h-20 rounded-full opacity-5 transition-transform group-hover:scale-110"
                    :class="stat.color.replace('text-', 'bg-')"></div>
                <div class="flex items-center justify-between z-10">
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ stat.title }}</span>
                    <div class="p-2 rounded-lg" :class="stat.color">
                        <component :is="stat.icon" class="w-4 h-4" />
                    </div>
                </div>
                <div class="flex flex-col gap-1 z-10">
                    <h2 class="text-2xl font-black text-slate-900">{{ stat.value }}</h2>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded flex items-center gap-0.5"
                            :class="stat.trendUp ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'">
                            <component :is="stat.trendUp ? ArrowUpRight : ArrowDownRight" class="w-3 h-3" />
                            {{ stat.trend }}
                        </span>
                        <span v-if="stat.sub" class="text-xs text-slate-400">{{ stat.sub }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="font-bold text-slate-900 text-lg">Aktivitas Jurnal Mingguan</h3>
                        <p class="text-sm text-slate-500">Frekuensi pengisian logbook minggu ini</p>
                    </div>
                    <div class="p-2 rounded-lg bg-blue-50 text-blue-600">
                        <TrendingUp class="w-5 h-5" />
                    </div>
                </div>
                <div class="flex-1 min-h-[280px] relative w-full">
                    <Bar :data="weeklyActivityData" :options="weeklyActivityOptions" />
                </div>
            </div>

            <div class="lg:col-span-1 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="font-bold text-slate-900 text-lg">Distribusi Tugas</h3>
                        <p class="text-sm text-slate-500">Status penyelesaian tugas</p>
                    </div>
                    <div class="p-2 rounded-lg bg-emerald-50 text-emerald-600">
                        <PieChart class="w-5 h-5" />
                    </div>
                </div>
                <div class="flex-1 min-h-[280px] relative w-full flex items-center justify-center">
                    <Doughnut :data="taskStatusData" :options="taskStatusOptions" />
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-3xl font-black text-slate-900">{{ totalTasks }}</span>
                        <span class="text-xs text-slate-500 uppercase font-bold tracking-wider">Total</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-xl border border-slate-200 flex items-center gap-3">
                <div class="p-2 rounded-lg bg-emerald-50 text-emerald-600">
                    <CheckCircle2 class="w-5 h-5" />
                </div>
                <div>
                    <p class="text-xs text-slate-500">In Progress</p>
                    <p class="text-lg font-bold text-slate-900">{{ stats?.in_progress_tasks || 0 }}</p>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 flex items-center gap-3">
                <div class="p-2 rounded-lg bg-amber-50 text-amber-600">
                    <Clock class="w-5 h-5" />
                </div>
                <div>
                    <p class="text-xs text-slate-500">Under Review</p>
                    <p class="text-lg font-bold text-slate-900">{{ stats?.review_tasks || 0 }}</p>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 flex items-center gap-3">
                <div class="p-2 rounded-lg bg-blue-50 text-blue-600">
                    <AlertTriangle class="w-5 h-5" />
                </div>
                <div>
                    <p class="text-xs text-slate-500">Izin/Sakit</p>
                    <p class="text-lg font-bold text-slate-900">{{ stats?.leave_days || 0 }} hari</p>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 flex items-center gap-3">
                <div class="p-2 rounded-lg bg-purple-50 text-purple-600">
                    <Award class="w-5 h-5" />
                </div>
                <div>
                    <p class="text-xs text-slate-500">Rata-rata Rating</p>
                    <p class="text-lg font-bold text-slate-900">{{ stats?.avg_rating || '-' }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
