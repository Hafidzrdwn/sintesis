<script setup>
import { AlertTriangle, X } from 'lucide-vue-next';

const props = defineProps({
  show: { type: Boolean, default: false },
  title: { type: String, default: 'Konfirmasi' },
  message: { type: String, default: 'Apakah Anda yakin?' },
  confirmText: { type: String, default: 'Ya, Lanjutkan' },
  cancelText: { type: String, default: 'Batal' },
  variant: { type: String, default: 'danger' }, // danger, warning, info
  loading: { type: Boolean, default: false },
});

const emit = defineEmits(['confirm', 'cancel']);

const variantClasses = {
  danger: {
    icon: 'bg-red-100 text-red-600',
    button: 'bg-red-500 hover:bg-red-600 text-white',
  },
  warning: {
    icon: 'bg-amber-100 text-amber-600',
    button: 'bg-amber-500 hover:bg-amber-600 text-white',
  },
  info: {
    icon: 'bg-blue-100 text-blue-600',
    button: 'bg-blue-500 hover:bg-blue-600 text-white',
  },
};

const classes = variantClasses[props.variant] || variantClasses.danger;
</script>

<template>
  <Teleport to="body">
    <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0"
      enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100"
      leave-to-class="opacity-0">
      <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="emit('cancel')"></div>

        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-150 ease-in"
          leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
          <div v-if="show" class="relative bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden">
            <div class="p-6 text-center">
              <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center" :class="classes.icon">
                <AlertTriangle class="w-8 h-8" />
              </div>
              <h3 class="text-xl font-bold text-slate-900 mb-2">{{ title }}</h3>
              <p class="text-slate-500">{{ message }}</p>
            </div>

            <div class="flex gap-3 p-4 border-t border-slate-200 bg-slate-50">
              <button @click="emit('cancel')" :disabled="loading"
                class="flex-1 px-4 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-semibold hover:bg-slate-100 disabled:opacity-50 cursor-pointer">
                {{ cancelText }}
              </button>
              <button @click="emit('confirm')" :disabled="loading"
                class="flex-1 px-4 py-2.5 rounded-xl font-semibold disabled:opacity-50 cursor-pointer"
                :class="classes.button">
                {{ loading ? 'Loading...' : confirmText }}
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>
