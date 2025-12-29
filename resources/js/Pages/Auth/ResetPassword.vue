<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import BaseButton from '@/Components/BaseButton.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Eye, EyeOff, ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

defineOptions({ layout: AuthLayout });
</script>

<template>

    <Head title="Reset Kata Sandi" />

    <!-- Header Text -->
    <div class="flex flex-col items-center gap-2 mb-8 text-center">
        <h3 class="text-2xl font-bold leading-tight text-slate-900">Reset Kata Sandi</h3>
        <p class="text-slate-500 text-[15px] leading-normal">
            Masukkan kata sandi baru untuk akun Anda.
        </p>
    </div>

    <form @submit.prevent="submit" class="flex flex-col gap-5">
        <!-- Email Input (Read Only) -->
        <div class="flex flex-col gap-2">
            <label class="text-base font-medium text-slate-900" for="email">Email</label>
            <input
                class="form-input block w-full rounded-lg border bg-slate-100 text-slate-600 placeholder:text-slate-400 h-12 px-4 text-base cursor-not-allowed"
                id="email" v-model="form.email" type="email" readonly />
            <div v-if="form.errors.email" class="text-sm text-danger">
                {{ form.errors.email }}
            </div>
        </div>

        <!-- Password Input -->
        <div class="flex flex-col gap-2">
            <label class="text-base font-medium text-slate-900" for="password">Kata Sandi Baru</label>
            <div class="relative group">
                <input
                    class="form-input block w-full rounded-lg border bg-slate-50 text-slate-900 placeholder:text-slate-400 h-12 px-4 pr-10 text-base transition-shadow focus:outline-0 focus:ring-2"
                    :class="{
                        'border-slate-300 focus:ring-primary/50': !form.errors.password,
                        'border-danger focus:ring-danger/50': form.errors.password
                    }" id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'"
                    placeholder="Minimal 8 karakter" required autofocus />
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

        <!-- Confirm Password Input -->
        <div class="flex flex-col gap-2">
            <label class="text-base font-medium text-slate-900" for="password_confirmation">Konfirmasi Kata
                Sandi</label>
            <div class="relative group">
                <input
                    class="form-input block w-full rounded-lg border bg-slate-50 text-slate-900 placeholder:text-slate-400 h-12 px-4 pr-10 text-base transition-shadow focus:outline-0 focus:ring-2"
                    :class="{
                        'border-slate-300 focus:ring-primary/50': !form.errors.password_confirmation,
                        'border-danger focus:ring-danger/50': form.errors.password_confirmation
                    }" id="password_confirmation" v-model="form.password_confirmation"
                    :type="showConfirmPassword ? 'text' : 'password'" placeholder="Ulangi kata sandi baru" required />
                <button type="button"
                    class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600 transition-colors cursor-pointer"
                    @click="showConfirmPassword = !showConfirmPassword">
                    <EyeOff v-if="showConfirmPassword" class="w-5 h-5" />
                    <Eye v-else class="w-5 h-5" />
                </button>
            </div>
            <div v-if="form.errors.password_confirmation" class="text-sm text-danger">
                {{ form.errors.password_confirmation }}
            </div>
        </div>

        <!-- Submit Button -->
        <BaseButton type="submit" variant="primary" size="lg" full :disabled="form.processing" class="mt-2">
            {{ form.processing ? 'Menyimpan...' : 'Reset Kata Sandi' }}
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
