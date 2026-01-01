<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import {
    Shield,
    Search,
    Filter,
    ChevronLeft,
    ChevronRight,
    User,
    Clock,
    Globe,
    FileText,
    Eye,
    X
} from 'lucide-vue-next';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    logs: Object,
    filters: Object,
    availableActions: Object,
    users: Array,
    modelTypes: Array,
});

// Filter state
const search = ref(props.filters?.search || '');
const selectedAction = ref(props.filters?.action || '');
const selectedUser = ref(props.filters?.user_id || '');
const selectedModelType = ref(props.filters?.model_type || '');
const startDate = ref(props.filters?.start_date || '');
const endDate = ref(props.filters?.end_date || '');

// Modal state
const showDetailModal = ref(false);
const selectedLog = ref(null);

// Debounced search
let searchTimeout = null;
watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
});

const applyFilters = () => {
    router.get(route('admin.audit'), {
        search: search.value || undefined,
        action: selectedAction.value || undefined,
        user_id: selectedUser.value || undefined,
        model_type: selectedModelType.value || undefined,
        start_date: startDate.value || undefined,
        end_date: endDate.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    search.value = '';
    selectedAction.value = '';
    selectedUser.value = '';
    selectedModelType.value = '';
    startDate.value = '';
    endDate.value = '';
    router.get(route('admin.audit'));
};

const hasActiveFilters = computed(() => {
    return search.value || selectedAction.value || selectedUser.value ||
        selectedModelType.value || startDate.value || endDate.value;
});

const viewDetails = (log) => {
    selectedLog.value = log;
    showDetailModal.value = true;
};

const closeModal = () => {
    showDetailModal.value = false;
    selectedLog.value = null;
};

const getActionColor = (action) => {
    const colors = {
        'created': 'bg-emerald-100 text-emerald-700',
        'updated': 'bg-blue-100 text-blue-700',
        'deleted': 'bg-red-100 text-red-700',
        'restored': 'bg-purple-100 text-purple-700',
        'login': 'bg-cyan-100 text-cyan-700',
        'logout': 'bg-slate-100 text-slate-700',
        'login_failed': 'bg-amber-100 text-amber-700',
        'password_reset': 'bg-orange-100 text-orange-700',
        'export': 'bg-indigo-100 text-indigo-700',
        'import': 'bg-teal-100 text-teal-700',
    };
    return colors[action] || 'bg-slate-100 text-slate-700';
};

const getMethodColor = (method) => {
    const colors = {
        'GET': 'text-emerald-600',
        'POST': 'text-blue-600',
        'PUT': 'text-amber-600',
        'PATCH': 'text-orange-600',
        'DELETE': 'text-red-600',
    };
    return colors[method] || 'text-slate-600';
};

const formatValue = (value) => {
    if (value === null || value === undefined) return '-';
    if (typeof value === 'object') return JSON.stringify(value, null, 2);
    if (typeof value === 'boolean') return value ? 'Ya' : 'Tidak';
    return String(value);
};
</script>

<template>

    <Head title="Audit Log" />

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center">
                    <Shield class="w-6 h-6 text-primary" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Audit Log</h1>
                    <p class="text-sm text-slate-500">Rekaman aktivitas sistem</p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center gap-2 mb-4">
                <Filter class="w-5 h-5 text-slate-500" />
                <h2 class="font-semibold text-slate-800">Filter</h2>
                <button v-if="hasActiveFilters" @click="clearFilters"
                    class="ml-auto text-sm text-red-600 hover:text-red-700 flex items-center gap-1">
                    <X class="w-4 h-4" />
                    Reset Filter
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="lg:col-span-2">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Cari</label>
                    <div class="relative">
                        <Search class="absolute left-3 top-2.5 w-4 h-4 text-slate-400" />
                        <input v-model="search" type="text" placeholder="Cari user, IP, atau URL..."
                            class="w-full h-10 pl-10 pr-4 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" />
                    </div>
                </div>

                <!-- Action Filter -->
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Aksi</label>
                    <select v-model="selectedAction" @change="applyFilters"
                        class="w-full h-10 px-3 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-primary">
                        <option value="">Semua Aksi</option>
                        <option v-for="(label, key) in availableActions" :key="key" :value="key">
                            {{ label }}
                        </option>
                    </select>
                </div>

                <!-- User Filter -->
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">User</label>
                    <select v-model="selectedUser" @change="applyFilters"
                        class="w-full h-10 px-3 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-primary">
                        <option value="">Semua User</option>
                        <option v-for="user in users" :key="user.id" :value="user.id">
                            {{ user.name }}
                        </option>
                    </select>
                </div>

                <!-- Model Type Filter -->
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Model</label>
                    <select v-model="selectedModelType" @change="applyFilters"
                        class="w-full h-10 px-3 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-primary">
                        <option value="">Semua Model</option>
                        <option v-for="type in modelTypes" :key="type.value" :value="type.label">
                            {{ type.label }}
                        </option>
                    </select>
                </div>

                <!-- Start Date -->
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Dari Tanggal</label>
                    <input v-model="startDate" @change="applyFilters" type="date"
                        class="w-full h-10 px-3 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-primary" />
                </div>

                <!-- End Date -->
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Sampai Tanggal</label>
                    <input v-model="endDate" @change="applyFilters" type="date"
                        class="w-full h-10 px-3 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-primary" />
                </div>
            </div>
        </div>

        <!-- Logs Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                                Waktu</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                                User</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                                Aksi</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                                Model</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">IP
                                Address</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">
                                Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="log in logs.data" :key="log.id" class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <Clock class="w-4 h-4 text-slate-400" />
                                    <div>
                                        <p class="text-sm font-medium text-slate-900">{{ log.created_at_human }}</p>
                                        <p class="text-xs text-slate-500">{{ log.created_at }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div v-if="log.user" class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                                        <User class="w-4 h-4 text-primary" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-slate-900">{{ log.user.name }}</p>
                                        <p class="text-xs text-slate-500">{{ log.user.email }}</p>
                                    </div>
                                </div>
                                <span v-else class="text-sm text-slate-400">System</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold"
                                    :class="getActionColor(log.action)">
                                    {{ log.action_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <FileText class="w-4 h-4 text-slate-400" />
                                    <div>
                                        <p class="text-sm font-medium text-slate-900">{{ log.model_type }}</p>
                                        <p v-if="log.model_id" class="text-xs text-slate-500">ID: {{ log.model_id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <Globe class="w-4 h-4 text-slate-400" />
                                    <span class="text-sm text-slate-600">{{ log.ip_address || '-' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button @click="viewDetails(log)"
                                    class="p-2 rounded-lg hover:bg-slate-100 transition-colors text-slate-500 hover:text-primary">
                                    <Eye class="w-5 h-5" />
                                </button>
                            </td>
                        </tr>
                        <tr v-if="logs.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center">
                                <Shield class="w-12 h-12 text-slate-300 mx-auto mb-3" />
                                <p class="text-slate-500 font-medium">Tidak ada log ditemukan</p>
                                <p class="text-sm text-slate-400">Coba ubah filter pencarian</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="logs.data.length > 0"
                class="px-6 py-4 border-t border-slate-200 flex items-center justify-between">
                <p class="text-sm text-slate-600">
                    Menampilkan {{ logs.from }} - {{ logs.to }} dari {{ logs.total }} log
                </p>
                <div class="flex items-center gap-2">
                    <button @click="router.get(logs.prev_page_url)" :disabled="!logs.prev_page_url"
                        class="p-2 rounded-lg border border-slate-200 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        <ChevronLeft class="w-5 h-5" />
                    </button>
                    <span class="px-3 py-1 text-sm font-medium text-slate-700">
                        {{ logs.current_page }} / {{ logs.last_page }}
                    </span>
                    <button @click="router.get(logs.next_page_url)" :disabled="!logs.next_page_url"
                        class="p-2 rounded-lg border border-slate-200 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        <ChevronRight class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <Teleport to="body">
        <div v-if="showDetailModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50" @click="closeModal"></div>
                <div class="relative bg-white rounded-2xl shadow-xl max-w-3xl w-full max-h-[90vh] overflow-hidden">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                                <FileText class="w-5 h-5 text-primary" />
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900">Detail Audit Log</h3>
                                <p class="text-xs text-slate-500">{{ selectedLog?.created_at }}</p>
                            </div>
                        </div>
                        <button @click="closeModal" class="p-2 rounded-lg hover:bg-slate-100 transition-colors">
                            <X class="w-5 h-5 text-slate-500" />
                        </button>
                    </div>

                    <!-- Modal Content -->
                    <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)] space-y-6">
                        <!-- Info Grid -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-4 bg-slate-50 rounded-xl">
                                <p class="text-xs text-slate-500 mb-1">User</p>
                                <p class="font-medium text-slate-900">{{ selectedLog?.user?.name || 'System' }}</p>
                                <p class="text-xs text-slate-500">{{ selectedLog?.user?.email }}</p>
                            </div>
                            <div class="p-4 bg-slate-50 rounded-xl">
                                <p class="text-xs text-slate-500 mb-1">Aksi</p>
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold"
                                    :class="getActionColor(selectedLog?.action)">
                                    {{ selectedLog?.action_label }}
                                </span>
                            </div>
                            <div class="p-4 bg-slate-50 rounded-xl">
                                <p class="text-xs text-slate-500 mb-1">Model</p>
                                <p class="font-medium text-slate-900">{{ selectedLog?.model_type }}</p>
                                <p class="text-xs text-slate-500">ID: {{ selectedLog?.model_id }}</p>
                            </div>
                            <div class="p-4 bg-slate-50 rounded-xl">
                                <p class="text-xs text-slate-500 mb-1">IP Address</p>
                                <p class="font-medium text-slate-900">{{ selectedLog?.ip_address || '-' }}</p>
                            </div>
                        </div>

                        <!-- Request Info -->
                        <div class="p-4 bg-slate-50 rounded-xl">
                            <p class="text-xs text-slate-500 mb-2">Request</p>
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-sm font-bold" :class="getMethodColor(selectedLog?.method)">
                                    {{ selectedLog?.method }}
                                </span>
                                <span class="text-sm text-slate-600 truncate">{{ selectedLog?.url }}</span>
                            </div>
                        </div>

                        <!-- Changes -->
                        <div v-if="selectedLog?.changes && Object.keys(selectedLog.changes).length > 0">
                            <h4 class="font-semibold text-slate-900 mb-3">Perubahan</h4>
                            <div class="border border-slate-200 rounded-xl overflow-hidden">
                                <table class="w-full text-sm">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left font-bold text-slate-600">Field</th>
                                            <th class="px-4 py-2 text-left font-bold text-slate-600">Nilai Lama</th>
                                            <th class="px-4 py-2 text-left font-bold text-slate-600">Nilai Baru</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <tr v-for="(change, field) in selectedLog.changes" :key="field">
                                            <td class="px-4 py-2 font-medium text-slate-900">{{ field }}</td>
                                            <td class="px-4 py-2 text-red-600 font-mono text-xs bg-red-50">
                                                {{ formatValue(change.old) }}
                                            </td>
                                            <td class="px-4 py-2 text-emerald-600 font-mono text-xs bg-emerald-50">
                                                {{ formatValue(change.new) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- User Agent -->
                        <div class="p-4 bg-slate-50 rounded-xl">
                            <p class="text-xs text-slate-500 mb-2">User Agent</p>
                            <p class="text-sm text-slate-600 break-all">{{ selectedLog?.user_agent || '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
