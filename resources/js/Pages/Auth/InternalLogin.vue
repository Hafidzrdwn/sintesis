<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    identity: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const showPassword = ref(false);
</script>

<template>
    <Head title="Login Internal Perusahaan - SINTESIS" />

    <div class="flex min-h-screen w-full font-display bg-white text-text-main antialiased">
        <!-- Left Side: Branding (Desktop Only) -->
        <div class="hidden lg:flex w-1/2 relative bg-primary-subtle overflow-hidden items-center justify-center p-12">
            <!-- Background Decorations -->
            <div class="absolute inset-0 opacity-[0.03] bg-cover bg-center mix-blend-multiply" style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuAd4b35-Jft82Igpb82JNsO2qYwDZgL9PmIzKNOnsJlPSGOOAhWQbr2ebG0UEIGVocVc-3odh5P3aojXcwrYFP6et7io199fdxZlwyA0miDfi3plAzgW9nWijJbDJB0JuwZOCu5Q1by6X8EUgqLSpGZQM056QIulLLBMmrwFqI_pfJv_1_EznXDUDw9f7Eut3MVG0sWSwuWILbTr794yO_6lLV-ToS-13ISe79SFW7mH10qvtiSfTndaheaUJDPULzmlTBcWUX2p_R3");'></div>
            <div class="absolute -top-24 -right-24 h-96 w-96 rounded-full bg-blue-200/40 blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 h-96 w-96 rounded-full bg-white/60 blur-3xl"></div>
            
            <div class="relative z-10 max-w-lg text-text-main">
                <div class="mb-8 flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white border border-blue-100 shadow-sm text-primary">
                        <span class="material-symbols-outlined text-3xl">hub</span>
                    </div>
                    <h1 class="text-3xl font-bold tracking-tight text-primary">SINTESIS</h1>
                </div>
                <h2 class="text-5xl font-black leading-tight tracking-tight mb-6 text-slate-900">
                    Membangun Masa Depan Talenta Digital.
                </h2>
                <p class="text-xl text-slate-600 font-medium leading-relaxed">
                    Platform manajemen internal terpadu untuk monitoring produktivitas, absensi, dan analitik peserta magang Inosoft Trans Sistem.
                </p>
                <div class="mt-12 flex gap-4 text-sm font-semibold text-slate-700">
                    <div class="flex items-center gap-2 rounded-lg bg-white/50 px-3 py-2 border border-white/60 shadow-sm backdrop-blur-sm">
                        <span class="material-symbols-outlined text-[20px] text-primary">verified_user</span>
                        <span>Secure Access</span>
                    </div>
                    <div class="flex items-center gap-2 rounded-lg bg-white/50 px-3 py-2 border border-white/60 shadow-sm backdrop-blur-sm">
                        <span class="material-symbols-outlined text-[20px] text-primary">analytics</span>
                        <span>Real-time Analytics</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="flex w-full lg:w-1/2 flex-col justify-center items-center p-6 lg:p-12 relative bg-white">
            <!-- Mobile Header -->
            <div class="lg:hidden absolute top-6 left-6 flex items-center gap-2 text-primary">
                <span class="material-symbols-outlined text-3xl">hub</span>
                <span class="font-bold text-xl">SINTESIS</span>
            </div>

            <div class="w-full max-w-md space-y-8 bg-white p-0 rounded-none shadow-none border-none">
                <div class="space-y-2">
                    <div class="inline-flex items-center rounded-full bg-primary-subtle px-3 py-1 text-xs font-bold uppercase tracking-wider text-primary border border-primary/20">
                        Internal Staff Only
                    </div>
                    <h2 class="text-3xl lg:text-4xl font-black tracking-tight text-slate-900">
                        Masuk ke SINTESIS
                    </h2>
                    <p class="text-slate-500 text-base">
                        Portal Internal - Admin & HRD Inosoft
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium leading-6 text-slate-700" for="identity">
                            Nama Pengguna / Email
                        </label>
                        <div class="relative rounded-lg shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="material-symbols-outlined text-slate-400 text-[20px]">person</span>
                            </div>
                            <input 
                                v-model="form.identity"
                                id="identity" 
                                name="identity" 
                                type="text" 
                                placeholder="admin@inosoft.com" 
                                class="block w-full rounded-lg border-0 py-3.5 pl-10 text-slate-900 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary bg-white sm:text-sm sm:leading-6 transition-all duration-200 hover:ring-slate-300"
                            />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium leading-6 text-slate-700" for="password">
                                Kata Sandi
                            </label>
                        </div>
                        <div class="relative rounded-lg shadow-sm group">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="material-symbols-outlined text-slate-400 text-[20px]">lock</span>
                            </div>
                            <input 
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                id="password" 
                                name="password" 
                                placeholder="••••••••" 
                                class="block w-full rounded-lg border-0 py-3.5 pl-10 pr-10 text-slate-900 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary bg-white sm:text-sm sm:leading-6 transition-all duration-200 hover:ring-slate-300"
                            />
                            <div 
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer text-slate-400 hover:text-slate-600 transition-colors"
                            >
                                <span class="material-symbols-outlined text-[20px]">{{ showPassword ? 'visibility_off' : 'visibility' }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <a href="#" class="text-sm font-semibold text-primary hover:text-primary-hover transition-colors">Lupa kata sandi?</a>
                        </div>
                    </div>

                    <div>
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="flex w-full justify-center rounded-lg bg-primary px-3 py-3.5 text-sm font-semibold leading-6 text-white shadow-md shadow-blue-200 hover:bg-primary-hover hover:shadow-lg hover:shadow-blue-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary transition-all duration-200 transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Masuk
                        </button>
                    </div>
                </form>

                <div class="pt-8 border-t border-slate-100">
                    <p class="text-center text-xs text-slate-400">
                        © 2025 SINTESIS. Hak cipta dilindungi.<br/>
                        Halaman ini terbatas untuk penggunaan internal perusahaan.
                    </p>
                    <div class="mt-4 flex justify-center gap-4">
                        <a href="#" class="text-xs text-slate-400 hover:text-primary transition-colors">Kebijakan Privasi</a>
                        <span class="text-xs text-slate-300">•</span>
                        <a href="#" class="text-xs text-slate-400 hover:text-primary transition-colors">Bantuan Teknis</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
