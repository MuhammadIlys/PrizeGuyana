
<script setup>
import { ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import Header from '@/Components/Front/Header.vue'
import Footer from '@/Components/Front/Footer.vue'
import ToastContainer from '@/Components/Toasts/ToastContainer.vue'
import UserSidebar from '@/Components/User/UserSidebar.vue'
import { useToast } from '@/Composables/useToast'

const page = usePage()

const { success, error } = useToast()

watch(
    () => page.props.flash?.success,
    (message) => {
        if (message) {
            success(message)
        }
    },
    { immediate: true }
)

watch(
    () => page.props.flash?.error,
    (message) => {
        if (message) {
            error(message)
        }
    },
    { immediate: true }
)

const isSidebarCollapsed = ref(false)

defineProps({
    appName: {
        type: String,
        default: 'Prize Guyana',
    },
})
</script>

<template>
    <div class="relative flex min-h-screen bg-[#0a0a0a] text-neutral-100">
        <UserSidebar
            :is-collapsed="isSidebarCollapsed"
            @toggle-collapse="isSidebarCollapsed = !isSidebarCollapsed"
        />

        <!-- Main Page Content Area -->
        <div
            class="min-w-0 flex-1 transition-[padding] duration-300"
            :class="isSidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64'"
        >
            <main class="p-4 sm:p-6 lg:p-8">
                <slot />
            </main>
        </div>

        <ToastContainer />
    </div>
</template>
