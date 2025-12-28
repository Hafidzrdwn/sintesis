<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineOptions({ layout: AuthenticatedLayout });
</script>

<template>
    <Head title="Manajemen Tugas" />
    <div class="p-6 bg-white rounded-2xl shadow-sm border border-slate-200">
        <h1 class="text-2xl font-bold text-slate-800 mb-4">Manajemen Tugas (PM)</h1>
        <p class="text-slate-600">Fitur pemberian dan review tugas akan tampil di sini.</p>
    </div>
</template>
