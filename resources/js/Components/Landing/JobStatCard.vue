<script setup>
import { computed } from 'vue';

const props = defineProps({
    label: String,
    value: [String, Number],
    subLabel: String,
    icon: String,
    color: {
        type: String,
        default: 'blue', // blue, yellow, indigo, red, green
        validator: (value) => ['blue', 'yellow', 'indigo', 'red', 'green'].includes(value)
    }
});

const borderClass = computed(() => {
    const classes = {
        blue: 'border-l-blue-500',
        yellow: 'border-l-yellow-500',
        indigo: 'border-l-indigo-500',
        red: 'border-l-red-500',
        green: 'border-l-green-500'
    };
    return classes[props.color] || classes.blue;
});

const iconColorClass = computed(() => {
    const classes = {
        blue: 'text-blue-500',
        yellow: 'text-yellow-500',
        indigo: 'text-indigo-500',
        red: 'text-red-500',
        green: 'text-green-500'
    };
    return classes[props.color] || classes.blue;
});
</script>

<template>
    <div :class="['bg-white p-5 rounded-xl border border-slate-100 shadow-sm flex flex-col gap-1 hover:shadow-md transition-shadow border-l-[3px]', borderClass]">
        <div class="flex items-center justify-between mb-1">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">{{ label }}</span>
            <span :class="['material-symbols-outlined text-[20px]', iconColorClass]">{{ icon }}</span>
        </div>
        <div class="text-2xl font-bold text-slate-800">{{ value }}</div>
        <div class="text-[11px] text-slate-400 font-medium">{{ subLabel }}</div>
    </div>
</template>
