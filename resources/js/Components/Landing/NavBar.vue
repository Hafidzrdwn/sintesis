<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage, router, Link } from '@inertiajs/vue3';
import BaseButton from '@/Components/BaseButton.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const isMobileMenuOpen = ref(false);
const page = usePage();

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const navLinks = [
    { name: 'Lowongan', href: '#lowongan' },
    { name: 'Cara Kerja', href: '#cara-kerja' },
    { name: 'Testimoni', href: '#testimoni' },
];

const handleNavClick = (e, href) => {
    isMobileMenuOpen.value = false;

    if (href.startsWith('#')) {
        e.preventDefault();

        if (page.url === '/' || page.url.startsWith('/#')) {
            const element = document.querySelector(href);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth' });
                history.pushState(null, null, href);
            }
        }
        else {
            router.get('/' + href);
        }
    }
};

const handleLogout = () => {
    router.post(route('logout'));
};

const isProfileOpen = ref(false);

const closeDropdown = (e) => {
    if (!e.target.closest('#profile-dropdown-container')) {
        isProfileOpen.value = false;
    }
};

onMounted(() => window.addEventListener('click', closeDropdown));
onUnmounted(() => window.removeEventListener('click', closeDropdown));

const isLoggedIn = computed(() => {
    return !!page.props.auth.user;
});

const user = computed(() => {
    return page.props.auth.user;
});
</script>

<template>
    <nav class="sticky top-0 z-50 w-full bg-white/95 backdrop-blur-sm border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <ApplicationLogo />

                <!-- Desktop Menu -->
                <div v-if="!isLoggedIn" class="hidden md:flex items-center gap-10">
                    <a v-for="item in navLinks" :key="item.name" :href="item.href"
                        @click="handleNavClick($event, item.href)"
                        class="text-base font-medium text-slate-600 hover:text-primary transition-colors">
                        {{ item.name }}
                    </a>
                    <BaseButton rounded="full" href="/login">Masuk</BaseButton>
                </div>

                <div v-else class="flex items-center gap-5">
                    <div id="profile-dropdown-container" class="relative">
                        <button @click="isProfileOpen = !isProfileOpen"
                            class="hidden md:flex items-center gap-1 pr-4 border-r border-gray-200 cursor-pointer hover:opacity-80 transition-opacity focus:outline-none">
                            <div class="flex gap-3 items-center justify-center">
                                <div class="text-right">
                                    <p class="text-sm font-bold text-gray-900 leading-none">
                                        {{ user.name }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">Calon Intern</p>
                                </div>
                                <img v-if="user.avatar" :src="user.avatar" alt="Avatar"
                                    class="h-10 w-10 rounded-full bg-primary/10 ring-2 ring-white shadow-sm flex items-center justify-center text-primary font-bold text-lg">
                                <div v-else
                                    class="h-10 w-10 rounded-full bg-primary/10 ring-2 ring-white shadow-sm flex items-center justify-center text-primary font-bold text-lg">
                                    {{ user.name.charAt(0).toUpperCase() }}
                                </div>
                            </div>
                            <span class="material-symbols-outlined text-gray-400">keyboard_arrow_down</span>
                        </button>

                        <transition enter-active-class="transition ease-out duration-100"
                            enter-from-class="transform opacity-0 scale-95"
                            enter-to-class="transform opacity-100 scale-100"
                            leave-active-class="transition ease-in duration-75"
                            leave-from-class="transform opacity-100 scale-100"
                            leave-to-class="transform opacity-0 scale-95">
                            <div v-show="isProfileOpen"
                                class="absolute right-5 mt-4 w-48 origin-top-right rounded-lg border border-primary/50 bg-white py-2 shadow-lg focus:outline-none z-50">
                                <div class="px-4 py-2 border-b border-gray-50 md:hidden">
                                    <p class="text-sm font-bold text-gray-900 truncate">{{ user.name }}
                                    </p>
                                    <p class="text-xs text-gray-500">Calon Intern</p>
                                </div>

                                <Link :href="route('dashboard')"
                                    class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer"
                                    :class="{ 'bg-primary text-white hover:bg-primary-hover': $page.url == '/dashboard' }">
                                    <span class="material-symbols-outlined fill">dashboard</span>
                                    Dashboard
                                </Link>
                                <Link :href="route('profile.edit')"
                                    class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer"
                                    :class="{ 'bg-primary text-white hover:bg-primary-hover': $page.url == '/profile' }"
                                    >
                                    <span class="material-symbols-outlined fill">person_edit</span>
                                    Edit Profil
                                </Link>
                            </div>
                        </transition>
                    </div>

                    <BaseButton 
                        size="sm" 
                        variant="outlineDanger" 
                        @click="handleLogout"
                        responsiveClass="hidden" 
                        class="md:inline-flex"
                    >
                        <span class="material-symbols-outlined">logout</span>
                        <span class="hidden sm:inline">Keluar</span>
                    </BaseButton>
                </div>

                <button class="md:hidden text-slate-600 hover:text-primary cursor-pointer" @click="toggleMobileMenu">
                    <span class="material-symbols-outlined scale-125">
                        {{ isMobileMenuOpen ? 'close' : 'menu' }}
                    </span>
                </button>
            </div>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div v-show="isMobileMenuOpen" class="md:hidden border-t border-slate-200 bg-white">
            <div v-if="!isLoggedIn" class="px-4 py-4 space-y-5">
                <a v-for="item in navLinks" :key="item.name" :href="item.href"
                    class="block text-base font-medium text-slate-600 hover:text-primary transition-colors"
                    @click="handleNavClick($event, item.href)">
                    {{ item.name }}
                </a>
                <div class="pt-2">
                    <BaseButton href="/login" rounded="full" full>Masuk</BaseButton>
                </div>
            </div>
            <div v-else class="px-4 py-4 space-y-5">
                <div class="flex gap-3 items-center justify-start">
                    <div
                        class="h-10 w-10 rounded-full bg-primary/10 ring-2 ring-white shadow-sm flex items-center justify-center text-primary font-bold text-lg">
                        {{ user.name.charAt(0) }}
                    </div>
                    <div class="text-left">
                        <p class="text-sm font-bold text-gray-900 leading-none">
                            {{ user.name }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Calon Intern</p>
                    </div>
                </div>
                <Link :href="route('dashboard')"
                    class="text-base font-medium block cursor-pointer transition-colors"
                    :class="{
                        'text-slate-600 hover:text-primary': $page.url != '/dashboard',
                        'text-primary hover:text-primary-hover': $page.url == '/dashboard'
                    }">
                    Dashboard
                </Link>
                <Link :href="route('profile.edit')"
                    class="text-base font-medium block cursor-pointer transition-colors"
                    :class="{
                        'text-slate-600 hover:text-primary': $page.url != '/profile',
                        'text-primary hover:text-primary-hover': $page.url == '/profile'
                    }"
                    >
                    Edit Profil
                </Link>
                <div class="pt-2">
                    <BaseButton @click="handleLogout" rounded="full" variant="outlineDanger"
                        full>
                        <span class="material-symbols-outlined">logout</span>
                        <span class="inline">Keluar</span>
                    </BaseButton>
                </div>
            </div>
        </div>
    </nav>
</template>
