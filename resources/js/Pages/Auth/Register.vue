<script setup>
import BaseButton from '@/Components/BaseButton.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

defineOptions({
    layout: (h, page) => h(AuthLayout, { maxWidth: 'max-w-[520px]' }, () => page),
});
</script>

<template>

    <Head title="Pendaftaran Pengguna" />

    <!-- Header -->
    <div class="flex flex-col items-center mb-0 gap-2 text-center text-slate-900">
        <h2 class="text-3xl font-bold tracking-tight">Pendaftaran Pengguna</h2>
        <p class="text-slate-500 text-sm font-normal leading-normal max-w-sm mx-auto">
            Bergabung dengan SINTESIS untuk manajemen magang, monitoring, dan analitik yang lebih baik.
        </p>
    </div>

    <form @submit.prevent="submit" class="flex flex-col gap-5 mt-6">
        <!-- Name Input -->
        <div class="flex flex-col gap-2">
            <label class="text-slate-900 text-base font-medium leading-normal">Nama Lengkap</label>
            <input
                class="form-input flex w-full resize-none overflow-hidden rounded-lg text-slate-900 focus:outline-0 focus:ring-2 border bg-slate-50 h-12 placeholder:text-slate-400 px-4 text-base font-normal leading-normal transition-all"
                :class="{
                    'border-slate-300 focus:ring-primary/50': !form.errors.name,
                    'border-danger focus:ring-danger/50': form.errors.name
                }" placeholder="Masukkan nama lengkap" type="text" v-model="form.name" required autofocus />
            <div v-if="form.errors.name" class="text-sm text-danger">
                {{ form.errors.name }}
            </div>
        </div>

        <!-- Email Input -->
        <div class="flex flex-col gap-2">
            <label class="text-slate-900 text-base font-medium leading-normal">Email</label>
            <input
                class="form-input flex w-full resize-none overflow-hidden rounded-lg text-slate-900 focus:outline-0 focus:ring-2 border bg-slate-50 h-12 placeholder:text-slate-400 px-4 text-base font-normal leading-normal transition-all"
                :class="{
                    'border-slate-300 focus:ring-primary/50': !form.errors.email,
                    'border-danger focus:ring-danger/50': form.errors.email
                }" placeholder="contoh@inosoft.com" type="text" v-model="form.email" required />
            <small class="text-text-muted">Silakan gunakan alamat email aktif untuk menerima pembaruan terkait lamaran
                pekerjaan yang Anda ajukan.</small>
            <div v-if="form.errors.email" class="text-sm text-danger">
                {{ form.errors.email }}
            </div>
        </div>

        <!-- Password Input -->
        <div class="flex flex-col gap-2">
            <label class="text-slate-900 text-base font-medium leading-normal">Kata Sandi</label>
            <div class="relative flex w-full items-center rounded-lg">
                <input
                    class="form-input flex w-full resize-none overflow-hidden rounded-lg text-slate-900 focus:outline-0 focus:ring-2 border bg-slate-50 h-12 placeholder:text-slate-400 px-4 pr-12 text-base font-normal leading-normal transition-all"
                    :class="{
                        'border-slate-300 focus:ring-primary/50': !form.errors.password,
                        'border-danger focus:ring-danger/50': form.errors.password
                    }" placeholder="Masukkan kata sandi Anda" :type="showPassword ? 'text' : 'password'"
                    v-model="form.password" required />
                <button @click="showPassword = !showPassword"
                    class="absolute right-0 top-0 h-full px-4 flex items-center justify-center text-slate-400 hover:text-primary transition-colors cursor-pointer"
                    type="button">
                    <span class="material-symbols-outlined">{{ showPassword ? 'visibility_off' :
                        'visibility' }}</span>
                </button>
            </div>
            <div v-if="form.errors.password" class="text-sm text-danger">
                {{ form.errors.password }}
            </div>
        </div>

        <!-- Confirm Password Input -->
        <div class="flex flex-col gap-2">
            <label class="text-slate-900 text-base font-medium leading-normal">Konfirmasi Kata Sandi</label>
            <div class="relative flex w-full items-center rounded-lg">
                <input
                    class="form-input flex w-full resize-none overflow-hidden rounded-lg text-slate-900 focus:outline-0 focus:ring-2 border bg-slate-50 h-12 placeholder:text-slate-400 px-4 pr-12 text-base font-normal leading-normal transition-all"
                    :class="{
                        'border-slate-300 focus:ring-primary/50': !form.errors.password_confirmation,
                        'border-danger focus:ring-danger/50': form.errors.password_confirmation
                    }" placeholder="*********" :type="showConfirmPassword ? 'text' : 'password'"
                    v-model="form.password_confirmation" required />
                <button @click="showConfirmPassword = !showConfirmPassword"
                    class="absolute right-0 top-0 h-full px-4 flex items-center justify-center text-slate-400 hover:text-primary transition-colors cursor-pointer"
                    type="button">
                    <span class="material-symbols-outlined">{{ showConfirmPassword ? 'visibility_off' :
                        'visibility' }}</span>
                </button>
            </div>
            <div v-if="form.errors.password_confirmation" class="text-sm text-danger">
                {{ form.errors.password_confirmation }}
            </div>
        </div>

        <BaseButton type="submit" size="lg" :loading="form.processing" :disabled="form.processing">Daftar
        </BaseButton>

        <div class="relative my-2">
            <div aria-hidden="true" class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-slate-200"></div>
            </div>
            <div class="relative flex justify-center">
                <span class="bg-white px-3 text-xs font-medium text-slate-500 uppercase tracking-wide">Atau</span>
            </div>
        </div>

        <BaseButton type="button" variant="outlineSecondary" size="lg" :loading="form.processing"
            :disabled="form.processing">
            <svg class="w-5 h-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                    fill="#4285F4"></path>
                <path
                    d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                    fill="#34A853"></path>
                <path
                    d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                    fill="#FBBC05"></path>
                <path
                    d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                    fill="#EA4335"></path>
            </svg>
            <span class="truncate">Daftar dengan Google</span>
        </BaseButton>
    </form>

    <div class="mt-6 text-center">
        <p class="text-slate-500 text-sm">
            Sudah punya akun?
            <Link :href="route('login')" class="text-primary hover:text-primary-hover font-semibold hover:underline">
                Masuk di
                sini</Link>
        </p>
    </div>
</template>
