<script setup>
defineProps({
    title: String,
    subtitle: String,
    center: {
        type: Boolean,
        default: true
    }
});
</script>

<template>
    <div :class="[center ? 'text-center max-w-3xl mx-auto' : '', 'mb-16']">
        <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4">{{ title }}</h2>
        <p class="text-slate-600 text-lg">{{ subtitle }}</p>
    </div>
</template>
