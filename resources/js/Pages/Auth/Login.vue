<script setup>
import BaseButton from '@/Components/BaseButton.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

defineOptions({ layout: AuthLayout })
</script>

<template>

    <Head title="Login Pengguna" />

    <!-- Header Text -->
    <div class="flex flex-col items-center gap-2 mb-8 text-center">
        <h3 class="text-2xl font-bold leading-tight text-slate-900">Selamat Datang Kembali</h3>
        <p class="text-slate-500 text-[15px] leading-normal">Masuk untuk mengelola presensi dan
            tugas magang.</p>
    </div>

    <div v-if="status" class="mb-4 text-sm font-medium text-green-600 text-center">
        {{ status }}
    </div>

    <form @submit.prevent="submit" class="flex flex-col gap-5">
        <div class="flex flex-col gap-2">
            <label class="text-base font-medium text-slate-900" for="email">Email</label>
            <input
                class="form-input block w-full rounded-lg border bg-slate-50 text-slate-900 placeholder:text-slate-400 h-12 px-4 text-base transition-shadow focus:outline-0 focus:ring-2"
                :class="{
                    'border-slate-300 focus:ring-primary/50': !form.errors.email,
                    'border-danger focus:ring-danger/50': form.errors.email
                }" id="email" v-model="form.email" placeholder="nama@inosoft.com" type="text" required autofocus />
            <div v-if="form.errors.email" class="text-sm text-danger">
                {{ form.errors.email }}
            </div>
        </div>

        <div class="flex flex-col gap-2">
            <label class="text-base font-medium text-slate-900" for="password">Kata
                Sandi</label>
            <div class="relative group">
                <input
                    class="form-input block w-full rounded-lg border bg-slate-50 text-slate-900 placeholder:text-slate-400 h-12 px-4 pr-10 text-base transition-shadow focus:outline-0 focus:ring-2"
                    :class="{
                        'border-slate-300 focus:ring-primary/50': !form.errors.password,
                        'border-danger focus:ring-danger/50': form.errors.password
                    }" id="password" v-model="form.password" placeholder="Masukkan kata sandi Anda" required
                    :type="showPassword ? 'text' : 'password'" />
                <button @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-primary transition-colors cursor-pointer"
                    type="button">
                    <span class="material-symbols-outlined">{{ showPassword ? 'visibility_off' :
                        'visibility' }}</span>
                </button>
            </div>

            <div v-if="form.errors.password" class="text-sm text-danger">
                {{ form.errors.password }}
            </div>

            <div class="flex justify-end mt-1">
                <Link v-if="canResetPassword" :href="route('password.request')"
                    class="text-sm font-medium text-primary hover:text-primary-hover transition-colors">
                    Lupa kata sandi?
                </Link>
            </div>
        </div>

        <BaseButton type="submit" size="lg" :loading="form.processing" :disabled="form.processing">Masuk
        </BaseButton>
    </form>

    <div class="relative my-6">
        <div aria-hidden="true" class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-slate-200"></div>
        </div>
        <div class="relative flex justify-center">
            <span class="bg-white px-3 text-xs font-medium text-slate-500 uppercase tracking-wide">Atau</span>
        </div>
    </div>

    <BaseButton type="button" variant="outlineSecondary" size="lg" :loading="form.processing"
        :disabled="form.processing">
        <svg class="h-5 w-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
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
        <span class="truncate">Masuk dengan Google</span>
    </BaseButton>

    <div class="mt-6 text-center">
        <p class="text-slate-500 text-sm">
            Belum punya akun?
            <Link :href="route('register')" class="text-primary hover:text-primary-hover font-semibold hover:underline">
                Daftar di
                sini</Link>
        </p>
    </div>
</template>
