<script setup>
import BaseButton from '@/Components/BaseButton.vue';
import LandingLayout from '@/Layouts/LandingLayout.vue';
import { Head, useForm, Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    openJobs: {
        type: Array,
        default: () => [],
    },
    selectedJob: {
        type: Object,
        default: null,
    },
    institutions: {
        type: Array,
        default: () => [],
    },
});

const currentStep = ref(1);
const totalSteps = 4;
const page = usePage();
const stepErrors = ref({});

const authUser = computed(() => page.props.auth.user);
const selectedJobId = ref(props.selectedJob?.id || '');

const currentJob = computed(() => {
    if (!selectedJobId.value) return null;
    return props.openJobs.find(j => j.id === selectedJobId.value) || props.selectedJob;
});

watch(() => props.selectedJob, (newJob) => {
    if (newJob) {
        selectedJobId.value = newJob.id;
    }
}, { immediate: true });

const form = useForm({
    job_id: selectedJobId.value,
    // Step 1: Identitas
    full_name: authUser.value?.name || '',
    email: authUser.value?.email || '',
    phone: authUser.value?.phone || '',
    institution_id: '',
    referral: '',

    // Step 2: Kompetensi
    skills: [],
    other_skills: '',
    databases: [],
    other_databases: '',
    operating_systems: [],
    other_os: '',

    // Step 3: Minat
    other_interest: '',
    demo_required: null,
    self_description: '',
    portfolio_url: '',

    // Step 4: Dokumen
    start_date: '',
    end_date: '',
    letter_file: null,
    id_card_file: null,
    cv_file: null,
});

watch(selectedJobId, (newId) => {
    form.job_id = newId;
});

const validateStep = (step) => {
    stepErrors.value = {};

    if (step === 1) {
        if (!selectedJobId.value) {
            stepErrors.value.job = 'Silakan pilih posisi yang ingin dilamar.';
        }
        if (!form.full_name.trim()) {
            stepErrors.value.full_name = 'Nama lengkap wajib diisi.';
        }
        if (!form.email.trim()) {
            stepErrors.value.email = 'Email wajib diisi.';
        }
        if (!form.phone.trim()) {
            stepErrors.value.phone = 'Nomor WhatsApp wajib diisi.';
        }
        if (!form.institution_id) {
            stepErrors.value.institution_id = 'Nama sekolah/universitas wajib dipilih.';
        }
    }

    if (step === 3) {
        if (!form.self_description.trim() || form.self_description.length < 20) {
            stepErrors.value.self_description = 'Deskripsi diri wajib diisi (minimal 20 karakter).';
        }
    }

    if (step === 4) {
        if (!form.start_date) {
            stepErrors.value.start_date = 'Tanggal mulai wajib diisi.';
        }
        if (!form.end_date) {
            stepErrors.value.end_date = 'Tanggal berakhir wajib diisi.';
        }
        if (!form.letter_file) {
            stepErrors.value.letter_file = 'Surat izin magang wajib diunggah.';
        }
        if (!form.id_card_file) {
            stepErrors.value.id_card_file = 'Kartu pelajar/KTM wajib diunggah.';
        }
        if (!form.cv_file) {
            stepErrors.value.cv_file = 'CV/Resume wajib diunggah.';
        }
    }

    return Object.keys(stepErrors.value).length === 0;
};

const nextStep = () => {
    if (!validateStep(currentStep.value)) {
        return;
    }

    if (currentStep.value < totalSteps) {
        currentStep.value++;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

const submit = () => {
    if (!validateStep(4)) {
        return;
    }

    form.post(route('internship.store'), {
        forceFormData: true,
        preserveScroll: true,
    });
};

const handleFileChange = (e, field) => {
    form[field] = e.target.files[0];
    if (stepErrors.value[field]) {
        delete stepErrors.value[field];
    }
};

const triggerFileInput = (id) => {
    document.getElementById(id).click();
};

const steps = [
    { number: 1, title: 'Identitas & Kontak' },
    { number: 2, title: 'Kompetensi Teknis' },
    { number: 3, title: 'Minat & Profil' },
    { number: 4, title: 'Administrasi & Dokumen' },
];

// Institution Select State
const institutionsList = ref([...props.institutions]);
const institutionSearch = ref('');
const showInstitutionDropdown = ref(false);
const selectedInstitution = ref(null);
const isCreatingInstitution = ref(false);

const filteredInstitutions = computed(() => {
    if (!institutionSearch.value) return institutionsList.value;
    const search = institutionSearch.value.toLowerCase();
    return institutionsList.value.filter(i => i.name.toLowerCase().includes(search));
});

const hasExactMatch = computed(() => {
    if (!institutionSearch.value) return true;
    return institutionsList.value.some(i => i.name.toLowerCase() === institutionSearch.value.toLowerCase());
});

const selectInstitution = (institution) => {
    selectedInstitution.value = institution;
    form.institution_id = institution.id;
    institutionSearch.value = institution.name;
    showInstitutionDropdown.value = false;
    if (stepErrors.value.institution_id) {
        delete stepErrors.value.institution_id;
    }
};

const createNewInstitution = async () => {
    if (!institutionSearch.value.trim() || isCreatingInstitution.value) return;

    isCreatingInstitution.value = true;
    try {
        const response = await axios.post('/institutions', {
            name: institutionSearch.value.trim()
        });

        const newInstitution = response.data;
        institutionsList.value.push(newInstitution);
        institutionsList.value.sort((a, b) => a.name.localeCompare(b.name));
        selectInstitution(newInstitution);
    } catch (error) {
        if (error.response?.data?.errors?.name) {
            stepErrors.value.institution_id = error.response.data.errors.name[0];
        } else {
            stepErrors.value.institution_id = 'Gagal menambahkan institusi baru.';
        }
    } finally {
        isCreatingInstitution.value = false;
    }
};

const clearInstitution = () => {
    selectedInstitution.value = null;
    form.institution_id = '';
    institutionSearch.value = '';
};

// Click outside handler
const institutionDropdownRef = ref(null);
const institutionInputRef = ref(null);

const handleClickOutside = (event) => {
    if (institutionDropdownRef.value && !institutionDropdownRef.value.contains(event.target) &&
        institutionInputRef.value && !institutionInputRef.value.contains(event.target)) {
        showInstitutionDropdown.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

defineOptions({ layout: LandingLayout })
</script>

<template>

    <Head title="Formulir Pendaftaran Magang" />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 pt-10 pb-16 overflow-hidden">

        <div class="mb-6">
            <h2 class="text-3xl font-black text-slate-900 mb-2 tracking-tight">Formulir Pendaftaran Magang</h2>
            <p class="text-slate-500 text-lg">Silakan lengkapi data diri, kualifikasi teknis, dan dokumen administrasi
                Anda di bawah ini.</p>
        </div>

        <!-- Selected Job Display -->
        <div v-if="currentJob"
            class="mb-8 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm relative overflow-hidden flex items-center gap-6">
            <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-primary"></div>
            <div
                class="hidden sm:flex w-14 h-14 rounded-full bg-blue-50 items-center justify-center text-primary shrink-0">
                <span class="material-symbols-outlined text-[32px]">badge</span>
            </div>
            <div class="flex-1">
                <p class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-1">Posisi Lamaran Terpilih</p>
                <h3 class="text-xl md:text-2xl font-bold text-slate-900 leading-tight">{{ currentJob.title }}</h3>
                <div class="flex items-center gap-3 mt-2">
                    <span class="text-sm text-slate-500 flex items-center gap-1">
                        <span class="material-symbols-outlined text-base">location_on</span>
                        {{ currentJob.location || 'Surabaya' }}
                    </span>
                    <span class="text-sm text-slate-500 flex items-center gap-1">
                        <span class="material-symbols-outlined text-base">work</span>
                        {{ currentJob.type }}
                    </span>
                </div>
            </div>
            <button @click="selectedJobId = ''"
                class="text-slate-400 hover:text-red-500 transition-colors p-2 cursor-pointer" title="Ganti posisi">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <!-- Job Selection if not selected -->
        <div v-else class="mb-8 bg-amber-50 border border-amber-200 rounded-2xl p-6">
            <div class="flex items-start gap-4 mb-4">
                <span class="material-symbols-outlined text-amber-600 text-2xl">warning</span>
                <div>
                    <h3 class="text-lg font-bold text-amber-800">Pilih Posisi Terlebih Dahulu</h3>
                    <p class="text-amber-700 text-sm mt-1">Silakan pilih posisi magang yang ingin Anda lamar dari daftar
                        di bawah ini.</p>
                </div>
            </div>

            <div v-if="openJobs.length > 0" class="grid gap-3 mt-4">
                <label v-for="job in openJobs" :key="job.id"
                    class="flex items-center gap-4 p-4 bg-white border-2 rounded-xl cursor-pointer transition-all hover:border-primary/50"
                    :class="selectedJobId === job.id ? 'border-primary bg-blue-50' : 'border-slate-200'">
                    <input v-model="selectedJobId" :value="job.id" type="radio" name="job_selection"
                        class="w-5 h-5 text-primary border-slate-300 focus:ring-primary" />
                    <div class="flex-1">
                        <p class="font-bold text-slate-900">{{ job.title }}</p>
                        <p class="text-sm text-slate-500">{{ job.location || 'Surabaya' }} • {{ job.type }}</p>
                    </div>
                </label>
            </div>
            <p v-if="stepErrors.job" class="text-red-500 text-sm mt-3">{{ stepErrors.job }}</p>
            <div v-if="openJobs.length === 0" class="text-center py-8">
                <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">work_off</span>
                <p class="text-slate-500">Belum ada lowongan yang tersedia saat ini.</p>
                <BaseButton href="/" variant="outline" class="mt-4">Kembali ke Beranda</BaseButton>
            </div>
        </div>

        <form @submit.prevent="submit"
            class="bg-white rounded-2xl shadow-sm border border-slate-200 relative">

            <!-- Step Progress Indicator -->
            <div class="px-8 pt-8 pb-4 border-b border-slate-100">
                <div class="flex items-center justify-center max-w-xl mx-auto">
                    <template v-for="(step, idx) in steps" :key="step.number">
                        <!-- Step Item -->
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all"
                                :class="currentStep > step.number
                                    ? 'bg-green-500 text-white'
                                    : currentStep === step.number
                                        ? 'bg-primary text-white ring-4 ring-primary/20'
                                        : 'bg-slate-100 text-slate-400'">
                                <span v-if="currentStep > step.number"
                                    class="material-symbols-outlined text-lg">check</span>
                                <span v-else>{{ step.number }}</span>
                            </div>
                            <span class="hidden sm:block text-xs font-medium mt-2 text-center whitespace-nowrap"
                                :class="currentStep >= step.number ? 'text-primary' : 'text-slate-400'">
                                {{ step.title }}
                            </span>
                        </div>
                        <!-- Connecting Line -->
                        <div v-if="idx < steps.length - 1"
                            class="w-12 sm:w-20 h-0.5 mx-1 sm:mx-2 -mt-5 sm:-mt-0 transition-all"
                            :class="currentStep > step.number ? 'bg-green-500' : 'bg-slate-200'"></div>
                    </template>
                </div>
                <p class="sm:hidden text-center text-sm font-medium text-primary mt-3">
                    {{ steps[currentStep - 1]?.title }}
                </p>
            </div>

            <!-- Step 1: Identitas & Kontak -->
            <div v-show="currentStep === 1" class="p-8 border-b border-slate-100 animate-fade-in-up">
                <div class="flex items-center gap-3 mb-6">
                    <span
                        class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-primary font-bold text-sm">1</span>
                    <h3 class="text-xl font-bold text-slate-800">Identitas & Kontak</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Lengkap <span
                                class="text-red-500">*</span></label>
                        <input v-model="form.full_name"
                            class="w-full h-12 px-4 bg-slate-50 border rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all placeholder:text-slate-400"
                            :class="stepErrors.full_name || form.errors.full_name ? 'border-red-500' : 'border-slate-300'"
                            placeholder="Masukkan nama lengkap sesuai KTP/KTM" type="text" />
                        <p v-if="stepErrors.full_name || form.errors.full_name" class="text-red-500 text-sm mt-1">{{
                            stepErrors.full_name || form.errors.full_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Alamat Email <span
                                class="text-red-500">*</span></label>
                        <div class="relative">
                            <span
                                class="material-symbols-outlined absolute left-3 top-3 text-slate-400 text-[20px]">mail</span>
                            <input v-model="form.email" :disabled="!!authUser?.email"
                                class="w-full h-12 pl-10 pr-4 border rounded-lg focus:outline-none transition-all placeholder:text-slate-400"
                                :class="[
                                    stepErrors.email || form.errors.email ? 'border-red-500' : 'border-slate-300',
                                    authUser?.email ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : 'bg-slate-50 focus:border-primary focus:ring-1 focus:ring-primary'
                                ]" placeholder="contoh@email.com" type="email" />
                        </div>
                        <p v-if="stepErrors.email || form.errors.email" class="text-red-500 text-sm mt-1">{{
                            stepErrors.email ||
                            form.errors.email }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nomor WhatsApp <span
                                class="text-red-500">*</span></label>
                        <div class="relative">
                            <span
                                class="material-symbols-outlined absolute left-3 top-3 text-slate-400 text-[20px]">call</span>
                            <input v-model="form.phone"
                                class="w-full h-12 pl-10 pr-4 bg-slate-50 border rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all placeholder:text-slate-400"
                                :class="stepErrors.phone || form.errors.phone ? 'border-red-500' : 'border-slate-300'"
                                placeholder="0812xxxxxxxx" type="tel" />
                        </div>
                        <p v-if="stepErrors.phone || form.errors.phone" class="text-red-500 text-sm mt-1">{{
                            stepErrors.phone ||
                            form.errors.phone }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Sekolah / Universitas <span
                                class="text-red-500">*</span></label>
                        <div class="relative">
                            <span
                                class="material-symbols-outlined absolute left-3 top-3 text-slate-400 text-[20px] z-10">school</span>

                            <!-- Selected Institution Display -->
                            <div v-if="selectedInstitution"
                                class="w-full h-12 pl-10 pr-10 bg-slate-50 border border-slate-300 rounded-lg flex items-center">
                                <span class="text-slate-900 truncate">{{ selectedInstitution.name }}</span>
                                <button type="button" @click="clearInstitution"
                                    class="absolute right-3 text-slate-400 hover:text-red-500 transition-colors cursor-pointer">
                                    <span class="material-symbols-outlined text-[20px]">close</span>
                                </button>
                            </div>

                            <!-- Search Input -->
                            <div v-else class="relative" style="overflow: visible;">
                                <input ref="institutionInputRef" v-model="institutionSearch"
                                    @focus="showInstitutionDropdown = true" @click.stop
                                    class="w-full h-12 pl-10 pr-4 bg-slate-50 border rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all placeholder:text-slate-400"
                                    :class="stepErrors.institution_id || form.errors.institution_id ? 'border-red-500' : 'border-slate-300'"
                                    placeholder="Cari atau ketik nama institusi..." type="text" autocomplete="off" />

                                <!-- Dropdown -->
                                <div v-if="showInstitutionDropdown" ref="institutionDropdownRef" @click.stop
                                    class="absolute left-0 right-0 z-[9999] mt-1 bg-white border border-slate-200 rounded-xl shadow-xl max-h-60 overflow-y-auto"
                                    style="position: absolute;">

                                    <!-- Filtered Results -->
                                    <button v-for="institution in filteredInstitutions.slice(0, 10)"
                                        :key="institution.id" type="button" @click="selectInstitution(institution)"
                                        class="w-full text-left px-4 py-3 hover:bg-primary/5 transition-colors border-b border-slate-100 last:border-0 cursor-pointer">
                                        <span class="text-slate-700">{{ institution.name }}</span>
                                    </button>

                                    <!-- No Results + Create New -->
                                    <div v-if="filteredInstitutions.length === 0 && institutionSearch.trim()"
                                        class="p-4 text-center">
                                        <p class="text-slate-500 text-sm mb-3">Tidak ditemukan.</p>
                                        <button type="button" @click="createNewInstitution"
                                            :disabled="isCreatingInstitution"
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors text-sm font-medium disabled:opacity-50 cursor-pointer">
                                            <span class="material-symbols-outlined text-[18px]">add</span>
                                            {{ isCreatingInstitution ? 'Menambahkan...' : `Tambahkan
                                            "${institutionSearch.trim()}"`
                                            }}
                                        </button>
                                    </div>

                                    <!-- Show create option if no exact match -->
                                    <button v-else-if="!hasExactMatch && institutionSearch.trim()" type="button"
                                        @click="createNewInstitution" :disabled="isCreatingInstitution"
                                        class="w-full text-left px-4 py-3 bg-blue-50 hover:bg-blue-100 transition-colors flex items-center gap-2 cursor-pointer">
                                        <span
                                            class="material-symbols-outlined text-primary text-[18px]">add_circle</span>
                                        <span class="text-primary text-sm font-medium">
                                            {{ isCreatingInstitution ? 'Menambahkan...' : `Tambahkan
                                            "${institutionSearch.trim()}"`
                                            }}
                                        </span>
                                    </button>

                                    <!-- Empty State -->
                                    <div v-if="!institutionSearch && institutionsList.length === 0"
                                        class="p-4 text-center text-slate-400 text-sm">
                                        Ketik nama institusi untuk mencari...
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="text-slate-400 text-xs mt-1">Pilih dari daftar atau ketik untuk menambah institusi
                            baru</p>
                        <p v-if="stepErrors.institution_id || form.errors.institution_id"
                            class="text-red-500 text-sm mt-1">{{
                                stepErrors.institution_id || form.errors.institution_id }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Staf Inosoft Pemberi Referensi
                            <span class="text-slate-400 font-normal">(Opsional)</span></label>
                        <div class="relative">
                            <span
                                class="material-symbols-outlined absolute left-3 top-3 text-slate-400 text-[20px]">person_pin</span>
                            <input v-model="form.referral"
                                class="w-full h-12 pl-10 pr-4 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all placeholder:text-slate-400"
                                placeholder="Nama staf jika ada" type="text" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Kompetensi Teknis -->
            <div v-show="currentStep === 2" class="p-8 border-b border-slate-100 bg-slate-50/50 animate-fade-in-up">
                <div class="flex items-center gap-3 mb-6">
                    <span
                        class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-primary font-bold text-sm">2</span>
                    <h3 class="text-xl font-bold text-slate-800">Kompetensi Teknis</h3>
                </div>
                <div class="space-y-8">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-3">Bahasa Pemrograman
                            Dikuasai</label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-3">
                            <label
                                v-for="skill in ['JavaScript / TS', 'PHP', 'Java', 'Python', 'Go (Golang)', 'C# / .NET', 'Dart / Flutter', 'Kotlin / Swift']"
                                :key="skill"
                                class="flex items-center gap-2 p-3 bg-white border border-slate-200 rounded-lg cursor-pointer hover:border-primary/50 transition-colors">
                                <input v-model="form.skills" :value="skill"
                                    class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary"
                                    type="checkbox" />
                                <span class="text-sm text-slate-700">{{ skill }}</span>
                            </label>
                        </div>
                        <input v-model="form.other_skills"
                            class="w-full h-10 px-4 bg-white border border-slate-300 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm placeholder:text-slate-400"
                            placeholder="Sebutkan bahasa pemrograman lain jika ada..." type="text" />
                        <small clas="text-xs text-muted">Pisahkan dengan koma tanpa spasi (jika lebih dari 1).</small>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-3">Database Dikuasai</label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-3">
                            <label v-for="db in ['MySQL / MariaDB', 'PostgreSQL', 'SQL Server', 'MongoDB']" :key="db"
                                class="flex items-center gap-2 p-3 bg-white border border-slate-200 rounded-lg cursor-pointer hover:border-primary/50 transition-colors">
                                <input v-model="form.databases" :value="db"
                                    class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary"
                                    type="checkbox" />
                                <span class="text-sm text-slate-700">{{ db }}</span>
                            </label>
                        </div>
                        <input v-model="form.other_databases"
                            class="w-full h-10 px-4 bg-white border border-slate-300 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm placeholder:text-slate-400"
                            placeholder="Sebutkan database lain jika ada..." type="text" />
                        <small clas="text-xs text-muted">Pisahkan dengan koma tanpa spasi (jika lebih dari 1).</small>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-3">Sistem Operasi Dikuasai</label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-3">
                            <label v-for="os in ['Windows', 'Linux (Ubuntu/etc)', 'macOS']" :key="os"
                                class="flex items-center gap-2 p-3 bg-white border border-slate-200 rounded-lg cursor-pointer hover:border-primary/50 transition-colors">
                                <input v-model="form.operating_systems" :value="os"
                                    class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary"
                                    type="checkbox" />
                                <span class="text-sm text-slate-700">{{ os }}</span>
                            </label>
                        </div>
                        <input v-model="form.other_os"
                            class="w-full h-10 px-4 bg-white border border-slate-300 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm placeholder:text-slate-400"
                            placeholder="Sebutkan OS lain jika ada..." type="text" />
                        <small clas="text-xs text-muted">Pisahkan dengan koma tanpa spasi (jika lebih dari 1).</small>
                    </div>
                </div>
            </div>

            <!-- Step 3: Minat & Profil -->
            <div v-show="currentStep === 3" class="p-8 border-b border-slate-100 animate-fade-in-up">
                <div class="flex items-center gap-3 mb-6">
                    <span
                        class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-primary font-bold text-sm">3</span>
                    <h3 class="text-xl font-bold text-slate-800">Minat & Profil</h3>
                </div>
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Bidang lain yang ingin dikerjakan
                            (selain peran utama) <span class="text-slate-400 font-normal">(Opsional)</span></label>
                        <input v-model="form.other_interest"
                            class="w-full h-12 px-4 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all placeholder:text-slate-400"
                            placeholder="Contoh: UI/UX Design, QA Testing, Project Management..." type="text" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-3">Apakah Anda wajib
                            mendemonstrasikan hasil kerja magang ke Guru/Dosen?</label>
                        <div class="flex gap-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input v-model="form.demo_required" :value="true"
                                    class="w-4 h-4 text-primary border-slate-300 focus:ring-primary" type="radio" />
                                <span class="text-slate-700">Ya, Wajib</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input v-model="form.demo_required" :value="false"
                                    class="w-4 h-4 text-primary border-slate-300 focus:ring-primary" type="radio" />
                                <span class="text-slate-700">Tidak Wajib</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsikan Diri Anda Secara
                            Singkat <span class="text-red-500">*</span></label>
                        <textarea v-model="form.self_description"
                            class="w-full p-4 h-32 bg-slate-50 border rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary resize-none transition-all placeholder:text-slate-400"
                            :class="stepErrors.self_description || form.errors.self_description ? 'border-red-500' : 'border-slate-300'"
                            placeholder="Ceritakan tentang motivasi, kekuatan, dan apa yang ingin Anda capai selama magang..."></textarea>
                        <p v-if="stepErrors.self_description || form.errors.self_description"
                            class="text-red-500 text-sm mt-1">{{
                                stepErrors.self_description || form.errors.self_description }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tautan Portofolio Keterampilan &
                            Kemampuan</label>
                        <div class="relative">
                            <span
                                class="material-symbols-outlined absolute left-3 top-3 text-slate-400 text-[20px]">link</span>
                            <input v-model="form.portfolio_url"
                                class="w-full h-12 pl-10 pr-4 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all placeholder:text-slate-400"
                                placeholder="https://github.com/username atau https://linkedin.com/in/username"
                                type="url" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 4: Administrasi & Dokumen -->
            <div v-show="currentStep === 4" class="p-8 bg-slate-50/50 animate-fade-in-up">
                <div class="flex items-center gap-3 mb-6">
                    <span
                        class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-primary font-bold text-sm">4</span>
                    <h3 class="text-xl font-bold text-slate-800">Administrasi & Dokumen</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Rencana Tanggal Mulai <span
                                class="text-red-500">*</span></label>
                        <div class="relative">
                            <input v-model="form.start_date"
                                class="w-full h-12 px-4 bg-white border rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-slate-700"
                                :class="stepErrors.start_date || form.errors.start_date ? 'border-red-500' : 'border-slate-300'"
                                type="date" />
                        </div>
                        <p v-if="stepErrors.start_date || form.errors.start_date" class="text-red-500 text-sm mt-1">{{
                            stepErrors.start_date || form.errors.start_date }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Rencana Tanggal Berakhir <span
                                class="text-red-500">*</span></label>
                        <div class="relative">
                            <input v-model="form.end_date"
                                class="w-full h-12 px-4 bg-white border rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-slate-700"
                                :class="stepErrors.end_date || form.errors.end_date ? 'border-red-500' : 'border-slate-300'"
                                type="date" />
                        </div>
                        <p v-if="stepErrors.end_date || form.errors.end_date" class="text-red-500 text-sm mt-1">{{
                            stepErrors.end_date || form.errors.end_date }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="group relative">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Surat Izin Magang <span
                                class="text-red-500">*</span></label>
                        <div @click="triggerFileInput('letter_input')"
                            class="border-2 border-dashed rounded-xl p-6 flex flex-col items-center justify-center text-center bg-white hover:bg-blue-50 transition-all cursor-pointer h-40"
                            :class="stepErrors.letter_file || form.errors.letter_file ? 'border-red-400 hover:border-red-500' : 'border-slate-300 hover:border-primary/50'">
                            <span
                                class="material-symbols-outlined text-4xl text-slate-400 group-hover:text-primary mb-2">upload_file</span>
                            <p class="text-xs text-slate-500 font-medium">{{ form.letter_file ? form.letter_file.name :
                                'Klik untuk unggah PDF' }}</p>
                            <p class="text-[10px] text-slate-400 mt-1">Maks. 2MB</p>
                            <input id="letter_input" accept=".pdf" class="hidden" type="file"
                                @change="(e) => handleFileChange(e, 'letter_file')" />
                        </div>
                        <p v-if="stepErrors.letter_file || form.errors.letter_file" class="text-red-500 text-sm mt-1">{{
                            stepErrors.letter_file || form.errors.letter_file }}</p>
                    </div>
                    <div class="group relative">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Kartu Pelajar / KTM <span
                                class="text-red-500">*</span></label>
                        <div @click="triggerFileInput('id_card_input')"
                            class="border-2 border-dashed rounded-xl p-6 flex flex-col items-center justify-center text-center bg-white hover:bg-blue-50 transition-all cursor-pointer h-40"
                            :class="stepErrors.id_card_file || form.errors.id_card_file ? 'border-red-400 hover:border-red-500' : 'border-slate-300 hover:border-primary/50'">
                            <span
                                class="material-symbols-outlined text-4xl text-slate-400 group-hover:text-primary mb-2">id_card</span>
                            <p class="text-xs text-slate-500 font-medium">{{ form.id_card_file ? form.id_card_file.name
                                : 'Klik untuk unggah Scan' }}</p>
                            <p class="text-[10px] text-slate-400 mt-1">PDF/JPG/PNG</p>
                            <input id="id_card_input" accept=".pdf,.jpg,.jpeg,.png" class="hidden" type="file"
                                @change="(e) => handleFileChange(e, 'id_card_file')" />
                        </div>
                        <p v-if="stepErrors.id_card_file || form.errors.id_card_file" class="text-red-500 text-sm mt-1">
                            {{
                                stepErrors.id_card_file || form.errors.id_card_file }}</p>
                    </div>
                    <div class="group relative">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">CV / Resume Terbaru <span
                                class="text-red-500">*</span></label>
                        <div @click="triggerFileInput('cv_input')"
                            class="border-2 border-dashed rounded-xl p-6 flex flex-col items-center justify-center text-center bg-white hover:bg-blue-50 transition-all cursor-pointer h-40"
                            :class="stepErrors.cv_file || form.errors.cv_file ? 'border-red-400 hover:border-red-500' : 'border-slate-300 hover:border-primary/50'">
                            <span
                                class="material-symbols-outlined text-4xl text-slate-400 group-hover:text-primary mb-2">description</span>
                            <p class="text-xs text-slate-500 font-medium">{{ form.cv_file ? form.cv_file.name : 'Klik untuk unggah CV' }}</p>
                            <p class="text-[10px] text-slate-400 mt-1">PDF Only (Maks. 2MB)</p>
                            <input id="cv_input" accept=".pdf" class="hidden" type="file"
                                @change="(e) => handleFileChange(e, 'cv_file')" />
                        </div>
                        <p v-if="stepErrors.cv_file || form.errors.cv_file" class="text-red-500 text-sm mt-1">{{
                            stepErrors.cv_file
                            || form.errors.cv_file }}</p>
                    </div>
                </div>

                <!-- General errors -->
                <p v-if="form.errors.job_id" class="text-red-500 text-sm mt-4 text-center">{{ form.errors.job_id }}</p>
            </div>

            <div class="p-8 border-t border-slate-200 bg-white rounded-b-2xl flex flex-col-reverse sm:flex-row justify-end gap-4">
                <BaseButton v-if="currentStep > 1" @click="prevStep" variant="secondary" type="button">
                    Kembali
                </BaseButton>
                <BaseButton v-else href="/#lowongan" variant="secondary" type="button">
                    Batal
                </BaseButton>

                <BaseButton v-if="currentStep < totalSteps" @click="nextStep" type="button">
                    <span>Lanjut</span>
                    <span class="material-symbols-outlined">arrow_forward</span>
                </BaseButton>
                <BaseButton v-else type="submit" :disabled="form.processing">
                    <span class="material-symbols-outlined">send</span>
                    <span>{{ form.processing ? 'Mengirim...' : 'Kirim Pendaftaran' }}</span>
                </BaseButton>
            </div>
        </form>
    </div>
</template>

<style scoped>
.animate-fade-in-up {
    animation: fadeInUp 0.5s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
