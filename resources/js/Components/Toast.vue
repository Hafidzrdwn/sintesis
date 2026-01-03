<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { CheckCircle, XCircle, AlertTriangle, Info } from 'lucide-vue-next';

const page = usePage();
const toast = ref({ show: false, type: '', message: '' });

const showToast = (type, message) => {
  toast.value = { show: true, type, message };
  setTimeout(() => toast.value.show = false, 4000);
};

watch(() => page.props.flash, (flash) => {
  if (flash?.success) showToast('success', flash.success);
  if (flash?.error) showToast('error', flash.error);
  if (flash?.warning) showToast('warning', flash.warning);
  if (flash?.info) showToast('info', flash.info);
}, { immediate: true, deep: true });

defineExpose({ showToast });
</script>

<template>
  <Teleport to="body">
    <Transition enter-active-class="transition ease-out duration-300"
      enter-from-class="opacity-0 translate-y-2 scale-95" enter-to-class="opacity-100 translate-y-0 scale-100"
      leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100 translate-y-0 scale-100"
      leave-to-class="opacity-0 translate-y-2 scale-95">
      <div v-if="toast.show"
        class="fixed bottom-6 right-6 z-50 px-5 py-3.5 rounded-xl shadow-lg text-white font-medium flex items-center gap-3 max-w-sm"
        :class="{
          'bg-emerald-500': toast.type === 'success',
          'bg-red-500': toast.type === 'error',
          'bg-amber-500': toast.type === 'warning',
          'bg-blue-500': toast.type === 'info',
        }">
        <CheckCircle v-if="toast.type === 'success'" class="w-5 h-5 shrink-0" />
        <XCircle v-else-if="toast.type === 'error'" class="w-5 h-5 shrink-0" />
        <AlertTriangle v-else-if="toast.type === 'warning'" class="w-5 h-5 shrink-0" />
        <Info v-else class="w-5 h-5 shrink-0" />
        <span>{{ toast.message }}</span>
        <button @click="toast.show = false"
          class="ml-auto p-1 hover:bg-white/20 rounded-lg transition-colors cursor-pointer">
          <XCircle class="w-4 h-4" />
        </button>
      </div>
    </Transition>
  </Teleport>
</template>
