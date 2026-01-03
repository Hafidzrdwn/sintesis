<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Search, CheckCircle, XCircle, Trash2, Clock, MessageSquare, AlertTriangle } from 'lucide-vue-next';
import axios from 'axios';
import { getInitials } from '@/utils/helpers';

const props = defineProps({
  testimonials: Object,
  stats: Object,
  filters: Object,
});

defineOptions({ layout: AuthenticatedLayout });

const searchQuery = ref(props.filters?.search || '');
const activeFilter = ref(props.filters?.status || 'all');
const actionLoading = ref(null);
const toast = ref({ show: false, type: '', message: '' });

const confirmModal = ref({
  show: false,
  type: '',
  id: null,
  title: '',
  message: '',
  confirmText: '',
  confirmClass: '',
});

const filterTabs = [
  { key: 'all', label: 'Semua', icon: MessageSquare },
  { key: 'pending', label: 'Menunggu', icon: Clock },
  { key: 'approved', label: 'Disetujui', icon: CheckCircle },
  { key: 'rejected', label: 'Ditolak', icon: XCircle },
];

const applyFilters = () => {
  router.get(route('admin.testimonials'), {
    status: activeFilter.value,
    search: searchQuery.value,
  }, { preserveState: true, replace: true });
};

watch(activeFilter, applyFilters);

let searchTimeout = null;
watch(searchQuery, () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(applyFilters, 400);
});

const showToast = (type, message) => {
  toast.value = { show: true, type, message };
  setTimeout(() => toast.value.show = false, 3000);
};

const openConfirmModal = (type, id) => {
  const configs = {
    approve: {
      title: 'Setujui Testimoni',
      message: 'Testimoni ini akan ditampilkan di halaman utama. Lanjutkan?',
      confirmText: 'Ya, Setujui',
      confirmClass: 'bg-emerald-500 hover:bg-emerald-600',
    },
    reject: {
      title: 'Tolak Testimoni',
      message: 'Testimoni ini tidak akan ditampilkan. Lanjutkan?',
      confirmText: 'Ya, Tolak',
      confirmClass: 'bg-amber-500 hover:bg-amber-600',
    },
    delete: {
      title: 'Hapus Testimoni',
      message: 'Testimoni akan dihapus permanen dan tidak dapat dikembalikan. Lanjutkan?',
      confirmText: 'Ya, Hapus',
      confirmClass: 'bg-red-500 hover:bg-red-600',
    },
  };

  confirmModal.value = {
    show: true,
    type,
    id,
    ...configs[type],
  };
};

const closeConfirmModal = () => {
  confirmModal.value.show = false;
};

const executeAction = async () => {
  const { type, id } = confirmModal.value;
  closeConfirmModal();
  actionLoading.value = id;

  try {
    if (type === 'approve') {
      await axios.post(route('admin.testimonials.approve', id));
      showToast('success', 'Testimoni berhasil disetujui');
    } else if (type === 'reject') {
      await axios.post(route('admin.testimonials.reject', id));
      showToast('success', 'Testimoni berhasil ditolak');
    } else if (type === 'delete') {
      await axios.delete(route('admin.testimonials.destroy', id));
      showToast('success', 'Testimoni berhasil dihapus');
    }
    router.reload({ only: ['testimonials', 'stats'] });
  } catch (e) {
    showToast('error', 'Gagal melakukan aksi');
  } finally {
    actionLoading.value = null;
  }
};

const getStatusBadge = (status) => {
  const badges = {
    pending: { bg: 'bg-amber-100', text: 'text-amber-700', label: 'Menunggu Review' },
    approved: { bg: 'bg-emerald-100', text: 'text-emerald-700', label: 'Disetujui' },
    rejected: { bg: 'bg-red-100', text: 'text-red-700', label: 'Ditolak' },
  };
  return badges[status] || badges.pending;
};

const renderStars = (rating) => {
  return '★'.repeat(rating) + '☆'.repeat(5 - rating);
};
</script>

<template>

  <Head title="Manajemen Testimonial" />

  <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 translate-y-2"
    enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200"
    leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-2">
    <div v-if="toast.show"
      class="fixed bottom-6 right-6 z-50 px-6 py-3 rounded-xl shadow-lg text-white font-semibold flex items-center gap-2"
      :class="toast.type === 'success' ? 'bg-emerald-500' : 'bg-red-500'">
      <CheckCircle v-if="toast.type === 'success'" class="w-5 h-5" />
      <XCircle v-else class="w-5 h-5" />
      {{ toast.message }}
    </div>
  </Transition>

  <div class="flex flex-col gap-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">
          Manajemen Testimonial
        </h1>
        <p class="text-slate-500 mt-1">Review dan kelola testimoni dari alumni magang</p>
      </div>

      <div class="relative">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
        <input v-model="searchQuery" type="text" placeholder="Cari nama atau konten..."
          class="pl-10 pr-4 py-2.5 w-full md:w-72 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
      </div>
    </div>

    <div class="flex flex-wrap gap-2">
      <button v-for="tab in filterTabs" :key="tab.key" @click="activeFilter = tab.key"
        class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-medium transition-all cursor-pointer" :class="activeFilter === tab.key
          ? 'bg-primary text-white shadow-md shadow-primary/20'
          : 'bg-white text-slate-600 border border-slate-200 hover:border-primary/30 hover:text-primary'">
        <component :is="tab.icon" class="w-4 h-4" />
        <span>{{ tab.label }}</span>
        <span class="px-2 py-0.5 text-xs rounded-full"
          :class="activeFilter === tab.key ? 'bg-white/20' : 'bg-slate-100'">
          {{ stats?.[tab.key] || 0 }}
        </span>
      </button>
    </div>

    <div v-if="testimonials?.data?.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
      <div v-for="testimonial in testimonials.data" :key="testimonial.id"
        class="group bg-white rounded-2xl border border-slate-200 p-5 hover:shadow-lg hover:border-primary/30 transition-all duration-300">
        <div class="flex items-start flex-col gap-4 mb-4">
          <div class="flex items-center gap-3">
            <img v-if="testimonial.user_avatar" :src="testimonial.user_avatar" :alt="testimonial.user_name"
              class="w-12 h-12 rounded-full object-cover" />
            <div v-else
              class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-lg">
              {{ getInitials(testimonial.user_name) }}
            </div>
            <div>
              <h3 class="font-bold text-slate-800">{{ testimonial.user_name }}</h3>
              <p class="text-sm text-slate-500">{{ testimonial.position }}</p>
            </div>
          </div>
          <span class="px-2.5 py-1 text-xs font-semibold rounded-full" :class="[
            getStatusBadge(testimonial.status).bg,
            getStatusBadge(testimonial.status).text
          ]">
            {{ getStatusBadge(testimonial.status).label }}
          </span>
        </div>

        <div class="flex items-center gap-2 mb-3">
          <span class="text-amber-400 text-lg tracking-wider">{{ renderStars(testimonial.rating) }}</span>
          <span class="text-sm text-slate-500">({{ testimonial.rating }}/5)</span>
        </div>

        <div class="relative mb-4">
          <p class="text-slate-600 italic line-clamp-4">
            "{{ testimonial.content }}"
          </p>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
          <span class="text-xs text-slate-400 flex items-center gap-1">
            <Clock class="w-3.5 h-3.5" />
            {{ testimonial.created_at }}
          </span>

          <div class="flex items-center gap-1">
            <button v-if="testimonial.status === 'pending'" @click="openConfirmModal('approve', testimonial.id)"
              :disabled="actionLoading === testimonial.id"
              class="p-2 rounded-lg text-emerald-600 hover:bg-emerald-50 transition-colors cursor-pointer disabled:opacity-50"
              title="Setujui">
              <CheckCircle class="w-5 h-5" />
            </button>
            <button v-if="testimonial.status === 'pending'" @click="openConfirmModal('reject', testimonial.id)"
              :disabled="actionLoading === testimonial.id"
              class="p-2 rounded-lg text-amber-500 hover:bg-amber-50 transition-colors cursor-pointer disabled:opacity-50"
              title="Tolak">
              <XCircle class="w-5 h-5" />
            </button>
            <button @click="openConfirmModal('delete', testimonial.id)" :disabled="actionLoading === testimonial.id"
              class="p-2 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 transition-colors cursor-pointer disabled:opacity-50"
              title="Hapus">
              <Trash2 class="w-5 h-5" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
      <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <MessageSquare class="w-10 h-10 text-slate-300" />
      </div>
      <h3 class="text-lg font-bold text-slate-700 mb-2">Tidak Ada Testimonial</h3>
      <p class="text-slate-500">
        {{ activeFilter === 'pending' ? 'Tidak ada testimoni yang menunggu review' :
          activeFilter === 'approved' ? 'Belum ada testimoni yang disetujui' :
            activeFilter === 'rejected' ? 'Tidak ada testimoni yang ditolak' :
              'Belum ada testimoni dari alumni' }}
      </p>
    </div>

    <div v-if="testimonials?.links?.length > 3" class="flex items-center justify-center gap-2">
      <template v-for="(link, i) in testimonials.links" :key="i">
        <button v-if="link.url" @click="router.get(link.url)" v-html="link.label"
          class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors cursor-pointer" :class="link.active
            ? 'bg-primary text-white'
            : 'bg-white text-slate-600 border border-slate-200 hover:border-primary hover:text-primary'" />
      </template>
    </div>
  </div>

  <Teleport to="body">
    <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0"
      enter-to-class="opacity-100" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100"
      leave-to-class="opacity-0">
      <div v-if="confirmModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="closeConfirmModal"></div>

        <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-150"
          leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
          <div v-if="confirmModal.show" class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6 z-10">
            <div class="flex flex-col items-center text-center">
              <div class="w-16 h-16 rounded-full flex items-center justify-center mb-4" :class="{
                'bg-emerald-100': confirmModal.type === 'approve',
                'bg-amber-100': confirmModal.type === 'reject',
                'bg-red-100': confirmModal.type === 'delete',
              }">
                <CheckCircle v-if="confirmModal.type === 'approve'" class="w-8 h-8 text-emerald-600" />
                <AlertTriangle v-else-if="confirmModal.type === 'reject'" class="w-8 h-8 text-amber-600" />
                <Trash2 v-else class="w-8 h-8 text-red-600" />
              </div>

              <h3 class="text-xl font-bold text-slate-800 mb-2">{{ confirmModal.title }}</h3>
              <p class="text-slate-500 mb-6">{{ confirmModal.message }}</p>

              <div class="flex items-center gap-3 w-full">
                <button @click="closeConfirmModal"
                  class="flex-1 px-4 py-2.5 bg-slate-100 text-slate-700 font-semibold rounded-xl hover:bg-slate-200 transition-colors cursor-pointer">
                  Batal
                </button>
                <button @click="executeAction"
                  class="flex-1 px-4 py-2.5 text-white font-semibold rounded-xl transition-colors cursor-pointer"
                  :class="confirmModal.confirmClass">
                  {{ confirmModal.confirmText }}
                </button>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>
