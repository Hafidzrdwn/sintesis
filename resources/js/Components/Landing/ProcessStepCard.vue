<script setup>
defineProps({
    number: [String, Number],
    title: String,
    description: String,
    icon: String,
});
</script>

<template>
    <div class="group p-8 rounded-2xl bg-white border border-slate-200 hover:border-primary/50 hover:shadow-xl hover:shadow-primary/5 transition-all duration-300">
        <div class="w-14 h-14 rounded-xl bg-blue-50 text-primary flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
            <span class="material-symbols-outlined text-3xl">{{ icon }}</span>
        </div>
        <h3 class="text-xl font-bold text-slate-900 mb-3">{{ number }}. {{ title }}</h3>
        <p class="text-slate-600 text-sm leading-relaxed">{{ description }}</p>
    </div>
</template>
