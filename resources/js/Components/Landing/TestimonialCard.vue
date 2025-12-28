<script setup>
defineProps({
    name: String,
    role: String,
    quote: String,
    initial: String,
    stars: {
        type: Number,
        default: 5
    }
});
</script>

<template>
    <div class="bg-slate-50 p-8 rounded-2xl border border-slate-100">
        <div class="flex items-center mb-4 gap-3">
        <span class="text-slate-700 text-md font-semibold">{{ stars }}</span>
        <div class="flex items-center gap-1 text-yellow-400">
            <template v-for="i in 5" :key="i">
                <span v-if="i <= Math.floor(stars)" class="material-symbols-outlined text-xl" :class="{ 'fill': i <= Math.floor(stars) }">star</span>
                <span v-else-if="i === Math.ceil(stars) && !Number.isInteger(stars)" class="material-symbols-outlined text-xl">star_half</span>
                <span v-else class="material-symbols-outlined text-xl">star_border</span>
            </template>
        </div>
        </div>
        <p class="text-slate-700 italic mb-6">"{{ quote }}"</p>
        <div class="flex items-center gap-4">
            <div class="size-10 rounded-full bg-slate-300 flex items-center justify-center font-bold text-slate-600">
                {{ initial }}
            </div>
            <div>
                <p class="font-bold text-slate-900 text-sm">{{ name }}</p>
                <p class="text-slate-500 text-xs">{{ role }}</p>
            </div>
        </div>
    </div>
</template>


