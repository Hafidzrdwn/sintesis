<script setup>
defineProps({
    value: String,
    label: String,
});
</script>

<template>
    <div class="p-4">
        <p class="text-4xl font-black text-primary mb-2">{{ value }}</p>
        <p class="text-sm font-semibold text-slate-600">{{ label }}</p>
    </div>
</template>
