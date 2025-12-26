<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <footer class="bg-white border-t border-slate-200 py-12 lg:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-start gap-12">
                <div class="space-y-4 max-w-sm">
                    <div class="flex items-center gap-2 text-slate-900">
                        <div class="size-8 text-primary">
                            <svg fill="currentColor" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                                <path d="M36.7273 44C33.9891 44 31.6043 39.8386 30.3636 33.69C29.123 39.8386 26.7382 44 24 44C21.2618 44 18.877 39.8386 17.6364 33.69C16.3957 39.8386 14.0109 44 11.2727 44C7.25611 44 4 35.0457 4 24C4 12.9543 7.25611 4 11.2727 4C14.0109 4 16.3957 8.16144 17.6364 14.31C18.877 8.16144 21.2618 4 24 4C26.7382 4 29.123 8.16144 30.3636 14.31C31.6043 8.16144 33.9891 4 36.7273 4C40.7439 4 44 12.9543 44 24C44 35.0457 40.7439 44 36.7273 44Z"></path>
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="font-black text-xl tracking-tight leading-none">SINTESIS</span>
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Inosoft Trans Sistem</span>
                        </div>
                    </div>
                    <p class="text-sm text-slate-500 leading-relaxed">
                        Sistem Informasi Terintegrasi untuk manajemen sumber daya magang dan analitik produktivitas internal.
                    </p>
                    <div class="text-sm text-slate-400">
                        © 2025 PT Inosoft Trans Sistem.
                    </div>
                </div>
                <div class="flex flex-wrap gap-12 lg:gap-24">
                    <div>
                        <h4 class="font-bold text-slate-900 mb-4">Internal</h4>
                        <ul class="space-y-3 text-sm text-slate-600">
                            <li><a class="hover:text-primary transition-colors" href="#">SOP Magang</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 mb-4">Dukungan</h4>
                        <ul class="space-y-3 text-sm text-slate-600">
                            <li><a class="hover:text-primary transition-colors" href="#">Helpdesk IT</a></li>
                            <li><a class="hover:text-primary transition-colors" href="#">Kontak HR</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 mb-4">Sosial Media</h4>
                        <div class="flex gap-4">
                            <a class="size-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 hover:bg-primary hover:text-white transition-all" href="#">
                                <span class="material-symbols-outlined text-lg">public</span>
                            </a>
                            <a class="size-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 hover:bg-primary hover:text-white transition-all" href="#">
                                <span class="material-symbols-outlined text-lg">group</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>
