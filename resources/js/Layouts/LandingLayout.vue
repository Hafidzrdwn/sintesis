<script setup>
import NavBar from '@/Components/Landing/NavBar.vue';
import Footer from '@/Components/Landing/Footer.vue';
</script>

<template>
    <div class="bg-white text-text-main font-display overflow-x-hidden antialiased selection:bg-primary/20 selection:text-primary min-h-screen flex flex-col">
        <NavBar />
        
        <main class="flex-grow">
            <slot />
        </main>
        
        <Footer />
    </div>
</template>
