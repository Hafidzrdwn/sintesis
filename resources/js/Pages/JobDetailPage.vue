<script setup>
import { ref, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import LandingLayout from '@/Layouts/LandingLayout.vue';
import ProcessStepCard from '@/Components/Landing/ProcessStepCard.vue';
import JobStatCard from '@/Components/Landing/JobStatCard.vue';
import BaseButton from '@/Components/BaseButton.vue';
import { formatDate } from '@/utils/helpers';

const props = defineProps({
    job: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        default: () => ({
            total: 0,
            pending: 0,
            reviewed: 0,
            rejected: 0,
            accepted: 0,
        }),
    },
    canApply: {
        type: Boolean,
        default: true,
    },
});

const page = usePage();
const isLoggedIn = computed(() => !!page.props.auth?.user);

const activeTab = ref('Tentang');
const tabs = ['Tentang', 'Persyaratan', 'Benefit', 'Proses Seleksi'];

// Share functionality
const shareToast = ref(false);
const shareToastMessage = ref('');

const shareJob = async () => {
    const url = window.location.href;
    const title = `${props.job.title} - Magang di Inosoft Trans Sistem`;
    
    if (navigator.share) {
        try {
            await navigator.share({
                title: title,
                text: `Lowongan magang ${props.job.title} di Inosoft Trans Sistem. Daftar sekarang!`,
                url: url,
            });
            return;
        } catch (err) {
            console.error('Share failed:', err);
        }
    }
    
    try {
        await navigator.clipboard.writeText(url);
        shareToastMessage.value = 'Link berhasil disalin!';
        shareToast.value = true;
        setTimeout(() => {
            shareToast.value = false;
        }, 3000);
    } catch (err) {
        shareToastMessage.value = 'Gagal menyalin link';
        shareToast.value = true;
        setTimeout(() => {
            shareToast.value = false;
        }, 3000);
    }
};

const selectionSteps = [
    {
        number: 1,
        title: 'Pilih Posisi',
        description: "Cari lowongan yang berstatus 'Terbuka' dan sesuai dengan keahlian teknis Anda.",
        icon: 'search'
    },
    {
        number: 2,
        title: 'Registrasi',
        description: "Lengkapi profil kandidat dan unggah portofolio pada halaman detail lowongan.",
        icon: 'app_registration'
    },
    {
        number: 3,
        title: 'Seleksi',
        description: "Tim HR Inosoft akan melakukan screening CV dan wawancara kompetensi.",
        icon: 'assignment_turned_in'
    },
    {
        number: 4,
        title: 'Onboarding',
        description: "Mulai periode magang dengan akses penuh ke tools dan ekosistem SINTESIS.",
        icon: 'rocket_launch'
    }
];

const statItems = computed(() => [
    { label: 'Total', value: props.stats.total, subLabel: 'Pelamar', icon: 'group', color: 'blue' },
    { label: 'Menunggu', value: props.stats.pending, subLabel: 'Belum review', icon: 'hourglass_top', color: 'yellow' },
    { label: 'Ditinjau', value: props.stats.reviewed, subLabel: 'Screening', icon: 'find_in_page', color: 'indigo' },
    { label: 'Ditolak', value: props.stats.rejected, subLabel: 'Tidak lolos', icon: 'close', color: 'red' },
    { label: 'Diterima', value: props.stats.accepted, subLabel: 'Onboarding', icon: 'check', color: 'green' },
]);

defineOptions({
    layout: (h, page) => h(LandingLayout, { mainClass: 'bg-slate-50' }, () => page),
});
</script>

<template>

    <Head :title="`${job.title} - Detail Lowongan`" />

    <div class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <!-- Breadcrumb & Back -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <Link href="/#lowongan" class="hover:text-primary transition-colors">Lowongan</Link>
                <span class="material-symbols-outlined text-base">chevron_right</span>
                <span class="text-slate-900 font-medium">{{ job.title }}</span>
            </div>
            <Link href="/"
                class="hidden sm:flex items-center gap-1 text-sm text-slate-500 hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-lg">arrow_back</span>
                Kembali
            </Link>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 md:p-8">
                    <!-- Header -->
                    <div class="flex flex-col sm:flex-row gap-6 items-start mb-8">
                        <div
                            class="bg-gradient-to-br from-primary to-blue-700 size-16 sm:size-20 rounded-xl flex items-center justify-center text-white shrink-0 shadow-lg">
                            <span class="material-symbols-outlined scale-150">code</span>
                        </div>
                        <div class="flex-1 w-full">
                            <div class="flex flex-col sm:flex-row justify-between items-start gap-2">
                                <div>
                                    <div class="flex items-center gap-3 mb-2">
                                        <span :class="[
                                            'text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded border',
                                            job.status === 'open' 
                                                ? 'bg-green-50 text-green-700 border-green-100'
                                                : 'bg-red-50 text-red-700 border-red-100'
                                        ]">{{ job.statusLabel }}</span>
                                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded border bg-blue-50 text-blue-700 border-blue-100">{{ job.type }}</span>
                                    </div>
                                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 leading-tight mb-2">
                                        {{ job.title }}</h1>
                                    <p class="text-slate-500 font-medium flex items-center gap-1">
                                        Inosoft Trans Sistem
                                        <span class="text-slate-300">•</span>
                                        <span class="text-primary text-sm">Internship</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <div class="border-b border-slate-200 mb-6">
                        <div class="flex gap-2 overflow-x-auto no-scrollbar">
                            <button v-for="tab in tabs" :key="tab" @click="activeTab = tab"
                                class="px-4 py-3 text-sm font-medium border-b-2 transition-all cursor-pointer whitespace-nowrap"
                                :class="[
                                    activeTab === tab
                                        ? 'text-primary border-primary'
                                        : 'text-slate-500 border-transparent hover:text-primary hover:border-primary/30'
                                ]">
                                {{ tab }}
                            </button>
                        </div>
                    </div>

                    <!-- Tab Content -->
                    <div class="min-h-[300px]">
                        <!-- Tentang -->
                        <div v-if="activeTab === 'Tentang'"
                            class="prose prose-slate prose-sm max-w-none text-slate-600 leading-relaxed space-y-8">
                            <section>
                                <h3 class="text-lg font-bold text-slate-900 mb-4">Deskripsi Pekerjaan</h3>
                                <p>{{ job.description }}</p>
                            </section>
                            <section v-if="job.responsibilities && job.responsibilities.length > 0">
                                <h3 class="text-lg font-bold text-slate-900 mb-4">Tanggung Jawab</h3>
                                <ul class="list-disc pl-5 space-y-2 marker:text-slate-400">
                                    <li v-for="item in job.responsibilities" :key="item">{{ item }}</li>
                                </ul>
                            </section>
                        </div>

                        <!-- Persyaratan -->
                        <div v-if="activeTab === 'Persyaratan'"
                            class="prose prose-slate prose-sm max-w-none text-slate-600 leading-relaxed">
                            <section v-if="job.requirements && job.requirements.length > 0">
                                <h3 class="text-lg font-bold text-slate-900 mb-4">Kualifikasi Minimum</h3>
                                <ul class="list-disc pl-5 space-y-2 marker:text-slate-400">
                                    <li v-for="item in job.requirements" :key="item">{{ item }}</li>
                                </ul>
                            </section>
                            <p v-else class="text-slate-500 italic">Persyaratan belum ditentukan.</p>
                        </div>

                        <!-- Benefit -->
                        <div v-if="activeTab === 'Benefit'"
                            class="prose prose-slate prose-sm max-w-none text-slate-600 leading-relaxed">
                            <section v-if="job.benefits && job.benefits.length > 0">
                                <h3 class="text-lg font-bold text-slate-900 mb-4">Benefit</h3>
                                <ul class="list-disc pl-5 space-y-2 marker:text-slate-400">
                                    <li v-for="item in job.benefits" :key="item">{{ item }}</li>
                                </ul>
                            </section>
                            <p v-else class="text-slate-500 italic">Informasi benefit belum tersedia.</p>
                        </div>

                        <!-- Proses Seleksi -->
                        <div v-if="activeTab === 'Proses Seleksi'">
                            <div class="grid gap-6">
                                <ProcessStepCard v-for="step in selectionSteps" :key="step.number" v-bind="step" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column (Sidebar) -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 sticky top-24">
                    <h3 class="font-bold text-slate-900 mb-5 pb-4 border-b border-slate-100">Informasi Lowongan</h3>

                    <div class="space-y-5 mb-8">
                        <div class="flex items-start gap-3.5">
                            <div class="bg-slate-50 p-2 rounded-lg text-slate-400 shrink-0">
                                <span class="material-symbols-outlined text-[20px]">location_on</span>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 font-medium mb-0.5">Lokasi</p>
                                <p class="text-sm font-semibold text-slate-800">{{ job.location || 'Surabaya' }} ({{ job.type }})</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3.5">
                            <div class="bg-slate-50 p-2 rounded-lg text-slate-400 shrink-0">
                                <span class="material-symbols-outlined text-[20px]">calendar_month</span>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 font-medium mb-0.5">Durasi</p>
                                <p class="text-sm font-semibold text-slate-800">{{ job.duration || '3 - 6 Bulan' }}</p>
                            </div>
                        </div>
                        <div v-if="job.deadline" class="flex items-start gap-3.5">
                            <div class="bg-slate-50 p-2 rounded-lg text-slate-400 shrink-0">
                                <span class="material-symbols-outlined text-[20px]">event_busy</span>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 font-medium mb-0.5">Batas Daftar</p>
                                <p class="text-sm font-semibold text-slate-800">{{ formatDate(job.deadline) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3">
                        <!-- Can apply: show button -->
                        <BaseButton 
                            v-if="job.status === 'open' && canApply"
                            :href="isLoggedIn ? route('internship.apply', job.slug) : route('register')"
                            variant="primary" 
                            size="lg" 
                            full
                        >
                            {{ isLoggedIn ? 'Daftar Sekarang' : 'Daftar & Login' }}
                        </BaseButton>
                        <!-- Cannot apply: has active application -->
                        <div v-else-if="job.status === 'open' && !canApply && isLoggedIn" 
                            class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-center">
                            <p class="text-amber-700 font-semibold">Lamaran Sedang Diproses</p>
                            <p class="text-amber-600 text-sm mt-1">Anda sudah memiliki lamaran aktif. Tunggu pengumuman terlebih dahulu.</p>
                        </div>
                        <!-- Job closed -->
                        <div v-else-if="job.status !== 'open'" class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                            <p class="text-red-700 font-semibold">Lowongan Ditutup</p>
                            <p class="text-red-600 text-sm mt-1">Pendaftaran sudah tidak tersedia</p>
                        </div>
                        <BaseButton @click="shareJob" variant="outline" size="lg" full>
                            <span class="material-symbols-outlined">share</span>
                            Bagikan
                        </BaseButton>
                    </div>
                    <div v-if="!isLoggedIn && job.status === 'open'" class="mt-4 text-center">
                        <p class="text-base text-slate-400">
                            Buat akun & login untuk melamar.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="pt-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-900">Status & Statistik Pelamar</h3>
                <span
                    class="hidden sm:inline-block text-xs font-medium text-slate-500 bg-white border border-slate-200 px-3 py-1 rounded-full">
                    Data Real-time
                </span>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <JobStatCard v-for="stat in statItems" :key="stat.label" v-bind="stat" />
            </div>
        </div>
    </div>

    <!-- Share Toast Notification -->
    <Transition name="toast">
        <div v-if="shareToast" 
            class="fixed bottom-6 left-1/2 -translate-x-1/2 bg-slate-900 text-white px-6 py-3 rounded-xl shadow-xl flex items-center gap-3 z-50">
            <span class="material-symbols-outlined text-green-400">check_circle</span>
            <span class="font-medium">{{ shareToastMessage }}</span>
        </div>
    </Transition>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}

.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

/* Toast transitions */
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateX(-50%) translateY(20px);
}
</style>
