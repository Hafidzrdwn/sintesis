<script setup>
import { getDocumentUrl } from '@/utils/helpers';
import { Link } from '@inertiajs/vue3';

defineProps({
    title: String,
    type: String, 
    status: {
        type: String,
        default: 'Terbuka'
    },
    description: String,
    updatedAt: String,
    image: String,
    href: {
        type: String,
        default: '#'
    }
});
</script>

<template>
    <div 
        class="bg-white rounded-2xl overflow-hidden border border-slate-200 hover:shadow-xl transition-all duration-300 flex flex-col group"
        :class="{'grayscale opacity-75 hover:grayscale-0 hover:opacity-100': status === 'Ditutup'}"
    >
        <div class="h-48 bg-cover bg-center relative" :style="{
            backgroundImage: 
            image ? `url('${getDocumentUrl(image)}')` : 'url(/images/hero.png)',
         }">
            <div class="absolute inset-0 bg-slate-900/40 group-hover:bg-slate-900/30 transition-colors"></div>
            <div class="absolute top-4 left-4 bg-white/20 backdrop-blur-md border border-white/30 px-3 py-1 rounded-full">
                <span class="text-xs font-bold text-white uppercase tracking-wider">{{ type }}</span>
            </div>
        </div>
        <div class="p-6 flex flex-col flex-1">
            <div class="mb-4">
                <h3 class="text-xl font-bold text-slate-900 group-hover:text-primary transition-colors">{{ title }}</h3>
                <div class="mt-2">
                    <span 
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border"
                        :class="[
                            status === 'Terbuka' ? 'bg-emerald-100 text-emerald-800 border-transparent' : 'bg-slate-100 text-slate-600 border-slate-200'
                        ]"
                    >
                        <span 
                            class="w-1.5 h-1.5 rounded-full mr-1.5"
                            :class="[status === 'Terbuka' ? 'bg-emerald-500' : 'bg-slate-400']"
                        ></span>
                        {{ status }}
                    </span>
                </div>
            </div>
            <p class="text-slate-600 text-sm mb-6 line-clamp-3 flex-1">
                {{ description }}
            </p>
            <div class="flex items-center justify-between mt-auto pt-4 border-t border-slate-100">
                <span class="text-slate-400 text-xs font-medium">Updated: {{ updatedAt }}</span>
                <Link :href="href" 
                    v-if="status === 'Terbuka'"
                    class="text-sm font-bold text-primary hover:text-primary-hover flex items-center gap-1 cursor-pointer"
                >
                    Lihat Detail <span class="material-symbols-outlined text-sm">arrow_outward</span>
                </Link>
                 <button 
                    v-else
                    class="text-sm font-bold text-slate-400 cursor-not-allowed flex items-center gap-1" 
                    disabled
                >
                    Penuh <span class="material-symbols-outlined text-sm">lock</span>
                </button>
            </div>
        </div>
    </div>
</template>
