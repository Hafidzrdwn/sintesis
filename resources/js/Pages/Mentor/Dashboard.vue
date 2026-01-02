<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    Users, ClipboardCheck, BookOpen, Award, Clock, AlertTriangle,
    ArrowRight, CheckCircle2, Timer, TrendingUp, Calendar, Sparkles
} from 'lucide-vue-next';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    mentees: Array,
    taskStats: Object,
    logbookStats: Object,
    evaluationStats: Object,
    recentTasksForReview: Array,
    recentLogbooks: Array,
    greeting: String,
});

const user = computed(() => usePage().props.auth?.user);

const activeMentees = computed(() => props.mentees?.filter(m => m.status === 'active') || []);

const getProgressColor = (progress) => {
    if (progress >= 90) return 'text-emerald-500';
    if (progress >= 70) return 'text-blue-500';
    if (progress >= 50) return 'text-amber-500';
    return 'text-slate-400';
};

const getProgressBg = (progress) => {
    if (progress >= 90) return 'stroke-emerald-500';
    if (progress >= 70) return 'stroke-blue-500';
    if (progress >= 50) return 'stroke-amber-500';
    return 'stroke-slate-300';
};

const getPriorityColor = (priority) => {
    const colors = {
        urgent: 'bg-red-100 text-red-700 border-red-200',
        high: 'bg-orange-100 text-orange-700 border-orange-200',
        medium: 'bg-blue-100 text-blue-700 border-blue-200',
        low: 'bg-slate-100 text-slate-600 border-slate-200',
    };
    return colors[priority] || colors.medium;
};

const circumference = 2 * Math.PI * 36;
</script>

<template>

    <Head title="Dashboard Mentor" />

    <div class="flex flex-col gap-6">
        <div class="relative overflow-hidden bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-primary/5 rounded-full blur-2xl"></div>
            <div class="absolute -right-5 -bottom-5 w-32 h-32 bg-blue-500/5 rounded-full blur-xl"></div>

            <div class="relative flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 text-primary mb-1">
                        <Sparkles class="w-4 h-4" />
                        <span class="text-sm font-medium">{{ greeting }}</span>
                    </div>
                    <h1 class="text-2xl font-bold text-slate-800">{{ user?.name }}</h1>
                    <p class="text-slate-500 mt-1">Kelola dan pantau perkembangan mentee Anda</p>
                </div>

                <div class="hidden md:flex items-center gap-3">
                    <div class="flex items-center gap-2 bg-emerald-50 text-emerald-700 px-4 py-2 rounded-full">
                        <Users class="w-4 h-4" />
                        <span class="font-semibold">{{ activeMentees.length }}</span>
                        <span class="text-sm">Mentee Aktif</span>
                    </div>
                    <div v-if="taskStats?.review > 0"
                        class="flex items-center gap-2 bg-amber-50 text-amber-700 px-4 py-2 rounded-full animate-pulse">
                        <ClipboardCheck class="w-4 h-4" />
                        <span class="font-semibold">{{ taskStats.review }}</span>
                        <span class="text-sm">Perlu Review</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 flex flex-col gap-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <Users class="w-5 h-5 text-primary" />
                        Mentee Saya
                    </h2>
                    <span class="text-sm text-slate-500">{{ activeMentees.length }} aktif</span>
                </div>

                <div v-if="activeMentees.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-for="mentee in activeMentees" :key="mentee.id"
                        class="group bg-white rounded-xl border border-slate-200 p-4 hover:shadow-lg hover:border-primary/30 transition-all duration-300">
                        <div class="flex items-start gap-4">
                            <div class="relative shrink-0">
                                <svg class="w-20 h-20 -rotate-90">
                                    <circle cx="40" cy="40" r="36" fill="none" stroke-width="6"
                                        class="stroke-slate-100" />
                                    <circle cx="40" cy="40" r="36" fill="none" stroke-width="6"
                                        :class="getProgressBg(Math.round(mentee.progress))" stroke-linecap="round"
                                        :stroke-dasharray="circumference"
                                        :stroke-dashoffset="circumference - (Math.round(mentee.progress) / 100) * circumference" />
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-sm font-bold"
                                        :class="getProgressColor(Math.round(mentee.progress))">
                                        {{ Math.round(mentee.progress) }}%
                                    </span>
                                </div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <img v-if="mentee.intern_avatar" :src="mentee.intern_avatar"
                                        class="w-6 h-6 rounded-full object-cover" />
                                    <div v-else
                                        class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center">
                                        <span class="text-xs font-bold text-primary">
                                            {{ mentee.intern_name?.charAt(0) }}
                                        </span>
                                    </div>
                                    <h3 class="font-semibold text-slate-800 truncate">{{ mentee.intern_name }}</h3>
                                </div>
                                <p class="text-xs text-slate-500 mb-2">{{ mentee.position }}</p>

                                <div class="flex items-center gap-3 text-xs">
                                    <span class="flex items-center gap-1 text-slate-400">
                                        <Calendar class="w-3 h-3" />
                                        {{ mentee.end_date }}
                                    </span>
                                    <span v-if="mentee.days_remaining <= 14"
                                        class="flex items-center gap-1 text-amber-600 font-medium">
                                        <Timer class="w-3 h-3" />
                                        {{ mentee.days_remaining }} hari lagi
                                    </span>
                                </div>

                                <div class="flex items-center gap-2 mt-3">
                                    <Link v-if="mentee.can_evaluate && !mentee.is_evaluated"
                                        :href="route('mentor.evaluation')"
                                        class="flex items-center gap-1 px-3 py-1.5 bg-emerald-500 text-white text-xs font-medium rounded-lg hover:bg-emerald-600 transition-colors">
                                        <Award class="w-3 h-3" />
                                        Evaluasi
                                    </Link>
                                    <span v-else-if="mentee.is_evaluated"
                                        class="flex items-center gap-1 px-3 py-1.5 bg-emerald-100 text-emerald-700 text-xs font-medium rounded-lg">
                                        <CheckCircle2 class="w-3 h-3" />
                                        Selesai
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-slate-50 rounded-xl p-8 text-center">
                    <Users class="w-12 h-12 text-slate-300 mx-auto mb-3" />
                    <p class="text-slate-500">Belum ada mentee aktif</p>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-slate-800 flex items-center gap-2">
                            <ClipboardCheck class="w-5 h-5 text-primary" />
                            Ringkasan Tugas
                        </h3>
                        <Link :href="route('mentor.tasks')"
                            class="text-sm text-primary hover:underline flex items-center gap-1">
                            Lihat Semua
                            <ArrowRight class="w-4 h-4" />
                        </Link>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="text-center p-3 bg-slate-50 rounded-xl">
                            <p class="text-2xl font-bold text-slate-700">{{ taskStats?.pending || 0 }}</p>
                            <p class="text-xs text-slate-500">Pending</p>
                        </div>
                        <div class="text-center p-3 bg-blue-50 rounded-xl">
                            <p class="text-2xl font-bold text-blue-600">{{ taskStats?.in_progress || 0 }}</p>
                            <p class="text-xs text-blue-600">Dikerjakan</p>
                        </div>
                        <div class="text-center p-3 bg-amber-50 rounded-xl">
                            <p class="text-2xl font-bold text-amber-600">{{ taskStats?.review || 0 }}</p>
                            <p class="text-xs text-amber-600">Review</p>
                        </div>
                        <div class="text-center p-3 bg-emerald-50 rounded-xl">
                            <p class="text-2xl font-bold text-emerald-600">{{ taskStats?.completed || 0 }}</p>
                            <p class="text-xs text-emerald-600">Selesai</p>
                        </div>
                    </div>

                    <div v-if="taskStats?.overdue > 0"
                        class="mt-4 flex items-center gap-3 p-3 bg-red-50 border border-red-200 rounded-xl">
                        <AlertTriangle class="w-5 h-5 text-red-500 shrink-0" />
                        <p class="text-sm text-red-700">
                            <span class="font-semibold">{{ taskStats.overdue }} tugas</span> melewati deadline
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-6">
                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <h3 class="font-bold text-slate-800 mb-4">Aksi Cepat</h3>
                    <div class="flex flex-col gap-2">
                        <Link :href="route('mentor.tasks')"
                            class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl hover:bg-primary/5 hover:border-primary/20 border border-transparent transition-all group">
                            <div
                                class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                                <ClipboardCheck class="w-5 h-5 text-primary" />
                            </div>
                            <div>
                                <p class="font-medium text-slate-800">Kelola Tugas</p>
                                <p class="text-xs text-slate-500">Buat & review tugas</p>
                            </div>
                        </Link>
                        <Link :href="route('mentor.logbook')"
                            class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl hover:bg-primary/5 hover:border-primary/20 border border-transparent transition-all group">
                            <div
                                class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                                <BookOpen class="w-5 h-5 text-blue-600" />
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-slate-800">Review Logbook</p>
                                <p class="text-xs text-slate-500">{{ logbookStats?.pending_review || 0 }} menunggu</p>
                            </div>
                            <span v-if="logbookStats?.pending_review > 0"
                                class="px-2 py-1 bg-blue-500 text-white text-xs font-bold rounded-full">
                                {{ logbookStats.pending_review }}
                            </span>
                        </Link>
                        <Link :href="route('mentor.evaluation')"
                            class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl hover:bg-primary/5 hover:border-primary/20 border border-transparent transition-all group">
                            <div
                                class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center group-hover:bg-emerald-200 transition-colors">
                                <Award class="w-5 h-5 text-emerald-600" />
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-slate-800">Evaluasi</p>
                                <p class="text-xs text-slate-500">{{ evaluationStats?.pending || 0 }} siap evaluasi</p>
                            </div>
                            <span v-if="evaluationStats?.pending > 0"
                                class="px-2 py-1 bg-emerald-500 text-white text-xs font-bold rounded-full">
                                {{ evaluationStats.pending }}
                            </span>
                        </Link>
                    </div>
                </div>

                <div v-if="recentTasksForReview?.length > 0" class="bg-white rounded-xl border border-slate-200 p-5">
                    <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <Clock class="w-4 h-4 text-amber-500" />
                        Menunggu Review
                    </h3>
                    <div class="flex flex-col gap-3">
                        <div v-for="task in recentTasksForReview" :key="task.id"
                            class="flex items-center gap-3 p-3 bg-amber-50/50 rounded-lg border border-amber-100">
                            <img v-if="task.intern_avatar" :src="task.intern_avatar"
                                class="w-8 h-8 rounded-full object-cover" />
                            <div v-else class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center">
                                <span class="text-xs font-bold text-amber-700">{{ task.intern_name?.charAt(0) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-800 truncate">{{ task.title }}</p>
                                <p class="text-xs text-slate-500">{{ task.intern_name }} • {{ task.submitted_at }}</p>
                            </div>
                            <span class="px-2 py-0.5 text-xs font-medium rounded border"
                                :class="getPriorityColor(task.priority)">
                                {{ task.priority }}
                            </span>
                        </div>
                    </div>
                </div>

                <div v-if="recentLogbooks?.length > 0" class="bg-white rounded-xl border border-slate-200 p-5">
                    <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <BookOpen class="w-4 h-4 text-blue-500" />
                        Logbook Terbaru
                    </h3>
                    <div class="flex flex-col gap-3">
                        <div v-for="logbook in recentLogbooks" :key="logbook.id"
                            class="flex items-center gap-3 p-3 bg-blue-50/50 rounded-lg border border-blue-100">
                            <img v-if="logbook.intern_avatar" :src="logbook.intern_avatar"
                                class="w-8 h-8 rounded-full object-cover" />
                            <div v-else class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                <span class="text-xs font-bold text-blue-700">{{ logbook.intern_name?.charAt(0)
                                }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-800">{{ logbook.intern_name }}</p>
                                <p class="text-xs text-slate-500">{{ logbook.date }} • {{ logbook.activities_count }}
                                    aktivitas</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-primary to-blue-600 rounded-xl p-5 text-white">
                    <div class="flex items-center gap-2 mb-3">
                        <TrendingUp class="w-5 h-5" />
                        <h3 class="font-bold">Ringkasan</h3>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-3xl font-bold">{{ evaluationStats?.completed || 0 }}</p>
                            <p class="text-sm text-white/80">Evaluasi Selesai</p>
                        </div>
                        <div>
                            <p class="text-3xl font-bold">{{ taskStats?.completed || 0 }}</p>
                            <p class="text-sm text-white/80">Tugas Selesai</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
