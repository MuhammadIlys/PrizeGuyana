<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import logoUrl from '/resources/images/logo.png';
import FrontLayout from '../../Layouts/FrontLayout.vue';
import { ref } from 'vue';

defineOptions({
    layout: FrontLayout,
});

const showPassword = ref(false);

defineProps({
    status: String,
    canResetPassword: {
        type: Boolean,
        default: true,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('user.login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>

    <Head title="Log In" />

    <div class="min-h-screen w-full bg-[#0a0a0a] text-white flex flex-col justify-center items-center px-4 py-12 relative overflow-hidden font-sans"
        style="background: radial-gradient(circle at 0% 0%, rgba(130, 65, 10, 0.35) 0%, rgba(20, 15, 10, 0.7) 40%, rgba(10, 10, 10, 1) 80%);">
        <!-- Background Top Border Line -->
        <div
            class="absolute top-0 left-0 right-0 h-[1px] bg-gradient-to-r from-transparent via-amber-500/30 to-transparent">
        </div>

        <!-- Container Card -->
        <div class="w-full max-w-md z-10">

            <!-- Logo Header -->
            <div class="text-center mb-8">
                <Link :href="route('home')" class="inline-block">
                    <!-- Adjust logo image path as needed -->
                    <img :src="logoUrl" alt="Logo" class="h-16 mx-auto object-contain drop-shadow-md" />
                </Link>
                <h1 class="mt-4 text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                    Welcome back
                </h1>
                <p class="mt-1.5 text-xs sm:text-sm text-neutral-400">
                    Enter your details to access your account &amp; tickets
                </p>
            </div>

            <!-- Main Login Form Box -->
            <div
                class="bg-[#121212]/90 backdrop-blur-md border border-neutral-800/90 rounded-2xl p-6 sm:p-8 shadow-2xl relative">

                <!-- Top Subtle Card Glow Accent -->
                <div
                    class="absolute top-0 left-1/4 right-1/4 h-[1px] bg-gradient-to-r from-transparent via-amber-500/40 to-transparent">
                </div>

                <!-- Session Status (e.g. Password Reset Message) -->
                <div v-if="status"
                    class="mb-4 text-xs text-emerald-400 font-semibold bg-emerald-500/10 p-3 rounded-lg border border-emerald-500/20 text-center">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-5">

                    <!-- Email Input -->
                    <div>
                        <label for="email"
                            class="block text-xs font-bold text-neutral-300 uppercase tracking-wider mb-2">
                            Email Address
                        </label>
                        <div
                            class="relative rounded-xl bg-[#0a0a0a] border border-neutral-800 focus-within:border-amber-500 transition-colors shadow-inner">
                            <input id="email" v-model="form.email" type="email" required autofocus
                                autocomplete="username" placeholder="you@example.com"
                                class="w-full bg-transparent px-4 py-3 text-sm text-white placeholder-neutral-600 focus:outline-none border-none ring-0 focus:ring-0 rounded-xl" />
                        </div>
                        <p v-if="form.errors.email" class="mt-1.5 text-xs text-red-500 font-medium">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Password Input -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="password"
                                class="block text-xs font-bold text-neutral-300 uppercase tracking-wider">
                                Password
                            </label>
                            <Link v-if="canResetPassword" :href="route('forgot.password')"
                                class="text-xs text-amber-500 hover:text-amber-400 font-medium transition-colors">
                                Forgot password?
                            </Link>
                        </div>
                        <div
                            class="relative rounded-xl bg-[#0a0a0a] border border-neutral-800 focus-within:border-amber-500 transition-colors shadow-inner flex items-center">
                            <input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'"
                                required autocomplete="current-password" placeholder="••••••••"
                                class="w-full bg-transparent pl-4 pr-11 py-3 text-sm text-white placeholder-neutral-600 focus:outline-none border-none ring-0 focus:ring-0 rounded-xl" />

                            <!-- Toggle Show/Hide Password Button -->
                            <button type="button" @click="showPassword = !showPassword"
                                class="cursor-pointer absolute right-3 text-neutral-400 hover:text-amber-400 focus:outline-none transition-colors p-1"
                                :title="showPassword ? 'Hide password' : 'Show password'">

                                <!-- Eye Icon (Show) -->
                                <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>

                                <!-- Eye Off Icon (Hide) -->
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.025 10.025 0 013.222-.863c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="mt-1.5 text-xs text-red-500 font-medium">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Remember Me Checkbox -->
                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input v-model="form.remember" type="checkbox"
                                class="w-4 h-4 rounded bg-[#0a0a0a] border-neutral-700 text-amber-500 focus:ring-amber-500/20 focus:ring-offset-0 transition cursor-pointer" />
                            <span class="text-xs text-neutral-400 font-medium">Remember me</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" :disabled="form.processing"
                        class="cursor-pointer w-full py-3.5 px-4 bg-gradient-to-r from-amber-400 via-yellow-500 to-amber-500 hover:brightness-110 active:scale-[0.99] text-black font-extrabold text-sm rounded-xl transition-all shadow-lg border border-yellow-300/30 disabled:opacity-50 mt-2">
                        {{ form.processing ? 'Signing In...' : 'Sign In' }}
                    </button>

                </form>

                <!-- Divider Line -->
                <div class="relative my-6 text-center">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-neutral-800"></div>
                    </div>
                    <span class="relative bg-[#121212] px-3 text-xs text-neutral-500 uppercase font-semibold">
                        Or
                    </span>
                </div>

                <!-- Register Redirect Link -->
                <div class="text-center">
                    <p class="text-xs text-neutral-400 font-medium">
                        Don't have an account?
                        <Link :href="route('register')" class="text-amber-500 hover:underline font-bold ml-1">
                            Create an account
                        </Link>
                    </p>
                </div>

            </div>

            <!-- Footer Privacy Link -->
            <div class="mt-8 text-center text-xs text-neutral-500">
                <p>By logging in, you agree to our
                    <Link :href="route('home')" class="underline hover:text-neutral-400">Terms</Link> and
                    <Link :href="route('home')" class="underline hover:text-neutral-400">Privacy Policy</Link>.
                </p>
            </div>

        </div>
    </div>


</template>