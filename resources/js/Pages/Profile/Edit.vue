<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, usePage, Link, router } from '@inertiajs/vue3';
import LandingLayout from '@/Layouts/LandingLayout.vue';
import BaseButton from '@/Components/BaseButton.vue';

defineOptions({ layout: LandingLayout });

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const form = useForm({
    name: user.value.name,
    phone: user.value.phone || '',
    avatar: null,
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const activeTab = ref('profile');
const showDeleteModal = ref(false);
const avatarPreview = ref(null);

const deleteForm = useForm({
    password: '',
});

// Avatar preview handler
const handleAvatarChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.avatar = file;
        avatarPreview.value = URL.createObjectURL(file);
    }
};

const triggerAvatarInput = () => {
    document.getElementById('avatar-input').click();
};

const updateProfile = () => {
    router.post(route('profile.update'), {
        _method: 'put',
        name: form.name,
        phone: form.phone,
        avatar: form.avatar,
    }, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            avatarPreview.value = null;
            form.avatar = null;
        },
        onError: (errors) => {
            form.errors = errors;
        },
    });
};

const updatePassword = () => {
    passwordForm.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
        },
    });
};

const deleteAccount = () => {
    deleteForm.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
        },
    });
};

// Current avatar to display
const displayAvatar = computed(() => {
    if (avatarPreview.value) return avatarPreview.value;
    return user.value.avatar;
});
</script>

<template>

    <Head title="Edit Profil" />

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-3">
                <Link href="/dashboard"
                    class="text-slate-400 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined">arrow_back</span>
                </Link>
                <h1 class="text-3xl font-bold text-slate-900">Pengaturan Profil</h1>
            </div>
            <p class="text-slate-500">Kelola informasi akun dan keamanan Anda.</p>
        </div>

        <!-- Tabs -->
        <div class="flex gap-1 mb-6 bg-slate-100 p-1 rounded-xl w-fit">
            <button @click="activeTab = 'profile'"
                class="px-5 py-2.5 rounded-lg text-sm font-medium transition-all cursor-pointer"
                :class="activeTab === 'profile' 
                    ? 'bg-white text-primary shadow-sm' 
                    : 'text-slate-600 hover:text-slate-900'">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">person</span>
                    Profil
                </span>
            </button>
            <button @click="activeTab = 'security'"
                class="px-5 py-2.5 rounded-lg text-sm font-medium transition-all cursor-pointer"
                :class="activeTab === 'security' 
                    ? 'bg-white text-primary shadow-sm' 
                    : 'text-slate-600 hover:text-slate-900'">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">lock</span>
                    Keamanan
                </span>
            </button>
        </div>

        <!-- Profile Tab -->
        <div v-show="activeTab === 'profile'" class="space-y-6">
            <!-- Profile Info Card -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h2 class="text-lg font-bold text-slate-900">Informasi Profil</h2>
                    <p class="text-sm text-slate-500 mt-1">Perbarui informasi dasar akun Anda.</p>
                </div>
                <form @submit.prevent="updateProfile" class="p-6 space-y-6">
                    <!-- Avatar Upload -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-3">Foto Profil</label>
                        <div class="flex items-center gap-6">
                            <div class="relative group cursor-pointer" @click="triggerAvatarInput">
                                <img v-if="displayAvatar" :src="displayAvatar" alt="Avatar"
                                    class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg" />
                                <div v-else
                                    class="w-24 h-24 rounded-full bg-primary/10 flex items-center justify-center text-primary text-3xl font-bold border-4 border-white shadow-lg">
                                    {{ user.name.charAt(0).toUpperCase() }}
                                </div>
                                <div class="absolute inset-0 bg-black/50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <span class="material-symbols-outlined text-white text-2xl">photo_camera</span>
                                </div>
                            </div>
                            <div>
                                <BaseButton type="button" variant="outline" size="sm" @click="triggerAvatarInput">
                                    <span class="material-symbols-outlined text-lg">upload</span>
                                    Unggah Foto
                                </BaseButton>
                                <p class="text-xs text-slate-500 mt-2">JPG, PNG, GIF. Maks 2MB.</p>
                            </div>
                            <input id="avatar-input" type="file" accept="image/*" class="hidden" @change="handleAvatarChange" />
                        </div>
                        <p v-if="form.errors.avatar" class="text-red-500 text-sm mt-2">{{ form.errors.avatar }}</p>
                    </div>

                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Lengkap</label>
                        <input v-model="form.name" type="text"
                            class="w-full h-12 px-4 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                            placeholder="Nama lengkap Anda" />
                        <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
                    </div>

                    <!-- Email (Display Only) -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Alamat Email
                            <span class="text-slate-400 font-normal">(Tidak dapat diubah)</span>
                        </label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-3 text-slate-400 text-[20px]">mail</span>
                            <input :value="user.email" type="email" disabled
                                class="w-full h-12 pl-10 pr-4 bg-slate-100 border border-slate-200 rounded-lg text-slate-500 cursor-not-allowed" />
                        </div>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nomor Telepon</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-3 text-slate-400 text-[20px]">call</span>
                            <input v-model="form.phone" type="tel"
                                class="w-full h-12 pl-10 pr-4 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                                placeholder="08123456789" />
                        </div>
                        <p v-if="form.errors.phone" class="text-red-500 text-sm mt-1">{{ form.errors.phone }}</p>
                    </div>

                    <!-- Email Verification Notice -->
                    <div v-if="mustVerifyEmail && !user.email_verified_at"
                        class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-amber-600">warning</span>
                            <div>
                                <p class="text-amber-800 font-medium">Email belum diverifikasi</p>
                                <p class="text-amber-700 text-sm mt-1">
                                    Silakan cek inbox email Anda dan klik link verifikasi.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Success Message -->
                    <div v-if="status === 'profile-updated'"
                        class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center gap-2 text-green-700">
                            <span class="material-symbols-outlined">check_circle</span>
                            <span class="font-medium">Profil berhasil diperbarui!</span>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4">
                        <BaseButton type="submit" :disabled="form.processing">
                            <span class="material-symbols-outlined" v-if="!form.processing">save</span>
                            <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}</span>
                        </BaseButton>
                    </div>
                </form>
            </div>
        </div>

        <!-- Security Tab -->
        <div v-show="activeTab === 'security'" class="space-y-6">
            <!-- Change Password Card -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h2 class="text-lg font-bold text-slate-900">Ubah Password</h2>
                    <p class="text-sm text-slate-500 mt-1">Pastikan password Anda aman dan mudah diingat.</p>
                </div>
                <form @submit.prevent="updatePassword" class="p-6 space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Password Saat Ini</label>
                        <input v-model="passwordForm.current_password" type="password"
                            class="w-full h-12 px-4 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                            placeholder="Masukkan password saat ini" />
                        <p v-if="passwordForm.errors.current_password" class="text-red-500 text-sm mt-1">
                            {{ passwordForm.errors.current_password }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Password Baru</label>
                        <input v-model="passwordForm.password" type="password"
                            class="w-full h-12 px-4 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                            placeholder="Masukkan password baru" />
                        <p v-if="passwordForm.errors.password" class="text-red-500 text-sm mt-1">
                            {{ passwordForm.errors.password }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Konfirmasi Password Baru</label>
                        <input v-model="passwordForm.password_confirmation" type="password"
                            class="w-full h-12 px-4 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                            placeholder="Ulangi password baru" />
                        <p v-if="passwordForm.errors.password_confirmation" class="text-red-500 text-sm mt-1">
                            {{ passwordForm.errors.password_confirmation }}
                        </p>
                    </div>

                    <!-- Success Message -->
                    <div v-if="passwordForm.recentlySuccessful"
                        class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center gap-2 text-green-700">
                            <span class="material-symbols-outlined">check_circle</span>
                            <span class="font-medium">Password berhasil diubah!</span>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4">
                        <BaseButton type="submit" :disabled="passwordForm.processing">
                            <span class="material-symbols-outlined" v-if="!passwordForm.processing">lock_reset</span>
                            <span>{{ passwordForm.processing ? 'Mengubah...' : 'Ubah Password' }}</span>
                        </BaseButton>
                    </div>
                </form>
            </div>

            <!-- Danger Zone Card -->
            <div class="bg-white rounded-xl border border-red-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-red-100 bg-red-50">
                    <h2 class="text-lg font-bold text-red-700">Zona Berbahaya</h2>
                    <p class="text-sm text-red-600 mt-1">Tindakan di bawah ini bersifat permanen.</p>
                </div>
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-slate-900">Hapus Akun</p>
                            <p class="text-sm text-slate-500 mt-1">
                                Setelah dihapus, semua data Anda akan hilang secara permanen.
                            </p>
                        </div>
                        <BaseButton variant="outlineDanger" @click="showDeleteModal = true">
                            <span class="material-symbols-outlined">delete_forever</span>
                            Hapus Akun
                        </BaseButton>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Account Modal -->
    <Transition name="modal">
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50" @click="showDeleteModal = false"></div>
            <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                <div class="text-center mb-6">
                    <div
                        class="w-16 h-16 mx-auto bg-red-100 rounded-full flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-red-600 text-3xl">warning</span>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900">Hapus Akun?</h3>
                    <p class="text-slate-500 mt-2">
                        Tindakan ini tidak dapat dibatalkan. Semua data Anda akan dihapus secara permanen.
                    </p>
                </div>
                <form @submit.prevent="deleteAccount" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Masukkan password untuk konfirmasi
                        </label>
                        <input v-model="deleteForm.password" type="password"
                            class="w-full h-12 px-4 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 transition-all"
                            placeholder="Password Anda" />
                        <p v-if="deleteForm.errors.password" class="text-red-500 text-sm mt-1">
                            {{ deleteForm.errors.password }}
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <BaseButton type="button" variant="secondary" full @click="showDeleteModal = false">
                            Batal
                        </BaseButton>
                        <BaseButton type="submit" variant="danger" full :disabled="deleteForm.processing">
                            {{ deleteForm.processing ? 'Menghapus...' : 'Ya, Hapus Akun' }}
                        </BaseButton>
                    </div>
                </form>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
</style>
