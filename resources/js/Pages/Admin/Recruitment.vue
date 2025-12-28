<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    Search, 
    Filter, 
    ChevronLeft, 
    ChevronRight, 
    Eye, 
    CheckCircle, 
    XCircle,
    User,
    Mail,
    Phone,
    MapPin,
    GraduationCap,
    Download
} from 'lucide-vue-next';

defineOptions({ layout: AuthenticatedLayout });

// -- Mock Data --
const applicants = ref([
    { id: 1, name: 'Budi Santoso', university: 'Politeknik Elektronika Negeri Surabaya', position: 'Frontend Developer', gpa: 3.85, status: 'Masuk', appliedDate: '2024-12-25', email: 'budi@pens.ac.id', phone: '08123456789', cv: 'budi_cv.pdf' },
    { id: 2, name: 'Siti Aminah', university: 'Universitas Indonesia', position: 'UI/UX Designer', gpa: 3.90, status: 'Menunggu', appliedDate: '2024-12-26', email: 'siti@ui.ac.id', phone: '08129876543', cv: 'siti_cv.pdf' },
    { id: 3, name: 'Rizky Pratama', university: 'Institut Teknologi Sepuluh Nopember', position: 'Backend Developer', gpa: 3.75, status: 'Ditolak', appliedDate: '2024-12-24', email: 'rizky@its.ac.id', phone: '08567890123', cv: 'rizky_cv.pdf' },
    { id: 4, name: 'Dewi Lestari', university: 'Universitas Brawijaya', position: 'Digital Marketing', gpa: 3.65, status: 'Masuk', appliedDate: '2024-12-20', email: 'dewi@ub.ac.id', phone: '08134567890', cv: 'dewi_cv.pdf' },
    { id: 5, name: 'Ahmad Faisal', university: 'Telkom University', position: 'Mobile Developer', gpa: 3.55, status: 'Menunggu', appliedDate: '2024-12-27', email: 'ahmad@telkom.ac.id', phone: '08765432109', cv: 'ahmad_cv.pdf' },
]);

// -- State --
const searchQuery = ref('');
const statusFilter = ref('Semua');
const selectedApplicant = ref(null);
const showDetailModal = ref(false);

// -- Computed --
const filteredApplicants = computed(() => {
    return applicants.value.filter(app => {
        const matchesSearch = app.name.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
                              app.university.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesStatus = statusFilter.value === 'Semua' || app.status === statusFilter.value;
        return matchesSearch && matchesStatus;
    });
});

// -- Methods --
const openDetail = (applicant) => {
    selectedApplicant.value = applicant;
    showDetailModal.value = true;
};

const closeDetail = () => {
    showDetailModal.value = false;
    setTimeout(() => selectedApplicant.value = null, 300);
};

const updateStatus = (status) => {
    if (selectedApplicant.value) {
        selectedApplicant.value.status = status;
        closeDetail();
    }
};

const getStatusColor = (status) => {
    switch (status) {
        case 'Masuk': return 'bg-success/10 text-success border-success/20';
        case 'Menunggu': return 'bg-warning/10 text-warning border-warning/20';
        case 'Ditolak': return 'bg-danger/10 text-danger border-danger/20';
        default: return 'bg-slate-100 text-slate-500 border-slate-200';
    }
};

const getInitials = (name) => {
    return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};
</script>

<template>
    <Head title="Rekrutmen" />

    <div class="flex flex-col gap-6">
        <!-- Header -->
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Rekrutmen Magang</h1>
            <p class="text-slate-500">Kelola dan seleksi pendaftar program magang SINTESIS.</p>
        </div>

        <!-- Filters & Actions -->
        <div class="flex flex-col sm:flex-row justify-between gap-4 bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <div class="relative w-full sm:w-72">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                    <input 
                        v-model="searchQuery" 
                        type="text" 
                        placeholder="Cari nama atau universitas..." 
                        class="w-full pl-10 pr-4 py-2 rounded-xl border-slate-200 focus:border-primary focus:ring-primary text-sm"
                    >
                </div>
            </div>
            <div class="flex items-center gap-2">
                <select v-model="statusFilter" class="px-4 py-2 rounded-xl border-slate-200 focus:border-primary focus:ring-primary text-sm font-medium text-slate-600 bg-slate-50 cursor-pointer">
                    <option value="Semua">Semua Status</option>
                    <option value="Menunggu">Menunggu</option>
                    <option value="Masuk">Diterima</option>
                    <option value="Ditolak">Ditolak</option>
                </select>
                <button class="flex items-center gap-2 px-4 py-2 bg-slate-900 text-white rounded-xl text-sm font-bold hover:bg-slate-800 transition-colors shadow-lg shadow-slate-200">
                    <Download class="w-4 h-4" />
                    <span>Export</span>
                </button>
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 font-bold text-slate-700">Nama Pendaftar</th>
                            <th class="px-6 py-4 font-bold text-slate-700">Universitas</th>
                            <th class="px-6 py-4 font-bold text-slate-700">Posisi</th>
                            <th class="px-6 py-4 font-bold text-slate-700 text-center">IPK</th>
                            <th class="px-6 py-4 font-bold text-slate-700 text-center">Status</th>
                            <th class="px-6 py-4 font-bold text-slate-700 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="applicant in filteredApplicants" :key="applicant.id" class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xs">
                                        {{ getInitials(applicant.name) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 group-hover:text-primary transition-colors">{{ applicant.name }}</div>
                                        <div class="text-xs text-slate-500">{{ applicant.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-600 space-y-1">
                                <div class="font-medium">{{ applicant.university }}</div>
                                <div class="text-xs text-slate-400">Teknik Informatika</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                    {{ applicant.position }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center font-mono font-bold text-slate-700">
                                {{ applicant.gpa.toFixed(2) }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-bold border" :class="getStatusColor(applicant.status)">
                                    {{ applicant.status === 'Masuk' ? 'Diterima' : applicant.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button @click="openDetail(applicant)" class="p-2 text-slate-400 hover:text-primary hover:bg-primary/5 rounded-lg transition-all">
                                    <Eye class="w-5 h-5" />
                                </button>
                            </td>
                        </tr>
                        <tr v-if="filteredApplicants.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                Tidak ada data pendaftar ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="border-t border-slate-200 px-6 py-4 flex items-center justify-between">
                <div class="text-xs text-slate-500">
                    Menampilkan <span class="font-bold text-slate-900">{{ filteredApplicants.length }}</span> dari <span class="font-bold text-slate-900">{{ applicants.length }}</span> data
                </div>
                <div class="flex gap-2">
                    <button class="p-2 border border-slate-200 rounded-lg hover:bg-slate-50 disabled:opacity-50" disabled>
                        <ChevronLeft class="w-4 h-4 text-slate-600" />
                    </button>
                    <button class="p-2 border border-slate-200 rounded-lg hover:bg-slate-50 disabled:opacity-50" disabled>
                        <ChevronRight class="w-4 h-4 text-slate-600" />
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <Teleport to="body">
        <div v-if="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="closeDetail"></div>
            <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
                <!-- Modal Header -->
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-bold text-lg text-slate-800">Detail Pendaftar</h3>
                    <button @click="closeDetail" class="p-1 rounded-full hover:bg-slate-200 transition-colors">
                        <XCircle class="w-6 h-6 text-slate-400" />
                    </button>
                </div>

                <div class="p-6 flex flex-col gap-6" v-if="selectedApplicant">
                    <!-- Profile Header -->
                    <div class="flex items-start gap-6">
                         <div class="w-20 h-20 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-2xl border-4 border-white shadow-sm">
                            {{ getInitials(selectedApplicant.name) }}
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h2 class="text-xl font-bold text-slate-900">{{ selectedApplicant.name }}</h2>
                                    <p class="text-slate-500 font-medium">{{ selectedApplicant.position }}</p>
                                </div>
                                 <span class="px-3 py-1 rounded-full text-xs font-bold border" :class="getStatusColor(selectedApplicant.status)">
                                    {{ selectedApplicant.status === 'Masuk' ? 'Diterima' : selectedApplicant.status }}
                                </span>
                            </div>
                            
                            <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                                <div class="flex items-center gap-2 text-slate-600">
                                    <GraduationCap class="w-4 h-4 text-slate-400" />
                                    <span>{{ selectedApplicant.university }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-slate-600">
                                    <MapPin class="w-4 h-4 text-slate-400" />
                                    <span>Surabaya, Indonesia</span>
                                </div>
                                <div class="flex items-center gap-2 text-slate-600">
                                    <Mail class="w-4 h-4 text-slate-400" />
                                    <span>{{ selectedApplicant.email }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-slate-600">
                                    <Phone class="w-4 h-4 text-slate-400" />
                                    <span>{{ selectedApplicant.phone }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-6">
                        <h4 class="font-bold text-slate-800 mb-4">Berkas Lamaran</h4>
                        <div class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 bg-slate-50 group hover:border-primary/50 transition-colors cursor-pointer">
                            <div class="p-3 bg-white rounded-lg border border-slate-200 shadow-sm text-danger">
                                <FileText class="w-6 h-6" />
                            </div>
                            <div class="flex-1">
                                <div class="font-bold text-slate-900 text-sm group-hover:text-primary transition-colors">Curriculum Vitae (CV)</div>
                                <div class="text-xs text-slate-500">PDF Document • 2.4 MB</div>
                            </div>
                            <Download class="w-5 h-5 text-slate-400 group-hover:text-primary" />
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-4 pt-4">
                        <button @click="updateStatus('Masuk')" class="flex-1 py-3 bg-success hover:bg-emerald-600 text-white rounded-xl font-bold shadow-lg shadow-success/20 transition-all active:scale-95 flex items-center justify-center gap-2">
                            <CheckCircle class="w-5 h-5" />
                            Terima Lamaran
                        </button>
                        <button @click="updateStatus('Ditolak')" class="flex-1 py-3 bg-white border border-danger text-danger hover:bg-red-50 rounded-xl font-bold transition-all active:scale-95 flex items-center justify-center gap-2">
                             <XCircle class="w-5 h-5" />
                            Tolak
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
