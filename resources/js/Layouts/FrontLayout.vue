<script setup>
import { Link } from '@inertiajs/vue3';
import Header from '@/Components/Front/Header.vue';
import Footer from '@/Components/Front/Footer.vue';
import ToastContainer from '@/Components/Toasts/ToastContainer.vue';

defineProps({
    appName: {
        type: String,
        default: 'Prize Guyana',
    },
});
</script>

<template>
    <div class="min-h-screen flex flex-col font-sans">
        <!-- Main Public Header -->
        <Header />

        <!-- Page Content (Slot Injection) -->
        <main class="flex-1 w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <slot />
        </main>

        <!-- Public Footer -->
        <Footer />

        <!-- Global Toast Container -->
        <ToastContainer />
    </div>
</template>