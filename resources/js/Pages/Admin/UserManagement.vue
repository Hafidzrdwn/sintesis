<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import BaseButton from '@/Components/BaseButton.vue';
import { getInitials, getAvatarUrl, formatDateTime } from '@/utils/helpers';
import {
    Search,
    ChevronLeft,
    ChevronRight,
    Eye,
    EyeOff,
    Edit,
    Trash2,
    Plus,
    Users,
    UserCheck,
    User,
    Shield,
    GraduationCap,
    Briefcase,
    Mail,
    Phone,
    Calendar,
    X,
    BriefcaseBusiness,
    UserX,
} from 'lucide-vue-next';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    users: Object,
    stats: Object,
    roleOptions: Array,
    statusOptions: Array,
    jobs: Array,
    mentors: Array,
    filters: Object,
});

const searchQuery = ref(props.filters?.search || '');
const roleFilter = ref(props.filters?.role || 'all');
const statusFilter = ref(props.filters?.status || 'all');
const selectedUser = ref(null);
const showDetailModal = ref(false);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const isLoading = ref(false);

const createForm = useForm({
    name: '',
    username: '',
    email: '',
    phone: '',
    password: '',
    role: '',
    job_id: '',
    custom_position: '',
    use_custom_position: false,
    mentor_id: '',
    start_date: '',
    end_date: '',
    status: 'active',
});
const showCreatePassword = ref(false);

const editForm = useForm({
    name: '',
    username: '',
    email: '',
    phone: '',
    password: '',
    role: '',
    job_id: '',
    custom_position: '',
    use_custom_position: false,
    mentor_id: '',
    start_date: '',
    end_date: '',
    status: '',
});
const showEditPassword = ref(false);

let searchTimeout = null;
watch(searchQuery, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
});

watch([roleFilter, statusFilter], () => {
    applyFilters();
});

const applyFilters = () => {
    isLoading.value = true;
    router.get(route('admin.users'), {
        search: searchQuery.value || undefined,
        role: roleFilter.value !== 'all' ? roleFilter.value : undefined,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => isLoading.value = false,
    });
};

const openDetail = (user) => {
    selectedUser.value = user;
    showDetailModal.value = true;
};

const closeDetail = () => {
    showDetailModal.value = false;
    setTimeout(() => selectedUser.value = null, 300);
};

const openCreate = () => {
    createForm.reset();
    showCreateModal.value = true;
};

const closeCreate = () => {
    showCreateModal.value = false;
    createForm.reset();
};

const openEdit = (user) => {
    selectedUser.value = user;
    editForm.name = user.name;
    editForm.username = user.username || '';
    editForm.email = user.email;
    editForm.phone = user.phone || '';
    editForm.password = '';
    showEditPassword.value = false;
    if (user.role === 'intern') {
        editForm.role = user.has_active_internship ? 'active_intern' : 'candidate';
    } else {
        editForm.role = user.role;
    }
    const activeInternship = user.internships_as_intern?.[0];
    editForm.job_id = activeInternship?.job_id || '';
    editForm.custom_position = activeInternship?.custom_position || '';
    editForm.use_custom_position = !!activeInternship?.custom_position && !activeInternship?.job_id;
    editForm.mentor_id = activeInternship?.mentor_id || '';
    const startDate = activeInternship?.start_date || '';
    const endDate = activeInternship?.end_date || '';
    editForm.start_date = startDate.includes('T') ? startDate.split('T')[0] : startDate;
    editForm.end_date = endDate.includes('T') ? endDate.split('T')[0] : endDate;
    editForm.status = user.status;
    showEditModal.value = true;
};

const closeEdit = () => {
    showEditModal.value = false;
    editForm.reset();
    setTimeout(() => selectedUser.value = null, 300);
};

const openDelete = (user) => {
    selectedUser.value = user;
    showDeleteModal.value = true;
};

const closeDelete = () => {
    showDeleteModal.value = false;
    setTimeout(() => selectedUser.value = null, 300);
};

const submitCreate = () => {
    createForm.post(route('admin.users.store'), {
        preserveScroll: true,
        onSuccess: () => closeCreate(),
    });
};

const submitEdit = () => {
    editForm.put(route('admin.users.update', selectedUser.value.id), {
        preserveScroll: true,
        onSuccess: () => closeEdit(),
    });
};

const confirmDelete = () => {
    router.delete(route('admin.users.destroy', selectedUser.value.id), {
        preserveScroll: true,
        onSuccess: () => closeDelete(),
    });
};

const getRoleConfig = (user) => {
    let roleConfigs = [];
    const configs = {
        activeIntern: {
            label: 'Intern Aktif',
            color: 'bg-emerald-100 text-emerald-700 border-emerald-200',
            icon: GraduationCap
        },
        candidate: {
            label: 'Kandidat',
            color: 'bg-amber-100 text-amber-700 border-amber-200',
            icon: User
        },
        alumni: {
            label: 'Alumni Inosoft',
            color: 'bg-primary text-white border-primary/10',
            icon: BriefcaseBusiness
        },
        admin: {
            label: 'Admin',
            color: 'bg-purple-100 text-purple-700 border-purple-200',
            icon: Shield
        },
        mentor: {
            label: 'Mentor',
            color: 'bg-blue-100 text-blue-700 border-blue-200',
            icon: Briefcase
        },
        terminated: {
            label: 'Pernah Diberhentikan',
            color: 'bg-red-100 text-red-700 border-red-200',
            icon: UserX
        },
        unknown: {
            label: 'Unknown',
            color: 'bg-slate-100 text-slate-700 border-slate-200',
            icon: User
        }
    }

    if (user.role === 'intern') {
        if (user.has_active_internship) {
            roleConfigs.push(configs.activeIntern)
        } else {
            roleConfigs.push(configs.candidate);

            if (user.is_alumni) {
                roleConfigs.push(configs.alumni);
            }

            if (user.internships_as_intern?.[0].status === 'terminated') {
                roleConfigs.push(configs.terminated);
            }
        }

        return roleConfigs;
    }

    roleConfigs.push(configs[user.role] || configs.unknown);
    return roleConfigs;
};

const getStatusConfig = (status) => {
    const configs = {
        active: { label: 'Aktif', color: 'bg-success/10 text-success border-success/20' },
        inactive: { label: 'Tidak Aktif', color: 'bg-danger/10 text-danger border-danger/20' },
    };
    return configs[status] || configs.inactive;
};
</script>

<template>

    <Head title="Manajemen User" />

    <div class="flex flex-col gap-6">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen User</h1>
                <p class="text-slate-500">Kelola semua user dalam sistem SINTESIS.</p>
            </div>
            <BaseButton variant="primary" @click="openCreate">
                <Plus class="w-4 h-4" />
                Tambah User
            </BaseButton>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-slate-100 rounded-lg">
                        <Users class="w-5 h-5 text-slate-600" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-slate-900">{{ stats.total }}</p>
                        <p class="text-xs text-slate-500">Total User</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <Shield class="w-5 h-5 text-purple-600" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-slate-900">{{ stats.admin }}</p>
                        <p class="text-xs text-slate-500">Admin</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <Briefcase class="w-5 h-5 text-blue-600" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-slate-900">{{ stats.mentor }}</p>
                        <p class="text-xs text-slate-500">Mentor</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-amber-100 rounded-lg">
                        <User class="w-5 h-5 text-amber-600" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-slate-900">{{ stats.candidate }}</p>
                        <p class="text-xs text-slate-500">Kandidat</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-emerald-100 rounded-lg">
                        <GraduationCap class="w-5 h-5 text-emerald-600" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-slate-900">{{ stats.active_intern }}</p>
                        <p class="text-xs text-slate-500">Intern Aktif</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-success/10 rounded-lg">
                        <UserCheck class="w-5 h-5 text-success" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-slate-900">{{ stats.active }}</p>
                        <p class="text-xs text-slate-500">Aktif</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div
            class="flex flex-col sm:flex-row justify-between gap-4 bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <div class="relative w-full sm:w-72">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                    <input v-model="searchQuery" type="text" placeholder="Cari nama, email, username..."
                        class="w-full pl-10 pr-4 py-2 rounded-xl border-slate-200 focus:border-primary focus:ring-primary text-sm">
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <select v-model="roleFilter"
                    class="px-4 py-2 rounded-xl border-slate-200 focus:border-primary focus:ring-primary text-sm font-medium text-slate-600 bg-slate-50 cursor-pointer">
                    <option value="all">Semua Role</option>
                    <option v-for="role in roleOptions" :key="role.value" :value="role.value">{{ role.label }}</option>
                </select>
                <select v-model="statusFilter"
                    class="px-4 py-2 rounded-xl border-slate-200 focus:border-primary focus:ring-primary text-sm font-medium text-slate-600 bg-slate-50 cursor-pointer">
                    <option value="all">Semua Status</option>
                    <option v-for="status in statusOptions" :key="status.value" :value="status.value">{{ status.label }}
                    </option>
                </select>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 font-bold text-slate-700">User</th>
                            <th class="px-6 py-4 font-bold text-slate-700">Username</th>
                            <th class="px-6 py-4 font-bold text-slate-700">Role</th>
                            <th class="px-6 py-4 font-bold text-slate-700">Status</th>
                            <th class="px-6 py-4 font-bold text-slate-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="user in users.data" :key="user.id"
                            class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <img v-if="getAvatarUrl(user)" :src="getAvatarUrl(user)" :alt="user.name"
                                        class="w-10 h-10 rounded-full object-cover ring-2 ring-white shadow-sm object-top" />
                                    <div v-else
                                        class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xs">
                                        {{ getInitials(user.name) }}
                                    </div>
                                    <div>
                                        <div
                                            class="font-bold text-slate-900 group-hover:text-primary transition-colors">
                                            {{ user.name }}</div>
                                        <div class="text-xs text-slate-500">{{ user.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                <span v-if="user.username" class="font-medium">@{{ user.username }}</span>
                                <span v-else class="text-slate-400">-</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-2"> <span v-for="(role, index) in getRoleConfig(user)"
                                        :key="index"
                                        class="px-3 py-1 rounded-full text-xs font-bold border inline-flex items-center gap-1"
                                        :class="role.color">
                                        <component :is="role.icon" class="w-3 h-3" />
                                        {{ role.label }}

                                        <template v-if="user.intern_position && role.label === 'Intern Aktif'">
                                            ({{ user.intern_position }})
                                        </template>
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 rounded-full text-xs font-bold border"
                                    :class="getStatusConfig(user.status).color">
                                    {{ getStatusConfig(user.status).label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="openDetail(user)"
                                        class="p-2 text-slate-400 hover:text-primary hover:bg-primary/5 rounded-lg transition-all cursor-pointer">
                                        <Eye class="w-4 h-4" />
                                    </button>
                                    <button @click="openEdit(user)"
                                        class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all cursor-pointer">
                                        <Edit class="w-4 h-4" />
                                    </button>
                                    <button @click="openDelete(user)"
                                        class="p-2 text-slate-400 hover:text-danger hover:bg-danger/5 rounded-lg transition-all cursor-pointer">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="users.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                Tidak ada data user ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="border-t border-slate-200 px-6 py-4 flex items-center justify-between">
                <div class="text-xs text-slate-500">
                    Menampilkan <span class="font-bold text-slate-900">{{ users.from || 0 }}</span> - <span
                        class="font-bold text-slate-900">{{ users.to || 0 }}</span> dari <span
                        class="font-bold text-slate-900">{{
                            users.total }}</span> data
                </div>
                <div class="flex gap-2">
                    <button @click="router.get(users.prev_page_url)" :disabled="!users.prev_page_url"
                        class="p-2 border border-slate-200 rounded-lg hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                        <ChevronLeft class="w-4 h-4 text-slate-600" />
                    </button>
                    <button @click="router.get(users.next_page_url)" :disabled="!users.next_page_url"
                        class="p-2 border border-slate-200 rounded-lg hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                        <ChevronRight class="w-4 h-4 text-slate-600" />
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="showDetailModal && selectedUser" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="closeDetail"></div>
                <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="font-bold text-lg text-slate-800">Detail User</h3>
                        <button @click="closeDetail"
                            class="p-1 cursor-pointer rounded-full hover:bg-slate-200 transition-colors">
                            <X class="w-6 h-6 text-slate-400" />
                        </button>
                    </div>

                    <div class="p-6 flex flex-col gap-6">
                        <div class="flex items-center gap-4">
                            <img v-if="getAvatarUrl(selectedUser)" :src="getAvatarUrl(selectedUser)"
                                :alt="selectedUser.name"
                                class="w-16 h-16 rounded-full object-cover border-4 border-white shadow-sm object-top" />
                            <div v-else
                                class="w-16 h-16 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xl border-4 border-white shadow-sm">
                                {{ getInitials(selectedUser.name) }}
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-slate-900">{{ selectedUser.name }}</h2>
                                <p v-if="selectedUser.username" class="text-slate-500 font-medium">@{{
                                    selectedUser.username }}</p>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <span v-for="(role, index) in getRoleConfig(selectedUser)" :key="index"
                                class="px-3 py-1 rounded-full text-xs font-bold border inline-flex items-center gap-1"
                                :class="role.color">
                                <component :is="role.icon" class="w-3 h-3" />
                                {{ role.label }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-bold border"
                                :class="getStatusConfig(selectedUser.status).color">
                                {{ getStatusConfig(selectedUser.status).label }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 gap-3 text-sm">
                            <div class="flex items-center gap-3 text-slate-600">
                                <GraduationCap class="w-5 h-5 text-slate-400" />
                                <span>{{ selectedUser.university || '-' }}</span>
                            </div>
                            <div class="flex items-center gap-3 text-slate-600">
                                <Mail class="w-4 h-4 text-slate-400" />
                                <span>{{ selectedUser.email }}</span>
                            </div>
                            <div class="flex items-center gap-3 text-slate-600">
                                <Phone class="w-4 h-4 text-slate-400" />
                                <span>{{ selectedUser.phone || '-' }}</span>
                            </div>
                            <div class="flex items-center gap-3 text-slate-600">
                                <Calendar class="w-4 h-4 text-slate-400" />
                                <span>Bergabung {{ formatDateTime(selectedUser.created_at) }}</span>
                            </div>
                        </div>

                        <div class="flex gap-3 pt-4 border-t border-slate-100">
                            <BaseButton variant="secondary" full @click="closeDetail">Tutup</BaseButton>
                            <BaseButton variant="primary" full @click="closeDetail(); openEdit(selectedUser)">
                                <Edit class="w-4 h-4" />
                                Edit User
                            </BaseButton>
                        </div>
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
                <div
                    class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
                    <div
                        class="bg-primary/5 px-6 py-4 border-b border-primary/10 flex items-center justify-between shrink-0">
                        <h3 class="font-bold text-lg text-primary">Tambah User Baru</h3>
                        <button @click="closeCreate"
                            class="p-1 cursor-pointer rounded-full hover:bg-primary/10 transition-colors">
                            <X class="w-6 h-6 text-primary" />
                        </button>
                    </div>

                    <form @submit.prevent="submitCreate" class="p-6 flex flex-col gap-4 overflow-y-auto">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Lengkap <span
                                    class="text-danger">*</span></label>
                            <input v-model="createForm.name" type="text"
                                class="w-full h-12 px-4 text-base rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary focus:ring-2"
                                placeholder="Masukkan nama lengkap" />
                            <p v-if="createForm.errors.name" class="mt-1 text-xs text-danger">{{ createForm.errors.name
                                }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Username</label>
                            <input v-model="createForm.username" type="text"
                                class="w-full h-12 px-4 text-base rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary focus:ring-2"
                                placeholder="username" />
                            <p v-if="createForm.errors.username" class="mt-1 text-xs text-danger">{{
                                createForm.errors.username }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email <span
                                    class="text-danger">*</span></label>
                            <input v-model="createForm.email" type="email"
                                class="w-full h-12 px-4 text-base rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary focus:ring-2"
                                placeholder="email@example.com" />
                            <p v-if="createForm.errors.email" class="mt-1 text-xs text-danger">{{
                                createForm.errors.email }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Telepon</label>
                            <input v-model="createForm.phone" type="text"
                                class="w-full h-12 px-4 text-base rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary focus:ring-2"
                                placeholder="08xxxxxxxxxx" />
                            <p v-if="createForm.errors.phone" class="mt-1 text-xs text-danger">{{
                                createForm.errors.phone }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Password <span
                                    class="text-danger">*</span></label>
                            <div class="relative">
                                <input v-model="createForm.password" :type="showCreatePassword ? 'text' : 'password'"
                                    class="w-full h-12 px-4 pr-12 text-base rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary focus:ring-2"
                                    placeholder="Minimal 8 karakter" />
                                <button type="button" @click="showCreatePassword = !showCreatePassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                    <EyeOff v-if="showCreatePassword" class="w-5 h-5" />
                                    <Eye v-else class="w-5 h-5" />
                                </button>
                            </div>
                            <p v-if="createForm.errors.password" class="mt-1 text-xs text-danger">{{
                                createForm.errors.password }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Role <span
                                        class="text-danger">*</span></label>
                                <select v-model="createForm.role"
                                    class="w-full h-12 px-4 text-base rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary focus:ring-2">
                                    <option value="" disabled>Pilih Role</option>
                                    <option v-for="role in roleOptions" :key="role.value" :value="role.value">{{
                                        role.label }}</option>
                                </select>
                                <p v-if="createForm.errors.role" class="mt-1 text-xs text-danger">{{
                                    createForm.errors.role }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Status <span
                                        class="text-danger">*</span></label>
                                <select v-model="createForm.status"
                                    class="w-full h-12 px-4 text-base rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary focus:ring-2">
                                    <option v-for="status in statusOptions" :key="status.value" :value="status.value">{{
                                        status.label }}</option>
                                </select>
                                <p v-if="createForm.errors.status" class="mt-1 text-xs text-danger">{{
                                    createForm.errors.status }}</p>
                            </div>
                        </div>

                        <!-- Job/Position Select (shown when role is active_intern) -->
                        <div v-if="createForm.role === 'active_intern'" class="flex flex-col gap-3">
                            <!-- Toggle for custom position -->
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="createForm.use_custom_position"
                                    class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary" />
                                <span class="text-sm text-slate-600">Gunakan posisi custom</span>
                            </label>

                            <!-- Job Select (when NOT using custom) -->
                            <div v-if="!createForm.use_custom_position">
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Posisi dari Job <span
                                        class="text-danger">*</span></label>
                                <select v-model="createForm.job_id"
                                    class="w-full h-12 px-4 text-base rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary focus:ring-2">
                                    <option value="" disabled>Pilih Posisi</option>
                                    <option v-for="job in jobs" :key="job.id" :value="job.id">{{ job.title }}</option>
                                </select>
                                <p v-if="createForm.errors.job_id" class="mt-1 text-xs text-danger">{{
                                    createForm.errors.job_id }}</p>
                            </div>

                            <!-- Custom Position Input (when using custom) -->
                            <div v-else>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Posisi Custom <span
                                        class="text-danger">*</span></label>
                                <input v-model="createForm.custom_position" type="text"
                                    class="w-full h-12 px-4 text-base rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary focus:ring-2"
                                    placeholder="Masukkan nama posisi" />
                                <p v-if="createForm.errors.custom_position" class="mt-1 text-xs text-danger">{{
                                    createForm.errors.custom_position }}</p>
                            </div>

                            <!-- Mentor Select -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Mentor <span
                                        class="text-danger">*</span></label>
                                <select v-model="createForm.mentor_id"
                                    class="w-full h-12 px-4 text-base rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary focus:ring-2">
                                    <option value="" disabled>Pilih Mentor</option>
                                    <option v-for="mentor in mentors" :key="mentor.id" :value="mentor.id">{{ mentor.name
                                        }}</option>
                                </select>
                                <p v-if="createForm.errors.mentor_id" class="mt-1 text-xs text-danger">{{
                                    createForm.errors.mentor_id }}</p>
                            </div>

                            <!-- Date Range -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tanggal Mulai <span
                                            class="text-danger">*</span></label>
                                    <input v-model="createForm.start_date" type="date"
                                        class="w-full h-12 px-4 text-base rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary focus:ring-2" />
                                    <p v-if="createForm.errors.start_date" class="mt-1 text-xs text-danger">{{
                                        createForm.errors.start_date }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tanggal Selesai
                                        <span class="text-danger">*</span></label>
                                    <input v-model="createForm.end_date" type="date"
                                        class="w-full h-12 px-4 text-base rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary focus:ring-2" />
                                    <p v-if="createForm.errors.end_date" class="mt-1 text-xs text-danger">{{
                                        createForm.errors.end_date }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-sm text-blue-700">
                            <strong>Catatan:</strong> User yang dibuat oleh admin akan otomatis terverifikasi email-nya.
                        </div>

                        <div class="flex gap-3 pt-4 border-t border-slate-100">
                            <BaseButton type="button" variant="secondary" full @click="closeCreate">Batal</BaseButton>
                            <BaseButton type="submit" variant="primary" full :disabled="createForm.processing">
                                <Plus class="w-4 h-4" />
                                {{ createForm.processing ? 'Menyimpan...' : 'Tambah User' }}
                            </BaseButton>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </Teleport>

    <!-- Edit Modal -->
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="showEditModal && selectedUser" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="closeEdit"></div>
                <div
                    class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
                    <div
                        class="bg-blue-50 px-6 py-4 border-b border-blue-100 flex items-center justify-between shrink-0">
                        <h3 class="font-bold text-lg text-blue-700">Edit User</h3>
                        <button @click="closeEdit"
                            class="p-1 cursor-pointer rounded-full hover:bg-blue-100 transition-colors">
                            <X class="w-6 h-6 text-blue-600" />
                        </button>
                    </div>

                    <form @submit.prevent="submitEdit" class="p-6 flex flex-col gap-4 overflow-y-auto">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Lengkap <span
                                    class="text-danger">*</span></label>
                            <input v-model="editForm.name" type="text"
                                class="w-full h-12 px-4 text-base rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary focus:ring-2" />
                            <p v-if="editForm.errors.name" class="mt-1 text-xs text-danger">{{ editForm.errors.name }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Username</label>
                            <input v-model="editForm.username" type="text"
                                class="w-full h-12 px-4 text-base rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary focus:ring-2" />
                            <p v-if="editForm.errors.username" class="mt-1 text-xs text-danger">{{
                                editForm.errors.username }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email <span
                                    class="text-danger">*</span></label>
                            <input v-model="editForm.email" type="email"
                                class="w-full h-12 px-4 text-base rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary focus:ring-2" />
                            <p v-if="editForm.errors.email" class="mt-1 text-xs text-danger">{{ editForm.errors.email }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Telepon</label>
                            <input v-model="editForm.phone" type="text"
                                class="w-full h-12 px-4 text-base rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary focus:ring-2" />
                            <p v-if="editForm.errors.phone" class="mt-1 text-xs text-danger">{{ editForm.errors.phone }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Password Baru</label>
                            <div class="relative">
                                <input v-model="editForm.password" :type="showEditPassword ? 'text' : 'password'"
                                    class="w-full h-12 px-4 pr-12 text-base rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary focus:ring-2"
                                    placeholder="Kosongkan jika tidak ingin mengubah" />
                                <button type="button" @click="showEditPassword = !showEditPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                    <EyeOff v-if="showEditPassword" class="w-5 h-5" />
                                    <Eye v-else class="w-5 h-5" />
                                </button>
                            </div>
                            <p v-if="editForm.errors.password" class="mt-1 text-xs text-danger">{{
                                editForm.errors.password }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Role <span
                                        class="text-danger">*</span></label>
                                <select v-model="editForm.role"
                                    class="w-full h-12 px-4 text-base rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary focus:ring-2">
                                    <option value="" disabled>Pilih Role</option>
                                    <option v-for="role in roleOptions" :key="role.value" :value="role.value">{{
                                        role.label }}</option>
                                </select>
                                <p v-if="editForm.errors.role" class="mt-1 text-xs text-danger">{{ editForm.errors.role
                                    }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Status <span
                                        class="text-danger">*</span></label>
                                <select v-model="editForm.status"
                                    class="w-full h-12 px-4 text-base rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary focus:ring-2">
                                    <option v-for="status in statusOptions" :key="status.value" :value="status.value">{{
                                        status.label }}</option>
                                </select>
                                <p v-if="editForm.errors.status" class="mt-1 text-xs text-danger">{{
                                    editForm.errors.status }}</p>
                            </div>
                        </div>

                        <!-- Job/Position Select (shown when role is active_intern) -->
                        <div v-if="editForm.role === 'active_intern'" class="flex flex-col gap-3">
                            <!-- Toggle for custom position -->
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="editForm.use_custom_position"
                                    class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary" />
                                <span class="text-sm text-slate-600">Gunakan posisi custom</span>
                            </label>

                            <!-- Job Select (when NOT using custom) -->
                            <div v-if="!editForm.use_custom_position">
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Posisi dari Job <span
                                        class="text-danger">*</span></label>
                                <select v-model="editForm.job_id"
                                    class="w-full h-12 px-4 text-base rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary focus:ring-2">
                                    <option value="" disabled>Pilih Posisi</option>
                                    <option v-for="job in jobs" :key="job.id" :value="job.id">{{ job.title }}</option>
                                </select>
                                <p v-if="editForm.errors.job_id" class="mt-1 text-xs text-danger">{{
                                    editForm.errors.job_id }}</p>
                            </div>

                            <!-- Custom Position Input (when using custom) -->
                            <div v-else>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Posisi Custom <span
                                        class="text-danger">*</span></label>
                                <input v-model="editForm.custom_position" type="text"
                                    class="w-full h-12 px-4 text-base rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary focus:ring-2"
                                    placeholder="Masukkan nama posisi" />
                                <p v-if="editForm.errors.custom_position" class="mt-1 text-xs text-danger">{{
                                    editForm.errors.custom_position }}</p>
                            </div>

                            <!-- Mentor Select -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Mentor <span
                                        class="text-danger">*</span></label>
                                <select v-model="editForm.mentor_id"
                                    class="w-full h-12 px-4 text-base rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary focus:ring-2">
                                    <option value="" disabled>Pilih Mentor</option>
                                    <option v-for="mentor in mentors" :key="mentor.id" :value="mentor.id">{{ mentor.name
                                        }}</option>
                                </select>
                                <p v-if="editForm.errors.mentor_id" class="mt-1 text-xs text-danger">{{
                                    editForm.errors.mentor_id }}</p>
                            </div>

                            <!-- Date Range -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tanggal Mulai <span
                                            class="text-danger">*</span></label>
                                    <input v-model="editForm.start_date" type="date"
                                        class="w-full h-12 px-4 text-base rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary focus:ring-2" />
                                    <p v-if="editForm.errors.start_date" class="mt-1 text-xs text-danger">{{
                                        editForm.errors.start_date }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tanggal Selesai
                                        <span class="text-danger">*</span></label>
                                    <input v-model="editForm.end_date" type="date"
                                        class="w-full h-12 px-4 text-base rounded-lg border border-slate-300 bg-slate-50 focus:border-primary focus:ring-primary focus:ring-2" />
                                    <p v-if="editForm.errors.end_date" class="mt-1 text-xs text-danger">{{
                                        editForm.errors.end_date }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-3 pt-4 border-t border-slate-100">
                            <BaseButton type="button" variant="secondary" full @click="closeEdit">Batal</BaseButton>
                            <BaseButton type="submit" variant="primary" full :disabled="editForm.processing">
                                <Edit class="w-4 h-4" />
                                {{ editForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                            </BaseButton>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </Teleport>

    <!-- Delete Confirmation Modal -->
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="showDeleteModal && selectedUser" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="closeDelete"></div>
                <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
                    <div class="bg-danger/10 px-6 py-4 border-b border-danger/20 flex items-center justify-between">
                        <h3 class="font-bold text-lg text-danger">Hapus User</h3>
                        <button @click="closeDelete"
                            class="p-1 cursor-pointer rounded-full hover:bg-danger/20 transition-colors">
                            <X class="w-6 h-6 text-danger" />
                        </button>
                    </div>
                    <div class="p-6 flex flex-col gap-4">
                        <p class="text-slate-600">
                            Apakah Anda yakin ingin menghapus user <strong class="text-slate-900">{{ selectedUser.name
                                }}</strong>?
                        </p>
                        <p class="text-sm text-slate-500">
                            Tindakan ini akan menonaktifkan akun user dan dapat dibatalkan oleh administrator.
                        </p>

                        <div class="flex gap-3 pt-4 border-t border-slate-100">
                            <BaseButton variant="secondary" full @click="closeDelete">Batal</BaseButton>
                            <BaseButton variant="danger" full @click="confirmDelete">
                                <Trash2 class="w-4 h-4" />
                                Hapus User
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
