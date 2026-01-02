<script setup>
import { Head, Link } from '@inertiajs/vue3';
import LandingLayout from '@/Layouts/LandingLayout.vue';
import BaseButton from '@/Components/BaseButton.vue';
import SectionHeader from '@/Components/Landing/SectionHeader.vue';
import JobCard from '@/Components/Landing/JobCard.vue';
import ProcessStepCard from '@/Components/Landing/ProcessStepCard.vue';
import TestimonialCard from '@/Components/Landing/TestimonialCard.vue';
import StatItem from '@/Components/Landing/StatItem.vue';
import { onMounted, onUnmounted, computed, ref } from 'vue';
import { formatRelativeTime } from '@/utils/helpers';
import axios from 'axios';

defineProps({
    jobs: {
        type: Array,
        default: () => [],
    },
});

defineOptions({ layout: LandingLayout })

const stats = [
    { value: '100+', label: 'Total Alumni' },
    { value: '30+', label: 'Mentor Ahli' },
    { value: '50+', label: 'Proyek Internal & Eksternal' },
    { value: '90%', label: 'Serapan Kerja' },
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

const defaultTestimonials = [
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

const testimonials = ref([]);
const currentSlide = ref(0);
const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024);
let slideInterval = null;

const displayedTestimonials = computed(() => {
    return testimonials.value.length > 0 ? testimonials.value : defaultTestimonials;
});

const itemsPerView = computed(() => {
    if (windowWidth.value >= 1024) return 3;
    if (windowWidth.value >= 768) return 2;
    return 1;
});

const totalSlides = computed(() => {
    return Math.ceil(displayedTestimonials.value.length / itemsPerView.value);
});

const needsSlider = computed(() => {
    return displayedTestimonials.value.length > itemsPerView.value;
});

const slideOffset = computed(() => {
    return currentSlide.value * (100 / itemsPerView.value);
});

const nextSlide = () => {
    if (!needsSlider.value) return;
    currentSlide.value = (currentSlide.value + 1) % totalSlides.value;
};

const prevSlide = () => {
    if (!needsSlider.value) return;
    currentSlide.value = (currentSlide.value - 1 + totalSlides.value) % totalSlides.value;
};

const goToSlide = (index) => {
    currentSlide.value = index;
};

const handleResize = () => {
    windowWidth.value = window.innerWidth;
    if (currentSlide.value >= totalSlides.value) {
        currentSlide.value = 0;
    }
};

const fetchTestimonials = async () => {
    try {
        const response = await axios.get('/api/testimonials');
        testimonials.value = response.data.map(t => ({
            name: t.user_name,
            role: 'Ex-' + t.position,
            quote: t.content,
            initial: t.user_name?.charAt(0) || '?',
            stars: t.rating,
            avatar: t.user_avatar,
        }));
    } catch (error) {
        console.log('Using default testimonials');
    }
};

let observer = null;

onMounted(() => {
    fetchTestimonials();

    window.addEventListener('resize', handleResize);

    slideInterval = setInterval(() => {
        if (needsSlider.value) nextSlide();
    }, 5000);

    observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    const targets = document.querySelectorAll('.reveal-on-scroll');
    targets.forEach(el => observer.observe(el));
});

onUnmounted(() => {
    if (observer) observer.disconnect();
    if (slideInterval) clearInterval(slideInterval);
    window.removeEventListener('resize', handleResize);
});
</script>

<template>

    <Head title="Portal Magang Inosoft" />

    <section class="relative pt-10 pb-20 lg:pb-24 overflow-hidden bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-20">
                <div class="flex-1 text-center lg:text-left reveal-on-scroll">
                    <div
                        class="inline-flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-full px-4 py-1.5 mb-4 hover:bg-slate-100 transition-colors cursor-default">
                        <span class="relative flex h-2.5 w-2.5">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-primary"></span>
                        </span>
                        <span class="text-slate-700 text-xs font-bold uppercase tracking-wide">Rekrutmen Terbuka</span>
                    </div>
                    <h2
                        class="text-4xl md:text-5xl lg:text-6xl font-black leading-[1.1] tracking-tight text-slate-900 mb-6">
                        Bergabung Bersama <br class="hidden lg:block" /><span class="text-primary">Inosoft Trans
                            Sistem</span>
                    </h2>
                    <p class="text-lg text-slate-600 leading-relaxed max-w-2xl mx-auto lg:mx-0 mb-6">
                        Portal resmi SINTESIS untuk manajemen program magang, monitoring produktivitas, dan pengembangan
                        talenta di lingkungan PT Inosoft Trans Sistem.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <BaseButton href="#lowongan" size="lg">Lihat Lowongan</BaseButton>
                        <BaseButton href="#cara-kerja" size="lg" variant="secondary">Alur Pendaftaran</BaseButton>
                    </div>
                </div>
                <div class="flex-1 w-full max-w-[600px] lg:max-w-none reveal-on-scroll stagger-2">
                    <div
                        class="relative rounded-2xl overflow-hidden shadow-2xl bg-slate-100 border border-slate-200 aspect-[4/3] group cursor-pointer">
                        <div class="absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-105"
                            style="background-image: url('/images/hero.png');">
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-tr from-slate-900/40 to-transparent"></div>
                        <div
                            class="absolute bottom-6 left-6 bg-white/95 backdrop-blur-sm p-4 rounded-xl shadow-lg border border-slate-100 flex items-center gap-4 max-w-xs animate-[float_6s_ease-in-out_infinite]">
                            <div class="bg-blue-50 p-3 rounded-lg text-primary">
                                <span class="material-symbols-outlined">home_work</span>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">PT Inosoft
                                    Trans Sistem</p>
                                <p class="text-slate-900 font-black text-xl">Founded in 2006</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-12 bg-slate-50 border-y border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center divide-x divide-slate-200/50">
                <StatItem v-for="stat in stats" :key="stat.label" :value="stat.value" :label="stat.label" />
            </div>
        </div>
    </section>

    <section class="py-20 lg:py-28 bg-white" id="cara-kerja">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="reveal-on-scroll">
                <SectionHeader title="Alur Pendaftaran"
                    subtitle="Proses seleksi transparan untuk calon talenta Inosoft." />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mt-12">
                <div v-for="(step, index) in steps" :key="step.number" class="reveal-on-scroll"
                    :class="`stagger-${index + 1}`">
                    <ProcessStepCard v-bind="step" />
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 lg:py-28 bg-slate-50 border-t border-slate-200" id="lowongan">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-end justify-between gap-6 mb-12 reveal-on-scroll">
                <div>
                    <h2 class="text-3xl md:text-4xl font-bold text-slate-900">Lowongan Tersedia</h2>
                    <p class="mt-3 text-slate-600">Bergabung dengan tim Inosoft Trans Sistem.</p>
                </div>
            </div>

            <div v-if="jobs.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div v-for="(job, index) in jobs" :key="job.id" class="reveal-on-scroll"
                    :class="`stagger-${(index % 3) + 1}`">
                    <JobCard :title="job.title" :type="job.type" :description="job.description"
                        :updatedAt="formatRelativeTime(job.updatedAt)" :image="job.image" :href="job.href" />
                </div>
            </div>

            <div v-else class="text-center py-16 bg-white rounded-xl border border-slate-200">
                <span class="material-symbols-outlined text-6xl text-slate-300 mb-4">work_off</span>
                <h3 class="text-xl font-bold text-slate-700 mb-2">Belum Ada Lowongan</h3>
                <p class="text-slate-500">Lowongan magang akan segera tersedia. Pantau terus halaman ini!</p>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-20 lg:py-28 bg-white" id="testimoni">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="reveal-on-scroll">
                <SectionHeader title="Pengalaman Alumni"
                    subtitle="Apa kata mereka tentang magang di Inosoft Trans Sistem." />
            </div>

            <div class="relative mt-12">
                <button v-if="needsSlider" @click="prevSlide"
                    class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 z-10 w-12 h-12 bg-white rounded-full shadow-lg border border-slate-200 items-center justify-center text-slate-600 hover:text-primary hover:border-primary transition-colors cursor-pointer hidden md:flex">
                    <span class="material-symbols-outlined">chevron_left</span>
                </button>
                <button v-if="needsSlider" @click="nextSlide"
                    class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 z-10 w-12 h-12 bg-white rounded-full shadow-lg border border-slate-200 items-center justify-center text-slate-600 hover:text-primary hover:border-primary transition-colors cursor-pointer hidden md:flex">
                    <span class="material-symbols-outlined">chevron_right</span>
                </button>

                <div class="overflow-hidden">
                    <div class="flex transition-transform duration-500 ease-out"
                        :style="{ transform: `translateX(-${slideOffset}%)` }">
                        <div v-for="(testimonial, index) in displayedTestimonials" :key="index"
                            class="w-full shrink-0 px-2 md:w-1/2 lg:w-1/3">
                            <TestimonialCard v-bind="testimonial" />
                        </div>
                    </div>
                </div>

                <div v-if="needsSlider" class="flex justify-center gap-2 mt-8">
                    <button v-for="n in totalSlides" :key="n" @click="goToSlide(n - 1)"
                        class="w-3 h-3 rounded-full transition-all duration-300 cursor-pointer"
                        :class="currentSlide === n - 1 ? 'bg-primary w-8' : 'bg-slate-300 hover:bg-slate-400'">
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>
