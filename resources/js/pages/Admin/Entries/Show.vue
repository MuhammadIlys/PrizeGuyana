
<script setup>
import { Head, Link } from '@inertiajs/vue3';

import AdminLayout from '../../../Layouts/AdminLayout.vue';
import { formatDate } from '../../../helpers/date';
import { formatMoney } from '../../../helpers/money';

defineOptions({
    layout: AdminLayout,
});

const props = defineProps({
    entry: {
        type: Object,
        required: true,
    },
});

function paymentClass(status) {
    const classes = {
        paid: 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400',
        pending: 'border-amber-500/20 bg-amber-500/10 text-amber-400',
        failed: 'border-red-500/20 bg-red-500/10 text-red-400',
        refunded: 'border-purple-500/20 bg-purple-500/10 text-purple-400',
        disputed: 'border-orange-500/20 bg-orange-500/10 text-orange-400',
    };

    return classes[status] || 'border-neutral-700 bg-neutral-900 text-neutral-400';
}

function ticketClass(status) {
    const classes = {
        assigned: 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400',
        available: 'border-amber-500/20 bg-amber-500/10 text-amber-400',
        unavailable: 'border-neutral-700 bg-neutral-900 text-neutral-400',
    };

    return classes[status] || 'border-neutral-700 bg-neutral-900 text-neutral-400';
}

function formatOrderTotal(amount, currency) {
    if (amount === null || amount === undefined || !currency) {
        return '—';
    }

    return formatMoney(amount, currency);
}

function formatEntryDate(date) {
    return date ? formatDate(date) : '—';
}

async function copyTicketNumber() {
    try {
        await navigator.clipboard.writeText(
            String(props.entry.ticket_number)
        );
    } catch (error) {
        console.error('Unable to copy ticket number:', error);
    }
}

function orderUrl() {
    return props.entry.order_encrypted_id
        ? route('admin.orders.show', props.entry.order_encrypted_id)
        : null;
}

function customerUrl() {
    return props.entry.customer_encrypted_id
        ? route('admin.customers.show', props.entry.customer_encrypted_id)
        : null;
}
</script>

<template>
    <Head :title="`Entry #${entry.ticket_number}`" />

    <div class="mx-auto w-full max-w-[1600px] space-y-6">

        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-400">
                    Entry Management
                </p>

                <h1 class="mt-1 text-2xl font-bold text-white">
                    Entry Details
                </h1>

                <p class="mt-1 text-sm text-neutral-400">
                    Review ticket assignment, customer, competition and order information.
                </p>
            </div>

            <Link
                :href="route('admin.entries.index')"
                class="inline-flex w-fit items-center gap-2 rounded-lg border border-neutral-700 bg-neutral-900 px-4 py-2.5 text-sm font-semibold text-neutral-300 transition hover:border-amber-500/40 hover:bg-amber-500/10 hover:text-amber-400"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 19l-7-7 7-7" />
                </svg>

                Back to Entries
            </Link>
        </div>

        <!-- Ticket Overview -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            <!-- Ticket Number -->
            <div class="rounded-2xl border border-neutral-800 bg-[#121212] p-5">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-neutral-400">
                        Ticket Number
                    </p>

                    <div class="rounded-xl bg-amber-500/10 p-2.5 text-amber-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M6 5h12M6 9h12M6 13h8m-8 4h8" />
                        </svg>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-2">
                    <p class="text-2xl font-bold text-amber-400">
                        #{{ entry.ticket_number }}
                    </p>

                    <button
                        type="button"
                        @click="copyTicketNumber"
                        title="Copy ticket number"
                        class="rounded-lg p-1.5 text-neutral-500 transition hover:bg-neutral-800 hover:text-amber-400"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M8 8V5a2 2 0 012-2h9a2 2 0 012 2v9a2 2 0 01-2 2h-3M5 8h9a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2v-9a2 2 0 012-2z" />
                        </svg>
                    </button>
                </div>

                <p class="mt-1 text-xs text-neutral-500">
                    Entry reference
                </p>
            </div>

            <!-- Ticket Status -->
            <div class="rounded-2xl border border-neutral-800 bg-[#121212] p-5">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-neutral-400">
                        Ticket Status
                    </p>

                    <div class="rounded-xl bg-emerald-500/10 p-2.5 text-emerald-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>

                <div class="mt-4">
                    <span
                        class="inline-flex rounded-full border px-3 py-1.5 text-xs font-semibold capitalize"
                        :class="ticketClass(entry.status)"
                    >
                        {{ entry.status }}
                    </span>
                </div>

                <p class="mt-2 text-xs text-neutral-500">
                    Current ticket allocation status
                </p>
            </div>

            <!-- Payment Status -->
            <div class="rounded-2xl border border-neutral-800 bg-[#121212] p-5">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-neutral-400">
                        Payment Status
                    </p>

                    <div class="rounded-xl bg-blue-500/10 p-2.5 text-blue-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h18M7 15h2m4 0h2M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z" />
                        </svg>
                    </div>
                </div>

                <div class="mt-4">
                    <span
                        v-if="entry.payment_status"
                        class="inline-flex rounded-full border px-3 py-1.5 text-xs font-semibold capitalize"
                        :class="paymentClass(entry.payment_status)"
                    >
                        {{ entry.payment_status.replaceAll('_', ' ') }}
                    </span>

                    <span v-else class="text-sm text-neutral-500">
                        No payment linked
                    </span>
                </div>

                <p class="mt-2 text-xs text-neutral-500">
                    Associated order payment
                </p>
            </div>

            <!-- Order Total -->
            <div class="rounded-2xl border border-neutral-800 bg-[#121212] p-5">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-neutral-400">
                        Order Total
                    </p>

                    <div class="rounded-xl bg-amber-500/10 p-2.5 text-amber-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-2.2 0-4 .9-4 2s1.8 2 4 2 4 .9 4 2-1.8 2-4 2m0-10V6m0 12v-2m9-4a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                <p class="mt-4 text-2xl font-bold text-white">
                    {{ formatOrderTotal(entry.order_total, entry.order_currency) }}
                </p>

                <p class="mt-1 text-xs text-neutral-500">
                    Total associated order value
                </p>
            </div>
        </div>

        <!-- Main Information -->
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

            <!-- Competition Information -->
            <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">
                <div class="border-b border-neutral-800 px-5 py-4">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-amber-500/10 p-2 text-amber-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M8 21h8m-4-4v4m-7-17h14l-1 9H6L5 4zm0 0H3m16 0h2" />
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-base font-semibold text-white">
                                Competition Information
                            </h2>

                            <p class="text-xs text-neutral-500">
                                Competition associated with this entry
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4 p-5">
                    <div>
                        <p class="text-xs font-medium text-neutral-500">
                            Competition Name
                        </p>

                        <p class="mt-1 font-semibold text-white">
                            {{ entry.competition_title || 'Unknown Competition' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-neutral-500">
                            Competition Slug
                        </p>

                        <p class="mt-1 break-all text-sm text-neutral-300">
                            {{ entry.competition_slug || '—' }}
                        </p>
                    </div>

                    <div class="border-t border-neutral-800 pt-4">
                        <Link
                            v-if="entry.competition_slug"
                            :href="route('admin.competitions.show', entry.competition_encrypted_id)"
                            class="inline-flex items-center gap-2 text-sm font-semibold text-amber-400 transition hover:text-amber-300"
                        >
                            View Competition

                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 17L17 7M7 7h10v10" />
                            </svg>
                        </Link>

                        <span v-else class="text-xs text-neutral-500">
                            Competition link unavailable
                        </span>
                    </div>
                </div>
            </section>

            <!-- Customer Information -->
            <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">
                <div class="border-b border-neutral-800 px-5 py-4">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-blue-500/10 p-2 text-blue-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m12-13a4 4 0 11-8 0 4 4 0 018 0zm2 4a4 4 0 014 4v2" />
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-base font-semibold text-white">
                                Customer Information
                            </h2>

                            <p class="text-xs text-neutral-500">
                                Customer associated with this entry
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4 p-5">
                    <div>
                        <p class="text-xs font-medium text-neutral-500">
                            Customer Name
                        </p>

                        <p class="mt-1 font-semibold text-white">
                            {{ entry.customer_name || 'No customer linked' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-neutral-500">
                            Email Address
                        </p>

                        <p class="mt-1 break-all text-sm text-neutral-300">
                            {{ entry.customer_email || '—' }}
                        </p>
                    </div>

                    <div class="border-t border-neutral-800 pt-4">
                        <Link
                            v-if="entry.customer_encrypted_id"
                            :href="customerUrl()"
                            class="inline-flex items-center gap-2 text-sm font-semibold text-amber-400 transition hover:text-amber-300"
                        >
                            View Customer Profile

                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 17L17 7M7 7h10v10" />
                            </svg>
                        </Link>

                        <span v-else class="text-xs text-neutral-500">
                            Customer profile unavailable
                        </span>
                    </div>
                </div>
            </section>

            <!-- Order Information -->
            <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">
                <div class="border-b border-neutral-800 px-5 py-4">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-emerald-500/10 p-2 text-emerald-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M4 4h16v16H4zM8 8h8M8 12h8M8 16h4" />
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-base font-semibold text-white">
                                Order Information
                            </h2>

                            <p class="text-xs text-neutral-500">
                                Purchase and payment details
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4 p-5">
                    <div>
                        <p class="text-xs font-medium text-neutral-500">
                            Order Number
                        </p>

                        <p class="mt-1 font-semibold text-white">
                            {{ entry.order_number || 'No order linked' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-neutral-500">
                            Order Status
                        </p>

                        <p class="mt-1 text-sm capitalize text-neutral-300">
                            {{ entry.order_status?.replaceAll('_', ' ') || '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-neutral-500">
                            Payment Status
                        </p>

                        <div class="mt-2">
                            <span
                                v-if="entry.payment_status"
                                class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold capitalize"
                                :class="paymentClass(entry.payment_status)"
                            >
                                {{ entry.payment_status.replaceAll('_', ' ') }}
                            </span>

                            <span v-else class="text-sm text-neutral-500">
                                —
                            </span>
                        </div>
                    </div>

                    <div class="border-t border-neutral-800 pt-4">
                        <Link
                            v-if="entry.order_encrypted_id"
                            :href="orderUrl()"
                            class="inline-flex items-center gap-2 text-sm font-semibold text-amber-400 transition hover:text-amber-300"
                        >
                            View Order Details

                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 17L17 7M7 7h10v10" />
                            </svg>
                        </Link>

                        <span v-else class="text-xs text-neutral-500">
                            Order details unavailable
                        </span>
                    </div>
                </div>
            </section>

            <!-- Assignment Information -->
            <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">
                <div class="border-b border-neutral-800 px-5 py-4">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-amber-500/10 p-2 text-amber-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v14H4V6a1 1 0 011-1z" />
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-base font-semibold text-white">
                                Assignment Information
                            </h2>

                            <p class="text-xs text-neutral-500">
                                Ticket assignment and record timestamps
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4 p-5">
                    <div>
                        <p class="text-xs font-medium text-neutral-500">
                            Assigned At
                        </p>

                        <p class="mt-1 text-sm font-medium text-neutral-200">
                            {{ formatEntryDate(entry.assigned_at) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-neutral-500">
                            Record Created
                        </p>

                        <p class="mt-1 text-sm text-neutral-300">
                            {{ formatEntryDate(entry.created_at) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-neutral-500">
                            Last Updated
                        </p>

                        <p class="mt-1 text-sm text-neutral-300">
                            {{ formatEntryDate(entry.updated_at) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-neutral-500">
                            Entry Reference
                        </p>

                        <p class="mt-1 text-sm text-neutral-300">
                            #{{ entry.ticket_number }}
                        </p>
                    </div>
                </div>
            </section>
        </div>

        <!-- Read Only Notice -->
        <div class="flex items-start gap-3 rounded-2xl border border-amber-500/20 bg-amber-500/5 p-4">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                    d="M12 9v3m0 4h.01M10.3 3.8L2.8 17a2 2 0 001.7 3h15a2 2 0 001.7-3l-7.5-13.2a2 2 0 00-3.4 0z" />
            </svg>

            <div>
                <p class="text-sm font-semibold text-amber-400">
                    Read-only entry record
                </p>

                <p class="mt-1 text-xs leading-relaxed text-neutral-400">
                    This page provides entry and ticket information for administrative review.
                    Ticket allocation, payment processing and entry status changes are not performed here.
                </p>
            </div>
        </div>

    </div>
</template>
