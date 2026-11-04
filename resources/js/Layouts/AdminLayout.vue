<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useToast } from '@/Composables/useToast';

import AdminSidebar from '@/Components/Admin/AdminSidebar.vue';
import ToastContainer from '@/Components/Toasts/ToastContainer.vue';

const page = usePage();

const {
    success,
    error,
} = useToast();

watch(
    () => page.props.flash?.success,
    (message) => {
        if (message) {
            success(message);
        }
    },
    { immediate: true }
);

watch(
    () => page.props.flash?.error,
    (message) => {
        if (message) {
            error(message);
        }
    },
    { immediate: true }
);

const isSidebarOpen = ref(false);
const isSidebarCollapsed = ref(false);
</script>

<template>
    <div class="min-h-screen bg-[#0d0d0d] text-white">

        <button type="button" @click="isSidebarOpen = true" aria-label="Open admin navigation"
            :aria-expanded="isSidebarOpen" aria-controls="admin-sidebar"
            class="fixed left-4 top-4 z-40 rounded-lg border border-neutral-800 bg-[#121212] p-2.5 text-neutral-300 shadow-lg transition-colors hover:bg-neutral-800 hover:text-white lg:hidden">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- Fixed Sidebar -->
        <AdminSidebar :is-open="isSidebarOpen" :is-collapsed="isSidebarCollapsed" @close="isSidebarOpen = false"
            @toggle-collapse="isSidebarCollapsed = !isSidebarCollapsed" />

        <!-- Dashboard Content -->
        <main class="min-h-screen transition-[margin] duration-200"
            :class="isSidebarCollapsed ? 'lg:ml-20' : 'lg:ml-64'">
            <div class="p-6 pt-20 lg:p-8 lg:pt-8">
                <slot />
            </div>
        </main>

    </div>
    <ToastContainer />

</template>