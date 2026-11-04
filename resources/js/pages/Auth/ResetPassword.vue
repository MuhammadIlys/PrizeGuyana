<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import FrontLayout from '@/Layouts/FrontLayout.vue';

defineOptions({
    layout: FrontLayout,
});

const props = defineProps({
    email: String,
    token: String,
});

const form = useForm({
    token: props.token,
    email: props.email || '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.update'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>

    <Head title="Reset Password" />

    <div class="min-h-[75vh] flex flex-col justify-center items-center px-4 py-12">
        <div class="w-full max-w-md">

            <div class="text-center mb-8">
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                    Set New Password
                </h1>
                <p class="mt-2 text-xs sm:text-sm text-neutral-400">
                    Please enter your email and choose a new password.
                </p>
            </div>

            <div
                class="bg-[#121212] border border-neutral-800/80 rounded-2xl p-6 sm:p-8 shadow-2xl relative overflow-hidden">
                <div
                    class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-transparent via-amber-500 to-transparent opacity-50">
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Email Input -->
                    <div>
                        <label for="email"
                            class="block text-xs font-semibold text-neutral-300 uppercase tracking-wider mb-2">
                            Email Address
                        </label>
                        <input id="email" v-model="form.email" type="email" required autofocus
                            class="w-full px-4 py-3 bg-[#181818] border border-neutral-800 rounded-xl text-neutral-100 text-sm focus:outline-none focus:border-amber-500 transition-colors"
                            :class="{ 'border-rose-500': form.errors.email }" />
                        <p v-if="form.errors.email" class="mt-1.5 text-xs text-rose-400 font-medium">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div class="mb-6 rounded-xl bg-neutral-900/60 border border-neutral-800 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wider text-neutral-400 mb-2">Password
                            Rules:</p>
                        <ul class="text-xs text-neutral-300 space-y-1.5 list-disc list-inside">
                            <li>Minimum <strong class="text-amber-400">8 characters</strong> in length</li>
                            <li>At least one <strong class="text-amber-400">uppercase</strong> and one <strong
                                    class="text-amber-400">lowercase</strong> letter</li>
                            <li>At least one <strong class="text-amber-400">number</strong> and one <strong
                                    class="text-amber-400">special symbol</strong> (@, #, $, etc.)</li>
                            <li>Must match the <strong class="text-amber-400">confirmation field</strong></li>
                        </ul>
                    </div>

                    <!-- New Password Input -->
                    <div>
                        <label for="password"
                            class="block text-xs font-semibold text-neutral-300 uppercase tracking-wider mb-2">
                            New Password
                        </label>
                        <input id="password" v-model="form.password" type="password" required placeholder="••••••••"
                            class="w-full px-4 py-3 bg-[#181818] border border-neutral-800 rounded-xl text-neutral-100 text-sm focus:outline-none focus:border-amber-500 transition-colors"
                            :class="{ 'border-rose-500': form.errors.password }" />
                        <p v-if="form.errors.password" class="mt-1.5 text-xs text-rose-400 font-medium">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Confirm Password Input -->
                    <div>
                        <label for="password_confirmation"
                            class="block text-xs font-semibold text-neutral-300 uppercase tracking-wider mb-2">
                            Confirm Password
                        </label>
                        <input id="password_confirmation" v-model="form.password_confirmation" type="password" required
                            placeholder="••••••••"
                            class="w-full px-4 py-3 bg-[#181818] border border-neutral-800 rounded-xl text-neutral-100 text-sm focus:outline-none focus:border-amber-500 transition-colors" />
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" :disabled="form.processing"
                        class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-black font-extrabold text-sm tracking-wide uppercase transition-all duration-200 active:scale-[0.99] disabled:opacity-50 cursor-pointer mt-2">
                        {{ form.processing ? 'Resetting...' : 'Reset Password' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>