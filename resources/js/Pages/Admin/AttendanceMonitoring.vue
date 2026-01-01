<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import {
  Search, ChevronLeft, ChevronRight, Calendar, Users, Clock,
  CheckCircle2, Building2, Home, TrendingUp, X, MapPin, Image, FileText, Eye
} from 'lucide-vue-next';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
  attendances: Object,
  internStats: Array,
  overallStats: Object,
  mentors: Array,
  filters: Object,
});

const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const selectedMonth = ref(props.filters?.month || new Date().getMonth() + 1);
const selectedYear = ref(props.filters?.year || new Date().getFullYear());
const searchQuery = ref(props.filters?.search || '');
const selectedStatus = ref(props.filters?.status || 'all');
const selectedMentor = ref(props.filters?.mentor_id || '');

const showDetailModal = ref(false);
const selectedAttendance = ref(null);

const periodLabel = computed(() => `${months[selectedMonth.value - 1]} ${selectedYear.value}`);

const applyFilters = () => {
  router.get(route('admin.attendance'), {
    month: selectedMonth.value,
    year: selectedYear.value,
    search: searchQuery.value,
    status: selectedStatus.value,
    mentor_id: selectedMentor.value,
  }, { preserveState: true, preserveScroll: true });
};

watch([selectedMonth, selectedYear, selectedStatus, selectedMentor], () => applyFilters());

let searchTimeout;
const handleSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => applyFilters(), 500);
};

const goToPrevMonth = () => {
  if (selectedMonth.value === 1) { selectedMonth.value = 12; selectedYear.value--; }
  else { selectedMonth.value--; }
};

const goToNextMonth = () => {
  if (selectedMonth.value === 12) { selectedMonth.value = 1; selectedYear.value++; }
  else { selectedMonth.value++; }
};

const getStatusColor = (status) => {
  const colors = {
    'present': 'bg-emerald-50 text-emerald-600 border-emerald-200',
    'late': 'bg-amber-50 text-amber-600 border-amber-200',
    'leave': 'bg-blue-50 text-blue-600 border-blue-200',
    'sick': 'bg-purple-50 text-purple-600 border-purple-200',
    'permit': 'bg-cyan-50 text-cyan-600 border-cyan-200',
  };
  return colors[status] || 'bg-slate-50 text-slate-600 border-slate-200';
};

const getStatusLabel = (status) => {
  const labels = { 'present': 'Hadir', 'late': 'Terlambat', 'leave': 'Cuti', 'sick': 'Sakit', 'permit': 'Izin' };
  return labels[status] || status;
};

const getRateColor = (rate) => {
  if (rate >= 90) return 'text-emerald-600';
  if (rate >= 75) return 'text-amber-600';
  return 'text-red-600';
};

const openDetail = (att) => {
  selectedAttendance.value = att;
  showDetailModal.value = true;
};

const closeDetail = () => {
  showDetailModal.value = false;
  selectedAttendance.value = null;
};

const openGoogleMaps = (lat, lng) => {
  window.open(`https://www.google.com/maps?q=${lat},${lng}`, '_blank');
};
</script>

<template>

  <Head title="Monitoring Absensi Global" />

  <div class="flex flex-col gap-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Monitoring Absensi</h1>
        <p class="text-slate-500 mt-1">Pantau kehadiran seluruh peserta magang.</p>
      </div>
      <div class="flex items-center gap-2">
        <button @click="goToPrevMonth" class="p-2 border border-slate-200 rounded-lg hover:bg-slate-50 cursor-pointer">
          <ChevronLeft class="w-4 h-4 text-slate-500" />
        </button>
        <div class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm font-medium flex items-center gap-2">
          <Calendar class="w-4 h-4 text-slate-400" />
          {{ periodLabel }}
        </div>
        <button @click="goToNextMonth" class="p-2 border border-slate-200 rounded-lg hover:bg-slate-50 cursor-pointer">
          <ChevronRight class="w-4 h-4 text-slate-500" />
        </button>
      </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
      <div class="bg-white p-4 rounded-xl border border-slate-200 text-center">
        <Users class="w-6 h-6 mx-auto mb-2 text-primary" />
        <p class="text-2xl font-black text-slate-900">{{ overallStats?.total_interns || 0 }}</p>
        <p class="text-xs text-slate-500">Total Intern</p>
      </div>
      <div class="bg-emerald-50 p-4 rounded-xl border border-emerald-200 text-center">
        <CheckCircle2 class="w-6 h-6 mx-auto mb-2 text-emerald-600" />
        <p class="text-2xl font-black text-emerald-600">{{ overallStats?.total_present || 0 }}</p>
        <p class="text-xs text-emerald-600">Total Hadir</p>
      </div>
      <div class="bg-amber-50 p-4 rounded-xl border border-amber-200 text-center">
        <Clock class="w-6 h-6 mx-auto mb-2 text-amber-600" />
        <p class="text-2xl font-black text-amber-600">{{ overallStats?.total_late || 0 }}</p>
        <p class="text-xs text-amber-600">Terlambat</p>
      </div>
      <div class="bg-blue-50 p-4 rounded-xl border border-blue-200 text-center">
        <Calendar class="w-6 h-6 mx-auto mb-2 text-blue-600" />
        <p class="text-2xl font-black text-blue-600">{{ overallStats?.total_leave || 0 }}</p>
        <p class="text-xs text-blue-600">Izin/Cuti</p>
      </div>
      <div class="bg-purple-50 p-4 rounded-xl border border-purple-200 text-center">
        <TrendingUp class="w-6 h-6 mx-auto mb-2 text-purple-600" />
        <p class="text-2xl font-black text-purple-600">{{ overallStats?.avg_working_hours || 0 }}</p>
        <p class="text-xs text-purple-600">Rata-rata Jam</p>
      </div>
    </div>

    <div v-if="internStats?.length" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="p-4 border-b border-slate-200 bg-slate-50/50 flex justify-between items-center">
        <h3 class="font-bold text-slate-900">Statistik per Intern</h3>
        <select v-model="selectedMentor"
          class="px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 cursor-pointer">
          <option value="">Semua Mentor</option>
          <option v-for="mentor in mentors" :key="mentor.id" :value="mentor.id">{{ mentor.name }}</option>
        </select>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead
            class="bg-slate-50 text-slate-500 font-semibold uppercase text-xs tracking-wider border-b border-slate-200">
            <tr>
              <th class="px-6 py-3">Nama</th>
              <th class="px-6 py-3">Mentor</th>
              <th class="px-6 py-3 text-center">Hadir</th>
              <th class="px-6 py-3 text-center">Terlambat</th>
              <th class="px-6 py-3 text-center">Izin</th>
              <th class="px-6 py-3 text-center">Alpha</th>
              <th class="px-6 py-3 text-center">Rate</th>
              <th class="px-6 py-3 text-center">Total Jam</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="intern in internStats" :key="intern.id" class="hover:bg-slate-50">
              <td class="px-6 py-4">
                <p class="font-medium text-slate-900">{{ intern.name }}</p>
                <p class="text-xs text-slate-400">{{ intern.email }}</p>
              </td>
              <td class="px-6 py-4 text-slate-500 text-xs">{{ intern.mentor_name }}</td>
              <td class="px-6 py-4 text-center">
                <span class="px-2 py-1 rounded-full bg-emerald-50 text-emerald-600 text-xs font-bold">{{ intern.present
                  }}</span>
              </td>
              <td class="px-6 py-4 text-center">
                <span class="px-2 py-1 rounded-full bg-amber-50 text-amber-600 text-xs font-bold">{{ intern.late
                  }}</span>
              </td>
              <td class="px-6 py-4 text-center">
                <span class="px-2 py-1 rounded-full bg-blue-50 text-blue-600 text-xs font-bold">{{ intern.leave
                  }}</span>
              </td>
              <td class="px-6 py-4 text-center">
                <span class="px-2 py-1 rounded-full text-xs font-bold"
                  :class="intern.absent > 0 ? 'bg-red-50 text-red-600' : 'bg-slate-50 text-slate-400'">{{ intern.absent
                  }}</span>
              </td>
              <td class="px-6 py-4 text-center font-bold" :class="getRateColor(intern.attendance_rate)">{{
                intern.attendance_rate }}%</td>
              <td class="px-6 py-4 text-center font-mono text-slate-600">{{ intern.total_hours }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="p-4 border-b border-slate-200 bg-slate-50/50 flex flex-col sm:flex-row justify-between gap-4">
        <h3 class="font-bold text-slate-900">Riwayat Absensi</h3>
        <div class="flex items-center gap-2 flex-wrap">
          <select v-model="selectedStatus"
            class="px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 cursor-pointer">
            <option value="all">Semua Status</option>
            <option value="present">Hadir</option>
            <option value="late">Terlambat</option>
            <option value="leave">Cuti</option>
            <option value="sick">Sakit</option>
            <option value="permit">Izin</option>
          </select>
          <div class="relative">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
            <input v-model="searchQuery" @input="handleSearch" type="text" placeholder="Cari nama..."
              class="pl-10 pr-4 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 w-48">
          </div>
        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead
            class="bg-slate-50 text-slate-500 font-semibold uppercase text-xs tracking-wider border-b border-slate-200">
            <tr>
              <th class="px-6 py-3">Nama</th>
              <th class="px-6 py-3">Tanggal</th>
              <th class="px-6 py-3">Check-In</th>
              <th class="px-6 py-3">Check-Out</th>
              <th class="px-6 py-3">Mode</th>
              <th class="px-6 py-3">Status</th>
              <th class="px-6 py-3 w-20">Detail</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="att in attendances?.data" :key="att.id" class="hover:bg-slate-50">
              <td class="px-6 py-4 font-medium text-slate-900">{{ att.user_name }}</td>
              <td class="px-6 py-4 text-slate-600">{{ att.date_formatted }}</td>
              <td class="px-6 py-4 font-mono text-slate-600">{{ att.check_in_time || '-' }}</td>
              <td class="px-6 py-4 font-mono text-slate-600">{{ att.check_out_time || '-' }}</td>
              <td class="px-6 py-4">
                <span class="flex items-center gap-1 text-xs">
                  <component :is="att.attendance_mode === 'WFO' ? Building2 : Home" class="w-3.5 h-3.5" />
                  {{ att.attendance_mode }}
                </span>
              </td>
              <td class="px-6 py-4">
                <span class="px-2 py-1 rounded-full text-xs font-bold border" :class="getStatusColor(att.status)">{{
                  getStatusLabel(att.status) }}</span>
              </td>
              <td class="px-6 py-4">
                <button @click="openDetail(att)"
                  class="p-2 hover:bg-primary/10 rounded-lg text-primary cursor-pointer transition-colors">
                  <Eye class="w-4 h-4" />
                </button>
              </td>
            </tr>
            <tr v-if="!attendances?.data?.length">
              <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                <Calendar class="w-12 h-12 mx-auto mb-3 text-slate-300" />
                <p>Tidak ada data absensi untuk periode ini.</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="attendances?.last_page > 1"
        class="p-4 border-t border-slate-200 flex items-center justify-between text-sm text-slate-500">
        <span>Halaman {{ attendances.current_page }} dari {{ attendances.last_page }}</span>
        <div class="flex gap-2">
          <button @click="router.get(attendances.prev_page_url)" :disabled="!attendances.prev_page_url"
            class="px-3 py-1 border border-slate-200 rounded-lg hover:bg-slate-50 disabled:opacity-50 cursor-pointer">Previous</button>
          <button @click="router.get(attendances.next_page_url)" :disabled="!attendances.next_page_url"
            class="px-3 py-1 border border-slate-200 rounded-lg hover:bg-slate-50 disabled:opacity-50 cursor-pointer">Next</button>
        </div>
      </div>
    </div>
  </div>

  <Teleport to="body">
    <div v-if="showDetailModal && selectedAttendance" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeDetail"></div>
      <div
        class="relative bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-5 border-b border-slate-200 sticky top-0 bg-white">
          <div>
            <h3 class="text-lg font-bold text-slate-900">Detail Absensi</h3>
            <p class="text-sm text-slate-500">{{ selectedAttendance.user_name }} - {{ selectedAttendance.date_formatted
              }}</p>
          </div>
          <button @click="closeDetail" class="p-2 hover:bg-slate-100 rounded-lg cursor-pointer">
            <X class="w-5 h-5 text-slate-400" />
          </button>
        </div>

        <div class="p-5 flex flex-col gap-5">
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center p-3 bg-slate-50 rounded-xl">
              <p class="text-xs text-slate-500 mb-1">Check-In</p>
              <p class="font-mono font-bold text-slate-900">{{ selectedAttendance.check_in_time || '-' }}</p>
            </div>
            <div class="text-center p-3 bg-slate-50 rounded-xl">
              <p class="text-xs text-slate-500 mb-1">Check-Out</p>
              <p class="font-mono font-bold text-slate-900">{{ selectedAttendance.check_out_time || '-' }}</p>
            </div>
            <div class="text-center p-3 bg-slate-50 rounded-xl">
              <p class="text-xs text-slate-500 mb-1">Durasi</p>
              <p class="font-mono font-bold text-slate-900">{{ selectedAttendance.working_hours || 0 }} jam</p>
            </div>
            <div class="text-center p-3 rounded-xl" :class="getStatusColor(selectedAttendance.status)">
              <p class="text-xs mb-1 opacity-70">Status</p>
              <p class="font-bold">{{ getStatusLabel(selectedAttendance.status) }}</p>
            </div>
          </div>

          <div v-if="selectedAttendance.check_in_photo || selectedAttendance.check_out_photo"
            class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-if="selectedAttendance.check_in_photo" class="border border-slate-200 rounded-xl overflow-hidden">
              <div class="bg-slate-50 px-3 py-2 border-b border-slate-200 flex items-center gap-2">
                <Image class="w-4 h-4 text-slate-400" />
                <span class="text-xs font-semibold text-slate-600">Foto Check-In</span>
              </div>
              <img :src="selectedAttendance.check_in_photo" alt="Check-in Photo" class="w-full h-48 object-cover" />
            </div>
            <div v-if="selectedAttendance.check_out_photo" class="border border-slate-200 rounded-xl overflow-hidden">
              <div class="bg-slate-50 px-3 py-2 border-b border-slate-200 flex items-center gap-2">
                <Image class="w-4 h-4 text-slate-400" />
                <span class="text-xs font-semibold text-slate-600">Foto Check-Out</span>
              </div>
              <img :src="selectedAttendance.check_out_photo" alt="Check-out Photo" class="w-full h-48 object-cover" />
            </div>
          </div>

          <div v-if="selectedAttendance.check_in_latitude && selectedAttendance.check_in_longitude"
            class="border border-slate-200 rounded-xl p-4">
            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center gap-2">
                <MapPin class="w-4 h-4 text-primary" />
                <span class="font-semibold text-slate-900">Lokasi Check-In</span>
              </div>
              <button
                @click="openGoogleMaps(selectedAttendance.check_in_latitude, selectedAttendance.check_in_longitude)"
                class="text-xs text-primary hover:underline cursor-pointer">
                Buka di Google Maps →
              </button>
            </div>
            <div class="grid grid-cols-3 gap-4 text-sm">
              <div>
                <p class="text-xs text-slate-500">Latitude</p>
                <p class="font-mono text-slate-700">{{ selectedAttendance.check_in_latitude }}</p>
              </div>
              <div>
                <p class="text-xs text-slate-500">Longitude</p>
                <p class="font-mono text-slate-700">{{ selectedAttendance.check_in_longitude }}</p>
              </div>
              <div>
                <p class="text-xs text-slate-500">Jarak dari Kantor</p>
                <p class="font-mono text-slate-700">{{ selectedAttendance.check_in_distance || '-' }} m</p>
              </div>
            </div>
          </div>

          <div v-if="selectedAttendance.notes" class="border border-blue-200 rounded-xl p-4 bg-blue-50">
            <div class="flex items-center gap-2 mb-2">
              <FileText class="w-4 h-4 text-blue-600" />
              <span class="font-semibold text-blue-800">Catatan</span>
            </div>
            <p class="text-sm text-blue-700">{{ selectedAttendance.notes }}</p>
          </div>

          <div class="flex items-center gap-4 text-xs text-slate-400">
            <span class="flex items-center gap-1">
              <component :is="selectedAttendance.attendance_mode === 'WFO' ? Building2 : Home" class="w-3.5 h-3.5" />
              {{ selectedAttendance.attendance_mode === 'WFO' ? 'Work From Office' : 'Work From Home' }}
            </span>
          </div>
        </div>

        <div class="p-5 border-t border-slate-200 bg-slate-50">
          <button @click="closeDetail"
            class="w-full px-4 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-semibold hover:bg-slate-100 cursor-pointer">Tutup</button>
        </div>
      </div>
    </div>
  </Teleport>
</template>
