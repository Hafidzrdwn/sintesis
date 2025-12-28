<script setup>
import BaseButton from '@/Components/BaseButton.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
    },
});

const page = usePage();
const flash = computed(() => page.props.flash);
const user = computed(() => page.props.auth.user);

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);

defineOptions({ layout: AuthLayout })
</script>

<template>
    <Head title="Verifikasi Email" />

    <!-- Header Text -->
    <div class="flex flex-col items-center gap-3 mb-6 text-center">
        <!-- Email Icon -->
        <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mb-2">
            <span class="material-symbols-outlined text-primary text-3xl">mark_email_unread</span>
        </div>
        <h3 class="text-2xl font-bold leading-tight text-slate-900">Verifikasi Email Anda</h3>
        <p class="text-slate-500 text-[15px] leading-relaxed max-w-sm">
            Terima kasih telah mendaftar! Sebelum melanjutkan, silakan verifikasi alamat email Anda dengan mengklik
            tautan yang baru saja kami kirimkan.
        </p>
    </div>

    <!-- User Email Display -->
    <div v-if="user" class="mb-6 p-4 bg-slate-50 rounded-lg border border-slate-200 text-center">
        <p class="text-sm text-slate-500">Email terdaftar:</p>
        <p class="text-base font-semibold text-slate-900">{{ user.email }}</p>
    </div>

    <!-- Success Message -->
    <div v-if="verificationLinkSent"
        class="mb-6 rounded-lg bg-green-50 p-4 text-sm text-green-700 border border-green-200 flex items-start gap-3">
        <span class="material-symbols-outlined text-green-600 text-xl flex-shrink-0">check_circle</span>
        <p>
            Tautan verifikasi baru telah dikirim ke alamat email yang Anda daftarkan.
            Silakan periksa kotak masuk atau folder spam Anda.
        </p>
    </div>

    <!-- Flash Messages -->
    <div v-if="flash?.error" class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-600 border border-red-200">
        {{ flash.error }}
    </div>
    <div v-if="flash?.success" class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-600 border border-green-200">
        {{ flash.success }}
    </div>

    <!-- Info Box -->
    <div class="mb-6 rounded-lg bg-amber-50 p-4 text-sm text-amber-700 border border-amber-200 flex items-start gap-3">
        <span class="material-symbols-outlined text-amber-600 text-xl flex-shrink-0">info</span>
        <p>
            Tidak menerima email? Periksa folder spam Anda atau klik tombol di bawah untuk mengirim ulang.
        </p>
    </div>

    <form @submit.prevent="submit" class="flex flex-col gap-4">
        <!-- Resend Button -->
        <BaseButton type="submit" size="lg" :loading="form.processing" :disabled="form.processing">
            <span class="material-symbols-outlined text-xl">send</span>
            Kirim Ulang Email Verifikasi
        </BaseButton>

        <!-- Logout Link -->
        <div class="text-center">
            <Link :href="route('logout')" method="post" as="button"
                class="text-sm font-medium text-slate-500 hover:text-primary transition-colors inline-flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">logout</span>
                Keluar dari Akun
            </Link>
        </div>
    </form>

    <!-- Footer Info -->
    <div class="mt-8 pt-6 border-t border-slate-200 text-center">
        <p class="text-xs text-slate-400">
            Setelah verifikasi, Anda akan dapat mengakses semua fitur SINTESIS.
        </p>
    </div>
</template>
