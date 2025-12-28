<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    User, 
    Lock, 
    Bell, 
    Camera, 
    Save,
    LogOut,
    ShieldCheck
} from 'lucide-vue-next';

defineOptions({ layout: AuthenticatedLayout });

const activeSection = ref('profile');

const menuItems = [
    { id: 'profile', label: 'Profil Saya', icon: User },
    { id: 'security', label: 'Keamanan', icon: Lock },
    { id: 'notifications', label: 'Notifikasi', icon: Bell },
];

// Mock Form Data
const profileForm = ref({
    name: 'Hafidz Ridwan',
    university: 'Universitas Indonesia',
    bio: 'UI/UX Designer Intern yang antusias belajar hal baru.',
    email: 'hafidz@student.ui.ac.id'
});

const passwordForm = ref({
    current: '',
    new: '',
    confirm: ''
});

const notificationsForm = ref({
    emailUpdates: true,
    taskReminders: true,
    mentorFeedback: true
});
</script>

<template>
    <Head title="Pengaturan Akun" />

    <div class="flex flex-col gap-8">
        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Pengaturan</h1>
            <p class="text-slate-500 mt-1">Kelola informasi profil dan preferensi akun Anda.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Sidebar Menu -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden sticky top-8">
                    <nav class="flex flex-col p-2">
                        <button 
                            v-for="item in menuItems" 
                            :key="item.id"
                            @click="activeSection = item.id"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all"
                            :class="activeSection === item.id ? 'bg-primary/10 text-primary' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
                        >
                            <component :is="item.icon" class="w-5 h-5" />
                            {{ item.label }}
                        </button>
                         <div class="h-px bg-slate-100 my-2"></div>
                         <button class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-red-600 hover:bg-red-50 transition-all text-left w-full">
                            <LogOut class="w-5 h-5" />
                            Keluar Sesi
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Content -->
            <div class="lg:col-span-3 flex flex-col gap-6">
                <!-- Profile Section -->
                <div v-if="activeSection === 'profile'" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 animate-in fade-in slide-in-from-right-4 duration-300">
                    <div class="flex items-center justify-between mb-8 border-b border-slate-100 pb-4">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">Profil Saya</h2>
                            <p class="text-sm text-slate-500">Informasi pribadi yang terlihat oleh mentor.</p>
                        </div>
                        <User class="w-8 h-8 text-primary/20" />
                    </div>

                    <div class="flex flex-col gap-8">
                         <!-- Photo Upload -->
                        <div class="flex items-center gap-6">
                            <div class="relative group">
                                <div class="w-24 h-24 rounded-full bg-slate-200 flex items-center justify-center text-slate-500 text-2xl font-bold border-4 border-slate-50 shadow-sm overflow-hidden">
                                     <!-- Placeholder/Image -->
                                     <span v-if="!profileForm.avatar">H</span>
                                </div>
                                <button class="absolute bottom-0 right-0 p-2 bg-primary text-white rounded-full shadow-lg hover:bg-primary-hover transition-all active:scale-95" title="Ubah Foto">
                                    <Camera class="w-4 h-4" />
                                </button>
                            </div>
                            <div class="flex flex-col">
                                <h3 class="font-bold text-slate-900">Foto Profil</h3>
                                <p class="text-xs text-slate-500 max-w-[200px] mt-1">Format JPG, GIF atau PNG. Maksimal ukuran 800KB.</p>
                            </div>
                        </div>

                        <!-- Form Fields -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-semibold text-slate-700">Nama Lengkap</label>
                                <input v-model="profileForm.name" type="text" class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-medium">
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-semibold text-slate-700">Email</label>
                                <input v-model="profileForm.email" type="email" class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-500 cursor-not-allowed text-sm font-medium" disabled>
                            </div>
                            <div class="col-span-1 md:col-span-2 flex flex-col gap-2">
                                <label class="text-sm font-semibold text-slate-700">Universitas / Institusi</label>
                                <input v-model="profileForm.university" type="text" class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-medium">
                            </div>
                            <div class="col-span-1 md:col-span-2 flex flex-col gap-2">
                                <label class="text-sm font-semibold text-slate-700">Bio Singkat</label>
                                <textarea v-model="profileForm.bio" rows="4" class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-medium resize-none"></textarea>
                                <p class="text-xs text-slate-400 text-right">{{ profileForm.bio.length }}/200</p>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-slate-100">
                             <button class="flex items-center gap-2 px-6 py-2.5 bg-primary text-white rounded-xl font-bold hover:bg-primary-hover shadow-lg shadow-primary/20 transition-all active:scale-95">
                                <Save class="w-4 h-4" />
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Security Section -->
                <div v-if="activeSection === 'security'" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 animate-in fade-in slide-in-from-right-4 duration-300">
                     <div class="flex items-center justify-between mb-8 border-b border-slate-100 pb-4">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">Keamanan</h2>
                            <p class="text-sm text-slate-500">Perbarui kata sandi dan keamanan akun.</p>
                        </div>
                        <ShieldCheck class="w-8 h-8 text-primary/20" />
                    </div>
                    
                    <div class="max-w-md flex flex-col gap-6">
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-semibold text-slate-700">Password Saat Ini</label>
                            <input v-model="passwordForm.current" type="password" class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-medium">
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-semibold text-slate-700">Password Baru</label>
                            <input v-model="passwordForm.new" type="password" class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-medium">
                        </div>
                         <div class="flex flex-col gap-2">
                            <label class="text-sm font-semibold text-slate-700">Konfirmasi Password Baru</label>
                            <input v-model="passwordForm.confirm" type="password" class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-medium">
                        </div>

                        <div class="pt-4">
                             <button class="flex items-center gap-2 px-6 py-2.5 bg-slate-900 text-white rounded-xl font-bold hover:bg-slate-800 shadow-lg shadow-slate-900/20 transition-all active:scale-95">
                                <Save class="w-4 h-4" />
                                Update Password
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Notifications Section -->
                <div v-if="activeSection === 'notifications'" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 animate-in fade-in slide-in-from-right-4 duration-300">
                     <div class="flex items-center justify-between mb-8 border-b border-slate-100 pb-4">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">Notifikasi</h2>
                            <p class="text-sm text-slate-500">Atur preferensi notifikasi email dan sistem.</p>
                        </div>
                        <Bell class="w-8 h-8 text-primary/20" />
                    </div>

                    <div class="flex flex-col gap-6">
                        <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-200">
                            <div>
                                <h3 class="font-bold text-slate-900">Update Email</h3>
                                <p class="text-sm text-slate-500">Terima ringkasan mingguan ke email Anda.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="notificationsForm.emailUpdates" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/30 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            </label>
                        </div>

                         <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-200">
                            <div>
                                <h3 class="font-bold text-slate-900">Pengingat Tugas</h3>
                                <p class="text-sm text-slate-500">Notifikasi saat tenggat waktu mendekat.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="notificationsForm.taskReminders" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/30 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            </label>
                        </div>

                         <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-200">
                            <div>
                                <h3 class="font-bold text-slate-900">Feedback Mentor</h3>
                                <p class="text-sm text-slate-500">Notifikasi instan saat mentor memberi feedback.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="notificationsForm.mentorFeedback" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/30 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
