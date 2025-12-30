<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import BaseButton from '@/Components/BaseButton.vue';
import { formatDate } from '@/utils/helpers';
import {
  Search,
  ChevronLeft,
  ChevronRight,
  Eye,
  Edit,
  Trash2,
  Plus,
  X,
  Briefcase,
  Users,
  DoorOpen,
  DoorClosed,
  MapPin,
  Calendar,
  Clock,
} from 'lucide-vue-next';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
  jobs: Object,
  stats: Object,
  statusOptions: Array,
  typeOptions: Array,
  filters: Object,
});

const searchQuery = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || 'all');
const typeFilter = ref(props.filters?.type || 'all');
const isLoading = ref(false);

const selectedJob = ref(null);
const showDetailModal = ref(false);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);

const createImagePreview = ref(null);
const editImagePreview = ref(null);
const imageError = ref('');

const createForm = useForm({
  title: '',
  type: 'On-site',
  status: 'open',
  location: '',
  description: '',
  responsibilities: [''],
  requirements: [''],
  benefits: [''],
  duration: '',
  deadline: '',
  image: null,
});

const editForm = useForm({
  title: '',
  type: '',
  status: '',
  location: '',
  description: '',
  responsibilities: [''],
  requirements: [''],
  benefits: [''],
  duration: '',
  deadline: '',
  image: null,
});

let searchTimeout = null;
watch(searchQuery, () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => applyFilters(), 300);
});

watch([statusFilter, typeFilter], () => applyFilters());

const applyFilters = () => {
  isLoading.value = true;
  router.get(route('admin.jobs'), {
    search: searchQuery.value || undefined,
    status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    type: typeFilter.value !== 'all' ? typeFilter.value : undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
    onFinish: () => isLoading.value = false,
  });
};

const getStatusConfig = (status) => {
  const configs = {
    open: { label: 'Terbuka', color: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
    closed: { label: 'Ditutup', color: 'bg-red-100 text-red-600 border-red-200' },
  };
  return configs[status] || configs.closed;
};

const getTypeConfig = (type) => {
  const configs = {
    Remote: { label: 'Remote', color: 'bg-blue-50 text-blue-700 border-blue-200' },
    Hybrid: { label: 'Hybrid', color: 'bg-purple-50 text-purple-700 border-purple-200' },
    'On-site': { label: 'On-site', color: 'bg-amber-50 text-amber-700 border-amber-200' },
  };
  return configs[type] || { label: type, color: 'bg-slate-100 text-slate-700 border-slate-200' };
};

const openDetail = (job) => {
  selectedJob.value = job;
  showDetailModal.value = true;
};

const closeDetail = () => {
  showDetailModal.value = false;
  setTimeout(() => selectedJob.value = null, 300);
};

const openCreate = () => {
  createForm.reset();
  createForm.clearErrors();
  createForm.responsibilities = [''];
  createForm.requirements = [''];
  createForm.benefits = [''];
  createImagePreview.value = null;
  imageError.value = '';
  showCreateModal.value = true;
};

const closeCreate = () => {
  showCreateModal.value = false;
  createForm.reset();
  createImagePreview.value = null;
  imageError.value = '';
};

const openEdit = (job) => {
  selectedJob.value = job;
  editForm.title = job.title;
  editForm.type = job.type;
  editForm.status = job.status;
  editForm.location = job.location;
  editForm.description = job.description;
  editForm.responsibilities = job.responsibilities?.length ? [...job.responsibilities] : [''];
  editForm.requirements = job.requirements?.length ? [...job.requirements] : [''];
  editForm.benefits = job.benefits?.length ? [...job.benefits] : [''];
  editForm.duration = job.duration || '';
  editForm.deadline = job.deadline ? job.deadline.split('T')[0] : '';
  editForm.image = null;
  editImagePreview.value = job.image ? `/storage/${job.image}` : null;
  imageError.value = '';
  editForm.clearErrors();
  showEditModal.value = true;
};

const closeEdit = () => {
  showEditModal.value = false;
  editForm.reset();
  editImagePreview.value = null;
  imageError.value = '';
};

const openDelete = (job) => {
  selectedJob.value = job;
  showDeleteModal.value = true;
};

const closeDelete = () => {
  showDeleteModal.value = false;
};

const submitCreate = () => {
  createForm.responsibilities = createForm.responsibilities.filter(r => r.trim());
  createForm.requirements = createForm.requirements.filter(r => r.trim());
  createForm.benefits = createForm.benefits.filter(b => b.trim());

  createForm.post(route('admin.jobs.store'), {
    preserveScroll: true,
    onSuccess: () => closeCreate(),
  });
};

const submitEdit = () => {
  editForm.responsibilities = editForm.responsibilities.filter(r => r.trim());
  editForm.requirements = editForm.requirements.filter(r => r.trim());
  editForm.benefits = editForm.benefits.filter(b => b.trim());

  editForm
    .transform((data) => ({
      ...data,
      _method: 'PUT',
    }))
    .post(route('admin.jobs.update', selectedJob.value.id), {
      preserveScroll: true,
      forceFormData: true,
    onSuccess: () => closeEdit(),
  });
};

const submitDelete = () => {
  router.delete(route('admin.jobs.destroy', selectedJob.value.id), {
    preserveScroll: true,
    onSuccess: () => closeDelete(),
  });
};

const toggleStatus = (job) => {
  router.patch(route('admin.jobs.update-status', job.id), {
    status: job.status === 'open' ? 'closed' : 'open',
  }, { preserveScroll: true });
};

const addArrayItem = (form, field) => {
  form[field].push('');
};

const removeArrayItem = (form, field, index) => {
  if (form[field].length > 1) {
    form[field].splice(index, 1);
  }
};

const getJobImage = (job) => {
  if (job.image) {
    return `/storage/${job.image}`;
  }
  return null;
};

const handleCreateImageChange = (event) => {
  const file = event.target.files[0];
  imageError.value = '';

  if (!file) {
    createForm.image = null;
    createImagePreview.value = null;
    return;
  }

  if (file.size > 2 * 1024 * 1024) {
    imageError.value = 'Ukuran gambar maksimal 2MB.';
    event.target.value = '';
    createForm.image = null;
    createImagePreview.value = null;
    return;
  }

  if (!file.type.startsWith('image/')) {
    imageError.value = 'File harus berupa gambar.';
    event.target.value = '';
    createForm.image = null;
    createImagePreview.value = null;
    return;
  }

  createForm.image = file;
  createImagePreview.value = URL.createObjectURL(file);
};

const handleEditImageChange = (event) => {
  const file = event.target.files[0];
  imageError.value = '';

  if (!file) {
    editForm.image = null;
    return;
  }

  if (file.size > 2 * 1024 * 1024) {
    imageError.value = 'Ukuran gambar maksimal 2MB.';
    event.target.value = '';
    editForm.image = null;
    return;
  }

  if (!file.type.startsWith('image/')) {
    imageError.value = 'File harus berupa gambar.';
    event.target.value = '';
    editForm.image = null;
    return;
  }

  editForm.image = file;
  editImagePreview.value = URL.createObjectURL(file);
};
</script>

<template>

  <Head title="Kelola Lowongan" />

  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Kelola Lowongan</h1>
        <p class="text-slate-500 mt-1">Kelola lowongan magang yang tersedia</p>
      </div>
      <BaseButton variant="primary" @click="openCreate">
        <Plus class="w-4 h-4 mr-2" />
        Tambah Lowongan
      </BaseButton>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center">
            <Briefcase class="w-6 h-6 text-slate-600" />
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
            <DoorOpen class="w-6 h-6 text-emerald-600" />
          </div>
          <div>
            <p class="text-2xl font-bold text-emerald-600">{{ stats.open }}</p>
            <p class="text-xs text-slate-500 font-medium">Terbuka</p>
          </div>
        </div>
      </div>
      <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center">
            <DoorClosed class="w-6 h-6 text-red-600" />
          </div>
          <div>
            <p class="text-2xl font-bold text-slate-600">{{ stats.closed }}</p>
            <p class="text-xs text-slate-500 font-medium">Ditutup</p>
          </div>
        </div>
      </div>
      <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center">
            <Users class="w-6 h-6 text-blue-600" />
          </div>
          <div>
            <p class="text-2xl font-bold text-blue-600">{{ stats.applicants }}</p>
            <p class="text-xs text-slate-500 font-medium">Total Pelamar</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
      <div class="flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[200px]">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
          <input v-model="searchQuery" type="text"
            class="w-full pl-10 pr-4 py-2 rounded-xl border-slate-200 focus:border-primary focus:ring-primary text-sm"
            placeholder="Cari judul atau lokasi..." />
        </div>
        <select v-model="statusFilter"
          class="px-4 py-2 rounded-xl border-slate-200 focus:border-primary focus:ring-primary text-sm font-medium text-slate-600 bg-slate-50 cursor-pointer">
          <option value="all">Semua Status</option>
          <option v-for="status in statusOptions" :key="status.value" :value="status.value">
            {{ status.label }}
          </option>
        </select>
        <select v-model="typeFilter"
          class="px-4 py-2 rounded-xl border-slate-200 focus:border-primary focus:ring-primary text-sm font-medium text-slate-600 bg-slate-50 cursor-pointer">
          <option value="all">Semua Tipe</option>
          <option v-for="type in typeOptions" :key="type.value" :value="type.value">
            {{ type.label }}
          </option>
        </select>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-slate-50/80 border-b border-slate-100">
            <tr>
              <th class="px-6 py-4 text-left font-bold text-slate-700">Lowongan</th>
              <th class="px-6 py-4 text-left font-bold text-slate-700">Tipe</th>
              <th class="px-6 py-4 text-left font-bold text-slate-700">Lokasi</th>
              <th class="px-6 py-4 text-left font-bold text-slate-700">Pelamar</th>
              <th class="px-6 py-4 text-left font-bold text-slate-700 whitespace-nowrap">Status <small>(Klik untuk
                  update)</small></th>
              <th class="px-6 py-4 text-left font-bold text-slate-700">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100" v-if="!isLoading">
            <tr v-for="job in jobs.data" :key="job.id" class="hover:bg-slate-50/50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center gap-3">
                  <div class="size-16 rounded-xl bg-primary/10 flex items-center justify-center overflow-hidden">
                    <img v-if="getJobImage(job)" :src="getJobImage(job)" :alt="job.title"
                      class="w-full h-full object-cover" />
                    <Briefcase v-else class="w-6 h-6 text-primary" />
                  </div>
                  <div>
                    <p class="font-semibold text-slate-900">{{ job.title }}</p>
                    <p class="text-xs text-slate-500">{{ job.duration || 'Durasi tidak ditentukan' }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-3 py-1 rounded-full text-xs font-bold border" :class="getTypeConfig(job.type).color">
                  {{ getTypeConfig(job.type).label }}
                </span>
              </td>
              <td class="px-6 py-4 text-slate-600 whitespace-nowrap">
                <div class="flex items-center gap-1">
                  <MapPin class="size-5 text-slate-400" />
                  {{ job.location }}
                </div>
              </td>
              <td class="px-6 py-4">
                <span
                  class="px-3 py-1 bg-primary/10 text-primary border border-primary/20 rounded-full text-xs font-bold">
                  {{ job.applicants_count }}
                </span>
              </td>
              <td class="px-6 py-4">
                <button @click="toggleStatus(job)"
                  class="px-3 py-1 rounded-full text-xs font-bold border cursor-pointer transition-all hover:shadow-md"
                  :class="getStatusConfig(job.status).color"
                  :title="`Klik untuk ${job.status === 'open' ? 'tutup' : 'buka'}`">
                  {{ getStatusConfig(job.status).label }}
                </button>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center justify-end gap-1">
                  <button @click="openDetail(job)"
                    class="p-2 text-slate-400 hover:text-primary hover:bg-primary/5 rounded-lg transition-all cursor-pointer"
                    title="Lihat Detail">
                    <Eye class="w-5 h-5" />
                  </button>
                  <button @click="openEdit(job)"
                    class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all cursor-pointer"
                    title="Edit">
                    <Edit class="w-5 h-5" />
                  </button>
                  <button @click="openDelete(job)"
                    class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all cursor-pointer"
                    title="Hapus">
                    <Trash2 class="w-5 h-5" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="jobs.data.length === 0">
              <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                Tidak ada data lowongan ditemukan.
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="6" class="px-6 py-12 text-center text-slate-500">Loading...</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="jobs.last_page > 1" class="flex items-center justify-between px-6 py-4 border-t border-slate-100">
        <p class="text-sm text-slate-500">
          Menampilkan {{ jobs.from }} - {{ jobs.to }} dari {{ jobs.total }} data
        </p>
        <div class="flex items-center gap-2">
          <button @click="router.get(jobs.prev_page_url)" :disabled="!jobs.prev_page_url"
            class="p-2 rounded-lg border border-slate-200 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
            <ChevronLeft class="w-5 h-5" />
          </button>
          <span class="px-3 py-1 text-sm font-medium text-slate-600">
            {{ jobs.current_page }} / {{ jobs.last_page }}
          </span>
          <button @click="router.get(jobs.next_page_url)" :disabled="!jobs.next_page_url"
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
      <div v-if="showDetailModal && selectedJob" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="closeDetail"></div>
        <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden">
          <div class="bg-gradient-to-r from-primary to-primary/80 px-6 py-4 flex items-center justify-between">
            <h3 class="font-bold text-lg text-white">Detail Lowongan</h3>
            <button @click="closeDetail" class="text-white/80 hover:text-white transition-colors cursor-pointer">
              <X class="w-6 h-6" />
            </button>
          </div>
          <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
            <div class="flex items-start gap-4">
              <div class="w-20 h-20 rounded-xl bg-primary/10 flex items-center justify-center overflow-hidden shrink-0">
                <img v-if="getJobImage(selectedJob)" :src="getJobImage(selectedJob)" :alt="selectedJob.title"
                  class="w-full h-full object-cover" />
                <Briefcase v-else class="w-10 h-10 text-primary" />
              </div>
              <div class="flex-1">
                <h4 class="font-bold text-xl text-slate-900">{{ selectedJob.title }}</h4>
                <div class="flex flex-wrap gap-2 mt-2">
                  <span class="px-3 py-1 rounded-full text-xs font-bold border"
                    :class="getStatusConfig(selectedJob.status).color">
                    {{ getStatusConfig(selectedJob.status).label }}
                  </span>
                  <span class="px-3 py-1 rounded-full text-xs font-bold border"
                    :class="getTypeConfig(selectedJob.type).color">
                    {{ getTypeConfig(selectedJob.type).label }}
                  </span>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm">
              <div class="flex items-center gap-2 text-slate-600">
                <MapPin class="w-4 h-4 text-slate-400" />
                {{ selectedJob.location }}
              </div>
              <div class="flex items-center gap-2 text-slate-600">
                <Clock class="w-4 h-4 text-slate-400" />
                {{ selectedJob.duration || '-' }}
              </div>
              <div class="flex items-center gap-2 text-slate-600">
                <Calendar class="w-4 h-4 text-slate-400" />
                Deadline: {{ selectedJob.deadline ? formatDate(selectedJob.deadline) : '-' }}
              </div>
              <div class="flex items-center gap-2 text-slate-600">
                <Users class="w-4 h-4 text-slate-400" />
                {{ selectedJob.applicants_count }} Pelamar
              </div>
            </div>

            <div class="border-t border-slate-100 pt-4">
              <h5 class="font-semibold text-slate-900 mb-2">Deskripsi</h5>
              <p class="text-slate-600 text-sm whitespace-pre-wrap">{{ selectedJob.description }}</p>
            </div>

            <div v-if="selectedJob.responsibilities?.length" class="border-t border-slate-100 pt-4">
              <h5 class="font-semibold text-slate-900 mb-2">Tanggung Jawab</h5>
              <ul class="list-disc list-inside text-slate-600 text-sm space-y-1">
                <li v-for="(item, i) in selectedJob.responsibilities" :key="i">{{ item }}</li>
              </ul>
            </div>

            <div v-if="selectedJob.requirements?.length" class="border-t border-slate-100 pt-4">
              <h5 class="font-semibold text-slate-900 mb-2">Persyaratan</h5>
              <ul class="list-disc list-inside text-slate-600 text-sm space-y-1">
                <li v-for="(item, i) in selectedJob.requirements" :key="i">{{ item }}</li>
              </ul>
            </div>

            <div v-if="selectedJob.benefits?.length" class="border-t border-slate-100 pt-4">
              <h5 class="font-semibold text-slate-900 mb-2">Benefit</h5>
              <ul class="list-disc list-inside text-slate-600 text-sm space-y-1">
                <li v-for="(item, i) in selectedJob.benefits" :key="i">{{ item }}</li>
              </ul>
            </div>
          </div>
          <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3">
            <BaseButton variant="secondary" @click="closeDetail">Tutup</BaseButton>
            <BaseButton variant="primary" @click="closeDetail(); openEdit(selectedJob)">Edit</BaseButton>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>

  <!-- Create Modal -->
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="closeCreate"></div>
        <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden">
          <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-lg text-slate-900">Tambah Lowongan</h3>
            <button @click="closeCreate" class="text-slate-400 hover:text-slate-600 cursor-pointer">
              <X class="w-6 h-6" />
            </button>
          </div>
          <form @submit.prevent="submitCreate" class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
            <div class="grid grid-cols-2 gap-4">
              <div class="col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Judul <span
                    class="text-danger">*</span></label>
                <input v-model="createForm.title" type="text"
                  class="w-full h-12 px-4 rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary"
                  placeholder="Contoh: Frontend Developer Intern" />
                <p v-if="createForm.errors.title" class="text-danger text-sm mt-1">{{ createForm.errors.title }}</p>
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Tipe <span
                    class="text-danger">*</span></label>
                <select v-model="createForm.type"
                  class="w-full h-12 px-4 rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary">
                  <option v-for="type in typeOptions" :key="type.value" :value="type.value">{{ type.label }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Status <span
                    class="text-danger">*</span></label>
                <select v-model="createForm.status"
                  class="w-full h-12 px-4 rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary">
                  <option v-for="status in statusOptions" :key="status.value" :value="status.value">{{ status.label }}
                  </option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Lokasi <span
                    class="text-danger">*</span></label>
                <input v-model="createForm.location" type="text"
                  class="w-full h-12 px-4 rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary"
                  placeholder="Contoh: Jakarta, Indonesia" />
                <p v-if="createForm.errors.location" class="text-danger text-sm mt-1">{{ createForm.errors.location }}
                </p>
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Durasi</label>
                <input v-model="createForm.duration" type="text"
                  class="w-full h-12 px-4 rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary"
                  placeholder="Contoh: 3 Bulan" />
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Deadline</label>
                <input v-model="createForm.deadline" type="date"
                  class="w-full h-12 px-4 rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary" />
              </div>
              <div class="col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Gambar <span
                    class="text-xs text-slate-400 font-normal">(Maks. 2MB)</span></label>
                <div class="flex items-start gap-4">
                  <div v-if="createImagePreview" class="w-20 h-20 rounded-xl overflow-hidden bg-slate-100 shrink-0">
                    <img :src="createImagePreview" alt="Preview" class="w-full h-full object-cover" />
                  </div>
                  <div class="flex-1">
                    <input type="file" @change="handleCreateImageChange" accept="image/*"
                      class="w-full h-12 px-4 py-3 rounded-lg border border-slate-300 bg-slate-50 text-sm file:mr-4 file:py-1 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer" />
                    <p v-if="imageError" class="text-danger text-sm mt-1">{{ imageError }}</p>
                    <p v-if="createForm.errors.image" class="text-danger text-sm mt-1">{{ createForm.errors.image }}</p>
                  </div>
                </div>
              </div>
              <div class="col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi <span
                    class="text-danger">*</span></label>
                <textarea v-model="createForm.description" rows="3"
                  class="w-full px-4 py-3 rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary resize-none"
                  placeholder="Deskripsi lowongan..."></textarea>
                <p v-if="createForm.errors.description" class="text-danger text-sm mt-1">{{
                  createForm.errors.description }}</p>
              </div>
              <div class="col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggung Jawab</label>
                <div v-for="(_, i) in createForm.responsibilities" :key="i" class="flex gap-2 mb-2">
                  <input v-model="createForm.responsibilities[i]" type="text"
                    class="flex-1 h-10 px-4 rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary text-sm"
                    placeholder="Tanggung jawab..." />
                  <button type="button" @click="removeArrayItem(createForm, 'responsibilities', i)"
                    class="px-3 text-red-500 hover:bg-red-50 rounded-lg cursor-pointer">×</button>
                </div>
                <button type="button" @click="addArrayItem(createForm, 'responsibilities')"
                  class="text-sm text-primary hover:underline cursor-pointer">+ Tambah</button>
              </div>
              <div class="col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Persyaratan</label>
                <div v-for="(_, i) in createForm.requirements" :key="i" class="flex gap-2 mb-2">
                  <input v-model="createForm.requirements[i]" type="text"
                    class="flex-1 h-10 px-4 rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary text-sm"
                    placeholder="Persyaratan..." />
                  <button type="button" @click="removeArrayItem(createForm, 'requirements', i)"
                    class="px-3 text-red-500 hover:bg-red-50 rounded-lg cursor-pointer">×</button>
                </div>
                <button type="button" @click="addArrayItem(createForm, 'requirements')"
                  class="text-sm text-primary hover:underline cursor-pointer">+ Tambah</button>
              </div>
              <div class="col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Benefit</label>
                <div v-for="(_, i) in createForm.benefits" :key="i" class="flex gap-2 mb-2">
                  <input v-model="createForm.benefits[i]" type="text"
                    class="flex-1 h-10 px-4 rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary text-sm"
                    placeholder="Benefit..." />
                  <button type="button" @click="removeArrayItem(createForm, 'benefits', i)"
                    class="px-3 text-red-500 hover:bg-red-50 rounded-lg cursor-pointer">×</button>
                </div>
                <button type="button" @click="addArrayItem(createForm, 'benefits')"
                  class="text-sm text-primary hover:underline cursor-pointer">+ Tambah</button>
              </div>
            </div>
          </form>
          <div class="px-6 py-4 border-t border-slate-100 flex gap-3">
            <BaseButton variant="secondary" full @click="closeCreate">Batal</BaseButton>
            <BaseButton variant="primary" full @click="submitCreate" :disabled="createForm.processing">
              {{ createForm.processing ? 'Menyimpan...' : 'Simpan' }}
            </BaseButton>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>

  <!-- Edit Modal -->
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="showEditModal && selectedJob" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="closeEdit"></div>
        <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden">
          <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-lg text-slate-900">Edit Lowongan</h3>
            <button @click="closeEdit" class="text-slate-400 hover:text-slate-600 cursor-pointer">
              <X class="w-6 h-6" />
            </button>
          </div>
          <form @submit.prevent="submitEdit" class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
            <div class="grid grid-cols-2 gap-4">
              <div class="col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Judul <span
                    class="text-danger">*</span></label>
                <input v-model="editForm.title" type="text"
                  class="w-full h-12 px-4 rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary" />
                <p v-if="editForm.errors.title" class="text-danger text-sm mt-1">{{ editForm.errors.title }}</p>
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Tipe <span
                    class="text-danger">*</span></label>
                <select v-model="editForm.type"
                  class="w-full h-12 px-4 rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary">
                  <option v-for="type in typeOptions" :key="type.value" :value="type.value">{{ type.label }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Status <span
                    class="text-danger">*</span></label>
                <select v-model="editForm.status"
                  class="w-full h-12 px-4 rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary">
                  <option v-for="status in statusOptions" :key="status.value" :value="status.value">{{ status.label }}
                  </option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Lokasi <span
                    class="text-danger">*</span></label>
                <input v-model="editForm.location" type="text"
                  class="w-full h-12 px-4 rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary" />
                <p v-if="editForm.errors.location" class="text-danger text-sm mt-1">{{ editForm.errors.location }}</p>
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Durasi</label>
                <input v-model="editForm.duration" type="text"
                  class="w-full h-12 px-4 rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary" />
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Deadline</label>
                <input v-model="editForm.deadline" type="date"
                  class="w-full h-12 px-4 rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary" />
              </div>
              <div class="col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Gambar <span
                    class="text-xs text-slate-400 font-normal">(Maks. 2MB)</span></label>
                <div class="flex items-start gap-4">
                  <div v-if="editImagePreview" class="w-20 h-20 rounded-xl overflow-hidden bg-slate-100 shrink-0">
                    <img :src="editImagePreview" alt="Preview" class="w-full h-full object-cover" />
                  </div>
                  <div class="flex-1">
                    <input type="file" @change="handleEditImageChange" accept="image/*"
                      class="w-full h-12 px-4 py-3 rounded-lg border border-slate-300 bg-slate-50 text-sm file:mr-4 file:py-1 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer" />
                    <p v-if="imageError" class="text-danger text-sm mt-1">{{ imageError }}</p>
                    <p v-if="editForm.errors.image" class="text-danger text-sm mt-1">{{ editForm.errors.image }}</p>
                  </div>
                </div>
              </div>
              <div class="col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi <span
                    class="text-danger">*</span></label>
                <textarea v-model="editForm.description" rows="3"
                  class="w-full px-4 py-3 rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary resize-none"></textarea>
                <p v-if="editForm.errors.description" class="text-danger text-sm mt-1">{{ editForm.errors.description }}
                </p>
              </div>
              <div class="col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggung Jawab</label>
                <div v-for="(_, i) in editForm.responsibilities" :key="i" class="flex gap-2 mb-2">
                  <input v-model="editForm.responsibilities[i]" type="text"
                    class="flex-1 h-10 px-4 rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary text-sm" />
                  <button type="button" @click="removeArrayItem(editForm, 'responsibilities', i)"
                    class="px-3 text-red-500 hover:bg-red-50 rounded-lg cursor-pointer">×</button>
                </div>
                <button type="button" @click="addArrayItem(editForm, 'responsibilities')"
                  class="text-sm text-primary hover:underline cursor-pointer">+ Tambah</button>
              </div>
              <div class="col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Persyaratan</label>
                <div v-for="(_, i) in editForm.requirements" :key="i" class="flex gap-2 mb-2">
                  <input v-model="editForm.requirements[i]" type="text"
                    class="flex-1 h-10 px-4 rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary text-sm" />
                  <button type="button" @click="removeArrayItem(editForm, 'requirements', i)"
                    class="px-3 text-red-500 hover:bg-red-50 rounded-lg cursor-pointer">×</button>
                </div>
                <button type="button" @click="addArrayItem(editForm, 'requirements')"
                  class="text-sm text-primary hover:underline cursor-pointer">+ Tambah</button>
              </div>
              <div class="col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Benefit</label>
                <div v-for="(_, i) in editForm.benefits" :key="i" class="flex gap-2 mb-2">
                  <input v-model="editForm.benefits[i]" type="text"
                    class="flex-1 h-10 px-4 rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary text-sm" />
                  <button type="button" @click="removeArrayItem(editForm, 'benefits', i)"
                    class="px-3 text-red-500 hover:bg-red-50 rounded-lg cursor-pointer">×</button>
                </div>
                <button type="button" @click="addArrayItem(editForm, 'benefits')"
                  class="text-sm text-primary hover:underline cursor-pointer">+ Tambah</button>
              </div>
            </div>
          </form>
          <div class="px-6 py-4 border-t border-slate-100 flex gap-3">
            <BaseButton variant="secondary" full @click="closeEdit">Batal</BaseButton>
            <BaseButton variant="primary" full @click="submitEdit" :disabled="editForm.processing">
              {{ editForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </BaseButton>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>

  <!-- Delete Modal -->
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="showDeleteModal && selectedJob" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="closeDelete"></div>
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
          <div class="p-6 text-center">
            <div class="w-16 h-16 mx-auto bg-red-100 rounded-full flex items-center justify-center mb-4">
              <Trash2 class="w-8 h-8 text-red-600" />
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-2">Hapus Lowongan?</h3>
            <p class="text-slate-500 mb-6">
              Lowongan "<strong>{{ selectedJob.title }}</strong>" akan dihapus permanen.
              Tindakan ini tidak dapat dibatalkan.
            </p>
            <div class="flex gap-3">
              <BaseButton variant="secondary" full @click="closeDelete">Batal</BaseButton>
              <BaseButton variant="danger" full @click="submitDelete">Hapus</BaseButton>
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
