<script setup>
import BaseButton from '@/Components/BaseButton.vue';
import LandingLayout from '@/Layouts/LandingLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { formatDate, formatDateTime } from '@/utils/helpers';

const props = defineProps({
    applicationStatus: {
        type: String,
        default: 'none',
    },
    application: {
        type: Object,
        default: null,
    },
    internship: {
        type: Object,
        default: null,
    },
});

console.log(props.internship)

const page = usePage();
const user = computed(() => page.props.auth.user);

// Status display config
const statusConfig = {
    pending: {
        label: 'Sedang Ditinjau',
        color: 'amber',
        icon: 'hourglass_top',
        message: 'Tim HRD kami sedang meninjau berkas lamaran Anda. Proses ini biasanya memakan waktu 2-4 hari kerja.',
    },
    reviewed: {
        label: 'Sudah Direview',
        color: 'blue',
        icon: 'fact_check',
        message: 'Lamaran Anda telah direview oleh tim kami. Mohon tunggu pengumuman selanjutnya.',
    },
    interview: {
        label: 'Jadwal Interview',
        color: 'purple',
        icon: 'groups',
        message: 'Selamat! Anda lolos ke tahap interview. Silakan hubungi HR (nomor tersedia di bagian bantuan) untuk detail jadwal.',
    },
    accepted: {
        label: 'Diterima',
        color: 'green',
        icon: 'check_circle',
        message: 'Selamat! Lamaran Anda diterima. Silakan tunggu informasi selanjutnya untuk memulai magang.',
    },
    rejected: {
        label: 'Tidak Lolos',
        color: 'red',
        icon: 'cancel',
        message: 'Mohon maaf, lamaran Anda belum dapat kami terima saat ini. Jangan menyerah, Anda dapat mencoba lagi di kesempatan berikutnya.',
    },
};

const currentStatus = computed(() => {
    if (!props.application) return null;
    return statusConfig[props.application.status] || statusConfig.pending;
});

const internshipStatusConfig = {
    terminated: {
        label: 'Magang Dihentikan',
        color: 'red',
        icon: 'block',
        message: 'Magang Anda telah dihentikan. Silakan lihat catatan dari HR untuk informasi lebih lanjut.',
    },
    completed: {
        label: 'Alumni Inosoft',
        color: 'emerald',
        icon: 'school',
        message: 'Selamat! Anda telah menyelesaikan program magang di PT Inosoft Trans Sistem. Terima kasih atas kontribusi Anda.',
    },
};

const internshipStatus = computed(() => {
    if (!props.internship) return null;
    return internshipStatusConfig[props.internship.status] || null;
});

defineOptions({
    layout: LandingLayout,
});
</script>

<template>

    <Head title="Dashboard Calon Intern" />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 pt-10 pb-16 overflow-hidden">
        <header class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Dashboard Calon Intern</h1>
                <p class="mt-1 text-md text-gray-500">
                    {{ applicationStatus === 'none' ? 'Mulai perjalanan magang Anda bersama kami.' : 'Pantau status lamaran aktif dan langkah selanjutnya dengan refresh halaman ini.' }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span v-if="user.status === 'active'"
                    class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                    <span class="mr-1.5 flex h-2 w-2 relative">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    Akun Aktif
                </span>
                <span v-else
                    class="inline-flex items-center rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-700 ring-1 ring-inset ring-red-600/20">
                    <span class="mr-1.5 flex h-2 w-2 relative">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                    </span>
                    Akun Tidak Aktif/Suspended
                </span>
            </div>
        </header>

        <!-- Terminated Internship State -->
        <section v-if="internship?.status === 'terminated'"
            class="bg-gradient-to-br from-red-50 to-rose-50 rounded-xl border border-red-200 p-8 mb-6">
            <div class="flex items-start gap-4">
                <div class="p-3 bg-red-100 rounded-xl flex items-center justify-center">
                    <span class="material-symbols-outlined text-red-600 scale-110">block</span>
                </div>
                <div class="flex-1">
                    <h3 class="text-xl font-bold text-red-900 mb-2">{{ internshipStatus?.label }}</h3>
                    <p class="text-sm text-red-700 mb-4">{{ internshipStatus?.message }}</p>

                    <div class="bg-white/60 rounded-lg p-4 border border-red-100 mb-4">
                        <div class="grid sm:grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-500 text-xs uppercase font-bold mb-1">Posisi</p>
                                <p class="font-semibold text-gray-900">{{ internship?.position }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500 text-xs uppercase font-bold mb-1">Mentor</p>
                                <p class="font-semibold text-gray-900">{{ internship?.mentor_name || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500 text-xs uppercase font-bold mb-1">Periode</p>
                                <p class="font-semibold text-gray-900">{{ formatDate(internship?.start_date) }} - {{
                                    formatDate(internship?.end_date) }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="internship?.notes" class="bg-red-100/50 rounded-lg p-4 border border-red-200">
                        <p class="text-xs uppercase font-bold text-red-700 mb-1">Catatan dari Admin/HR</p>
                        <p class="text-sm text-red-800 font-medium">{{ internship.notes }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Completed Internship (Alumni) State -->
        <section v-if="internship?.status === 'completed'"
            class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-xl border border-emerald-200 p-8 mb-6">
            <div class="flex items-start gap-4">
                <div class="p-3 bg-emerald-100 rounded-xl flex items-center justify-center">
                    <span class="material-symbols-outlined text-emerald-600 scale-110">school</span>
                </div>
                <div class="flex-1">
                    <h3 class="text-xl font-bold text-emerald-900 mb-2">{{ internshipStatus?.label }}</h3>
                    <p class="text-sm text-emerald-700 mb-4">{{ internshipStatus?.message }}</p>

                    <div class="bg-white/60 rounded-lg p-4 border border-emerald-100 mb-4">
                        <div class="grid sm:grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-500 text-xs uppercase font-bold mb-1">Posisi</p>
                                <p class="font-semibold text-gray-900">{{ internship?.position }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500 text-xs uppercase font-bold mb-1">Mentor</p>
                                <p class="font-semibold text-gray-900">{{ internship?.mentor_name || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500 text-xs uppercase font-bold mb-1">Periode Magang</p>
                                <p class="font-semibold text-gray-900">{{ formatDate(internship?.start_date) }} - {{
                                    formatDate(internship?.end_date) }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="internship?.notes"
                        class="bg-emerald-100/50 rounded-lg p-4 border border-emerald-200 mb-4">
                        <p class="text-xs uppercase font-bold text-emerald-700 mb-1">Catatan dari Admin/HR</p>
                        <p class="text-sm text-emerald-800 font-medium">{{ internship.notes }}</p>
                    </div>

                    <div v-if="internship?.mentor_recommendation"
                        class="bg-emerald-100/50 rounded-lg p-4 border border-emerald-200 mb-4">
                        <p class="text-xs uppercase font-bold text-emerald-700 mb-1">Rekomendasi dari Mentor</p>
                        <div class="text-sm text-emerald-800 font-medium rich-content" v-html="internship.mentor_recommendation"></div>
                    </div>

                    <div v-if="internship?.mentor_final_notes"
                        class="bg-emerald-100/50 rounded-lg p-4 border border-emerald-200 mb-4">
                        <p class="text-xs uppercase font-bold text-emerald-700 mb-1">Catatan dari Mentor</p>
                        <div class="text-sm text-emerald-800 font-medium rich-content" v-html="internship.mentor_final_notes"></div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <BaseButton v-if="internship?.certificate_url" :href="internship.certificate_url" external
                            target="_blank" variant="primary">
                            <span class="material-symbols-outlined">download</span>
                            Download Sertifikat
                        </BaseButton>
                        <BaseButton href="/#lowongan" variant="outlinePrimary">
                            <span class="material-symbols-outlined">work</span>
                            Lihat Lowongan Magang Baru
                        </BaseButton>
                    </div>
                </div>
            </div>
        </section>

        <div class="flex flex-col gap-6">
            <section v-if="applicationStatus === 'none'"
                class="bg-gradient-to-br from-primary/5 to-blue-50 rounded-xl border border-primary/20 p-8 text-center">
                <div class="max-w-lg mx-auto">
                    <div class="h-20 w-20 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-6">
                        <span class="material-symbols-outlined text-primary text-4xl">work</span>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-3">Belum Ada Lamaran</h2>
                    <p class="text-gray-600 mb-6">
                        Anda belum mengajukan lamaran magang. Pilih posisi yang tersedia dan mulai perjalanan karir Anda
                        bersama kami!
                    </p>
                    <BaseButton href="/#lowongan" size="lg">
                        <span class="material-symbols-outlined">search</span>
                        Cari Lowongan Tersedia
                    </BaseButton>
                </div>
            </section>
            <section v-else
                class="bg-white rounded-xl border border-gray-200 shadow-[0_2px_8px_rgba(0,0,0,0.04)] overflow-hidden">
                <div class="grid lg:grid-cols-5 h-full">
                    <div
                        class="lg:col-span-3 p-6 sm:p-8 flex flex-col border-b lg:border-b-0 lg:border-r border-gray-200 bg-gray-50/50">
                        <div class="flex-1">
                            <div class="flex items-start gap-5 mb-6">
                                <div
                                    class="h-16 w-16 min-w-[4rem] rounded-xl bg-white border border-gray-200 p-3 shadow-sm flex items-center justify-center overflow-hidden">
                                    <span class="material-symbols-outlined text-[32px] text-gray-400">work</span>
                                </div>
                                <div>
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        <span
                                            class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide bg-blue-100 text-blue-700 border border-blue-200">Internship</span>
                                    </div>
                                    <h2 class="text-xl font-bold text-gray-900 leading-tight">
                                        {{ application?.position_applied || 'Posisi Magang' }}
                                    </h2>
                                    <p class="text-sm text-gray-500 mt-1">Diajukan: {{
                                        formatDateTime(application?.created_at) }}</p>
                                </div>
                            </div>

                            <div v-if="currentStatus" :class="[
                                'border-l-4 rounded-r-lg p-4 mb-6',
                                `bg-${currentStatus.color}-50 border-${currentStatus.color}-400`
                            ]">
                                <h3
                                    :class="`text-lg font-bold text-${currentStatus.color}-900 flex items-center gap-2`">
                                    <span class="material-symbols-outlined text-[18px]">{{ currentStatus.icon }}</span>
                                    {{ currentStatus.label }}
                                </h3>
                                <p :class="`text-sm text-${currentStatus.color}-800 mt-1.5 leading-relaxed`">
                                    {{ currentStatus.message }}
                                </p>
                                <p v-if="application?.notes"
                                    :class="`text-base font-semibold text-${currentStatus.color}-800 mt-1.5 leading-relaxed`">
                                    Feedback Reviewer/HR : {{ application?.notes }}
                                </p>
                                <BaseButton v-if="application?.status === 'accepted'" :href="route('intern.dashboard')"
                                    size="md" class="mt-3" variant="outlinePrimary">
                                    Akses Dashboard Intern disini
                                    <span class="material-symbols-outlined scale-75">arrow_outward</span>
                                </BaseButton>
                            </div>

                            <div class="grid grid-cols-2 gap-y-4 gap-x-8 text-sm border-t border-gray-200 pt-5 mt-auto">
                                <div>
                                    <p class="text-gray-500 text-xs uppercase font-bold tracking-wider mb-1">Tanggal
                                        Magang</p>
                                    <p class="font-semibold text-gray-900">{{ formatDate(application?.start_date) + ' - ' + formatDate(application?.end_date) }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 text-xs uppercase font-bold tracking-wider mb-1">Asal
                                        Sekolah / Universitas</p>
                                    <p class="font-semibold text-gray-900">{{ application?.institution || '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-2 p-6 sm:p-8 bg-white flex flex-col">
                        <h3
                            class="font-bold text-lg text-gray-900 mb-6 flex items-center gap-2 pb-4 border-b border-gray-100">
                            <span class="material-symbols-outlined text-primary">history_edu</span>
                            Riwayat Progres
                        </h3>
                        <div class="relative pl-1 flex-1">
                            <div class="relative flex gap-4 pb-8">
                                <div class="flex flex-col items-center relative z-10">
                                    <div
                                        class="h-8 w-8 rounded-full bg-primary flex items-center justify-center shadow-md shadow-primary/20">
                                        <span class="material-symbols-outlined text-white font-bold">check</span>
                                    </div>
                                    <div class="w-0.5 h-full bg-primary/20 absolute top-8"></div>
                                </div>
                                <div>
                                    <p class="text-md font-bold text-gray-900">Registrasi Berhasil</p>
                                    <p class="text-sm text-gray-500 mt-0.5 font-medium">Akun terdaftar</p>
                                </div>
                            </div>

                            <div class="relative flex gap-4 pb-8">
                                <div class="flex flex-col items-center relative z-10">
                                    <div
                                        class="h-8 w-8 rounded-full bg-primary flex items-center justify-center shadow-md shadow-primary/20">
                                        <span
                                            class="material-symbols-outlined text-white font-bold">mark_email_read</span>
                                    </div>
                                    <div class="w-0.5 h-full bg-primary/20 absolute top-8"></div>
                                </div>
                                <div>
                                    <p class="text-md font-bold text-gray-900">Lamaran Terkirim</p>
                                    <p class="text-sm text-gray-500 mt-0.5 font-medium">{{
                                        formatDateTime(application?.created_at) }}</p>
                                </div>
                            </div>

                            <div class="relative flex gap-4 pb-8">
                                <div class="flex flex-col items-center relative z-10">
                                    <div :class="[
                                        'h-8 w-8 rounded-full flex items-center justify-center',
                                        application?.status === 'pending'
                                            ? 'bg-white border-2 border-amber-400'
                                            : 'bg-primary shadow-md shadow-primary/20'
                                    ]">
                                        <div v-if="application?.status === 'pending'"
                                            class="h-2.5 w-2.5 rounded-full bg-amber-400 animate-pulse"></div>
                                        <span v-else class="material-symbols-outlined text-white font-bold">check</span>
                                    </div>
                                    <div class="w-0.5 h-full bg-gray-100 absolute top-8"></div>
                                </div>
                                <div>
                                    <p :class="[
                                        'text-md font-bold',
                                        application?.status === 'pending' ? 'text-amber-600' : 'text-gray-900'
                                    ]">Seleksi Administrasi</p>
                                    <span v-if="application?.status === 'pending'"
                                        class="inline-flex mt-1 items-center px-2 py-0.5 rounded text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-100">
                                        Sedang Berlangsung
                                    </span>
                                    <p v-else class="text-sm text-gray-500 mt-0.5">{{
                                        formatDateTime(application?.reviewed_at) || 'Selesai' }}</p>
                                </div>
                            </div>

                            <div class="relative flex gap-4">
                                <div class="flex flex-col items-center relative z-10">
                                    <div :class="[
                                        'h-8 w-8 rounded-full flex items-center justify-center',
                                        application?.status === 'accepted' ? 'bg-green-500' :
                                            application?.status === 'rejected' ? 'bg-red-500' :
                                                'bg-gray-50 border border-gray-200 text-gray-300'
                                    ]">
                                        <span v-if="application?.status === 'accepted'"
                                            class="material-symbols-outlined text-white">check_circle</span>
                                        <span v-else-if="application?.status === 'rejected'"
                                            class="material-symbols-outlined text-white">cancel</span>
                                        <span v-else class="material-symbols-outlined">groups</span>
                                    </div>
                                </div>
                                <div>
                                    <p :class="[
                                        'text-md font-medium',
                                        application?.status === 'accepted' ? 'text-green-600' :
                                            application?.status === 'rejected' ? 'text-red-600' :
                                                'text-gray-400'
                                    ]">
                                        {{ application?.status === 'accepted' ? 'Diterima!' :
                                            application?.status === 'rejected' ? 'Tidak Lolos' : 'Pengumuman' }}
                                    </p>
                                    <p class="text-sm text-gray-400 mt-0.5">
                                        {{ application?.status === 'accepted' || application?.status === 'rejected'
                                            ? formatDateTime(application?.reviewed_at)
                                            : 'Menunggu jadwal' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section v-if="applicationStatus === 'rejected'"
                class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-xl border border-amber-200 p-6">
                <div class="flex flex-col sm:flex-row items-center gap-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Jangan Menyerah!</h3>
                        <p class="text-sm text-gray-600">
                            Kami membuka lowongan baru setiap bulan. Tingkatkan skill Anda dan coba lagi di kesempatan
                            berikutnya.
                        </p>
                    </div>
                    <BaseButton href="/#lowongan" variant="outlinePrimary">
                        <span class="material-symbols-outlined">search</span>
                        Lihat Lowongan Lain
                    </BaseButton>
                </div>
            </section>

            <section class="grid md:grid-cols-3 gap-6">
                <div
                    class="md:col-span-2 bg-gradient-to-br from-white to-blue-50 rounded-xl border border-gray-200 p-6 flex flex-col-reverse sm:flex-row items-center gap-6 relative overflow-hidden shadow-[0_2px_8px_rgba(0,0,0,0.04)] group">
                    <div
                        class="absolute top-0 right-0 w-40 h-40 bg-primary/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 pointer-events-none">
                    </div>
                    <div class="flex-1 z-10 relative">
                        <div
                            class="inline-flex items-center gap-1.5 px-5 py-1.5 rounded-full bg-white border border-blue-100 text-[10px] font-bold uppercase text-primary mb-3 shadow-sm">
                            <span class="material-symbols-outlined">stars</span>
                            <span>Peluang Baru</span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">
                            {{ applicationStatus === 'none' ? 'Mulai Karir Anda!' : 'Masih mencari posisi yang lebih cocok ? ' }}
                        </h3>
                        <p class="text-sm text-gray-600 mb-5 leading-relaxed font-body">
                            Jangan lewatkan kesempatan emas lainnya. Kami membuka lowongan baru setiap minggunya di
                            berbagai divisi.
                        </p>
                        <BaseButton href="/#lowongan">
                            <span>Jelajahi Lowongan</span>
                            <span
                                class="material-symbols-outlined group-hover:translate-x-1 transition-transform">arrow_forward</span>
                        </BaseButton>
                    </div>
                    <div
                        class="w-full sm:w-1/3 min-h-[160px] rounded-lg bg-cover bg-center shadow-inner border border-white/50 relative overflow-hidden bg-slate-200 flex items-center justify-center">
                        <span class="material-symbols-outlined text-4xl text-slate-300">image</span>
                    </div>
                </div>

                <div
                    class="md:col-span-1 bg-white rounded-xl border border-gray-200 p-6 shadow-[0_2px_8px_rgba(0,0,0,0.04)] flex flex-col justify-center">
                    <h3 class="text-base font-bold text-gray-900 mb-4">Butuh Bantuan?</h3>
                    <ul class="space-y-3">
                        <li>
                            <a href="#"
                                class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 border border-transparent hover:bg-white hover:border-gray-200 hover:shadow-sm transition-all group">
                                <div
                                    class="h-8 w-8 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-400 group-hover:text-primary transition-colors">
                                    <span class="material-symbols-outlined text-[18px]">help_center</span>
                                </div>
                                <div class="flex-1">
                                    <p
                                        class="text-sm font-bold text-gray-700 group-hover:text-primary transition-colors">
                                        Pusat Bantuan</p>
                                    <p class="text-[10px] text-gray-500">FAQ & Panduan</p>
                                </div>
                                <span
                                    class="material-symbols-outlined text-gray-300 text-[18px] group-hover:text-primary">chevron_right</span>
                            </a>
                        </li>
                        <li>
                            <a href="#"
                                class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 border border-transparent hover:bg-white hover:border-gray-200 hover:shadow-sm transition-all group">
                                <div
                                    class="h-8 w-8 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-400 group-hover:text-primary transition-colors">
                                    <span class="material-symbols-outlined text-[18px]">support_agent</span>
                                </div>
                                <div class="flex-1">
                                    <p
                                        class="text-sm font-bold text-gray-700 group-hover:text-primary transition-colors">
                                        Hubungi HR</p>
                                    <p class="text-[10px] text-gray-500">Senin - Jumat, 09:00 - 17:00</p>
                                </div>
                                <span
                                    class="material-symbols-outlined text-gray-300 text-[18px] group-hover:text-primary">chevron_right</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </section>
        </div>
    </div>

</template>
