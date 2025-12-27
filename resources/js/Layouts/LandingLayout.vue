<script setup>
import NavBar from '@/Components/Landing/NavBar.vue';
import Footer from '@/Components/Landing/Footer.vue';
import { computed } from 'vue';

const props = defineProps({
    mainClass: {
        type: String,
        default: ''
    }
})

const mainClasses = computed(() => {
    return [
        'flex-grow',
        props.mainClass
    ]
})
</script>

<template>
    <div class="bg-white text-text-main font-display overflow-x-hidden antialiased selection:bg-primary/20 selection:text-primary min-h-screen flex flex-col">
        <NavBar />
        
        <main :class="mainClasses">
            <slot />
        </main>
        
        <Footer />
    </div>
</template>
