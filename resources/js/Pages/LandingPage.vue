<script setup>
import { Head, Link } from '@inertiajs/vue3';
import LandingLayout from '@/Layouts/LandingLayout.vue';
import PrimaryButton from '@/Components/Landing/PrimaryButton.vue';
import SectionHeader from '@/Components/Landing/SectionHeader.vue';
import JobCard from '@/Components/Landing/JobCard.vue';
import ProcessStepCard from '@/Components/Landing/ProcessStepCard.vue';
import TestimonialCard from '@/Components/Landing/TestimonialCard.vue';
import StatItem from '@/Components/Landing/StatItem.vue';
import { onMounted } from 'vue';

// Data for dynamic rendering
const stats = [
    { value: '500+', label: 'Total Alumni' },
    { value: '50+', label: 'Mentor Ahli' },
    { value: '120', label: 'Proyek Internal' },
    { value: '98%', label: 'Serapan Kerja' },
];

const steps = [
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

const jobs = [
    {
        title: 'Magang Data Analyst',
        type: 'Remote',
        status: 'Terbuka',
        description: 'Menganalisis dataset internal untuk mendapatkan wawasan bisnis. Bekerja langsung dengan data warehouse Inosoft menggunakan SQL dan Python.',
        updatedAt: '2 hari lalu',
        image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuB3k1fvEBE1Nku2nHA0XdWpezFtnAU_3jOROOYFVuVRqi82j9PaCf8DNVRMTlhcbpnhPJIwtFEY5rTMJpZDtz53BZ0AQyptx6k3ht5BnStahuZHrOVWq3Miova1QzSxbotS0De58O1d4Jq2xVBa-nVcz8OeHSWuAtsSRW6BT4dDOkqIMEVyBl5a2B7-rIpLPxidU8ulrjGidi9AUefGyH1vbEAtw2w7d9T8FA_QrkmFEL_HF4DsFHk1id7AaWEDM-Ptl-q2pgT9Ltb-',
        href: '/lowongan/detail'
    },
    {
        title: 'Magang UI/UX Design',
        type: 'Hybrid',
        status: 'Terbuka',
        description: 'Merancang antarmuka aplikasi internal SINTESIS yang intuitif. Berkolaborasi dengan developer untuk mengimplementasikan design system perusahaan.',
        updatedAt: '5 hari lalu',
        image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuACtT8JbF6Dkg_H5N1rMwbN9qhTOr2pDuQxV7wIPVe5u5oGtGsBTw6LSugZi35iizpdnmPV8Nz1ABBKYWr-2QYlF4-L1R56OQWtSnJb3Ff6LLY15Rrt-8VOGs_OwJn9FEOxPTHOoIRn9Jjch640r2WfRuzq3BQVNGWkraqFBlXiVmcB8YzhYITTILkTFk6EdjGerzx6kLfhjPCi4NbuPI7jfqatQHX7TteN7qMFoGS7q7eB-UuG91Di44wRDh3JgW0-_gMqJelRk6hK',
        href: '/lowongan/detail'
    },
    {
        title: 'Magang Backend Developer',
        type: 'On-site',
        status: 'Ditutup',
        description: 'Membangun API dan layanan mikro yang kuat. Mengoptimalkan kinerja database dan memastikan ketersediaan tinggi layanan platform.',
        updatedAt: '1 minggu lalu',
        image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBNeK5WHYL0uMOqpi9Wa-wDooQ-3PN_gXwo6--KlI9wZdB5ItNW0HUwpe3b5Wl3oeFeJmPYJ99AinH0F6UFNRETNnZfms_uO4lLSN9QgbX8ID2vKvUJWV4z0wdQNl50qh1Ackm7sL-ULmPkrGSnmK7LGbe9zOjvisp68amlGClhsNywFRihWUlYzfrJ9b-lpiwQHYQlHGIHFafHoCPlRdXgmy91vs0yb4j3VLANTB52T-Mc2_Dr1hXlOJSA1fo_xAqc99WU2pUIhK8o'
    }
];

const testimonials = [
    {
        name: 'Andi Pratama',
        role: 'Ex-Data Analyst Intern',
        quote: 'Platform SINTESIS sangat membantu saya dalam mengatur tugas harian. Budaya kerja di Inosoft sangat suportif.',
        initial: 'A',
        stars: 4.8
    },
    {
        name: 'Siti Nurhaliza',
        role: 'Ex-UI/UX Intern',
        quote: 'Bukan hanya tempat magang, Inosoft memberikan pengalaman industri yang nyata. Sistem monitoringnya sangat profesional.',
        initial: 'S',
        stars: 5
    },
    {
        name: 'Rudi Hermawan',
        role: 'Ex-Backend Intern',
        quote: 'Saya mendapatkan banyak ilmu baru dan jaringan yang luas berkat program ini. Terima kasih Inosoft!',
        initial: 'R',
        stars: 4.5
    }
];

onMounted(() => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.reveal-on-scroll').forEach(el => observer.observe(el));
});
</script>

<template>
    <LandingLayout>
        <Head title="Portal Magang Inosoft" />

        <!-- Hero Section -->
        <section class="relative pt-16 pb-20 lg:pt-24 lg:pb-32 overflow-hidden bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-20">
                    <div class="flex-1 text-center lg:text-left reveal-on-scroll">
                        <div class="inline-flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-full px-4 py-1.5 mb-6 hover:bg-slate-100 transition-colors cursor-default">
                            <span class="relative flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-primary"></span>
                            </span>
                            <span class="text-slate-700 text-xs font-bold uppercase tracking-wide">Rekrutmen Internal Batch 4</span>
                        </div>
                        <h2 class="text-4xl md:text-5xl lg:text-6xl font-black leading-[1.1] tracking-tight text-slate-900 mb-6">
                            Bergabung Bersama <br class="hidden lg:block"/><span class="text-primary">Inosoft Trans Sistem</span>
                        </h2>
                        <p class="text-lg text-slate-600 leading-relaxed max-w-2xl mx-auto lg:mx-0 mb-8">
                            Portal resmi SINTESIS untuk manajemen program magang, monitoring produktivitas, dan pengembangan talenta di lingkungan PT Inosoft Trans Sistem.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            <PrimaryButton href="#lowongan" class="h-12 px-8 text-base transform hover:-translate-y-1 transition-transform">Lihat Lowongan</PrimaryButton>
                            <a href="#cara-kerja" class="inline-flex items-center justify-center h-12 px-8 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-base font-bold rounded-lg transition-all transform hover:-translate-y-1">
                                Alur Pendaftaran
                            </a>
                        </div>
                    </div>
                    <div class="flex-1 w-full max-w-[600px] lg:max-w-none reveal-on-scroll stagger-2">
                        <div class="relative rounded-2xl overflow-hidden shadow-2xl bg-slate-100 border border-slate-200 aspect-[4/3] group cursor-pointer">
                            <div class="absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-105" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAi_BGranDaaqyuhvmTMeo_Hvn6QTUy6e_C60-q74zansHV_9hgtNieckd2afm5MSQ5sY_Plrrq3wVS8hfrv6m5fpmPcrVKUKu1WfA1H2E0NAB8G8moesMfQybjuDBoOcsAhAJZ0-4GlxLA8xTQPIbEo-5olKDJpgnX1DYQBmpATND-QgxtpOhd0yHYW60dUv9lzSncFj_9i47t-yVMgNLrRfweXvv9u6SXSKdBQGxQbhCedILwwDCG199j-keEj0ZwVtgssEhM4jDx');"></div>
                            <div class="absolute inset-0 bg-gradient-to-tr from-slate-900/40 to-transparent"></div>
                            <!-- Floating Card Animation -->
                            <div class="absolute bottom-6 left-6 bg-white/95 backdrop-blur-sm p-4 rounded-xl shadow-lg border border-slate-100 flex items-center gap-4 max-w-xs animate-[float_6s_ease-in-out_infinite]">
                                <div class="bg-blue-50 p-3 rounded-lg text-primary">
                                    <span class="material-symbols-outlined">analytics</span>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Produktivitas Tim</p>
                                    <p class="text-slate-900 font-black text-xl">Optimal</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="py-12 bg-slate-50 border-y border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center divide-x divide-slate-200/50">
                    <StatItem v-for="stat in stats" :key="stat.label" :value="stat.value" :label="stat.label" />
                </div>
            </div>
        </section>

        <!-- Cara Kerja Section -->
        <section class="py-20 lg:py-28 bg-white" id="cara-kerja">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="reveal-on-scroll">
                    <SectionHeader 
                        title="Alur Pendaftaran" 
                        subtitle="Proses seleksi transparan untuk calon talenta Inosoft." 
                    />
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mt-12">
                    <div 
                        v-for="(step, index) in steps" 
                        :key="step.number" 
                        class="reveal-on-scroll" 
                        :class="`stagger-${index + 1}`"
                    >
                        <ProcessStepCard v-bind="step" />
                    </div>
                </div>
            </div>
        </section>

        <!-- Lowongan Section -->
        <section class="py-20 lg:py-28 bg-slate-50 border-t border-slate-200" id="lowongan">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row items-end justify-between gap-6 mb-12 reveal-on-scroll">
                    <div>
                        <h2 class="text-3xl md:text-4xl font-bold text-slate-900">Lowongan Tersedia</h2>
                        <p class="mt-3 text-slate-600">Bergabung dengan tim Inosoft Trans Sistem.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div 
                        v-for="(job, index) in jobs" 
                        :key="job.title" 
                        class="reveal-on-scroll" 
                        :class="`stagger-${index + 1}`"
                    >
                        <JobCard v-bind="job" />
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="py-20 lg:py-28 bg-white" id="testimoni">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="reveal-on-scroll">
                    <SectionHeader 
                        title="Pengalaman Alumni" 
                        subtitle="Apa kata mereka tentang magang di Inosoft Trans Sistem." 
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mt-12">
                    <div 
                        v-for="(testimonial, index) in testimonials" 
                        :key="testimonial.name" 
                        class="reveal-on-scroll" 
                        :class="`stagger-${index + 1}`"
                    >
                        <TestimonialCard v-bind="testimonial" />
                    </div>
                </div>
            </div>
        </section>
    </LandingLayout>
</template>
