
<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import FrontLayout from '../../Layouts/FrontLayout.vue';

defineOptions({
    layout: FrontLayout,
});

const props = defineProps({
    items: {
        type: Array,
        default: () => [],
    },
    currency: {
        type: String,
        default: 'USD',
    },
    subtotal: {
        type: Number,
        default: 0,
    },
    totalTickets: {
        type: Number,
        default: 0,
    },
    customer: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();

const form = useForm({
    terms_accepted: false,
});

const processing = computed(() => form.processing);

const formatMoney = (amount) => {
    const value = Number(amount ?? 0);
    const currencyCode = String(props.currency || 'USD').toUpperCase();

    try {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currencyCode,
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(Number.isFinite(value) ? value : 0);
    } catch {
        return `${currencyCode} ${(Number.isFinite(value) ? value : 0).toFixed(2)}`;
    }
};

const fullName = computed(() => {
    const name = [
        props.customer?.first_name,
        props.customer?.last_name,
    ].filter(Boolean).join(' ');

    return name || page.props.auth?.user?.name || 'Registered customer';
});

const submitCheckout = () => {
    if (processing.value || props.items.length === 0) return;

    form.post(route('checkout.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Secure Checkout" />

    <main class="relative min-h-screen overflow-hidden bg-[#0c0c0c] px-4 py-10 text-white sm:px-6 sm:py-14 lg:px-8">
        <!-- Background glow -->
        <div
            class="pointer-events-none absolute -right-48 -top-48 h-[520px] w-[520px] rounded-full opacity-20 blur-3xl"
            style="background: radial-gradient(circle, #f59e0b 0%, transparent 70%);"
        />

        <div class="relative z-10 mx-auto max-w-7xl">
            <!-- Header -->
            <header class="mb-10 flex flex-col justify-between gap-5 border-b border-neutral-800 pb-7 sm:flex-row sm:items-center">
                <div>
                    <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-amber-500/20 bg-amber-500/10 px-3 py-1.5 text-[11px] font-bold uppercase tracking-[0.18em] text-amber-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-400" />
                        Secure Checkout
                    </div>

                    <h1 class="text-3xl font-black tracking-tight sm:text-4xl">
                        Complete your <span class="text-amber-400">order</span>
                    </h1>

                    <p class="mt-2 max-w-xl text-sm leading-6 text-neutral-400">
                        Review your competition entries and confirm your details before continuing to payment.
                    </p>
                </div>

                <Link
                    :href="route('cart.index')"
                    class="inline-flex w-fit items-center gap-2 rounded-xl border border-neutral-800 bg-[#121212] px-4 py-3 text-sm font-semibold text-neutral-300 transition hover:border-amber-500/40 hover:text-amber-400"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M19 12H5m7 7-7-7 7-7" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    Back to cart
                </Link>
            </header>

            <div v-if="items.length > 0" class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12 lg:gap-10">
                <!-- Main checkout column -->
                <section class="space-y-6 lg:col-span-7">
                    <!-- Customer details -->
                    <div class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212] shadow-xl">
                        <div class="flex items-center gap-4 border-b border-neutral-800 px-5 py-5 sm:px-7">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/10 text-amber-400">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m8-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-base font-bold sm:text-lg">Customer information</h2>
                                <p class="mt-1 text-xs text-neutral-500">Your registered account details</p>
                            </div>

                            <!-- <span class="ml-auto rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-emerald-400">
                                Verified account
                            </span> -->
                        </div>

                        <div class="grid gap-4 p-5 sm:grid-cols-2 sm:p-7">
                            <div class="rounded-xl border border-neutral-800 bg-[#0d0d0d] p-4">
                                <p class="text-xs font-medium text-neutral-500">Full name</p>
                                <p class="mt-2 break-words text-sm font-semibold text-white">{{ fullName }}</p>
                            </div>

                            <div class="rounded-xl border border-neutral-800 bg-[#0d0d0d] p-4">
                                <p class="text-xs font-medium text-neutral-500">Email address</p>
                                <p class="mt-2 break-all text-sm font-semibold text-white">{{ customer.email || page.props.auth?.user?.email || '—' }}</p>
                            </div>

                            <div class="rounded-xl border border-neutral-800 bg-[#0d0d0d] p-4">
                                <p class="text-xs font-medium text-neutral-500">Country</p>
                                <p class="mt-2 text-sm font-semibold text-white">{{ customer.country || '—' }}</p>
                            </div>

                            <div class="rounded-xl border border-neutral-800 bg-[#0d0d0d] p-4">
                                <p class="text-xs font-medium text-neutral-500">Phone number</p>
                                <p class="mt-2 text-sm font-semibold text-white">{{ customer.phone_number || 'Not provided' }}</p>
                            </div>

                            <p class="text-xs leading-5 text-neutral-500 sm:col-span-2">
                                These details come from your account. To change them, update your profile before completing your purchase.
                            </p>
                        </div>
                    </div>

                    <!-- Competition entries -->
                    <div class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212] shadow-xl">
                        <div class="flex items-center justify-between gap-3 border-b border-neutral-800 px-5 py-5 sm:px-7">
                            <div>
                                <h2 class="text-base font-bold sm:text-lg">Your competition entries</h2>
                                <p class="mt-1 text-xs text-neutral-500">Review the competitions in your order.</p>
                            </div>

                            <span class="shrink-0 rounded-lg border border-neutral-800 bg-[#0c0c0c] px-3 py-2 text-xs font-bold text-neutral-300">
                                {{ items.length }} {{ items.length === 1 ? 'competition' : 'competitions' }}
                            </span>
                        </div>

                        <div class="divide-y divide-neutral-800/80">
                            <article
                                v-for="item in items"
                                :key="item.id"
                                class="flex gap-4 p-5 sm:p-7"
                            >
                                <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl border border-neutral-800 bg-[#0c0c0c] sm:h-24 sm:w-24">
                                    <img
                                        v-if="item.image"
                                        :src="item.image"
                                        :alt="item.title"
                                        class="h-full w-full object-cover"
                                    />

                                    <div v-else class="flex h-full w-full items-center justify-center text-amber-400">
                                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path d="M12 3v18m9-9H3" stroke-width="1.4" stroke-linecap="round" />
                                        </svg>
                                    </div>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <h3 class="break-words text-sm font-bold leading-6 text-white sm:text-base">
                                        {{ item.title }}
                                    </h3>

                                    <p class="mt-1 text-xs text-neutral-500">
                                        {{ item.quantity }} {{ item.quantity === 1 ? 'ticket' : 'tickets' }}
                                        × {{ formatMoney(item.ticket_price) }}
                                    </p>

                                    <p v-if="item.draw_at" class="mt-2 text-xs text-neutral-500">
                                        Draw date: {{ new Date(item.draw_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) }}
                                    </p>

                                    <p class="mt-3 text-sm font-extrabold text-amber-400">
                                        {{ formatMoney(item.line_total) }}
                                    </p>
                                </div>
                            </article>
                        </div>
                    </div>

                    <!-- Payment information -->
                    <div class="rounded-2xl border border-neutral-800 bg-[#121212] p-5 shadow-xl sm:p-7">
                        <div class="flex items-start gap-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/10 text-amber-400">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <rect x="3" y="5" width="18" height="14" rx="2" stroke-width="1.7" />
                                    <path d="M3 10h18m-14 5h3" stroke-width="1.7" stroke-linecap="round" />
                                </svg>
                            </div>

                            <div class="min-w-0">
                                <h2 class="text-base font-bold sm:text-lg">Payment method</h2>
                                <p class="mt-1 text-sm leading-6 text-neutral-400">
                                    Pay securely using Stripe. You'll continue to Stripe's hosted payment page to enter your payment details.
                                </p>

                                <div class="mt-4 flex flex-wrap items-center gap-2">
                                    <span class="rounded-md border border-neutral-700 bg-[#0c0c0c] px-3 py-1.5 text-xs font-bold text-neutral-300">Card</span>
                                    <span class="rounded-md border border-neutral-700 bg-[#0c0c0c] px-3 py-1.5 text-xs font-bold text-neutral-300">Secure payment</span>
                                    <span class="rounded-md border border-emerald-500/20 bg-emerald-500/10 px-3 py-1.5 text-xs font-bold text-emerald-400">Stripe protected</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Order summary -->
                <aside class="lg:sticky lg:top-8 lg:col-span-5">
                    <div class="relative overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212] p-5 shadow-2xl sm:p-7">
                        <div class="absolute left-1/4 right-1/4 top-0 h-px bg-gradient-to-r from-transparent via-amber-500/60 to-transparent" />

                        <div class="flex items-center justify-between border-b border-neutral-800 pb-5">
                            <h2 class="text-xl font-black">Order summary</h2>
                            <svg class="h-5 w-5 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M6 7h12l1 14H5L6 7Zm3 0V5a3 3 0 0 1 6 0v2" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>

                        <div class="space-y-4 py-5">
                            <div class="flex items-center justify-between gap-4 text-sm">
                                <span class="text-neutral-400">Total tickets</span>
                                <span class="font-bold text-white">{{ totalTickets }}</span>
                            </div>

                            <div class="flex items-center justify-between gap-4 text-sm">
                                <span class="text-neutral-400">Subtotal</span>
                                <span class="font-semibold text-white">{{ formatMoney(subtotal) }}</span>
                            </div>

                            <div class="flex items-center justify-between gap-4 text-sm">
                                <span class="text-neutral-400">Processing fee</span>
                                <span class="font-bold text-emerald-400">FREE</span>
                            </div>
                        </div>

                        <div class="border-t border-neutral-800 py-5">
                            <div class="flex items-end justify-between gap-3">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-widest text-neutral-500">Total due</p>
                                    <p class="mt-1 text-xs text-neutral-500">Including applicable fees</p>
                                </div>

                                <p class="text-2xl font-black tracking-tight text-amber-400 sm:text-3xl">
                                    {{ formatMoney(subtotal) }}
                                </p>
                            </div>
                        </div>

                        <div class="mb-5 rounded-xl border border-amber-500/15 bg-amber-500/5 p-4">
                            <div class="flex items-start gap-3">
                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M12 9v4m0 4h.01M10.3 3.9 1.8 18.5A1.7 1.7 0 0 0 3.3 21h17.4a1.7 1.7 0 0 0 1.5-2.5L13.7 3.9a2 2 0 0 0-3.4 0Z" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>

                                <p class="text-xs leading-5 text-neutral-400">
                                    Review your order before payment. Your tickets will be assigned after your payment has been confirmed.
                                </p>
                            </div>
                        </div>

                        <form @submit.prevent="submitCheckout" class="space-y-5">
                            <label class="flex cursor-pointer items-start gap-3">
                                <input
                                    v-model="form.terms_accepted"
                                    type="checkbox"
                                    required
                                    class="mt-1 h-4 w-4 shrink-0 rounded border-neutral-700 bg-[#0c0c0c] accent-amber-400 focus:ring-amber-500"
                                />

                                <span class="text-xs leading-5 text-neutral-400">
                                    I confirm that my account details and order are correct, and I agree to the applicable competition terms and conditions.
                                </span>
                            </label>

                            <p v-if="form.errors.terms_accepted" class="text-xs text-red-400">
                                {{ form.errors.terms_accepted }}
                            </p>

                            <p v-if="form.errors.message" class="rounded-lg border border-red-500/20 bg-red-500/10 p-3 text-sm text-red-300">
                                {{ form.errors.message }}
                            </p>

                            <button
                                type="submit"
                                :disabled="processing || !form.terms_accepted"
                                class="flex w-full items-center justify-center gap-3 rounded-xl bg-gradient-to-r from-amber-400 via-yellow-500 to-amber-500 px-5 py-4 text-sm font-black uppercase tracking-wider text-black shadow-lg shadow-amber-500/10 transition hover:brightness-110 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <svg v-if="processing" class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Z" />
                                </svg>

                                <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <rect x="3" y="5" width="18" height="14" rx="2" stroke-width="1.8" />
                                    <path d="M3 10h18" stroke-width="1.8" stroke-linecap="round" />
                                </svg>

                                {{ processing ? 'Preparing secure payment...' : 'Continue to secure payment' }}
                            </button>

                            <p class="text-center text-[11px] leading-5 text-neutral-500">
                                You will be redirected to Stripe to complete your payment securely.
                            </p>
                        </form>

                        <div class="mt-6 grid grid-cols-2 gap-3 border-t border-neutral-800 pt-5">
                            <div class="flex items-center gap-2 text-xs text-neutral-400">
                                <svg class="h-4 w-4 shrink-0 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z" stroke-width="1.7" stroke-linejoin="round" />
                                    <path d="m9 12 2 2 4-4" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Secure checkout
                            </div>

                            <div class="flex items-center justify-end gap-2 text-xs text-neutral-400">
                                <svg class="h-4 w-4 shrink-0 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M12 8v4l3 2m6-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Fast payment
                            </div>
                        </div>
                    </div>
                </aside>
            </div>

            <!-- Empty cart state -->
            <div v-else class="mx-auto max-w-xl rounded-2xl border border-neutral-800 bg-[#121212] px-6 py-16 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-neutral-800 bg-[#0c0c0c] text-amber-400">
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M3 3h2l2.4 12.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 2-1.6L21 8H6" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                        <circle cx="10" cy="21" r="1" />
                        <circle cx="18" cy="21" r="1" />
                    </svg>
                </div>

                <h2 class="mt-6 text-2xl font-black">Your cart is empty</h2>

                <p class="mx-auto mt-3 max-w-sm text-sm leading-6 text-neutral-400">
                    Add a competition to your cart before proceeding to checkout.
                </p>

                <Link
                    :href="route('cart.index')"
                    class="mt-7 inline-flex items-center justify-center rounded-xl bg-amber-400 px-6 py-3 text-sm font-extrabold text-black transition hover:bg-amber-300"
                >
                    Return to cart
                </Link>
            </div>

            <footer class="mt-10 text-center text-xs leading-5 text-neutral-600">
                Please review the applicable competition terms before purchasing. Payments are processed securely by Stripe.
            </footer>
        </div>
    </main>
</template>
