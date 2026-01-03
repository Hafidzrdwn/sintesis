<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import BaseButton from '@/Components/BaseButton.vue';
import Toast from '@/Components/Toast.vue';
import { getInitials, getAvatarUrl, formatDate } from '@/utils/helpers';
import {
  Search,
  ChevronLeft,
  ChevronRight,
  Eye,
  X,
  Users,
  CheckCircle,
  Clock,
  XCircle,
  RefreshCw,
  UserCheck,
  Calendar,
  Briefcase,
  FileText,
} from 'lucide-vue-next';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
  internships: Object,
  stats: Object,
  statusOptions: Array,
  mentors: Array,
  filters: Object,
});

const searchQuery = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || 'all');
const mentorFilter = ref(props.filters?.mentor_id || '');
const periodStart = ref(props.filters?.period_start || '');
const periodEnd = ref(props.filters?.period_end || '');
const selectedInternship = ref(null);
const showDetailModal = ref(false);
const showActionModal = ref(false);
const actionType = ref('');
const isLoading = ref(false);

const actionForm = ref({
  status: '',
  end_date: '',
  notes: '',
  mentor_id: '',
});

const page = usePage();
const errors = computed(() => page.props.errors || {});

let searchTimeout = null;
watch(searchQuery, (value) => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    applyFilters();
  }, 300);
});

watch([statusFilter, mentorFilter, periodStart, periodEnd], () => {
  applyFilters();
});

const applyFilters = () => {
  isLoading.value = true;
  router.get(route('admin.internships'), {
    search: searchQuery.value || undefined,
    status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    mentor_id: mentorFilter.value || undefined,
    period_start: periodStart.value || undefined,
    period_end: periodEnd.value || undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
    onFinish: () => isLoading.value = false,
  });
};

const getStatusConfig = (status) => {
  const configs = {
    active: { label: 'Aktif', color: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
    extended: { label: 'Diperpanjang', color: 'bg-blue-50 text-blue-700 border-blue-200' },
    completed: { label: 'Selesai', color: 'bg-slate-100 text-slate-700 border-slate-200' },
    terminated: { label: 'Diberhentikan', color: 'bg-red-50 text-red-700 border-red-200' },
  };
  return configs[status] || { label: status, color: 'bg-slate-100 text-slate-700 border-slate-200' };
};

const openDetail = (internship) => {
  selectedInternship.value = internship;
  showDetailModal.value = true;
};

const closeDetail = () => {
  showDetailModal.value = false;
  setTimeout(() => selectedInternship.value = null, 300);
};

const openAction = (internship, type) => {
  selectedInternship.value = internship;
  actionType.value = type;
  const typeMapping = {
    complete: 'completed',
    extend: 'extended',
    terminate: 'terminated',
  };
  actionForm.value = {
    status: type === 'change_mentor' ? '' : typeMapping[type],
    end_date: '',
    notes: '',
    mentor_id: internship.mentor_id || '',
  };
  showActionModal.value = true;
};

const closeAction = () => {
  showActionModal.value = false;
  actionType.value = '';
  actionForm.value = { status: '', end_date: '', notes: '', mentor_id: '' };
};

const submitAction = () => {
  if (!selectedInternship.value) return;

  if (actionType.value === 'change_mentor') {
    router.patch(route('admin.internships.update-mentor', selectedInternship.value.id), {
      mentor_id: actionForm.value.mentor_id,
    }, {
      preserveScroll: true,
      onSuccess: () => closeAction(),
    });
  } else {
    router.patch(route('admin.internships.update-status', selectedInternship.value.id), {
      status: actionForm.value.status,
      end_date: actionForm.value.end_date || undefined,
      notes: actionForm.value.notes || undefined,
    }, {
      preserveScroll: true,
      onSuccess: () => closeAction(),
    });
  }
};

const getPosition = (internship) => {
  return internship.custom_position || internship.job?.title || '-';
};

const actionLabels = {
  complete: { title: 'Selesaikan Internship', icon: CheckCircle, color: 'text-emerald-600' },
  extend: { title: 'Perpanjang Internship', icon: RefreshCw, color: 'text-blue-600' },
  terminate: { title: 'Hentikan Internship', icon: XCircle, color: 'text-red-600' },
  change_mentor: { title: 'Ubah Mentor', icon: UserCheck, color: 'text-primary' },
};
</script>

<template>
  <Toast />

  <Head title="Data Peserta Magang" />

  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Kelola Data Peserta Magang</h1>
        <p class="text-slate-500 mt-1">Kelola status dan lifecycle peserta magang</p>
      </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
      <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center">
            <Users class="w-6 h-6 text-slate-600" />
          </div>
          <div>
            <p class="text-2xl font-bold text-slate-900">{{ stats.total }}</p>
            <p class="text-xs text-slate-500 font-medium">Total</p>
          </div>
        </div>
      </div>
      <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center">
            <CheckCircle class="w-6 h-6 text-emerald-600" />
          </div>
          <div>
            <p class="text-2xl font-bold text-emerald-600">{{ stats.active }}</p>
            <p class="text-xs text-slate-500 font-medium">Aktif</p>
          </div>
        </div>
      </div>
      <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center">
            <RefreshCw class="w-6 h-6 text-blue-600" />
          </div>
          <div>
            <p class="text-2xl font-bold text-blue-600">{{ stats.extended }}</p>
            <p class="text-xs text-slate-500 font-medium">Diperpanjang</p>
          </div>
        </div>
      </div>
      <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center">
            <Clock class="w-6 h-6 text-slate-600" />
          </div>
          <div>
            <p class="text-2xl font-bold text-slate-600">{{ stats.completed }}</p>
            <p class="text-xs text-slate-500 font-medium">Selesai</p>
          </div>
        </div>
      </div>
      <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center">
            <XCircle class="w-6 h-6 text-red-600" />
          </div>
          <div>
            <p class="text-2xl font-bold text-red-600">{{ stats.terminated }}</p>
            <p class="text-xs text-slate-500 font-medium">Diberhentikan</p>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
      <div class="flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[200px]">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
          <input v-model="searchQuery" type="text"
            class="w-full pl-10 pr-4 py-2 rounded-xl border-slate-200 focus:border-primary focus:ring-primary text-sm"
            placeholder="Cari nama/email intern..." />
        </div>
        <select v-model="statusFilter"
          class="px-4 py-2 rounded-xl border-slate-200 focus:border-primary focus:ring-primary text-sm font-medium text-slate-600 bg-slate-50 cursor-pointer">
          <option value="all">Semua Status</option>
          <option v-for="status in statusOptions" :key="status.value" :value="status.value">
            {{ status.label }}
          </option>
        </select>
        <select v-model="mentorFilter"
          class="px-4 py-2 rounded-xl border-slate-200 focus:border-primary focus:ring-primary text-sm font-medium text-slate-600 bg-slate-50 cursor-pointer">
          <option value="">Semua Mentor</option>
          <option v-for="mentor in mentors" :key="mentor.id" :value="mentor.id">
            {{ mentor.name }}
          </option>
        </select>
        <div class="flex items-center gap-2">
          <Calendar class="w-4 h-4 text-slate-400" />
          <input v-model="periodStart" type="date"
            class="px-3 py-2 rounded-xl border-slate-200 focus:border-primary focus:ring-primary text-sm text-slate-600 bg-slate-50"
            title="Periode Mulai" />
          <span class="text-slate-400">-</span>
          <input v-model="periodEnd" type="date"
            class="px-3 py-2 rounded-xl border-slate-200 focus:border-primary focus:ring-primary text-sm text-slate-600 bg-slate-50"
            title="Periode Selesai" />
        </div>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-slate-50/80 border-b border-slate-100">
            <tr>
              <th class="px-6 py-4 text-left font-bold text-slate-700">Intern</th>
              <th class="px-6 py-4 text-left font-bold text-slate-700">Posisi</th>
              <th class="px-6 py-4 text-left font-bold text-slate-700">Mentor</th>
              <th class="px-6 py-4 text-left font-bold text-slate-700">Periode</th>
              <th class="px-6 py-4 font-bold text-slate-700">Status</th>
              <th class="px-6 py-4 font-bold text-slate-700">Catatan</th>
              <th class="px-6 py-4 text-left font-bold text-slate-700">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100" v-if="!isLoading">
            <tr v-for="internship in internships.data" :key="internship.id"
              class="hover:bg-slate-50/50 transition-colors">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full overflow-hidden bg-primary/10 flex items-center justify-center">
                    <img v-if="getAvatarUrl(internship.intern)" :src="getAvatarUrl(internship.intern)"
                      :alt="internship.intern.name" class="w-full h-full object-cover object-top" />
                    <span v-else class="text-sm font-bold text-primary">
                      {{ getInitials(internship.intern?.name || '') }}
                    </span>
                  </div>
                  <div>
                    <p class="font-semibold text-slate-900">{{ internship.intern?.name }}</p>
                    <p class="text-xs text-slate-500">{{ internship.intern?.email }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                  {{ getPosition(internship) }}
                </span>
              </td>
              <td class="px-6 py-4 text-slate-600">
                {{ internship.mentor?.name || '-' }}
              </td>
              <td class="px-6 py-4 text-slate-600 text-sm whitespace-nowrap">
                {{ formatDate(internship.start_date) }} - {{ formatDate(internship.end_date) }}
              </td>
              <td class="px-6 py-4 text-center">
                <span class="px-3 py-1 rounded-full text-xs font-bold border"
                  :class="getStatusConfig(internship.status).color">
                  {{ getStatusConfig(internship.status).label }}
                </span>
              </td>
              <td class="px-6 py-4 text-slate-600 text-sm">
                {{ internship.notes || '-' }}
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center justify-start gap-1">
                  <button @click="openDetail(internship)"
                    class="p-2 text-slate-400 hover:text-primary hover:bg-primary/5 rounded-lg transition-all cursor-pointer"
                    title="Lihat Detail">
                    <Eye class="w-5 h-5" />
                  </button>
                  <button v-if="['active', 'extended'].includes(internship.status)"
                    @click="openAction(internship, 'complete')"
                    class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-all cursor-pointer"
                    title="Selesaikan">
                    <CheckCircle class="w-5 h-5" />
                  </button>
                  <button v-if="['active', 'extended', 'completed'].includes(internship.status)"
                    @click="openAction(internship, 'extend')"
                    class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all cursor-pointer"
                    title="Perpanjang">
                    <RefreshCw class="w-5 h-5" />
                  </button>
                  <button v-if="['active', 'extended'].includes(internship.status)"
                    @click="openAction(internship, 'terminate')"
                    class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all cursor-pointer"
                    title="Hentikan">
                    <XCircle class="w-5 h-5" />
                  </button>
                  <button v-if="['active', 'extended'].includes(internship.status)"
                    @click="openAction(internship, 'change_mentor')"
                    class="p-2 text-slate-400 hover:text-primary hover:bg-primary/5 rounded-lg transition-all cursor-pointer"
                    title="Ubah Mentor">
                    <UserCheck class="w-5 h-5" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="internships.data.length === 0">
              <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                Tidak ada data internship ditemukan.
              </td>
            </tr>
          </tbody>
          <tbody v-else class="divide-y divide-slate-100">
            <tr>
              <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                Loading data...
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="internships.last_page > 1"
        class="flex items-center justify-between px-6 py-4 border-t border-slate-100">
        <p class="text-sm text-slate-500">
          Menampilkan {{ internships.from }} - {{ internships.to }} dari {{ internships.total }} data
        </p>
        <div class="flex items-center gap-2">
          <button @click="router.get(internships.prev_page_url)" :disabled="!internships.prev_page_url"
            class="p-2 rounded-lg border border-slate-200 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
            <ChevronLeft class="w-5 h-5" />
          </button>
          <span class="px-3 py-1 text-sm font-medium text-slate-600">
            {{ internships.current_page }} / {{ internships.last_page }}
          </span>
          <button @click="router.get(internships.next_page_url)" :disabled="!internships.next_page_url"
            class="p-2 rounded-lg border border-slate-200 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
            <ChevronRight class="w-5 h-5" />
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Detail Modal -->
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="showDetailModal && selectedInternship" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="closeDetail"></div>
        <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden">
          <div class="bg-gradient-to-r from-primary to-primary/80 px-6 py-4 flex items-center justify-between">
            <h3 class="font-bold text-lg text-white">Detail Internship</h3>
            <button @click="closeDetail" class="text-white/80 hover:text-white transition-colors cursor-pointer">
              <X class="w-6 h-6" />
            </button>
          </div>
          <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto">
            <div class="flex items-center gap-4 pb-4 border-b border-slate-100">
              <div class="w-16 h-16 rounded-full overflow-hidden bg-primary/10 flex items-center justify-center">
                <img v-if="getAvatarUrl(selectedInternship.intern)" :src="getAvatarUrl(selectedInternship.intern)"
                  :alt="selectedInternship.intern.name" class="w-full h-full object-cover object-top" />
                <span v-else class="text-xl font-bold text-primary">
                  {{ getInitials(selectedInternship.intern?.name || '') }}
                </span>
              </div>
              <div>
                <h4 class="font-bold text-lg text-slate-900">{{ selectedInternship.intern?.name }}</h4>
                <p class="text-slate-500">{{ selectedInternship.intern?.email }}</p>
                <span class="inline-block mt-1 px-3 py-1 rounded-full text-xs font-bold border"
                  :class="getStatusConfig(selectedInternship.status).color">
                  {{ getStatusConfig(selectedInternship.status).label }}
                </span>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="flex items-start gap-3">
                <Briefcase class="w-5 h-5 text-slate-400 mt-0.5" />
                <div>
                  <p class="text-xs text-slate-500 font-medium">Posisi</p>
                  <p class="text-slate-900 font-semibold">{{ getPosition(selectedInternship) }}</p>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <UserCheck class="w-5 h-5 text-slate-400 mt-0.5" />
                <div>
                  <p class="text-xs text-slate-500 font-medium">Mentor</p>
                  <p class="text-slate-900 font-semibold">{{ selectedInternship.mentor?.name || '-' }}</p>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <Calendar class="w-5 h-5 text-slate-400 mt-0.5" />
                <div>
                  <p class="text-xs text-slate-500 font-medium">Tanggal Mulai</p>
                  <p class="text-slate-900 font-semibold">{{ formatDate(selectedInternship.start_date) }}</p>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <Calendar class="w-5 h-5 text-slate-400 mt-0.5" />
                <div>
                  <p class="text-xs text-slate-500 font-medium">Tanggal Selesai</p>
                  <p class="text-slate-900 font-semibold">{{ formatDate(selectedInternship.end_date) }}</p>
                </div>
              </div>
            </div>

            <div v-if="selectedInternship.notes" class="pt-4 border-t border-slate-100">
              <div class="flex items-start gap-3">
                <FileText class="w-5 h-5 text-slate-400 mt-0.5" />
                <div class="flex-1">
                  <p class="text-xs text-slate-500 font-medium mb-2">Catatan</p>
                  <pre class="text-slate-700 text-sm whitespace-pre-wrap font-sans bg-slate-50 rounded-lg p-3">{{
                    selectedInternship.notes }}</pre>
                </div>
              </div>
            </div>
          </div>
          <div class="px-6 py-4 border-t border-slate-100 flex justify-end">
            <BaseButton variant="secondary" @click="closeDetail">Tutup</BaseButton>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>

  <!-- Action Modal -->
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="showActionModal && selectedInternship" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="closeAction"></div>
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
          <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <component :is="actionLabels[actionType]?.icon" class="w-6 h-6"
                :class="actionLabels[actionType]?.color" />
              <h3 class="font-bold text-lg text-slate-900">{{ actionLabels[actionType]?.title }}</h3>
            </div>
            <button @click="closeAction" class="text-slate-400 hover:text-slate-600 transition-colors cursor-pointer">
              <X class="w-6 h-6" />
            </button>
          </div>
          <div class="p-6 space-y-4">
            <p class="text-sm text-slate-600">
              Intern: <strong>{{ selectedInternship.intern?.name }}</strong>
            </p>

            <!-- Extend: Date picker -->
            <div v-if="actionType === 'extend'">
              <label class="block text-sm font-semibold text-slate-700 mb-2">
                Tanggal Selesai Baru <span class="text-danger">*</span>
              </label>
              <input v-model="actionForm.end_date" type="date"
                class="w-full h-12 px-4 rounded-lg border bg-slate-50 focus:ring-2"
                :class="errors.end_date ? 'border-danger focus:border-danger focus:ring-danger/20' : 'border-slate-300 focus:border-primary focus:ring-primary'" />
              <p v-if="errors.end_date" class="text-sm text-danger mt-1">{{ errors.end_date }}</p>
            </div>

            <!-- Terminate: Reason -->
            <div v-if="actionType === 'terminate'">
              <label class="block text-sm font-semibold text-slate-700 mb-2">
                Alasan Penghentian
              </label>
              <textarea v-model="actionForm.notes" rows="3"
                class="w-full px-4 py-3 rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary focus:ring-2 resize-none"
                placeholder="Masukkan alasan..."></textarea>
            </div>

            <!-- Complete: Notes -->
            <div v-if="actionType === 'complete'">
              <label class="block text-sm font-semibold text-slate-700 mb-2">
                Catatan (Opsional)
              </label>
              <textarea v-model="actionForm.notes" rows="3"
                class="w-full px-4 py-3 rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary focus:ring-2 resize-none"
                placeholder="Catatan penutupan..."></textarea>
            </div>

            <!-- Change Mentor: Select -->
            <div v-if="actionType === 'change_mentor'">
              <label class="block text-sm font-semibold text-slate-700 mb-2">
                Mentor Baru <span class="text-danger">*</span>
              </label>
              <select v-model="actionForm.mentor_id"
                class="w-full h-12 px-4 rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary focus:ring-2">
                <option value="" disabled>Pilih Mentor</option>
                <option v-for="mentor in mentors" :key="mentor.id" :value="mentor.id">
                  {{ mentor.name }}
                </option>
              </select>
            </div>
          </div>
          <div class="px-6 py-4 border-t border-slate-100 flex gap-3">
            <BaseButton variant="secondary" full @click="closeAction">Batal</BaseButton>
            <BaseButton
              :variant="actionType === 'terminate' ? 'danger' : actionType === 'complete' ? 'success' : 'primary'" full
              @click="submitAction"
              :disabled="(actionType === 'extend' && !actionForm.end_date) || (actionType === 'change_mentor' && !actionForm.mentor_id)">
              Konfirmasi
            </BaseButton>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: all 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from>div:last-child,
.modal-leave-to>div:last-child {
  transform: scale(0.95);
}
</style>
