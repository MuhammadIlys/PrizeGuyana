
<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, onUnmounted } from 'vue';
import logoUrl from '/resources/images/logo.png';
import FrontLayout from '../../Layouts/FrontLayout.vue';

defineOptions({
    layout: FrontLayout,
});

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
});

const page = usePage();

const form = useForm({
    otp: '',
});

const resendForm = useForm({});

const countdown = ref(60);
const canResend = ref(false);
let timer = null;

const maskedEmail = computed(() => {
    const [name, domain] = props.email.split('@');

    if (!domain) return props.email;

    const visible = name.substring(0, Math.min(2, name.length));
    return `${visible}${'*'.repeat(Math.max(2, name.length - visible.length))}@${domain}`;
});

const startCountdown = () => {
    clearInterval(timer);

    countdown.value = 60;
    canResend.value = false;

    timer = setInterval(() => {
        if (countdown.value > 0) {
            countdown.value--;
        } else {
            canResend.value = true;
            clearInterval(timer);
        }
    }, 1000);
};

const submit = () => {
    form.post(route('email.verify.submit'), {
        preserveScroll: true,
        onFinish: () => form.reset('otp'),
    });
};

const resendCode = () => {
    if (!canResend.value || resendForm.processing) return;

    resendForm.post(route('email.verify.resend'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('otp');
            startCountdown();
        },
    });
};

startCountdown();

onUnmounted(() => {
    clearInterval(timer);
});
</script>

<template>
    <Head title="Verify Your Email" />

    <div
        class="min-h-screen w-full bg-[#0a0a0a] text-white flex flex-col justify-center items-center px-4 py-12 relative overflow-hidden font-sans"
        style="background: radial-gradient(circle at 0% 0%, rgba(130, 65, 10, 0.35) 0%, rgba(20, 15, 10, 0.7) 40%, rgba(10, 10, 10, 1) 80%);"
    >
        <div class="absolute top-0 left-0 right-0 h-[1px] bg-gradient-to-r from-transparent via-amber-500/30 to-transparent"></div>

        <div class="w-full max-w-md z-10">

            <!-- Logo -->
            <div class="text-center mb-8">
                <Link :href="route('home')" class="inline-block">
                    <img
                        :src="logoUrl"
                        alt="PrizeGuyana"
                        class="h-16 mx-auto object-contain drop-shadow-md"
                    />
                </Link>

                <h1 class="mt-4 text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                    Verify Your Email
                </h1>

                <p class="mt-2 text-sm text-neutral-400">
                    One more step before joining PrizeGuyana.
                </p>
            </div>

            <!-- Verification Card -->
            <div class="bg-[#121212]/90 backdrop-blur-md border border-neutral-800/90 rounded-2xl p-6 sm:p-8 shadow-2xl relative">

                <div class="absolute top-0 left-1/4 right-1/4 h-[1px] bg-gradient-to-r from-transparent via-amber-500/40 to-transparent"></div>

                <!-- Email Icon -->
                <div class="flex justify-center mb-5">
                    <div class="w-16 h-16 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-8 h-8 text-amber-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                            />
                        </svg>
                    </div>
                </div>

                <div class="text-center mb-7">
                    <h2 class="text-lg font-bold text-white">
                        Check Your Inbox
                    </h2>

                    <p class="mt-2 text-sm text-neutral-400 leading-relaxed">
                        We've sent a 6-digit verification code to
                    </p>

                    <p class="mt-2 text-sm font-bold text-amber-400 break-all">
                        {{ maskedEmail }}
                    </p>
                </div>

                <!-- Success Message -->
                <div
                    v-if="page.props.flash?.success"
                    class="mb-5 p-3 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 text-sm"
                >
                    {{ page.props.flash.success }}
                </div>

                <!-- OTP Form -->
                <form @submit.prevent="submit" class="space-y-5">

                    <div>
                        <label
                            for="otp"
                            class="block text-xs font-bold text-neutral-300 uppercase tracking-wider mb-2"
                        >
                            Verification Code
                        </label>

                        <input
                            id="otp"
                            v-model="form.otp"
                            type="text"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            maxlength="6"
                            required
                            autofocus
                            placeholder="000000"
                            class="w-full bg-[#0a0a0a] border border-neutral-800 focus:border-amber-500 rounded-xl px-4 py-4 text-center text-2xl font-bold tracking-[0.6em] text-white placeholder-neutral-700 focus:outline-none transition-colors"
                        />

                        <p
                            v-if="form.errors.otp"
                            class="mt-2 text-xs text-red-500 font-medium"
                        >
                            {{ form.errors.otp }}
                        </p>

                        <p
                            v-if="form.errors.email"
                            class="mt-2 text-xs text-red-500 font-medium"
                        >
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing || form.otp.length !== 6"
                        class="w-full py-3.5 px-4 bg-gradient-to-r from-amber-400 via-yellow-500 to-amber-500 hover:brightness-110 active:scale-[0.99] text-black font-extrabold text-sm rounded-xl transition-all shadow-lg border border-yellow-300/30 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ form.processing ? 'Verifying...' : 'Verify Email Address' }}
                    </button>
                </form>

                <!-- Resend Code -->
                <div class="mt-7 text-center border-t border-neutral-800 pt-6">
                    <p class="text-xs text-neutral-400 mb-3">
                        Didn't receive the code?
                    </p>

                    <button
                        type="button"
                        @click="resendCode"
                        :disabled="!canResend || resendForm.processing"
                        class="text-sm font-bold transition-colors"
                        :class="canResend
                            ? 'text-amber-400 hover:text-amber-300 cursor-pointer'
                            : 'text-neutral-600 cursor-not-allowed'"
                    >
                        {{
                            resendForm.processing
                                ? 'Sending...'
                                : canResend
                                    ? 'Resend Verification Code'
                                    : `Resend code in ${countdown}s`
                        }}
                    </button>

                    <p
                        v-if="resendForm.errors.otp"
                        class="mt-2 text-xs text-red-500"
                    >
                        {{ resendForm.errors.otp }}
                    </p>
                </div>

                <p class="mt-6 text-center text-xs text-neutral-500 leading-relaxed">
                    Your verification code expires after 5 minutes.
                    Please do not share it with anyone.
                </p>
            </div>

            <!-- Footer -->
            <div class="mt-6 text-center">
                <p class="text-xs text-neutral-500">
                    Need help?
                    <Link
                        :href="route('home')"
                        class="text-amber-400 hover:underline font-semibold"
                    >
                        Return to Homepage
                    </Link>
                </p>
            </div>

        </div>
    </div>
</template>
