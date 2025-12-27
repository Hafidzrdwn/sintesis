<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const showMobileMenu = ref(false);
</script>

<template>
    <div class="bg-background-light font-display text-text-main antialiased overflow-x-hidden">
        <div class="relative flex h-screen w-full overflow-hidden">
            <!-- Sidebar (Desktop) -->
            <aside class="hidden lg:flex flex-col w-72 h-full bg-card-white border-r border-slate-200 p-6 flex-shrink-0 z-20 shadow-sm">
                <div class="flex flex-col h-full justify-between">
                    <div class="flex flex-col gap-8">
                        <!-- User Profile -->
                        <div class="flex items-center gap-4 px-2">
                             <!-- Using a placeholder/initials if no image, or the URL from design -->
                            <div class="bg-center bg-no-repeat bg-cover rounded-full size-12 ring-2 ring-slate-100 flex items-center justify-center bg-slate-200 text-slate-500 font-bold text-xl">
                                {{ $page.props.auth.user.name.charAt(0) }}
                            </div>
                            <div class="flex flex-col">
                                <h1 class="text-text-main text-base font-bold leading-tight">{{ $page.props.auth.user.name }}</h1>
                                <p class="text-text-secondary text-xs font-medium">Magang Desain UX</p>
                            </div>
                        </div>
                        
                        <!-- Navigation -->
                        <nav class="flex flex-col gap-2">
                            <Link :href="route('intern.dashboard')" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-primary text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary-hover">
                                <span class="material-symbols-outlined">dashboard</span>
                                <span class="text-sm font-semibold">Dasbor</span>
                            </Link>
                            <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl text-text-secondary hover:bg-slate-50 hover:text-primary transition-colors">
                                <span class="material-symbols-outlined">assignment</span>
                                <span class="text-sm font-medium">Tugas Saya</span>
                            </a>
                            <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl text-text-secondary hover:bg-slate-50 hover:text-primary transition-colors">
                                <span class="material-symbols-outlined">book</span>
                                <span class="text-sm font-medium">Buku Log</span>
                            </a>
                            <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl text-text-secondary hover:bg-slate-50 hover:text-primary transition-colors">
                                <span class="material-symbols-outlined">bar_chart</span>
                                <span class="text-sm font-medium">Analitik</span>
                            </a>
                        </nav>
                    </div>
                    
                    <!-- Bottom Actions -->
                    <div class="flex flex-col gap-2 border-t border-slate-100 pt-4">
                        <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl text-text-secondary hover:bg-slate-50 hover:text-primary transition-colors">
                            <span class="material-symbols-outlined">settings</span>
                            <span class="text-sm font-medium">Pengaturan</span>
                        </a>
                        <Link :href="route('logout')" method="post" as="button" class="flex items-center gap-3 px-4 py-3 rounded-xl text-danger hover:bg-red-50 transition-colors w-full text-left mt-1">
                            <span class="material-symbols-outlined">logout</span>
                            <span class="text-sm font-semibold">Keluar</span>
                        </Link>
                    </div>
                </div>
            </aside>

            <!-- Main Content Area -->
            <main class="flex-1 flex flex-col h-full overflow-y-auto relative z-10 bg-background-light">
                <!-- Mobile Header -->
                <div class="lg:hidden flex items-center justify-between p-4 bg-white border-b border-slate-200 sticky top-0 z-30">
                    <div class="font-bold text-lg text-primary">SINTESIS</div>
                    <button @click="showMobileMenu = !showMobileMenu" class="p-2 rounded-full hover:bg-slate-100 text-slate-600">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                </div>

                <!-- Page Content -->
                 <div class="layout-content-container flex flex-col max-w-[1200px] w-full mx-auto p-4 md:p-8 gap-8">
                    <slot />
                 </div>
            </main>
        </div>
    </div>
</template>
