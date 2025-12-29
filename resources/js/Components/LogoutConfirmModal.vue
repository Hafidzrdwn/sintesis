<script setup>
import { router } from '@inertiajs/vue3';
import BaseButton from '@/Components/BaseButton.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    logoutRoute: {
        type: String,
        default: 'logout'
    }
});

const emit = defineEmits(['close']);

const handleLogout = () => {
    router.post(route(props.logoutRoute), {}, {
        onSuccess: () => emit('close')
    });
};

const handleCancel = () => {
    emit('close');
};
</script>

<template>
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div 
                    class="absolute inset-0 bg-black/50 backdrop-blur-sm" 
                    @click="handleCancel"
                ></div>
                
                <div class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 transform transition-all">
                    <!-- Icon -->
                    <div class="flex justify-center mb-4">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-red-600 text-3xl">logout</span>
                        </div>
                    </div>
                    
                    <div class="text-center mb-6">
                        <h3 class="text-xl font-bold text-slate-900 mb-2">Keluar dari Akun?</h3>
                        <p class="text-slate-500 text-sm">
                            Anda akan keluar dari sesi saat ini. Pastikan semua pekerjaan sudah tersimpan.
                        </p>
                    </div>
                    
                    <div class="flex gap-3">
                        <BaseButton 
                            type="button" 
                            variant="secondary" 
                            full 
                            @click="handleCancel"
                        >
                            Batal
                        </BaseButton>
                        <BaseButton 
                            type="button" 
                            variant="danger" 
                            full 
                            @click="handleLogout"
                        >
                            <span class="material-symbols-outlined text-lg">logout</span>
                            Keluar
                        </BaseButton>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
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

.modal-enter-active .relative,
.modal-leave-active .relative {
    transition: transform 0.2s ease, opacity 0.2s ease;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
    transform: scale(0.95);
    opacity: 0;
}
</style>
