<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import FrontLayout from '@/Layouts/FrontLayout.vue';
import { useToast } from '@/Composables/useToast';

defineOptions({
    layout: FrontLayout,
});

defineProps({
    status: {
        type: String,
        default: null,
    },
});

const toast = useToast();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'), {
        onSuccess: () => {
            toast.success('Password reset link has been sent to your email.');
        },
        onError: () => {
            toast.error('Please check the form for errors and try again.');
        },
    });
};
</script>

<template>
    <Head title="Forgot Password" />

    <div class="min-h-[75vh] flex flex-col justify-center items-center px-4 py-12">
        <div class="w-full max-w-md">
            
            <!-- Header Section -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 mb-4 shadow-lg shadow-amber-500/5">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                    Forgot Password?
                </h1>
                <p class="mt-2 text-xs sm:text-sm text-neutral-400 leading-relaxed max-w-xs mx-auto">
                    No worries! Enter your registered email address and we'll send you a password reset link.
                </p>
            </div>

            <!-- Form Card -->
            <div class="bg-[#121212] border border-neutral-800/80 rounded-2xl p-6 sm:p-8 shadow-2xl backdrop-blur-md relative overflow-hidden">
                <!-- Top Accent Line -->
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-transparent via-amber-500 to-transparent opacity-50"></div>

                <!-- Status Banner (Laravel Session Status) -->
                <div v-if="status" class="mb-6 p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs sm:text-sm flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ status }}</span>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-xs font-semibold text-neutral-300 uppercase tracking-wider mb-2">
                            Email Address
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-neutral-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                autofocus
                                placeholder="name@example.com"
                                class="w-full pl-11 pr-4 py-3 bg-[#181818] border border-neutral-800 rounded-xl text-neutral-100 placeholder-neutral-500 text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors"
                                :class="{ 'border-rose-500/80 focus:border-rose-500 focus:ring-rose-500': form.errors.email }"
                            />
                        </div>
                        <p v-if="form.errors.email" class="mt-2 text-xs text-rose-400 font-medium">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-black font-extrabold text-sm tracking-wide uppercase transition-all duration-200 transform active:scale-[0.99] shadow-lg shadow-amber-500/20 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-black" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ form.processing ? 'Sending Link...' : 'Send Reset Link' }}</span>
                    </button>
                </form>

                <!-- Back to Login Footer -->
                <div class="mt-8 pt-6 border-t border-neutral-800/80 text-center">
                    <Link
                        :href="route('login')"
                        class="inline-flex items-center gap-2 text-xs font-semibold text-neutral-400 hover:text-amber-400 transition-colors group"
                    >
                        <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Back to Sign In</span>
                    </Link>
                </div>

            </div>
        </div>
    </div>
</template>