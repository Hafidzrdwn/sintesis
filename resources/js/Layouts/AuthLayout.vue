<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
defineProps({
    maxWidth: {
        type: String,
        default: 'max-w-[450px]',
    },
});
</script>

<template>
    <div
        class="relative flex min-h-screen w-full flex-col items-center justify-center overflow-hidden py-10 px-4 group/design-root font-display antialiased text-slate-900 transition-colors duration-200">
        <!-- Abstract Background Pattern -->
        <div class="absolute inset-0 -z-10 h-full w-full bg-white">
            <div
                class="absolute h-full w-full bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] dark:bg-[radial-gradient(#1e293b_1px,transparent_1px)] [background-size:20px_20px] [mask-image:radial-gradient(ellipse_80%_80%_at_50%_50%,#000_60%,transparent_100%)]">
            </div>
        </div>

        <div class="w-full flex flex-col gap-6" :class="maxWidth">
            <ApplicationLogo class="mx-auto" />

            <div
                class="flex flex-col rounded-2xl bg-white shadow-xl ring-1 ring-slate-900/5 p-6 sm:p-8">
                <slot />
            </div>

            <div class="text-center space-y-2">
                <p class="text-xs text-slate-500">
                    © 2025 SINTESIS. All rights reserved.
                </p>
                <div class="flex justify-center gap-4 text-xs text-slate-400">
                    <a class="hover:text-primary transition-colors" href="#">Privacy Policy</a>
                    <span>•</span>
                    <a class="hover:text-primary transition-colors" href="#">Terms of Service</a>
                </div>
            </div>
        </div>
    </div>
</template>
