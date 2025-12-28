<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

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
</script>

<template>
    <AuthLayout>
        <Head title="Lupa Kata Sandi" />

        <!-- Header Text -->
        <div class="flex flex-col items-center gap-2 mb-8 text-center">
            <h3 class="text-2xl font-bold leading-tight text-slate-900 dark:text-white">Lupa Kata Sandi?</h3>
            <p class="text-slate-500 dark:text-slate-400 text-[15px] leading-normal">
                Masukkan alamat email Anda dan kami akan mengirimkan tautan untuk mengatur ulang kata sandi.
            </p>
        </div>

        <div
            v-if="status"
            class="mb-4 text-sm font-medium text-green-600 text-center"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-5">
            <!-- Email Input -->
            <div class="flex flex-col gap-1.5">
                <label class="text-sm font-medium text-slate-900 dark:text-slate-200" for="email">Email</label>
                <input 
                    class="form-input block w-full rounded-lg border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 text-slate-900 dark:text-white placeholder:text-slate-400 h-11 px-4 focus:border-primary focus:ring-primary dark:focus:border-primary sm:text-sm transition-shadow" 
                    id="email" 
                    v-model="form.email"
                    placeholder="nama@inosoft.com" 
                    required 
                    type="email"
                    autofocus
                />
            </div>

            <!-- Submit Button -->
            <button 
                class="mt-2 flex w-full items-center justify-center rounded-lg bg-primary py-2.5 px-4 text-sm font-semibold text-white shadow-md shadow-primary/20 hover:bg-primary/90 hover:shadow-lg hover:shadow-primary/20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary transition-all active:scale-[0.98] cursor-pointer" 
                :class="{ 'opacity-70 cursor-not-allowed': form.processing }"
                :disabled="form.processing"    
                type="submit"
            >
                Kirim Tautan Reset
            </button>
            
            <!-- Back to Login -->
             <div class="flex justify-center mt-2">
                <Link 
                    :href="route('login')" 
                    class="text-sm font-medium text-slate-500 hover:text-primary transition-colors flex items-center gap-2"
                >
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    Kembali ke halaman masuk
                </Link>
            </div>
        </form>
    </AuthLayout>
</template>
