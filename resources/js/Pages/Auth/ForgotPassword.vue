<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import BaseButton from '@/Components/BaseButton.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};

defineOptions({ layout: AuthLayout });
</script>

<template>

    <Head title="Lupa Kata Sandi" />

    <!-- Header Text -->
    <div class="flex flex-col items-center gap-2 mb-8 text-center">
        <h3 class="text-2xl font-bold leading-tight text-slate-900">Lupa Kata Sandi?</h3>
        <p class="text-slate-500 text-[15px] leading-normal">
            Masukkan alamat email Anda dan kami akan mengirimkan tautan untuk mengatur ulang kata sandi.
        </p>
    </div>

    <!-- Status Message -->
    <div v-if="status"
        class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-600 border border-green-200 text-center">
        {{ status }}
    </div>

    <form @submit.prevent="submit" class="flex flex-col gap-5">
        <!-- Email Input -->
        <div class="flex flex-col gap-2">
            <label class="text-base font-medium text-slate-900" for="email">Email</label>
            <input
                class="form-input block w-full rounded-lg border bg-slate-50 text-slate-900 placeholder:text-slate-400 h-12 px-4 text-base transition-shadow focus:outline-0 focus:ring-2"
                :class="{
                    'border-slate-300 focus:ring-primary/50': !form.errors.email,
                    'border-danger focus:ring-danger/50': form.errors.email
                }" id="email" v-model="form.email" placeholder="nama@inosoft.com" required type="email" autofocus />
            <div v-if="form.errors.email" class="text-sm text-danger">
                {{ form.errors.email }}
            </div>
        </div>

        <!-- Submit Button -->
        <BaseButton type="submit" variant="primary" size="lg" full :disabled="form.processing" class="mt-2">
            {{ form.processing ? 'Mengirim...' : 'Kirim Tautan Reset' }}
        </BaseButton>

        <!-- Back to Login -->
        <div class="flex justify-center mt-2">
            <Link :href="route('login')"
                class="text-sm font-medium text-slate-500 hover:text-primary transition-colors flex items-center gap-2">
                <ArrowLeft class="w-4 h-4" />
                Kembali ke halaman masuk
            </Link>
        </div>
    </form>
</template>
