<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import ChartDataLabels from 'chartjs-plugin-datalabels';
import {
    Chart as ChartJS,
    ArcElement,
    BarElement,
    LineElement,
    PointElement,
    CategoryScale,
    LinearScale,
    Title,
    Tooltip,
    Legend,
    Filler
} from 'chart.js';
import { Doughnut, Bar, Line } from 'vue-chartjs';
import {
    Building2,
    TrendingUp,
    AlertTriangle,
    CheckCircle2,
    Gauge,
    Calendar,
    BarChart3,
    Users,
    Target,
    FileUser,
    Filter
} from 'lucide-vue-next';
import { formatDateShort } from '@/utils/helpers';
import { tooltip } from 'leaflet';

ChartJS.register(
    ArcElement,
    BarElement,
    LineElement,
    PointElement,
    CategoryScale,
    LinearScale,
    Title,
    Tooltip,
    Legend,
    Filler
);

ChartDataLabels.defaults.color = '#fff';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    universityData: Object,
    mentorData: Object,
    historicalData: Array,
    statusDistribution: Array,
    skillDistribution: Array | Object,
    monthlyByUniversity: Object,
});

function wrapTextByWord(text, maxLength = 30) {
    if (!text) return '';
    
    const words = text.split(' ');
    const lines = [];
    let currentLine = '';
    
    words.forEach(word => {
        const testLine = currentLine
        ? `${currentLine} ${word}`
        : word;

        if (testLine.length <= maxLength) {
            currentLine = testLine;
        } else {
            lines.push(currentLine);
            currentLine = word;
        }
    });
    
    if (currentLine) {
        lines.push(currentLine);
    }
    
    return lines;
}



// ==========================================
// DRILL-DOWN STATE
// ==========================================
const drillLevel = ref(0);
const selectedUniversity = ref(null);
const selectedPosition = ref(null);
const institutionSearch = ref('');

const displayedInstitutions = computed(() => {
    const top = props.universityData?.top || [];
    const all = props.universityData?.all || [];

    if (!institutionSearch.value.trim()) {
        return top;
    }

    const search = institutionSearch.value.toLowerCase();
    return all.filter(inst =>
        inst.name.toLowerCase().includes(search)
    ).slice(0, 10);
});

const isSearchEmpty = computed(() => {
    return institutionSearch.value.trim() !== '' && displayedInstitutions.value.length === 0;
});

const breadcrumbs = computed(() => {
    const crumbs = [{ label: 'Institusi', level: 0 }];
    if (drillLevel.value >= 1 && selectedUniversity.value) {
        crumbs.push({ label: selectedUniversity.value.name, level: 1 });
    }
    if (drillLevel.value >= 2 && selectedPosition.value) {
        crumbs.push({ label: selectedPosition.value.name, level: 2 });
    }
    return crumbs;
});

const navigateToBreadcrumb = (level) => {
    drillLevel.value = level;
    if (level === 0) {
        selectedUniversity.value = null;
        selectedPosition.value = null;
    } else if (level === 1) {
        selectedPosition.value = null;
    }
};

const handleDrillDown = (index) => {
    if (drillLevel.value === 0) {
        const topInstitutions = props.universityData?.top || [];
        const institutions = displayedInstitutions.value;

        if (!institutions[index].positions && institutions[index].id === topInstitutions[index].id) {
            selectedUniversity.value = topInstitutions[index];
        } else {
            selectedUniversity.value = institutions[index];
        }

        drillLevel.value = 1;
    } else if (drillLevel.value === 1 && selectedUniversity.value) {
        selectedPosition.value = selectedUniversity.value.positions[index];
        drillLevel.value = 2;
    }
};

const primaryColor = '#0055b3';
const chartColors = [
    '#0055b3', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
    '#06b6d4', '#ec4899', '#84cc16', '#f97316', '#6366f1'
];

const universityChartData = computed(() => {
    const data = displayedInstitutions.value || [];
    return {
        labels: data.map(u => u.name),
        datasets: [{
            label: 'Total Lamaran',
            data: data.map(u => u.total_applications),
            total_positions: data.map(u => u.total_positions),
            backgroundColor: chartColors,
            borderWidth: 0,
            hoverOffset: 8,
        }]
    };
})

const positionChartData = computed(() => ({
    labels: selectedUniversity.value?.positions?.map(p => p.name) || [],
    datasets: [
        {
            label: 'Total Lamaran',
            data: selectedUniversity.value?.positions?.map(p => p.total_applications) || [],
            backgroundColor: '#94a3b8',
            borderRadius: 6,
            barThickness: 25,
        },
        {
            label: 'Jumlah Pelamar (Unique)',
            data: selectedUniversity.value?.positions?.map(p => p.total_applicants) || [],
            backgroundColor: primaryColor,
            borderRadius: 6,
            barThickness: 25,
        }
    ]
}));

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '55%',
    onHover: (event, chartElement) => {
        event.native.target.style.cursor = chartElement.length ? 'pointer' : 'default';
    },
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                font: { family: 'Lexend', size: 11 },
                usePointStyle: true,
                padding: 12,
                boxWidth: 8,
            }
        },
        tooltip: {
            callbacks: {
                title: (items) => {
                    return wrapTextByWord(items[0].label)
                },
                label: (ctx) => {
                    return [
                        `Total Posisi Dilamar : ${ctx.dataset.total_positions[ctx.dataIndex]}`,
                        `Total Lamaran : ${ctx.raw} Lamaran`
                    ]
                }
            }
        },
        datalabels: {
            font: {
                weight: '600',
                size: 12,
            }
        }
    },
    onClick: (evt, elements) => {
        if (elements.length > 0) {
            handleDrillDown(elements[0].index);
        }
    }
};

const barOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: 'top',
            labels: {
                usePointStyle: true,
                font: { family: 'Lexend', size: 12 }
            }
        },
        tooltip: {
            mode: 'index',
            intersect: false,
        }
    },
    scales: {
        x: {
            grid: { display: false },
            ticks: { font: { family: 'Lexend', size: 11 } }
        },
        y: {
            beginAtZero: true,
            grid: { color: '#f1f5f9' },
            ticks: { font: { family: 'Lexend', size: 11 } }
        }
    },
    onHover: (event, chartElement) => {
        event.native.target.style.cursor = chartElement.length ? 'pointer' : 'default';
    },
    onClick: (evt, elements) => {
        if (elements.length > 0 && drillLevel.value === 1) {
            handleDrillDown(elements[0].index);
        }
    }
};

const getStatusConfig = (status) => {
    const configs = {
        pending: { label: 'Menunggu', color: 'bg-warning/10 text-warning border-warning/20' },
        reviewed: { label: 'Ditinjau', color: 'bg-blue-100 text-blue-600 border-blue-200' },
        interview: { label: 'Interview', color: 'bg-purple-100 text-purple-600 border-purple-200' },
        accepted: { label: 'Diterima', color: 'bg-success/10 text-success border-success/20' },
        rejected: { label: 'Ditolak', color: 'bg-danger/10 text-danger border-danger/20' },
        default: { label: 'No Status', color: 'bg-slate-100 text-slate-700 border-slate-200' },
    };
    return configs[status] || configs.default;
};

// ==========================================
// WHAT-IF ANALYSIS STATE
// ==========================================
const additionalInterns = ref(0);
const taskSlowdown = ref(0);
const targetInterns = ref(0);

const baseInterns = computed(() => Number(props.mentorData?.activeInterns ?? 0));
const baseMentors = computed(() => Number(props.mentorData?.mentorCount ?? 0));
const maxRatio = computed(() => Number(props.mentorData?.maxRatio ?? 0));
const averageInternshipDays = computed(() => Number(props.mentorData?.averageInternshipDays));

const simulatedInterns = computed(() => baseInterns.value + Number(additionalInterns.value));

const simulatedRatio = computed(() => {
    return baseMentors.value > 0 ? (simulatedInterns.value / baseMentors.value).toFixed(1) : 0;
});

const maxCapacity = computed(() => baseMentors.value * maxRatio.value);

const capacityStatus = computed(() => {
    if (simulatedInterns.value <= maxCapacity.value * 0.6) {
        return { status: 'safe', label: 'Kapasitas Aman', color: 'text-emerald-600', bg: 'bg-emerald-50' };
    } else if (simulatedInterns.value <= maxCapacity.value) {
        return { status: 'warning', label: 'Mendekati Batas', color: 'text-amber-600', bg: 'bg-amber-50' };
    } else {
        return { status: 'danger', label: 'Melebihi Kapasitas!', color: 'text-red-600', bg: 'bg-red-50' };
    }
});

const estimatedTimeline = computed(() => {
    const baseDays = averageInternshipDays.value;
    const delayedDays = Math.round(baseDays * (1 + Number(taskSlowdown.value) / 100));
    return { base: baseDays, delayed: delayedDays, diff: delayedDays - baseDays };
});

const mentorsNeededResult = ref(null);

const calculateMentorsNeeded = () => {
    const target = Number(targetInterns.value);
    if (target <= 0) {
        mentorsNeededResult.value = null;
        return;
    }

    const totalNeeded = baseInterns.value + target;
    const mentorsRequired = Math.ceil(totalNeeded / maxRatio.value);
    const additionalMentors = Math.max(0, mentorsRequired - baseMentors.value);

    mentorsNeededResult.value = {
        totalInterns: totalNeeded,
        mentorsRequired: mentorsRequired,
        currentMentors: baseMentors.value,
        additionalMentors: additionalMentors,
        newCapacity: mentorsRequired * maxRatio.value,
    };
};

// ==========================================
// FORECASTING
// ==========================================
const futureMonths = ref([]);
const forecastChartData = computed(() => {
    const historical = props.historicalData || [];
    const histLabels = historical.map(h => h.month);
    const histApplicants = historical.map(h => h.applicants);
    const histInterns = historical.map(h => h.interns);

    const avgGrowthApplicants = histApplicants.length > 1
        ? (histApplicants[histApplicants.length - 1] - histApplicants[0]) / (histApplicants.length - 1)
        : 2;
    const avgGrowthInterns = histInterns.length > 1
        ? (histInterns[histInterns.length - 1] - histInterns[0]) / (histInterns.length - 1)
        : 1;

    const base = new Date();
    futureMonths.value = Array.from({ length: 3 }, (_, i) => {
        const d = new Date(base);
        d.setMonth(base.getMonth() + i + 1);
        return d.toLocaleString('id-ID', {
            month: 'long',
            year: 'numeric'
        });
    });

    const lastApplicant = histApplicants[histApplicants.length - 1] || 20;
    const lastIntern = histInterns[histInterns.length - 1] || 10;

    const forecastApplicants = futureMonths.value.map((_, i) =>
        Math.round(lastApplicant + avgGrowthApplicants * (i + 1))
    );
    const forecastInterns = futureMonths.value.map((_, i) =>
        Math.round(lastIntern + avgGrowthInterns * (i + 1))
    );

    const allLabels = [...histLabels, ...futureMonths.value];

    return {
        labels: allLabels,
        datasets: [
            {
                label: 'Pendaftar (Aktual)',
                data: [...histApplicants, null, null, null],
                borderColor: primaryColor,
                backgroundColor: `${primaryColor}20`,
                fill: true,
                tension: 0.3,
                pointRadius: 4,
            },
            {
                label: 'Pendaftar (Prediksi)',
                data: [...Array(histLabels.length - 1).fill(null), lastApplicant, ...forecastApplicants],
                borderColor: primaryColor,
                borderDash: [5, 5],
                backgroundColor: 'transparent',
                tension: 0.3,
                pointRadius: 4,
                pointStyle: 'triangle',
            },
            {
                label: 'Intern Aktif (Aktual)',
                data: [...histInterns, null, null, null],
                borderColor: '#10b981',
                backgroundColor: '#10b98120',
                fill: true,
                tension: 0.3,
                pointRadius: 4,
            },
            {
                label: 'Intern Aktif (Prediksi)',
                data: [...Array(histLabels.length - 1).fill(null), lastIntern, ...forecastInterns],
                borderColor: '#10b981',
                borderDash: [5, 5],
                backgroundColor: 'transparent',
                tension: 0.3,
                pointRadius: 4,
                pointStyle: 'triangle',
            },
        ]
    };
});

const forecastOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top',
            labels: {
                font: { family: 'Lexend', size: 11 },
                usePointStyle: true,
            }
        },
    },
    scales: {
        x: {
            grid: { display: false },
            ticks: { font: { family: 'Lexend', size: 11 } }
        },
        y: {
            grid: { color: '#f1f5f9' },
            ticks: { font: { family: 'Lexend', size: 11 } }
        }
    }
};

// Total stats
const totalUniversities = computed(() => props.universityData?.all.length || 0);
const totalApplications = computed(() => props.universityData?.all.reduce((sum, u) => sum + u.total_applications, 0) || 0);

// ==========================================
// STATUS DISTRIBUTION CHART
// ==========================================
const statusColors = {
    pending: '#f59e0b',
    reviewed: '#0055b3',
    interview: '#8b5cf6',
    accepted: '#10b981',
    rejected: '#ef4444',
};

const statusChartData = computed(() => ({
    labels: props.statusDistribution?.map(s => s.label) || [],
    datasets: [{
        data: props.statusDistribution?.map(s => s.total) || [],
        status: props.statusDistribution?.map(s => s.status) || [],
        backgroundColor: props.statusDistribution?.map(s => statusColors[s.status]) || [],
        borderWidth: 0,
        hoverOffset: 8,
    }]
}));

const statusChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '55%',
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                font: { family: 'Lexend', size: 11 },
                usePointStyle: true,
                padding: 12,
            }
        },
        datalabels: {
            formatter: (value, ctx) => {
                const data = ctx.chart.data.datasets[0].data;
                const total = data.reduce((a, b) => a + b, 0);
                return (value !== 0) ? `(${value}) ${((value / total) * 100).toFixed(1)}%` : '';
            },
            font: {
                weight: '600',
                size: 11,
            }
        }
    },
    onHover: (event, chartElement) => {
        event.native.target.style.cursor = chartElement.length ? 'pointer' : 'default';
    },
    onClick: (evt, elements, chart) => {
        if (!elements.length) return;

        const { index } = elements[0];
        const status = chart.data.datasets[0].status[index];
        router.get(route('admin.recruitment', { status }));
    }
};

// ==========================================
// SKILL DISTRIBUTION CHART
// ==========================================
const skillTypeFilter = ref('combined');
const displayedSkills = computed(() => props.skillDistribution?.[skillTypeFilter.value] || []);

const skillChartData = computed(() => ({
    labels: displayedSkills.value.map(s => s.skill) || [],
    datasets: [{
        label: 'Jumlah',
        data: displayedSkills.value.map(s => s.total) || [],
        backgroundColor: primaryColor,
        borderRadius: 6,
        barThickness: 24,
    }]
}));

const skillChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    indexAxis: 'y',
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                label: (ctx) => `Jumlah : ${ctx.raw} orang`
            }
        },
        datalabels: {
            font: {
                weight: '600',
                size: 12,
            }
        }
    },
    scales: {
        x: {
            grid: { color: '#f1f5f9' },
            ticks: { font: { family: 'Lexend', size: 11 } }
        },
        y: {
            grid: { display: false },
            ticks: { font: { family: 'Lexend', size: 11 } }
        }
    }
};

// ==========================================
// MONTHLY BY UNIVERSITY CHART
// ==========================================
const monthlyUnivColors = ['#0055b3', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];

const monthlyUnivChartData = computed(() => {
    const universities = props.monthlyByUniversity?.universities || [];
    const data = props.monthlyByUniversity?.data || [];

    return {
        labels: data.map(d => d.month),
        datasets: universities.map((univ, i) => ({
            label: univ,
            data: data.map(d => d[univ]),
            backgroundColor: monthlyUnivColors[i % monthlyUnivColors.length],
            borderRadius: 4,
            barThickness: 16,
        }))
    };
});

const monthlyUnivChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top',
            labels: {
                font: { family: 'Lexend', size: 11 },
                usePointStyle: true,
            }
        },
        tooltip: {
            callbacks: {
                label: (ctx) => [
                    ...wrapTextByWord(ctx.dataset.label, 35),
                    `Total : ${ctx.raw} pelamar`
                ]
            }
        }
    },
    scales: {
        x: {
            stacked: true,
            grid: { display: false },
            ticks: { font: { family: 'Lexend', size: 11 } }
        },
        y: {
            stacked: true,
            grid: { color: '#f1f5f9' },
            ticks: { font: { family: 'Lexend', size: 11 } }
        }
    }
};
</script>

<template>

    <Head title="Monitoring Global" />

    <div class="space-y-6">
        <!-- Header -->
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Monitoring Global</h1>
            <p class="text-slate-500 mt-1">Sistem Informasi Eksekutif - Analisis strategis dan peramalan untuk decision
                making</p>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-start md:items-center md:flex-row flex-col gap-3">
                    <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center">
                        <Building2 class="w-6 h-6 text-primary" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-slate-900">{{ totalUniversities }}</p>
                        <p class="text-xs text-slate-500 font-medium">Sekolah/Universitas</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-start md:items-center md:flex-row flex-col gap-3">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center">
                        <FileUser class="w-6 h-6 text-emerald-600" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-emerald-600">{{ totalApplications }}</p>
                        <p class="text-xs text-slate-500 font-medium">Total Lamaran</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-start md:items-center md:flex-row flex-col gap-3">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center">
                        <Users class="w-6 h-6 text-blue-600" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-blue-600">{{ baseInterns }}</p>
                        <p class="text-xs text-slate-500 font-medium">Intern Aktif</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-start md:items-center md:flex-row flex-col gap-3">
                    <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center">
                        <Target class="w-6 h-6 text-purple-600" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-purple-600">{{ baseMentors }}</p>
                        <p class="text-xs text-slate-500 font-medium">Mentor Aktif</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-6">

            <!-- Drill-Down Analysis -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 max-h-[100vh]">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-5">
                        <div class="w-16 h-12 rounded-xl bg-primary/10 flex items-center justify-center">
                            <Building2 class="w-6 h-6 text-primary" />
                        </div>
                        <div>
                            <h2 class="font-bold text-slate-900">Distribusi Intensitas Lamaran Berdasarkan Sebaran
                                Institusi</h2>
                            <p class="text-xs text-slate-500">Klik chart untuk drill-down ke posisi</p>
                        </div>
                    </div>
                </div>

                <div v-if="drillLevel === 0" class="mb-4">
                    <div class="relative">
                        <span
                            class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-[18px]">search</span>
                        <input v-model="institutionSearch" type="text" placeholder="Cari institusi..."
                            class="w-full h-10 pl-10 pr-4 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" />
                    </div>
                    <p class="text-xs text-slate-400 mt-1">
                        {{ institutionSearch ? `Menampilkan hasil pencarian` : `Top 10 institusi` }}
                    </p>
                </div>

                <div class="flex items-center gap-2 mb-4 text-sm">
                    <template v-for="(crumb, i) in breadcrumbs" :key="i">
                        <button @click="navigateToBreadcrumb(crumb.level)"
                            class="text-primary hover:underline font-medium cursor-pointer"
                            :class="{ 'text-slate-500 cursor-default hover:no-underline': i === breadcrumbs.length - 1 }">
                            {{ crumb.label }}
                        </button>
                        <span v-if="i < breadcrumbs.length - 1" class="text-slate-400">/</span>
                    </template>
                </div>

                <!-- Chart Container -->
                <div class="h-80">
                    <div v-if="drillLevel === 0 && isSearchEmpty"
                        class="h-full flex flex-col items-center justify-center text-center p-6">
                        <span class="material-symbols-outlined text-slate-300 text-[64px] mb-2">search_off</span>
                        <p class="text-slate-500 font-medium">Institusi tidak ditemukan</p>
                        <p class="text-xs text-slate-400">Coba kata kunci lain atau cek ejaan kamu.</p>
                    </div>
                    <Doughnut v-else-if="drillLevel === 0" :data="universityChartData" :plugins="[ChartDataLabels]" :options="doughnutOptions" />

                    <Bar v-else-if="drillLevel === 1 && selectedUniversity" :data="positionChartData"
                        :options="barOptions" />

                    <div v-else-if="drillLevel === 2 && selectedPosition" class="overflow-auto h-full">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 sticky top-0">
                                <tr>
                                    <th class="px-4 py-3 text-left font-bold text-slate-700">Nama</th>
                                    <th class="px-4 py-3 text-left font-bold text-slate-700">Status</th>
                                    <th class="px-4 py-3 text-center font-bold text-slate-700">Tanggal Apply</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="(applicant, i) in selectedPosition.applicants" :key="i"
                                    class="hover:bg-slate-50">
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col items-start justify-center gap-2">
                                            <Link
                                                :href="`/admin/recruitment?job=${selectedPosition.slug}&search=${applicant.name}&status=${applicant.status}`"
                                                class="font-medium text-slate-900 underline">
                                                {{ applicant.name }}
                                            </Link>

                                            <p class="text-xs text-slate-500">{{ applicant.email }}</p>

                                            <span v-if="applicant.is_repeat"
                                                class="text-[10px] mt-2 bg-amber-100 text-amber-700 px-2 py-1 rounded-full font-bold uppercase">
                                                Repeat Applicant
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-bold"
                                            :class="getStatusConfig(applicant.status).color">
                                            {{ getStatusConfig(applicant.status).label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-slate-600">
                                        {{ formatDateShort(applicant.applied_at) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- What-If Analysis -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <div class="flex items-center gap-2 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center">
                        <Gauge class="w-5 h-5 text-amber-600" />
                    </div>
                    <div>
                        <h2 class="font-bold text-slate-900">What-If Analysis</h2>
                        <p class="text-xs text-slate-500">Simulasi kapasitas mentor & timeline</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="p-4 bg-slate-50 rounded-xl">
                        <label class="block text-sm font-semibold text-slate-700 mb-3">
                            Tambah Intern Baru: <span class="text-primary">+{{ additionalInterns }}</span>
                        </label>
                        <input type="range" v-model.number="additionalInterns" min="0" max="50" step="1"
                            class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-primary" />

                        <div class="grid grid-cols-3 gap-3 mt-4">
                            <div class="text-center p-3 bg-white rounded-xl border border-slate-100">
                                <p class="text-2xl font-bold text-primary">{{ baseMentors }}</p>
                                <p class="text-xs text-slate-500">Mentor</p>
                            </div>
                            <div class="text-center p-3 bg-white rounded-xl border border-slate-100">
                                <p class="text-2xl font-bold text-slate-900">{{ simulatedInterns }}</p>
                                <p class="text-xs text-slate-500">Total Intern</p>
                            </div>
                            <div class="text-center p-3 bg-white rounded-xl border border-slate-100">
                                <p class="text-2xl font-bold" :class="capacityStatus.color">1:{{ simulatedRatio }}</p>
                                <p class="text-xs text-slate-500">Rasio</p>
                            </div>
                        </div>

                        <div class="mt-4 p-3 rounded-xl flex items-center gap-3" :class="capacityStatus.bg">
                            <CheckCircle2 v-if="capacityStatus.status === 'safe'" class="w-5 h-5 text-emerald-600" />
                            <AlertTriangle v-else class="w-5 h-5" :class="capacityStatus.color" />
                            <div>
                                <p class="font-semibold text-sm" :class="capacityStatus.color">{{ capacityStatus.label
                                    }}</p>
                                <p class="text-xs text-slate-600">Kapasitas max: {{ maxCapacity }} intern (Ratio 1:5)
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-slate-50 rounded-xl">
                        <label class="block text-sm font-semibold text-slate-700 mb-3">
                            Perlambatan Pengerjaan Tugas: <span class="text-amber-600">+{{ taskSlowdown }}%</span>
                        </label>
                        <input type="range" v-model.number="taskSlowdown" min="0" max="100" step="5"
                            class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-amber-500" />

                        <div
                            class="flex items-center justify-between mt-4 p-3 bg-white rounded-xl border border-slate-100">
                            <div class="flex items-center gap-2">
                                <Calendar class="w-5 h-5 text-slate-500" />
                                <span class="text-sm text-slate-600">Estimasi durasi magang:</span>
                            </div>
                            <div class="text-right">
                                <span class="text-lg font-bold text-slate-900">{{ estimatedTimeline.delayed }}
                                    hari</span>
                                <span v-if="estimatedTimeline.diff > 0" class="text-sm text-red-500 ml-2">
                                    (+{{ estimatedTimeline.diff }} hari)
                                </span>
                            </div>
                        </div>
                        <p class="text-xs text-slate-400 mt-2">Rata-rata durasi magang: {{ estimatedTimeline.base }}
                            hari</p>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="p-4 bg-gradient-to-br from-primary/5 to-blue-50 rounded-xl border border-primary/10 grid lg:grid-cols-2 gap-5 items-center">
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-primary text-[20px]">calculate</span>
                    <h3 class="font-semibold text-slate-800">Optimasi: Hitung Kebutuhan Mentor</h3>
                </div>
                <p class="text-xs text-slate-600 mb-4">Masukkan jumlah intern baru yang ingin ditambahkan untuk
                    mengetahui
                    berapa mentor yang dibutuhkan.</p>

                <div class="flex gap-3">
                    <input type="number" v-model.number="targetInterns" placeholder="Jumlah intern baru" min="0"
                        class="flex-1 h-10 px-4 text-sm bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" />
                    <button @click="calculateMentorsNeeded"
                        class="px-4 h-10 bg-primary text-white text-sm font-semibold rounded-lg cursor-pointer hover:bg-primary/90 transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">bolt</span>
                        Hitung
                    </button>
                </div>
            </div>

            <!-- Result -->
            <div class="p-4 bg-white rounded-xl border border-slate-200">
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center p-3 bg-slate-50 rounded-lg">
                        <p class="text-2xl font-bold text-slate-900">{{ mentorsNeededResult?.totalInterns ?? baseInterns
                        }}</p>
                        <p class="text-xs text-slate-500">Total Intern <span v-if="!mentorsNeededResult">Saat Ini</span>
                        </p>
                    </div>
                    <div class="text-center p-3 bg-slate-50 rounded-lg">
                        <p class="text-2xl font-bold text-primary">{{ mentorsNeededResult?.mentorsRequired ??
                            baseMentors }}</p>
                        <p class="text-xs text-slate-500">{{ mentorsNeededResult ? 'Mentor Dibutuhkan' : 'Jumlah Mentor Saat Ini' }}</p>
                    </div>
                </div>

                <div v-if="mentorsNeededResult?.additionalMentors > 0"
                    class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg flex items-center gap-3">
                    <AlertTriangle class="w-5 h-5 text-amber-600 shrink-0" />
                    <div>
                        <p class="text-sm font-semibold text-amber-800">
                            Perlu tambah {{ mentorsNeededResult.additionalMentors }} mentor baru
                        </p>
                        <p class="text-xs text-amber-600">
                            Saat ini: {{ mentorsNeededResult.currentMentors }} mentor | Kapasitas baru: {{
                                mentorsNeededResult.newCapacity }} intern
                        </p>
                    </div>
                </div>
                <div v-else class="mt-4 p-3 bg-emerald-50 border border-emerald-200 rounded-lg flex items-center gap-3">
                    <CheckCircle2 class="w-5 h-5 text-emerald-600 shrink-0" />
                    <div>
                        <p class="text-sm font-semibold text-emerald-800">Kapasitas mentor mencukupi!</p>
                        <p class="text-xs text-emerald-600">
                            {{ mentorsNeededResult?.currentMentors ?? baseMentors }} mentor dapat menampung {{
                                mentorsNeededResult?.totalInterns ?? baseInterns }} intern
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-6">
            <!-- Status Distribution -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 flex flex-col">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center">
                        <Target class="w-5 h-5 text-purple-600" />
                    </div>
                    <div>
                        <h2 class="font-bold text-slate-900">Status Pelamar</h2>
                        <p class="text-xs text-slate-500">Distribusi status lamaran</p>
                    </div>
                </div>
                <div class="flex-1 relative">
                    <Doughnut :data="statusChartData" :plugins="[ChartDataLabels]" :options="statusChartOptions" />
                </div>
            </div>

            <!-- Skill Distribution -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 h-112 flex flex-col">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-cyan-50 flex items-center justify-center">
                        <BarChart3 class="w-5 h-5 text-cyan-600" />
                    </div>
                    <div>
                        <h2 class="font-bold text-slate-900">Top 10 Skills</h2>
                        <p class="text-xs text-slate-500">Skill yang paling banyak dimiliki pelamar</p>
                    </div>
                </div>
                <div class="relative mb-3">
                   <Filter class="absolute left-3 top-2.5 text-slate-400 size-5" />
                    <select v-model="skillTypeFilter" class="w-full h-10 pl-11 pr-4 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer">
                        <option value="combined">Semua Tipe</option>
                        <option value="programming">Programming Language</option>
                        <option value="database">Database</option>
                    </select>
                </div>
                <div class="flex-1 relative">
                    <Bar :data="skillChartData" :plugins="[ChartDataLabels]" :options="skillChartOptions" />
                </div>
            </div>
        </div>

        <!-- Monthly Applicants by University -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-center gap-2 mb-6">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center">
                    <TrendingUp class="w-5 h-5 text-indigo-600" />
                </div>
                <div>
                    <h2 class="font-bold text-slate-900">Trend Pelamar per Universitas (Top 5)</h2>
                    <p class="text-xs text-slate-500">Jumlah pelamar 6 bulan terakhir berdasarkan universitas</p>
                </div>
            </div>
            <div class="h-72">
                <Bar :data="monthlyUnivChartData" :plugins="[ChartDataLabels]" :options="monthlyUnivChartOptions" />
            </div>
        </div>

        <!-- Forecasting -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-center gap-2 mb-6">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                    <TrendingUp class="w-5 h-5 text-blue-600" />
                </div>
                <div>
                    <h2 class="font-bold text-slate-900">Peramalan (Forecasting)</h2>
                    <p class="text-xs text-slate-500">Trend 6 bulan terakhir + prediksi 3 bulan ke depan (garis
                        putus-putus)</p>
                </div>
            </div>

            <div class="h-80">
                <Line :data="forecastChartData" :options="forecastOptions" />
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                <div class="p-4 bg-primary/5 rounded-xl border border-primary/10">
                    <p class="text-2xl mb-2 font-bold text-primary">
                        {{Math.round(historicalData?.reduce((a, b) => a + b.applicants, 0) / (historicalData?.length ||
                            1))}}
                    </p>
                    <p class="text-sm text-slate-600">Rata-rata Pendaftar/Bulan</p>
                </div>
                <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-100">
                    <p class="text-2xl mb-2 font-bold text-emerald-600">
                        {{Math.round(historicalData?.reduce((a, b) => a + b.interns, 0) / (historicalData?.length ||
                            1))}}
                    </p>
                    <p class="text-sm text-slate-600">Rata-rata Intern/Bulan</p>
                </div>
                <div class="p-4 bg-amber-50 rounded-xl border border-amber-100">
                    <p class="text-2xl mb-2 font-bold text-amber-600">↑ {{ forecastChartData.datasets[1].data[forecastChartData.datasets[1].data.length - 1] }}</p>
                    <p class="text-sm text-slate-600">Prediksi Akhir Pendaftar ({{ futureMonths[2] }})</p>
                </div>
                <div class="p-4 bg-purple-50 rounded-xl border border-purple-100">
                    <p class="text-2xl mb-2 font-bold text-purple-600">↑ {{ forecastChartData.datasets[3].data[forecastChartData.datasets[3].data.length - 1] }}</p>
                    <p class="text-sm text-slate-600">Prediksi Akhir Intern ({{ futureMonths[2] }})</p>
                </div>
            </div>
        </div>
    </div>
</template>
