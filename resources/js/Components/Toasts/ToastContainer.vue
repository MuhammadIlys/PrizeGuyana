<script setup>
import { useToast } from '@/Composables/useToast';

const { toasts, removeToast } = useToast();
</script>

<template>
    <div class="fixed bottom-5 right-5 z-50 flex flex-col gap-3 max-w-sm w-full pointer-events-none px-4 sm:px-0">
        <TransitionGroup 
            enter-active-class="transition duration-300 ease-out" 
            enter-from-class="transform translate-y-4 opacity-0 scale-95" 
            enter-to-class="transform translate-y-0 opacity-100 scale-100" 
            leave-active-class="transition duration-200 ease-in" 
            leave-from-class="transform translate-y-0 opacity-100 scale-100" 
            leave-to-class="transform translate-y-2 opacity-0 scale-95"
        >
            <div 
                v-for="toast in toasts" 
                :key="toast.id" 
                class="pointer-events-auto flex items-center justify-between gap-3 p-4 rounded-xl border bg-[#121212]/95 backdrop-blur-md shadow-2xl text-sm font-medium transition-all"
                :class="{
                    'border-amber-500/40 text-amber-300 shadow-amber-500/10': toast.type === 'warning',
                    'border-emerald-500/40 text-emerald-300 shadow-emerald-500/10': toast.type === 'success',
                    'border-rose-500/40 text-rose-300 shadow-rose-500/10': toast.type === 'error',
                    'border-sky-500/40 text-sky-300 shadow-sky-500/10': toast.type === 'info',
                }"
            >
                <div class="flex items-center gap-3">
                    <!-- Warning Icon -->
                    <svg v-if="toast.type === 'warning'" class="w-5 h-5 text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>

                    <!-- Success Icon -->
                    <svg v-else-if="toast.type === 'success'" class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>

                    <!-- Message Text -->
                    <span class="text-xs sm:text-sm font-semibold tracking-wide text-neutral-200">
                        {{ toast.message }}
                    </span>
                </div>

                <!-- Close Button -->
                <button 
                    @click="removeToast(toast.id)" 
                    type="button" 
                    class="text-neutral-500 hover:text-white transition-colors p-1"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>