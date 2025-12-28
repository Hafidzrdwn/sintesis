<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineOptions({ layout: AuthenticatedLayout });
</script>

<template>
    <Head title="Monitoring Global" />
    <div class="p-6 bg-white rounded-2xl shadow-sm border border-slate-200">
        <h1 class="text-2xl font-bold text-slate-800 mb-4">Monitoring Global (SIE)</h1>
        <p class="text-slate-600">Analitik kehadiran dan performa global akan tampil di sini.</p>
    </div>
</template>
