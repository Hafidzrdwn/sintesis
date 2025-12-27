<script setup>
import { ref, onMounted, computed } from 'vue';

const props = defineProps({
    value: String,
    label: String,
});

const displayValue = ref(0);
const statElement = ref(null);

const numericValue = computed(() => {
    // Extract numbers from string "500+" -> 500
    return parseInt(props.value.replace(/\D/g, '')) || 0;
});

const suffix = computed(() => {
    // Extract suffix "500+" -> "+"
    return props.value.replace(/[0-9]/g, '');
});

onMounted(() => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                startCounting();
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    if (statElement.value) {
        observer.observe(statElement.value);
    }
});

const startCounting = () => {
    const duration = 2000; // 2 seconds
    const start = 0;
    const end = numericValue.value;
    const startTime = performance.now();

    const animate = (currentTime) => {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Easing function (easeOutExpo)
        const ease = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
        
        displayValue.value = Math.floor(start + (end - start) * ease);

        if (progress < 1) {
            requestAnimationFrame(animate);
        } else {
            displayValue.value = end;
        }
    };

    requestAnimationFrame(animate);
};
</script>

<template>
    <div ref="statElement" class="p-4 transform hover:-translate-y-2 transition-transform duration-300">
        <p class="text-4xl font-black text-primary mb-2 flex justify-center items-center">
            <span>{{ displayValue }}</span>
            <span>{{ suffix }}</span>
        </p>
        <p class="text-sm font-semibold text-slate-600">{{ label }}</p>
    </div>
</template>
