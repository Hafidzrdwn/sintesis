<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { 
    BarChart3, 
    PieChart, 
    TrendingUp, 
    Award, 
    CalendarCheck,
    ArrowUpRight,
    ArrowDownRight
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
import { ref } from 'vue';

defineOptions({ layout: AuthenticatedLayout });

// Register ChartJS Components
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

// Chart Data: Weekly Activity
const weeklyActivityData = {
  labels: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'],
  datasets: [
    {
      label: 'Log Harian',
      backgroundColor: '#0b5cad',
      borderRadius: 6,
      data: [1, 2, 1, 3, 2]
    }
  ]
};

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

// Chart Data: Task Status
const taskStatusData = {
  labels: ['Selesai', 'Proses', 'Review'],
  datasets: [
    {
      backgroundColor: ['#10b981', '#0b5cad', '#f59e0b'],
      borderWidth: 0,
      data: [12, 5, 3]
    }
  ]
};

const taskStatusOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
        position: 'bottom',
        labels: {
            usePointStyle: true,
            padding: 20,
            font: { family: 'inter', size: 12 }
        }
    }
  },
  cutout: '70%'
};

// Stats Data
const stats = [
    {
        title: 'Total Kehadiran',
        value: '92%',
        trend: '+2.5%',
        trendUp: true,
        icon: CalendarCheck,
        color: 'bg-emerald-50 text-emerald-600'
    },
    {
        title: 'Tugas Selesai',
        value: '12',
        sub: 'dari 15 tugas',
        trend: 'On Track',
        trendUp: true,
        icon: BarChart3,
        color: 'bg-blue-50 text-blue-600'
    },
    {
        title: 'Rata-rata Skor',
        value: '88.5',
        trend: '-1.2',
        trendUp: false,
        icon: Award,
        color: 'bg-purple-50 text-purple-600'
    }
];
</script>

<template>
    <Head title="Analitik Performa" />

    <div class="flex flex-col gap-8">
         <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Analitik Performa</h1>
                <p class="text-slate-500 mt-1">Ringkasan statistik dan perkembangan magang Anda.</p>
            </div>
            <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl p-1 shadow-sm">
                <button class="px-3 py-1.5 text-xs font-bold bg-slate-100 text-slate-700 rounded-lg shadow-sm">Mingguan</button>
                <button class="px-3 py-1.5 text-xs font-medium text-slate-500 hover:text-primary transition-colors">Bulanan</button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div v-for="(stat, index) in stats" :key="index" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-start justify-between relative overflow-hidden group hover:shadow-md transition-shadow">
               <!-- Decorator Circle -->
               <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full opacity-10 transition-transform group-hover:scale-110" :class="stat.color.replace('text-', 'bg-').replace('bg-', 'bg-')"></div>

                <div class="flex flex-col relative z-10">
                    <p class="text-slate-500 text-sm font-medium mb-1">{{ stat.title }}</p>
                    <h2 class="text-3xl font-black text-slate-900">{{ stat.value }}</h2>
                    <div class="flex items-center gap-2 mt-2">
                        <span 
                            class="text-xs font-bold px-1.5 py-0.5 rounded flex items-center gap-0.5"
                            :class="stat.trendUp ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'"
                        >
                            <component :is="stat.trendUp ? ArrowUpRight : ArrowDownRight" class="w-3 h-3" />
                            {{ stat.trend }}
                        </span>
                        <span v-if="stat.sub" class="text-xs text-slate-400">{{ stat.sub }}</span>
                    </div>
                </div>
                <div class="p-3 rounded-xl relative z-10" :class="stat.color">
                    <component :is="stat.icon" class="w-6 h-6" />
                </div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Bar Chart -->
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="font-bold text-slate-900 text-lg">Aktivitas Jurnal Mingguan</h3>
                        <p class="text-sm text-slate-500">Frekuensi pengisian logbook harian</p>
                    </div>
                    <button class="text-slate-400 hover:text-primary transition-colors">
                        <TrendingUp class="w-5 h-5" />
                    </button>
                </div>
                <div class="flex-1 min-h-[300px] relative w-full">
                    <Bar :data="weeklyActivityData" :options="weeklyActivityOptions" />
                </div>
            </div>

            <!-- Doughnut Chart -->
            <div class="lg:col-span-1 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="font-bold text-slate-900 text-lg">Status Tugas</h3>
                        <p class="text-sm text-slate-500">Distribusi penyelesaian tugas</p>
                    </div>
                    <button class="text-slate-400 hover:text-primary transition-colors">
                        <PieChart class="w-5 h-5" />
                    </button>
                </div>
                <div class="flex-1 min-h-[300px] relative w-full flex items-center justify-center">
                    <Doughnut :data="taskStatusData" :options="taskStatusOptions" />
                    <!-- Center Text -->
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none mt-4">
                        <span class="text-3xl font-black text-slate-900">20</span>
                        <span class="text-xs text-slate-500 uppercase font-bold tracking-wider">Total</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
