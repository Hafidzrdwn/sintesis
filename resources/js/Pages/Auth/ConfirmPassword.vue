<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import BaseButton from '@/Components/BaseButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Eye, EyeOff, ShieldCheck } from 'lucide-vue-next';

const form = useForm({
    password: '',
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};

defineOptions({ layout: AuthLayout });
</script>

<template>

    <Head title="Konfirmasi Kata Sandi" />

    <!-- Header Text -->
    <div class="flex flex-col items-center gap-2 mb-8 text-center">
        <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mb-2">
            <ShieldCheck class="w-8 h-8 text-primary" />
        </div>
        <h3 class="text-2xl font-bold leading-tight text-slate-900">Konfirmasi Kata Sandi</h3>
        <p class="text-slate-500 text-[15px] leading-normal">
            Ini adalah area aman. Silakan konfirmasi kata sandi Anda sebelum melanjutkan.
        </p>
    </div>

    <form @submit.prevent="submit" class="flex flex-col gap-5">
        <!-- Password Input -->
        <div class="flex flex-col gap-2">
            <label class="text-base font-medium text-slate-900" for="password">Kata Sandi</label>
            <div class="relative group">
                <input
                    class="form-input block w-full rounded-lg border bg-slate-50 text-slate-900 placeholder:text-slate-400 h-12 px-4 pr-10 text-base transition-shadow focus:outline-0 focus:ring-2"
                    :class="{
                        'border-slate-300 focus:ring-primary/50': !form.errors.password,
                        'border-danger focus:ring-danger/50': form.errors.password
                    }" id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'"
                    placeholder="Masukkan kata sandi Anda" required autocomplete="current-password" autofocus />
                <button type="button"
                    class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600 transition-colors cursor-pointer"
                    @click="showPassword = !showPassword">
                    <EyeOff v-if="showPassword" class="w-5 h-5" />
                    <Eye v-else class="w-5 h-5" />
                </button>
            </div>
            <div v-if="form.errors.password" class="text-sm text-danger">
                {{ form.errors.password }}
            </div>
        </div>

        <!-- Submit Button -->
        <BaseButton type="submit" variant="primary" size="lg" full :disabled="form.processing" class="mt-2">
            {{ form.processing ? 'Memverifikasi...' : 'Konfirmasi' }}
        </BaseButton>
    </form>
</template>
