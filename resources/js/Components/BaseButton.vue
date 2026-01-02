<script setup>
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    href: {
        type: String,
        default: null,
    },
    external: {
        type: Boolean,
        default: false, // If true, use native <a> instead of Inertia Link
    },
    method: {
        type: String,
        default: 'get', // get | post | put | patch | delete (for Inertia Link)
    },
    as: {
        type: String,
        default: null, // 'button' for POST links (for Inertia Link)
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
    rounded: {
        type: String,
        default: 'lg', // none | sm | md | lg | xl | full
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
    responsiveClass: {
        type: String,
        default: ''
    }
})

const baseBreakpoint = (props.responsiveClass) ? props.responsiveClass : 'inline-flex';
const baseClass =
    `${baseBreakpoint} items-center justify-center gap-2 font-bold transition-all cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-2`

const variants = {
    primary:
        'bg-primary text-white hover:bg-primary-hover shadow-lg shadow-primary/20 focus:ring-primary',
    outlinePrimary:
        'bg-white border border-primary text-primary hover:bg-primary/10 focus:ring-primary',
    secondary:
        'bg-slate-100 text-slate-700 hover:bg-slate-200 focus:ring-slate-300',
    outlineSecondary:
        'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 focus:ring-slate-300',
    danger:
        'bg-danger text-white hover:bg-danger-hover shadow-lg shadow-danger/20 focus:ring-danger',
    outlineDanger:
        'border border-red-200 bg-red-50 text-red-600 hover:bg-red-100 hover:border-red-300 focus:ring-2 focus:ring-red-500/50',
    success:
        'bg-success text-white hover:bg-success-hover shadow-lg shadow-success/20 focus:ring-success',
    outlineSuccess:
        'bg-white border border-success text-success hover:bg-success/10 focus:ring-success',
    ghost:
        'bg-transparent text-slate-700 hover:bg-slate-100 focus:ring-slate-300',
}

const roundeds = {
    none: 'rounded-none',
    sm: 'rounded-sm',
    md: 'rounded-md',
    lg: 'rounded-lg',
    xl: 'rounded-xl',
    full: 'rounded-full',
}

const sizes = {
    sm: 'h-10 px-4 text-sm',
    md: 'h-12 px-8 text-sm',
    lg: 'h-14 px-8 text-base',
}

const finalClass = computed(() => [
    baseClass,
    variants[props.variant],
    roundeds[props.rounded],
    sizes[props.size],
    props.full && 'w-full',
    (props.disabled || props.loading) &&
    'opacity-60 cursor-not-allowed pointer-events-none',
])

const componentBehavior = computed(() => {
    if (props.href) {
        // Use native <a> for external links or anchor links
        if (props.external || props.href.includes('#')) {
            return 'a'
        }
        return Link
    } else {
        return 'button'
    }
})

// Determine if we need as="button" for non-GET Inertia links
const linkAs = computed(() => {
    if (props.as) return props.as
    // Auto-set as="button" for POST/PUT/PATCH/DELETE methods
    if (props.method && props.method.toLowerCase() !== 'get') {
        return 'button'
    }
    return undefined
})

</script>

<template>
    <component :is="componentBehavior" :href="href" :type="!href ? type : undefined"
        :method="href && !external ? method : undefined"
        :as="href && !external ? linkAs : undefined"
        :disabled="disabled || loading" :class="finalClass">
        <!-- Loading state -->
        <span v-if="loading" class="animate-spin">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
            </svg>
        </span>

        <!-- Content -->
        <slot v-else />
    </component>
</template>

