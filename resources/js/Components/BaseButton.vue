<script setup>
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    href: {
        type: String,
        default: null,
    },
    type: {
        type: String,
        default: 'button', // button | submit | reset
    },
    variant: {
        type: String,
        default: 'primary', // primary | secondary | outline | danger | ghost
    },
    size: {
        type: String,
        default: 'md', // sm | md | lg
    },
    full: {
        type: Boolean,
        default: false,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
})

const baseClass =
    'inline-flex items-center justify-center gap-2 font-bold rounded-lg transition-all cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-2'

const variants = {
    primary:
        'bg-primary text-white hover:bg-primary/90 shadow-lg shadow-primary/20 focus:ring-primary',
    secondary:
        'bg-slate-100 text-slate-700 hover:bg-slate-200 focus:ring-slate-300',
    outline:
        'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 focus:ring-slate-300',
    danger:
        'bg-red-600 text-white hover:bg-red-700 shadow-lg shadow-red-600/20 focus:ring-red-500',
    ghost:
        'bg-transparent text-slate-700 hover:bg-slate-100 focus:ring-slate-300',
}

const sizes = {
    sm: 'h-9 px-4 text-sm',
    md: 'h-12 px-6 text-sm',
    lg: 'h-14 px-8 text-base',
}

const finalClass = computed(() => [
    baseClass,
    variants[props.variant],
    sizes[props.size],
    props.full && 'w-full',
    (props.disabled || props.loading) &&
        'opacity-60 cursor-not-allowed pointer-events-none',
])

</script>

<template>
    <component
        :is="href ? Link : 'button'"
        :href="href"
        :type="!href ? type : undefined"
        :disabled="disabled || loading"
        :class="finalClass"
    >
        <!-- Loading state -->
        <span v-if="loading" class="animate-spin">
            <svg
                class="h-4 w-4"
                fill="none"
                viewBox="0 0 24 24"
            >
                <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                />
                <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                />
            </svg>
        </span>

        <!-- Content -->
        <slot v-else />
    </component>
</template>

