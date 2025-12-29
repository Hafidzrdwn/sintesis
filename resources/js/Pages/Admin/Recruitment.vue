<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import BaseButton from '@/Components/BaseButton.vue';
import { getInitials, formatDate, getDocumentUrl, getAvatarUrl } from '@/utils/helpers';
import { 
    Search, 
    ChevronLeft, 
    ChevronRight, 
    Eye, 
    CheckCircle, 
    XCircle,
    Clock,
    Users,
    UserCheck,
    UserX,
    Mail,
    Phone,
    GraduationCap,
    Calendar,
    Download,
    FileText,
    ExternalLink,
    X
} from 'lucide-vue-next';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    applicants: Object,
    stats: Object,
    jobs: Array,
    mentors: Array,
    filters: Object,
});

const page = usePage();

// State
const searchQuery = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || 'all');
const jobFilter = ref(props.filters?.job_id || '');
const selectedApplicant = ref(null);
const showDetailModal = ref(false);
const isLoading = ref(false);

const showAcceptModal = ref(false);
const selectedMentor = ref('');
const acceptNotes = ref('');

let searchTimeout = null;
watch(searchQuery, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
});

watch([statusFilter, jobFilter], () => {
    applyFilters();
});

const applyFilters = () => {
    isLoading.value = true;
    router.get(route('admin.recruitment'), {
        search: searchQuery.value || undefined,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
        job_id: jobFilter.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => isLoading.value = false,
    });
};

const openDetail = (applicant) => {
    selectedApplicant.value = applicant;
    showDetailModal.value = true;
};

const closeDetail = () => {
    showDetailModal.value = false;
    setTimeout(() => selectedApplicant.value = null, 300);
};

const openAcceptModal = () => {
    showAcceptModal.value = true;
};

const closeAcceptModal = () => {
    showAcceptModal.value = false;
    selectedMentor.value = '';
    acceptNotes.value = '';
};

const updateStatus = (status) => {
    if (!selectedApplicant.value) return;

    if (status === 'accepted') {
        openAcceptModal();
        return;
    }

    router.patch(route('admin.recruitment.update-status', selectedApplicant.value.id), {
        status: status,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            closeDetail();
        },
    });
};

const confirmAccept = () => {
    if (!selectedApplicant.value) return;

    router.patch(route('admin.recruitment.update-status', selectedApplicant.value.id), {
        status: 'accepted',
        mentor_id: selectedMentor.value || null,
        notes: acceptNotes.value || null,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            closeAcceptModal();
            closeDetail();
        },
    });
};

const getStatusConfig = (status) => {
    const configs = {
        pending: { label: 'Menunggu', color: 'bg-warning/10 text-warning border-warning/20', icon: Clock },
        reviewed: { label: 'Ditinjau', color: 'bg-blue-100 text-blue-600 border-blue-200', icon: Eye },
        interview: { label: 'Interview', color: 'bg-purple-100 text-purple-600 border-purple-200', icon: Users },
        accepted: { label: 'Diterima', color: 'bg-success/10 text-success border-success/20', icon: UserCheck },
        rejected: { label: 'Ditolak', color: 'bg-danger/10 text-danger border-danger/20', icon: UserX },
    };
    return configs[status] || configs.pending;
};

const getUserAvatar = (applicant) => {
    return applicant.user?.avatar ? getAvatarUrl(applicant.user) : null;
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

        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-slate-100 rounded-lg">
                        <Users class="w-5 h-5 text-slate-600" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-slate-900">{{ stats.total }}</p>
                        <p class="text-xs text-slate-500">Total</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-warning/10 rounded-lg">
                        <Clock class="w-5 h-5 text-warning" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-slate-900">{{ stats.pending }}</p>
                        <p class="text-xs text-slate-500">Menunggu</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <Eye class="w-5 h-5 text-blue-600" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-slate-900">{{ stats.reviewed }}</p>
                        <p class="text-xs text-slate-500">Ditinjau</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <Users class="w-5 h-5 text-purple-600" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-slate-900">{{ stats.interview }}</p>
                        <p class="text-xs text-slate-500">Interview</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-success/10 rounded-lg">
                        <UserCheck class="w-5 h-5 text-success" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-slate-900">{{ stats.accepted }}</p>
                        <p class="text-xs text-slate-500">Diterima</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-danger/10 rounded-lg">
                        <UserX class="w-5 h-5 text-danger" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-slate-900">{{ stats.rejected }}</p>
                        <p class="text-xs text-slate-500">Ditolak</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="flex flex-col sm:flex-row justify-between gap-4 bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <div class="relative w-full sm:w-72">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                    <input 
                        v-model="searchQuery" 
                        type="text" 
                        placeholder="Cari nama, email, universitas..." 
                        class="w-full pl-10 pr-4 py-2 rounded-xl border-slate-200 focus:border-primary focus:ring-primary text-sm"
                    >
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <select v-model="statusFilter" class="px-4 py-2 rounded-xl border-slate-200 focus:border-primary focus:ring-primary text-sm font-medium text-slate-600 bg-slate-50 cursor-pointer">
                    <option value="all">Semua Status</option>
                    <option value="pending">Menunggu</option>
                    <option value="reviewed">Ditinjau</option>
                    <option value="interview">Interview</option>
                    <option value="accepted">Diterima</option>
                    <option value="rejected">Ditolak</option>
                </select>
                <select v-model="jobFilter" class="px-4 py-2 rounded-xl border-slate-200 focus:border-primary focus:ring-primary text-sm font-medium text-slate-600 bg-slate-50 cursor-pointer">
                    <option value="">Semua Posisi</option>
                    <option v-for="job in jobs" :key="job.id" :value="job.id">{{ job.title }}</option>
                </select>
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
                            <th class="px-6 py-4 font-bold text-slate-700 text-center">Tanggal Daftar</th>
                            <th class="px-6 py-4 font-bold text-slate-700 text-center">Nama Referensi Staff Inosoft</th>
                            <th class="px-6 py-4 font-bold text-slate-700 text-center">Status</th>
                            <th class="px-6 py-4 font-bold text-slate-700 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="applicant in applicants.data" :key="applicant.id" class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex flex-col items-start justify-center gap-3">
                                    <img v-if="getUserAvatar(applicant)" 
                                        :src="getUserAvatar(applicant)" 
                                        :alt="applicant.full_name"
                                        class="w-10 h-10 rounded-full object-cover ring-2 ring-white shadow-sm" />
                                    <div v-else class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xs">
                                        {{ getInitials(applicant.full_name) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 group-hover:text-primary transition-colors">{{ applicant.full_name }}</div>
                                        <div class="text-xs text-slate-500">{{ applicant.user?.email || '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                <div class="font-medium">{{ applicant.university || '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                    {{ applicant.job?.title || applicant.position_applied || '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center text-slate-600">
                                {{ formatDate(applicant.created_at) }}
                            </td>
                            <td class="px-6 py-4 text-center text-slate-600 text-xs">
                                {{ applicant.referral || '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-bold border" :class="getStatusConfig(applicant.status).color">
                                    {{ getStatusConfig(applicant.status).label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button @click="openDetail(applicant)" class="p-2 text-slate-400 hover:text-primary hover:bg-primary/5 rounded-lg transition-all cursor-pointer">
                                    <Eye class="w-5 h-5" />
                                </button>
                            </td>
                        </tr>
                        <tr v-if="applicants.data.length === 0">
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
                    Menampilkan <span class="font-bold text-slate-900">{{ applicants.from || 0 }}</span> - <span class="font-bold text-slate-900">{{ applicants.to || 0 }}</span> dari <span class="font-bold text-slate-900">{{ applicants.total }}</span> data
                </div>
                <div class="flex gap-2">
                    <button 
                        @click="router.get(applicants.prev_page_url)" 
                        :disabled="!applicants.prev_page_url"
                        class="p-2 border border-slate-200 rounded-lg hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <ChevronLeft class="w-4 h-4 text-slate-600" />
                    </button>
                    <button 
                        @click="router.get(applicants.next_page_url)" 
                        :disabled="!applicants.next_page_url"
                        class="p-2 border border-slate-200 rounded-lg hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <ChevronRight class="w-4 h-4 text-slate-600" />
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="showDetailModal && selectedApplicant" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="closeDetail"></div>
                <div class="relative w-full max-w-3xl max-h-[90vh] bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col">
                    <!-- Modal Header -->
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex items-center justify-between flex-shrink-0">
                        <h3 class="font-bold text-lg text-slate-800">Detail Pendaftar</h3>
                        <button @click="closeDetail" class="p-1 cursor-pointer rounded-full hover:bg-slate-200 transition-colors">
                            <X class="w-6 h-6 text-slate-400" />
                        </button>
                    </div>

                    <div class="p-6 flex flex-col gap-6 overflow-y-auto">
                        <!-- Profile Header -->
                        <div class="flex items-start gap-6">
                            <img v-if="getUserAvatar(selectedApplicant)" 
                                :src="getUserAvatar(selectedApplicant)" 
                                :alt="selectedApplicant.full_name"
                                class="w-20 h-20 rounded-full object-cover border-4 border-white shadow-sm flex-shrink-0" />
                            <div v-else class="w-20 h-20 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-2xl border-4 border-white shadow-sm flex-shrink-0">
                                {{ getInitials(selectedApplicant.full_name) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start gap-4">
                                    <div>
                                        <h2 class="text-xl font-bold text-slate-900">{{ selectedApplicant.full_name }}</h2>
                                        <p class="text-slate-500 font-medium">{{ selectedApplicant.job?.title || selectedApplicant.position_applied || '-' }}</p>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-xs font-bold border flex-shrink-0" :class="getStatusConfig(selectedApplicant.status).color">
                                        {{ getStatusConfig(selectedApplicant.status).label }}
                                    </span>
                                </div>
                                
                                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                    <div class="flex items-center gap-2 text-slate-600">
                                        <GraduationCap class="w-4 h-4 text-slate-400 flex-shrink-0" />
                                        <span>{{ selectedApplicant.university || '-' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-slate-600">
                                        <Mail class="w-4 h-4 text-slate-400 flex-shrink-0" />
                                        <span class="truncate">{{ selectedApplicant.user?.email || '-' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-slate-600">
                                        <Phone class="w-4 h-4 text-slate-400 flex-shrink-0" />
                                        <span>{{ selectedApplicant.phone || '-' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-slate-600">
                                        <Calendar class="w-4 h-4 text-slate-400 flex-shrink-0" />
                                        <span>{{ formatDate(selectedApplicant.start_date) }} - {{ formatDate(selectedApplicant.end_date) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Skills -->
                        <div v-if="selectedApplicant.skills?.length" class="border-t border-slate-100 pt-4">
                            <h4 class="font-bold text-slate-800 mb-3">Keahlian</h4>
                            <div class="flex flex-wrap gap-2">
                                <span v-for="skill in selectedApplicant.skills" :key="skill" class="px-3 py-1 bg-primary/10 text-primary rounded-full text-xs font-medium">
                                    {{ skill }}
                                </span>
                                <span v-if="selectedApplicant.other_skills" class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-medium">
                                    {{ selectedApplicant.other_skills }}
                                </span>
                            </div>
                        </div>

                        <!-- Self Description -->
                        <div v-if="selectedApplicant.self_description" class="border-t border-slate-100 pt-4">
                            <h4 class="font-bold text-slate-800 mb-3">Deskripsi Diri</h4>
                            <p class="text-slate-600 text-sm leading-relaxed">{{ selectedApplicant.self_description }}</p>
                        </div>

                        <!-- Documents -->
                        <div class="border-t border-slate-100 pt-4">
                            <h4 class="font-bold text-slate-800 mb-4">Berkas Lamaran</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <a v-if="selectedApplicant.cv_path" :href="getDocumentUrl(selectedApplicant.cv_path)" target="_blank" class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50 group hover:border-primary/50 transition-colors">
                                    <div class="p-2 bg-white rounded-lg border border-slate-200 text-danger">
                                        <FileText class="w-5 h-5" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium text-slate-900 text-sm group-hover:text-primary">CV</div>
                                        <div class="text-xs text-slate-500">PDF</div>
                                    </div>
                                    <Download class="w-4 h-4 text-slate-400 group-hover:text-primary" />
                                </a>
                                <a v-if="selectedApplicant.letter_path" :href="getDocumentUrl(selectedApplicant.letter_path)" target="_blank" class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50 group hover:border-primary/50 transition-colors">
                                    <div class="p-2 bg-white rounded-lg border border-slate-200 text-blue-600">
                                        <FileText class="w-5 h-5" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium text-slate-900 text-sm group-hover:text-primary">Surat Pengantar</div>
                                        <div class="text-xs text-slate-500">PDF</div>
                                    </div>
                                    <Download class="w-4 h-4 text-slate-400 group-hover:text-primary" />
                                </a>
                                <a v-if="selectedApplicant.id_card_path" :href="getDocumentUrl(selectedApplicant.id_card_path)" target="_blank" class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50 group hover:border-primary/50 transition-colors">
                                    <div class="p-2 bg-white rounded-lg border border-slate-200 text-emerald-600">
                                        <FileText class="w-5 h-5" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium text-slate-900 text-sm group-hover:text-primary">KTM/Identitas</div>
                                        <div class="text-xs text-slate-500">Image/PDF</div>
                                    </div>
                                    <Download class="w-4 h-4 text-slate-400 group-hover:text-primary" />
                                </a>
                                <a v-if="selectedApplicant.portfolio_url" :href="selectedApplicant.portfolio_url" target="_blank" class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50 group hover:border-primary/50 transition-colors">
                                    <div class="p-2 bg-white rounded-lg border border-slate-200 text-purple-600">
                                        <ExternalLink class="w-5 h-5" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium text-slate-900 text-sm group-hover:text-primary">Portfolio</div>
                                        <div class="text-xs text-slate-500 truncate">{{ selectedApplicant.portfolio_url }}</div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div v-if="selectedApplicant.status !== 'accepted' && selectedApplicant.status !== 'rejected'" class="flex gap-4 pt-4 border-t border-slate-100">
                            <button @click="updateStatus('accepted')" class="flex-1 py-3 bg-success hover:bg-emerald-600 text-white rounded-xl font-bold shadow-lg shadow-success/20 transition-all active:scale-95 flex items-center justify-center gap-2 cursor-pointer">
                                <CheckCircle class="w-5 h-5" />
                                Terima
                            </button>
                            <button @click="updateStatus('interview')" class="flex-1 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-bold shadow-lg shadow-purple-200 transition-all active:scale-95 flex items-center justify-center gap-2 cursor-pointer">
                                <Users class="w-5 h-5" />
                                Interview
                            </button>
                            <button @click="updateStatus('rejected')" class="flex-1 py-3 bg-white border border-danger text-danger hover:bg-red-50 rounded-xl font-bold transition-all active:scale-95 flex items-center justify-center gap-2 cursor-pointer">
                                <XCircle class="w-5 h-5" />
                                Tolak
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>

    <!-- Accept Modal with Mentor Selection -->
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="showAcceptModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="closeAcceptModal"></div>
                <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
                    <div class="bg-success/10 px-6 py-4 border-b border-success/20 flex items-center justify-between">
                        <h3 class="font-bold text-lg text-success">Terima Lamaran</h3>
                        <button @click="closeAcceptModal" class="p-1 rounded-full hover:bg-success/20 transition-colors">
                            <X class="w-6 h-6 text-success" />
                        </button>
                    </div>
                    <div class="p-6 flex flex-col gap-4">
                        <p class="text-slate-600 text-sm">Pilih mentor untuk pendaftar ini (opsional):</p>
                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Mentor Pembimbing</label>
                            <select v-model="selectedMentor" class="w-full px-4 py-2 rounded-xl border-slate-200 focus:border-primary focus:ring-primary text-sm">
                                <option value="">-- Pilih Mentor (Opsional) --</option>
                                <option v-for="mentor in mentors" :key="mentor.id" :value="mentor.id">{{ mentor.name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Catatan</label>
                            <textarea v-model="acceptNotes" rows="3" class="w-full px-4 py-2 rounded-xl border-slate-200 focus:border-primary focus:ring-primary text-sm resize-none" placeholder="Catatan untuk pendaftar..."></textarea>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <BaseButton variant="secondary" full @click="closeAcceptModal">Batal</BaseButton>
                            <BaseButton variant="primary" full @click="confirmAccept" class="bg-success hover:bg-emerald-600">
                                <CheckCircle class="w-4 h-4" />
                                Konfirmasi
                            </BaseButton>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
</style>
