<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    LayoutDashboard,
    ClipboardList,
    UserPlus,
    CalendarClock,
    AlertTriangle,
    CheckCircle2,
    ArrowRight,
    Users,
    Briefcase,
    FileUser,
    Activity,
    Clock,
    ExternalLink
} from 'lucide-vue-next';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    stats: Array,
    recentActivity: Array,
    endingSoon: Array,
    quickStats: Object,
});

const page = usePage();
const user = computed(() => {
    return page.props.auth.user;
});

const getIconComponent = (iconName) => {
    const icons = {
        'clipboard-list': ClipboardList,
        'user-plus': UserPlus,
        'calendar-clock': CalendarClock,
        'alert-triangle': AlertTriangle,
    };
    return icons[iconName] || ClipboardList;
};

const getColorClasses = (color) => {
    const colors = {
        'amber': {
            bg: 'bg-amber-50',
            icon: 'bg-amber-100 text-amber-600',
            text: 'text-amber-600',
            border: 'border-amber-200',
        },
        'blue': {
            bg: 'bg-blue-50',
            icon: 'bg-blue-100 text-blue-600',
            text: 'text-blue-600',
            border: 'border-blue-200',
        },
        'purple': {
            bg: 'bg-purple-50',
            icon: 'bg-purple-100 text-purple-600',
            text: 'text-purple-600',
            border: 'border-purple-200',
        },
        'red': {
            bg: 'bg-red-50',
            icon: 'bg-red-100 text-red-600',
            text: 'text-red-600',
            border: 'border-red-200',
        },
        'emerald': {
            bg: 'bg-emerald-50',
            icon: 'bg-emerald-100 text-emerald-600',
            text: 'text-emerald-600',
            border: 'border-emerald-200',
        },
    };
    return colors[color] || colors['blue'];
};

const getActivityColor = (action) => {
    const colors = {
        'created': 'bg-emerald-100 text-emerald-700',
        'updated': 'bg-blue-100 text-blue-700',
        'deleted': 'bg-red-100 text-red-700',
        'login': 'bg-cyan-100 text-cyan-700',
        'logout': 'bg-slate-100 text-slate-700',
    };
    return colors[action] || 'bg-slate-100 text-slate-700';
};

const getDaysLeftColor = (days) => {
    if (days <= 2) return 'text-red-600 bg-red-50';
    if (days <= 5) return 'text-amber-600 bg-amber-50';
    return 'text-emerald-600 bg-emerald-50';
};
</script>

<template>

    <Head title="Admin Dashboard" />

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
                <p class="text-md text-slate-500">Selamat datang, {{ user.name }}!</p>
            </div>
            <div class="text-right hidden md:block">
                <p class="text-sm text-slate-500">{{ new Date().toLocaleDateString('id-ID', {
                    weekday: 'long', year:
                        'numeric', month: 'long', day: 'numeric' }) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <Link v-for="stat in stats" :key="stat.key" :href="stat.link"
                class="group bg-white rounded-2xl border shadow-sm p-5 hover:shadow-md transition-all duration-200"
                :class="getColorClasses(stat.color).border">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center"
                            :class="getColorClasses(stat.color).icon">
                            <component :is="getIconComponent(stat.icon)" class="w-6 h-6" />
                        </div>
                        <div>
                            <p class="text-3xl font-bold" :class="getColorClasses(stat.color).text">
                                {{ stat.value }}
                            </p>
                            <p class="text-sm font-medium text-slate-700">{{ stat.label }}</p>
                        </div>
                    </div>
                    <ArrowRight
                        class="w-5 h-5 text-slate-300 group-hover:text-slate-500 group-hover:translate-x-1 transition-all" />
                </div>
                <p class="text-xs text-slate-500 mt-3">{{ stat.description }}</p>
            </Link>
        </div>

        <!-- Main Content Grid -->
        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Left Column: Recent Activity -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <Activity class="w-5 h-5 text-primary" />
                        <h2 class="font-bold text-slate-900">Aktivitas Terbaru</h2>
                    </div>
                    <Link :href="route('admin.audit')"
                        class="text-sm text-primary hover:underline flex items-center gap-1">
                        Lihat Semua
                        <ExternalLink class="w-4 h-4" />
                    </Link>
                </div>
                <div class="divide-y divide-slate-100">
                    <div v-for="activity in recentActivity" :key="activity.id"
                        class="px-6 py-4 flex items-center gap-4 hover:bg-slate-50/50 transition-colors">
                        <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center flex-shrink-0">
                            <Users class="w-5 h-5 text-slate-500" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-slate-900">
                                <span class="font-semibold">{{ activity.user }}</span>
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold mx-1.5"
                                    :class="getActivityColor(activity.action_type)">
                                    {{ activity.action }}
                                </span>
                                <span class="text-slate-600">{{ activity.model }}</span>
                            </p>
                            <p class="text-xs text-slate-400 mt-0.5 flex items-center gap-1">
                                <Clock class="w-3 h-3" />
                                {{ activity.time }}
                            </p>
                        </div>
                    </div>
                    <div v-if="recentActivity?.length === 0" class="px-6 py-12 text-center">
                        <Activity class="w-12 h-12 text-slate-300 mx-auto mb-3" />
                        <p class="text-slate-500">Belum ada aktivitas</p>
                    </div>
                </div>
            </div>

            <!-- Right Column: Ending Soon + Quick Stats -->
            <div class="space-y-6">
                <!-- Ending Soon -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2">
                        <CalendarClock class="w-5 h-5 text-purple-600" />
                        <h2 class="font-bold text-slate-900">Segera Berakhir</h2>
                    </div>
                    <div class="divide-y divide-slate-100">
                        <div v-for="intern in endingSoon" :key="intern.id"
                            class="px-5 py-3 flex items-center justify-between hover:bg-slate-50/50 transition-colors">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-slate-900 truncate">{{ intern.intern_name }}</p>
                                <p class="text-xs text-slate-500 truncate">{{ intern.position }}</p>
                            </div>
                            <div class="text-right flex-shrink-0 ml-3">
                                <span class="px-2 py-1 rounded-full text-xs font-bold"
                                    :class="getDaysLeftColor(intern.days_left)">
                                    {{ intern.days_left }} hari
                                </span>
                                <p class="text-xs text-slate-400 mt-1">{{ intern.end_date }}</p>
                            </div>
                        </div>
                        <div v-if="endingSoon?.length === 0" class="px-5 py-8 text-center">
                            <CheckCircle2 class="w-10 h-10 text-emerald-400 mx-auto mb-2" />
                            <p class="text-sm text-slate-500">Tidak ada magang berakhir dalam 2 minggu</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                    <h2 class="font-bold text-slate-900 mb-4">Statistik Cepat</h2>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                            <div class="flex items-center gap-2">
                                <FileUser class="w-5 h-5 text-slate-500" />
                                <span class="text-sm text-slate-600">Total Pelamar</span>
                            </div>
                            <span class="font-bold text-slate-900">{{ quickStats?.total_applicants || 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                            <div class="flex items-center gap-2">
                                <Briefcase class="w-5 h-5 text-slate-500" />
                                <span class="text-sm text-slate-600">Intern Aktif</span>
                            </div>
                            <span class="font-bold text-slate-900">{{ quickStats?.active_interns || 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                            <div class="flex items-center gap-2">
                                <Users class="w-5 h-5 text-slate-500" />
                                <span class="text-sm text-slate-600">Mentor Aktif</span>
                            </div>
                            <span class="font-bold text-slate-900">{{ quickStats?.total_mentors || 0 }}</span>
                        </div>
                        <div
                            class="flex items-center justify-between p-3 bg-primary/5 rounded-xl border border-primary/10">
                            <div class="flex items-center gap-2">
                                <UserPlus class="w-5 h-5 text-primary" />
                                <span class="text-sm text-slate-600">Pelamar Bulan Ini</span>
                            </div>
                            <span class="font-bold text-primary">{{ quickStats?.this_month_applications || 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <h2 class="font-bold text-slate-900 mb-4">Aksi Cepat</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <Link :href="route('admin.recruitment')"
                    class="flex items-center gap-3 p-4 bg-slate-50 rounded-xl hover:bg-slate-100 transition-colors group">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                        <ClipboardList class="w-5 h-5 text-primary" />
                    </div>
                    <div>
                        <p class="font-semibold text-slate-900 text-sm">Review Pelamar</p>
                        <p class="text-xs text-slate-500">Kelola lamaran</p>
                    </div>
                </Link>
                <Link :href="route('admin.internships')"
                    class="flex items-center gap-3 p-4 bg-slate-50 rounded-xl hover:bg-slate-100 transition-colors group">
                    <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                        <Briefcase class="w-5 h-5 text-emerald-600" />
                    </div>
                    <div>
                        <p class="font-semibold text-slate-900 text-sm">Kelola Magang</p>
                        <p class="text-xs text-slate-500">Lihat internship</p>
                    </div>
                </Link>
                <Link :href="route('admin.users')"
                    class="flex items-center gap-3 p-4 bg-slate-50 rounded-xl hover:bg-slate-100 transition-colors group">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                        <Users class="w-5 h-5 text-blue-600" />
                    </div>
                    <div>
                        <p class="font-semibold text-slate-900 text-sm">Kelola User</p>
                        <p class="text-xs text-slate-500">Intern & Mentor</p>
                    </div>
                </Link>
                <Link :href="route('admin.monitoring')"
                    class="flex items-center gap-3 p-4 bg-slate-50 rounded-xl hover:bg-slate-100 transition-colors group">
                    <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                        <Activity class="w-5 h-5 text-purple-600" />
                    </div>
                    <div>
                        <p class="font-semibold text-slate-900 text-sm">Monitoring</p>
                        <p class="text-xs text-slate-500">Statistik global</p>
                    </div>
                </Link>
            </div>
        </div>
    </div>
</template>
