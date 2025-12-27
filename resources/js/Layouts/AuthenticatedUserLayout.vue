<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const showDropdown = ref(false);
</script>

<template>
    <div class="bg-background-light text-slate-900 min-h-screen flex flex-col font-display antialiased">
        <!-- Navbar -->
        <nav class="sticky top-0 z-50 w-full bg-white/80 backdrop-blur-md border-b border-gray-200">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <!-- Logo -->
                    <Link :href="route('dashboard')" class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <span class="material-symbols-outlined text-[24px]">dataset</span>
                        </div>
                        <span class="text-xl font-bold tracking-tight text-gray-900">SINTESIS</span>
                    </Link>

                    <!-- User Actions -->
                    <div class="flex items-center gap-5">
                        <div class="hidden md:flex items-center gap-3 pr-5 border-r border-gray-200">
                            <div class="text-right">
                                <p class="text-sm font-bold text-gray-900 leading-none">{{ $page.props.auth.user.name }}</p>
                                <p class="text-xs text-gray-500 mt-1">Calon Intern</p>
                            </div>
                            <div class="h-10 w-10 rounded-full bg-gray-100 bg-cover bg-center ring-2 ring-white shadow-sm flex items-center justify-center text-gray-400 font-bold text-lg">
                                {{ $page.props.auth.user.name.charAt(0) }}
                            </div>
                        </div>
                        
                        <Link :href="route('logout')" method="post" as="button" class="group flex items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-bold text-red-600 transition-all hover:bg-red-100 hover:border-red-300 focus:ring-2 focus:ring-red-500/20 cursor-pointer">
                            <span class="material-symbols-outlined text-[20px]">logout</span>
                            <span class="hidden sm:inline">Keluar</span>
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-1 w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <slot />
            
            <!-- Footer -->
            <footer class="mt-12 border-t border-gray-200 pt-8 pb-4">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-gray-400">
                    <p>© 2024 Inosoft Trans Sistem. SINTESIS Intern Platform v1.0</p>
                    <div class="flex gap-4">
                        <a href="#" class="hover:text-primary transition-colors">Kebijakan Privasi</a>
                        <a href="#" class="hover:text-primary transition-colors">Syarat & Ketentuan</a>
                    </div>
                </div>
            </footer>
        </main>
    </div>
</template>
