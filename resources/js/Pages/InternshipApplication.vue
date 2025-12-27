<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const currentStep = ref(1);
const totalSteps = 4;

const form = useForm({
    // Step 1: Identitas
    name: '',
    email: '',
    whatsapp: '',
    school: '',
    referral: '',
    
    // Step 2: Kompetensi
    skills: [],
    other_skills: '',
    databases: [],
    other_databases: '',
    os: [],
    other_os: '',
    
    // Step 3: Minat
    other_interest: '',
    demo_required: null,
    self_description: '',
    portfolio_link: '',
    
    // Step 4: Dokumen
    start_date: '',
    end_date: '',
    letter_file: null,
    id_card_file: null,
    cv_file: null,
});

const nextStep = () => {
    // Basic validation could go here
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
    form.post(route('internship.apply'), { // Endpoint to be created or mocked
        forceFormData: true,
    });
};

const handleFileChange = (e, field) => {
    form[field] = e.target.files[0];
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
</script>

<template>
    <div class="bg-background-light text-slate-900 min-h-screen flex flex-col font-display antialiased">
        <Head title="Formulir Pendaftaran Magang" />

        <!-- Header -->
        <header class="sticky top-0 z-50 bg-white border-b border-slate-200 shadow-sm">
            <div class="max-w-[1200px] mx-auto px-6 h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 flex items-center justify-center text-primary bg-blue-50 rounded-lg">
                        <span class="material-symbols-outlined text-2xl">hub</span>
                    </div>
                    <h1 class="text-xl font-bold tracking-tight text-slate-900">SINTESIS</h1>
                </div>
                <Link :href="route('logout')" method="post" as="button" class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-600 hover:text-primary hover:bg-blue-50 rounded-lg transition-colors duration-200">
                    <span class="material-symbols-outlined text-[20px]">logout</span>
                    <span>Keluar</span>
                </Link>
            </div>
        </header>

        <main class="flex-grow w-full max-w-[960px] mx-auto px-6 py-10">
            <div class="mb-6">
                <h2 class="text-3xl font-black text-slate-900 mb-2 tracking-tight">Formulir Pendaftaran Magang</h2>
                <p class="text-slate-500 text-lg">Silakan lengkapi data diri, kualifikasi teknis, dan dokumen administrasi Anda di bawah ini.</p>
            </div>

            <div class="mb-8 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm relative overflow-hidden flex items-center gap-6">
                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-primary"></div>
                <div class="hidden sm:flex w-14 h-14 rounded-full bg-blue-50 items-center justify-center text-primary shrink-0">
                    <span class="material-symbols-outlined text-[32px]">badge</span>
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-1">Posisi Lamaran Terpilih</p>
                    <h3 class="text-xl md:text-2xl font-bold text-slate-900 leading-tight">Mendaftar untuk Posisi: Frontend Developer - Departemen Teknologi</h3>
                </div>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden relative">
                
                <!-- Step 1: Identitas & Kontak -->
                <div v-show="currentStep === 1" class="p-8 border-b border-slate-100 animate-fade-in-up">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-primary font-bold text-sm">1</span>
                        <h3 class="text-xl font-bold text-slate-800">Identitas & Kontak</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Lengkap</label>
                            <input v-model="form.name" class="w-full h-12 px-4 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all placeholder:text-slate-400" placeholder="Masukkan nama lengkap sesuai KTP/KTM" type="text"/>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Alamat Email</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-3 text-slate-400 text-[20px]">mail</span>
                                <input v-model="form.email" class="w-full h-12 pl-10 pr-4 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all placeholder:text-slate-400" placeholder="contoh@email.com" type="email"/>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Nomor WhatsApp</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-3 text-slate-400 text-[20px]">call</span>
                                <input v-model="form.whatsapp" class="w-full h-12 pl-10 pr-4 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all placeholder:text-slate-400" placeholder="0812xxxxxxxx" type="tel"/>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Sekolah / Universitas</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-3 text-slate-400 text-[20px]">school</span>
                                <input v-model="form.school" class="w-full h-12 pl-10 pr-4 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all placeholder:text-slate-400" placeholder="Nama instansi pendidikan" type="text"/>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Staf Inosoft Pemberi Referensi <span class="text-slate-400 font-normal">(Opsional)</span></label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-3 text-slate-400 text-[20px]">person_pin</span>
                                <input v-model="form.referral" class="w-full h-12 pl-10 pr-4 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all placeholder:text-slate-400" placeholder="Nama staf jika ada" type="text"/>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Kompetensi Teknis -->
                <div v-show="currentStep === 2" class="p-8 border-b border-slate-100 bg-slate-50/50 animate-fade-in-up">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-primary font-bold text-sm">2</span>
                        <h3 class="text-xl font-bold text-slate-800">Kompetensi Teknis</h3>
                    </div>
                    <div class="space-y-8">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-3">Bahasa Pemrograman Dikuasai</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-3">
                                <label v-for="skill in ['JavaScript / TS', 'PHP', 'Java', 'Python', 'Go (Golang)', 'C# / .NET', 'Dart / Flutter', 'Kotlin / Swift']" :key="skill" class="flex items-center gap-2 p-3 bg-white border border-slate-200 rounded-lg cursor-pointer hover:border-primary/50 transition-colors">
                                    <input v-model="form.skills" :value="skill" class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary" type="checkbox"/>
                                    <span class="text-sm text-slate-700">{{ skill }}</span>
                                </label>
                            </div>
                            <input v-model="form.other_skills" class="w-full h-10 px-4 bg-white border border-slate-300 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm placeholder:text-slate-400" placeholder="Sebutkan bahasa pemrograman lain jika ada..." type="text"/>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-3">Database Dikuasai</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-3">
                                <label v-for="db in ['MySQL / MariaDB', 'PostgreSQL', 'SQL Server', 'MongoDB']" :key="db" class="flex items-center gap-2 p-3 bg-white border border-slate-200 rounded-lg cursor-pointer hover:border-primary/50 transition-colors">
                                    <input v-model="form.databases" :value="db" class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary" type="checkbox"/>
                                    <span class="text-sm text-slate-700">{{ db }}</span>
                                </label>
                            </div>
                            <input v-model="form.other_databases" class="w-full h-10 px-4 bg-white border border-slate-300 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm placeholder:text-slate-400" placeholder="Sebutkan database lain jika ada..." type="text"/>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-3">Sistem Operasi Dikuasai</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-3">
                                <label v-for="os in ['Windows', 'Linux (Ubuntu/etc)', 'macOS']" :key="os" class="flex items-center gap-2 p-3 bg-white border border-slate-200 rounded-lg cursor-pointer hover:border-primary/50 transition-colors">
                                    <input v-model="form.os" :value="os" class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary" type="checkbox"/>
                                    <span class="text-sm text-slate-700">{{ os }}</span>
                                </label>
                            </div>
                            <input v-model="form.other_os" class="w-full h-10 px-4 bg-white border border-slate-300 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm placeholder:text-slate-400" placeholder="Sebutkan OS lain jika ada..." type="text"/>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Minat & Profil -->
                <div v-show="currentStep === 3" class="p-8 border-b border-slate-100 animate-fade-in-up">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-primary font-bold text-sm">3</span>
                        <h3 class="text-xl font-bold text-slate-800">Minat & Profil</h3>
                    </div>
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Bidang lain yang ingin dikerjakan (selain peran utama) <span class="text-slate-400 font-normal">(Opsional)</span></label>
                            <input v-model="form.other_interest" class="w-full h-12 px-4 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all placeholder:text-slate-400" placeholder="Contoh: UI/UX Design, QA Testing, Project Management..." type="text"/>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-3">Apakah Anda wajib mendemonstrasikan hasil kerja magang ke Guru/Dosen?</label>
                            <div class="flex gap-6">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input v-model="form.demo_required" :value="true" class="w-4 h-4 text-primary border-slate-300 focus:ring-primary" type="radio"/>
                                    <span class="text-slate-700">Ya, Wajib</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input v-model="form.demo_required" :value="false" class="w-4 h-4 text-primary border-slate-300 focus:ring-primary" type="radio"/>
                                    <span class="text-slate-700">Tidak Wajib</span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsikan Diri Anda Secara Singkat</label>
                            <textarea v-model="form.self_description" class="w-full p-4 h-32 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary resize-none transition-all placeholder:text-slate-400" placeholder="Ceritakan tentang motivasi, kekuatan, dan apa yang ingin Anda capai selama magang..."></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Tautan Portofolio Keterampilan & Kemampuan</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-3 text-slate-400 text-[20px]">link</span>
                                <input v-model="form.portfolio_link" class="w-full h-12 pl-10 pr-4 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all placeholder:text-slate-400" placeholder="https://github.com/username atau https://linkedin.com/in/username" type="url"/>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Administrasi & Dokumen -->
                <div v-show="currentStep === 4" class="p-8 bg-slate-50/50 animate-fade-in-up">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-primary font-bold text-sm">4</span>
                        <h3 class="text-xl font-bold text-slate-800">Administrasi & Dokumen</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Rencana Tanggal Mulai</label>
                            <div class="relative">
                                <input v-model="form.start_date" class="w-full h-12 px-4 bg-white border border-slate-300 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-slate-700" type="date"/>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Rencana Tanggal Berakhir</label>
                            <div class="relative">
                                <input v-model="form.end_date" class="w-full h-12 px-4 bg-white border border-slate-300 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-slate-700" type="date"/>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="group relative">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Surat Izin Magang</label>
                            <div @click="triggerFileInput('letter_input')" class="border-2 border-dashed border-slate-300 rounded-xl p-6 flex flex-col items-center justify-center text-center bg-white hover:bg-blue-50 hover:border-primary/50 transition-all cursor-pointer h-40">
                                <span class="material-symbols-outlined text-4xl text-slate-400 group-hover:text-primary mb-2">upload_file</span>
                                <p class="text-xs text-slate-500 font-medium">{{ form.letter_file ? form.letter_file.name : 'Klik untuk unggah PDF' }}</p>
                                <p class="text-[10px] text-slate-400 mt-1">Maks. 5MB</p>
                                <input id="letter_input" accept=".pdf,.jpg,.jpeg,.png" class="hidden" type="file" @change="(e) => handleFileChange(e, 'letter_file')"/>
                            </div>
                        </div>
                        <div class="group relative">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Kartu Pelajar / KTM</label>
                            <div @click="triggerFileInput('id_card_input')" class="border-2 border-dashed border-slate-300 rounded-xl p-6 flex flex-col items-center justify-center text-center bg-white hover:bg-blue-50 hover:border-primary/50 transition-all cursor-pointer h-40">
                                <span class="material-symbols-outlined text-4xl text-slate-400 group-hover:text-primary mb-2">id_card</span>
                                <p class="text-xs text-slate-500 font-medium">{{ form.id_card_file ? form.id_card_file.name : 'Klik untuk unggah Scan' }}</p>
                                <p class="text-[10px] text-slate-400 mt-1">PDF/JPG/PNG</p>
                                <input id="id_card_input" accept=".pdf,.jpg,.jpeg,.png" class="hidden" type="file" @change="(e) => handleFileChange(e, 'id_card_file')"/>
                            </div>
                        </div>
                        <div class="group relative">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">CV / Resume Terbaru</label>
                            <div @click="triggerFileInput('cv_input')" class="border-2 border-dashed border-slate-300 rounded-xl p-6 flex flex-col items-center justify-center text-center bg-white hover:bg-blue-50 hover:border-primary/50 transition-all cursor-pointer h-40">
                                <span class="material-symbols-outlined text-4xl text-slate-400 group-hover:text-primary mb-2">description</span>
                                <p class="text-xs text-slate-500 font-medium">{{ form.cv_file ? form.cv_file.name : 'Klik untuk unggah CV' }}</p>
                                <p class="text-[10px] text-slate-400 mt-1">PDF Only (Maks. 5MB)</p>
                                <input id="cv_input" accept=".pdf" class="hidden" type="file" @change="(e) => handleFileChange(e, 'cv_file')"/>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="p-8 border-t border-slate-200 bg-white flex flex-col-reverse sm:flex-row justify-end gap-4">
                    <button v-if="currentStep > 1" @click="prevStep" class="w-full sm:w-auto px-6 py-3 rounded-lg text-slate-600 font-bold hover:bg-slate-100 transition-colors" type="button">
                        Kembali
                    </button>
                    <button v-else class="w-full sm:w-auto px-6 py-3 rounded-lg text-slate-600 font-bold hover:bg-slate-100 transition-colors" type="button">
                        Simpan Draft
                    </button>

                    <button v-if="currentStep < totalSteps" @click="nextStep" class="w-full sm:w-auto px-8 py-3 rounded-lg bg-primary text-white font-bold hover:bg-blue-700 shadow-md shadow-blue-500/20 hover:shadow-lg transition-all flex items-center justify-center gap-2" type="button">
                        <span>Lanjut</span>
                        <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                    </button>
                    <button v-else type="submit" class="w-full sm:w-auto px-8 py-3 rounded-lg bg-primary text-white font-bold hover:bg-blue-700 shadow-md shadow-blue-500/20 hover:shadow-lg transition-all flex items-center justify-center gap-2" :disabled="form.processing">
                        <span class="material-symbols-outlined text-[20px]">send</span>
                        <span>{{ form.processing ? 'Mengirim...' : 'Kirim Pendaftaran' }}</span>
                    </button>
                </div>
            </form>

            <p class="text-center text-slate-400 text-sm mt-8 pb-8">
                © 2024 Inosoft Trans Sistem. Semua Hak Dilindungi. <br/>
                SINTESIS Internal Platform v2.0
            </p>
        </main>
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
