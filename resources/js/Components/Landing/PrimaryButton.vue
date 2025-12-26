<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: {
        type: String,
        default: null,
    },
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <Link
        v-if="href"
        :href="href"
        class="bg-primary hover:bg-primary-hover text-white text-sm font-bold h-10 px-6 rounded-full transition-all shadow-md shadow-primary/20 inline-flex items-center justify-center cursor-pointer"
    >
        <slot />
    </Link>
    <button
        v-else
        :type="type"
        class="bg-primary hover:bg-primary-hover text-white text-sm font-bold h-10 px-6 rounded-full transition-all shadow-md shadow-primary/20 inline-flex items-center justify-center cursor-pointer"
    >
        <slot />
    </button>
</template>
