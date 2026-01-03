<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { X } from 'lucide-vue-next';
import LogoutConfirmModal from '@/Components/LogoutConfirmModal.vue';
import { getInitials, getAvatarUrl } from '@/utils/helpers';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const showMobileMenu = ref(false);
const showLogoutModal = ref(false);
const page = usePage();
const user = computed(() => page.props.auth.user);
const userRole = computed(() => user.value.role || 'intern');

const isInternalStaff = computed(() => ['admin', 'mentor'].includes(userRole.value));

const logoutRoute = computed(() => isInternalStaff.value ? 'auth.internal.logout' : 'logout');

const openLogoutModal = () => {
    showMobileMenu.value = false;
    showLogoutModal.value = true;
};

// Menu Configurations
const menus = {
    admin: [
        { label: 'Dashboard', routeName: 'admin.dashboard', icon: 'dashboard', urlPrefix: '/admin/dashboard' },
        { label: 'Monitoring Global', routeName: 'admin.monitoring', icon: 'analytics', urlPrefix: '/admin/monitoring' },
        { label: 'Rekrutmen', routeName: 'admin.recruitment', icon: 'group_add', urlPrefix: '/admin/recruitment' },
        { label: 'Data User', routeName: 'admin.users', icon: 'manage_accounts', urlPrefix: '/admin/users' },
        { label: 'Data Peserta Magang', routeName: 'admin.internships', icon: 'badge', urlPrefix: '/admin/internships' },
        { label: 'Monitoring Absensi', routeName: 'admin.attendance', icon: 'event_available', urlPrefix: '/admin/attendance' },
        { label: 'Lowongan', routeName: 'admin.jobs', icon: 'work', urlPrefix: '/admin/jobs' },
        { label: 'Testimonial', routeName: 'admin.testimonials', icon: 'format_quote', urlPrefix: '/admin/testimonials' },
        { label: 'Audit Log', routeName: 'admin.audit', icon: 'security', urlPrefix: '/admin/audit' },
    ],
    mentor: [
        { label: 'Dashboard', routeName: 'mentor.dashboard', icon: 'dashboard', urlPrefix: '/mentor/dashboard' },
        { label: 'Monitoring Absensi', routeName: 'mentor.attendance', icon: 'event_available', urlPrefix: '/mentor/attendance' },
        { label: 'Manajemen Tugas', routeName: 'mentor.tasks', icon: 'task', urlPrefix: '/mentor/tasks' },
        { label: 'Review Logbook', routeName: 'mentor.logbook', icon: 'rate_review', urlPrefix: '/mentor/logbook' },
        { label: 'Penilaian Akhir', routeName: 'mentor.evaluation', icon: 'school', urlPrefix: '/mentor/evaluation' },
    ],
    intern: [
        { label: 'Dashboard', routeName: 'intern.dashboard', icon: 'dashboard', urlPrefix: '/intern/dashboard' },
        { label: 'Analitik', routeName: 'intern.analytics', icon: 'bar_chart', urlPrefix: '/intern/analytics' },
        { label: 'Absensi', routeName: 'intern.attendance', icon: 'schedule', urlPrefix: '/intern/attendance' },
        { label: 'Tugas Saya', routeName: 'intern.tasks', icon: 'assignment', urlPrefix: '/intern/tasks' },
        { label: 'Buku Log', routeName: 'intern.logbook', icon: 'book', urlPrefix: '/intern/logbook' },
    ]
};

const menuItems = computed(() => menus[userRole.value] || menus.intern);

const roleLabel = computed(() => {
    switch (userRole.value) {
        case 'admin': return 'Administrator';
        case 'mentor': return 'Mentor';
        case 'intern': return page.props.auth.intern_position || 'Anak Magang';
        default: return 'Unknown';
    }
});

const isActive = (prefix) => {
    return page.url.startsWith(prefix);
};

const isSettingsActive = computed(() => page.url === '/profile');
</script>

<template>
    <div class="bg-background-light font-display text-text-main antialiased overflow-x-hidden">
        <div class="relative flex h-screen w-full overflow-hidden">
            <div v-if="showMobileMenu" class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"
                    @click="showMobileMenu = false"></div>

                <div
                    class="fixed inset-y-0 left-0 w-72 bg-white p-6 shadow-xl transform transition-transform duration-300 ease-in-out flex flex-col h-full border-r border-slate-200">
                    <div class="flex items-center justify-between mb-8">
                        <ApplicationLogo />
                        <button @click="showMobileMenu = false"
                            class="p-2 rounded-full cursor-pointer hover:bg-slate-100 text-slate-500">
                            <X class="w-6 h-6" />
                        </button>
                    </div>

                    <div class="flex flex-col h-full justify-between overflow-y-auto">
                        <div class="flex flex-col gap-6">
                            <div
                                class="flex items-center gap-4 px-2 p-3 bg-slate-50 rounded-2xl border border-slate-100">
                                <img v-if="getAvatarUrl(user)" :src="getAvatarUrl(user)" :alt="user.name"
                                    class="size-11 ring-2 rounded-full object-cover object-top ring-white shadow shadow-primary">
                                <div v-else
                                    class="bg-center bg-no-repeat bg-cover rounded-full size-11 ring-2 ring-white flex items-center justify-center bg-slate-200 text-slate-500 font-bold text-lg uppercase">
                                    {{ getInitials(user.name) }}
                                </div>
                                <div class="flex flex-col">
                                    <h1 class="text-text-main text-sm font-bold leading-tight line-clamp-1">{{ user.name
                                        }}</h1>
                                    <p class="text-text-secondary text-[10px] font-medium uppercase tracking-wide">{{
                                        roleLabel }}</p>
                                </div>
                            </div>

                            <nav class="flex flex-col gap-2">
                                <Link v-for="item in menuItems" :key="item.routeName" :href="route(item.routeName)"
                                    @click="showMobileMenu = false"
                                    class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group"
                                    :class="isActive(item.urlPrefix) ? 'bg-primary/10 text-primary font-bold ring-1 ring-primary/20' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'">
                                    <span class="material-symbols-outlined"
                                        :class="{ 'text-primary fill': isActive(item.urlPrefix) }">{{ item.icon
                                        }}</span>
                                    <span class="text-sm">{{ item.label }}</span>
                                </Link>
                            </nav>
                        </div>

                        <div class="flex flex-col gap-2 border-t border-slate-100 pt-4">
                            <Link :href="route('profile.edit')" @click="showMobileMenu = false"
                                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group"
                                :class="isSettingsActive ? 'bg-primary/10 text-primary font-bold ring-1 ring-primary/20' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'">
                                <span class="material-symbols-outlined"
                                    :class="{ 'text-primary fill': isSettingsActive }">settings</span>
                                <span class="text-sm">Pengaturan</span>
                            </Link>
                            <button @click="openLogoutModal"
                                class="flex cursor-pointer items-center gap-3 px-4 py-3 rounded-xl text-danger hover:bg-red-50 transition-colors w-full text-left mt-1">
                                <span class="material-symbols-outlined">logout</span>
                                <span class="text-sm font-semibold">Keluar</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <aside
                class="hidden lg:flex flex-col w-72 h-full bg-card-white border-r border-slate-200 p-6 flex-shrink-0 z-20 shadow-sm relative">
                <div class="flex flex-col h-full">
                    <div class="flex items-center gap-4 px-2 mb-6 shrink-0">
                        <img v-if="getAvatarUrl(user)" :src="getAvatarUrl(user)" :alt="user.name"
                            class="size-14 ring-2 shadow shadow-primary object-cover object-top rounded-full ring-white">
                        <div v-else
                            class="bg-center bg-no-repeat bg-cover rounded-full size-14 ring-2 ring-white flex items-center justify-center bg-slate-200 text-slate-500 font-bold text-lg uppercase">
                            {{ getInitials(user.name) }}
                        </div>
                        <div class="flex flex-col gap-1">
                            <h1 class="text-text-main text-base font-bold leading-tight">{{ user.name }}</h1>
                            <p class="text-text-secondary text-xs uppercase font-medium">{{ roleLabel }}</p>
                        </div>
                    </div>

                    <nav class="flex flex-col gap-2 flex-1 overflow-y-auto sidebar-scroll">
                        <Link v-for="item in menuItems" :key="item.routeName" :href="route(item.routeName)"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group shrink-0"
                            :class="isActive(item.urlPrefix) ? 'bg-primary/10 text-primary font-bold ring-1 ring-primary/20' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'">
                            <span class="material-symbols-outlined"
                                :class="{ 'text-primary fill': isActive(item.urlPrefix) }">{{ item.icon
                                }}</span>
                            <span class="text-sm">{{ item.label }}</span>
                        </Link>
                    </nav>

                    <div class="flex flex-col gap-2 border-t border-slate-100 pt-4 mt-4 shrink-0">
                        <Link :href="route('profile.edit')"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group"
                            :class="isSettingsActive ? 'bg-primary/10 text-primary font-bold ring-1 ring-primary/20' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'">
                            <span class="material-symbols-outlined"
                                :class="{ 'text-primary fill': isSettingsActive }">settings</span>
                            <span class="text-sm">Pengaturan</span>
                        </Link>
                        <button @click="openLogoutModal"
                            class="flex cursor-pointer items-center gap-3 px-4 py-3 rounded-xl text-danger hover:bg-red-50 transition-colors w-full text-left mt-1">
                            <span class="material-symbols-outlined">logout</span>
                            <span class="text-sm font-semibold">Keluar</span>
                        </button>
                    </div>
                </div>
            </aside>

            <main class="flex-1 flex flex-col h-full overflow-y-auto relative z-10 bg-background-light">
                <div
                    class="lg:hidden flex items-center justify-between p-4 bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
                    <ApplicationLogo />
                    <button @click="showMobileMenu = !showMobileMenu"
                        class="p-2 rounded-full hover:bg-slate-100 text-slate-600 transition-colors active:bg-slate-200">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                </div>

                <div
                    class="layout-content-container flex flex-col max-w-[1200px] w-full mx-auto px-4 pt-4 pb-16 md:px-8 md:pt-8 gap-8 flex-1">
                    <slot />
                </div>

                <!-- Footer -->
                <footer class="mt-auto border-t border-slate-200 bg-white py-6">
                    <div
                        class="max-w-[1200px] mx-auto px-4 md:px-8 flex flex-col md:flex-row items-center justify-between gap-4 text-sm">
                        <div class="text-slate-500 font-medium">
                            &copy; 2025 <span class="font-bold text-primary">SINTESIS</span>. All rights reserved.
                        </div>
                        <div class="flex items-center gap-6 text-slate-400">
                            <a href="#" class="hover:text-primary transition-colors">Bantuan</a>
                            <a href="#" class="hover:text-primary transition-colors">Privasi</a>
                            <a href="#" class="hover:text-primary transition-colors">Syarat & Ketentuan</a>
                        </div>
                    </div>
                </footer>
            </main>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <LogoutConfirmModal :show="showLogoutModal" :logout-route="logoutRoute" @close="showLogoutModal = false" />
</template>

<style scoped>
.sidebar-scroll {
    scrollbar-width: thin;
    scrollbar-color: var(--color-primary) transparent;
    padding-right: 8px;
    margin-right: -4px;
}

.sidebar-scroll::-webkit-scrollbar {
    width: 6px;
}

.sidebar-scroll::-webkit-scrollbar-track {
    background: transparent;
    border-radius: 10px;
}

.sidebar-scroll::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
    transition: background 0.2s;
}

.sidebar-scroll::-webkit-scrollbar-thumb:hover {
    background: var(--color-primary);
}
</style>
