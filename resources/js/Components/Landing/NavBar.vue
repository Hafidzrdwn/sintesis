<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import PrimaryButton from './PrimaryButton.vue';

const isMobileMenuOpen = ref(false);

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const navLinks = [
    { name: 'Lowongan', href: '#lowongan' },
    { name: 'Cara Kerja', href: '#cara-kerja' },
    { name: 'Testimoni', href: '#testimoni' },
];
</script>

<template>
    <nav class="sticky top-0 z-50 w-full bg-white/95 backdrop-blur-sm border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <Link class="flex items-center gap-3 group" href="/">
                    <div class="size-9 text-primary group-hover:scale-105 transition-transform duration-300">
                        <svg fill="currentColor" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                            <path d="M36.7273 44C33.9891 44 31.6043 39.8386 30.3636 33.69C29.123 39.8386 26.7382 44 24 44C21.2618 44 18.877 39.8386 17.6364 33.69C16.3957 39.8386 14.0109 44 11.2727 44C7.25611 44 4 35.0457 4 24C4 12.9543 7.25611 4 11.2727 4C14.0109 4 16.3957 8.16144 17.6364 14.31C18.877 8.16144 21.2618 4 24 4C26.7382 4 29.123 8.16144 30.3636 14.31C31.6043 8.16144 33.9891 4 36.7273 4C40.7439 4 44 12.9543 44 24C44 35.0457 40.7439 44 36.7273 44Z"></path>
                        </svg>
                    </div>
                    <div class="flex flex-col">
                        <h1 class="text-2xl font-black tracking-tight text-slate-900 leading-none">SINTESIS</h1>
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Inosoft Trans Sistem</span>
                    </div>
                </Link>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center gap-8">
                    <Link 
                        v-for="item in navLinks" 
                        :key="item.name" 
                        :href="item.href" 
                        class="text-sm font-medium text-slate-600 hover:text-primary transition-colors"
                    >
                        {{ item.name }}
                    </Link>
                    <PrimaryButton href="/login">Masuk</PrimaryButton>
                </div>

                <!-- Mobile Menu Button -->
                <button 
                    class="md:hidden text-slate-600 hover:text-primary cursor-pointer"
                    @click="toggleMobileMenu"
                >
                    <span class="material-symbols-outlined text-3xl">menu</span>
                </button>
            </div>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div v-show="isMobileMenuOpen" class="md:hidden border-t border-slate-200 bg-white">
            <div class="px-4 py-4 space-y-4">
                 <Link 
                    v-for="item in navLinks" 
                    :key="item.name" 
                    :href="item.href" 
                    class="block text-sm font-medium text-slate-600 hover:text-primary transition-colors"
                    @click="isMobileMenuOpen = false"
                >
                    {{ item.name }}
                </Link>
                <div class="pt-2">
                     <PrimaryButton href="/login" class="w-full">Masuk</PrimaryButton>
                </div>
            </div>
        </div>
    </nav>
</template>
