<script setup>
defineProps({
    maxWidth: {
        type: String,
        default: 'max-w-[420px]',
    },
});
</script>

<template>
    <div class="relative flex min-h-screen w-full flex-col items-center justify-center overflow-hidden py-10 px-4 group/design-root bg-background-light dark:bg-background-dark font-display antialiased text-slate-900 dark:text-white transition-colors duration-200">
        <!-- Abstract Background Pattern -->
        <div class="absolute inset-0 -z-10 h-full w-full bg-background-light dark:bg-background-dark bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] dark:bg-[radial-gradient(#1f2937_1px,transparent_1px)] [background-size:20px_20px] [mask-image:radial-gradient(ellipse_60%_60%_at_50%_50%,#000_70%,transparent_100%)]"></div>
        
        <div class="w-full flex flex-col gap-6" :class="maxWidth">
            <!-- Logo Section -->
            <div class="flex flex-col items-center justify-center mb-2">
                <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-primary text-white shadow-lg shadow-primary/30">
                    <span class="material-symbols-outlined text-[28px]">dataset</span>
                </div>
                <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">SINTESIS</h2>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider mt-1">Inosoft Trans Sistem</p>
            </div>

            <!-- Card Content -->
            <div class="flex flex-col rounded-2xl bg-white dark:bg-[#1a232e] shadow-xl ring-1 ring-slate-900/5 dark:ring-white/10 p-6 sm:p-8">
                <slot />
            </div>

            <!-- Footer -->
            <div class="text-center space-y-2">
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    © 2024 Inosoft Trans Sistem. All rights reserved.
                </p>
                <div class="flex justify-center gap-4 text-xs text-slate-400 dark:text-slate-500">
                    <a class="hover:text-primary transition-colors" href="#">Privacy Policy</a>
                    <span>•</span>
                    <a class="hover:text-primary transition-colors" href="#">Terms of Service</a>
                </div>
            </div>
        </div>
    </div>
</template>
