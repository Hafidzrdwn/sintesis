<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    href: {
        type: String,
        default: null,
    },
    type: {
        type: String,
        default: 'button',
    },
    customClass: {
        type: String,
        default: null,
    },
});

const finalButtonClass = computed(() => {
    return props.customClass
        ? props.customClass
        : 'bg-primary hover:bg-primary-hover text-white text-sm font-bold h-10 px-6 rounded-full transition-all shadow-md shadow-primary/20 inline-flex items-center justify-center cursor-pointer'
})
</script>

<template>
    <Link
        v-if="href"
        :href="href"
        :class="finalButtonClass"
    >
        <slot />
    </Link>
    <button
        v-else
        :type="type"
        :class="finalButtonClass"
    >
        <slot />
    </button>
</template>
