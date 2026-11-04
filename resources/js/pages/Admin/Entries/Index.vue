
<script setup>
import { ref, watch, computed, onBeforeUnmount } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';

import AdminLayout from '../../../Layouts/AdminLayout.vue';
import { formatDate } from '../../../helpers/date';
import { formatMoney } from '../../../helpers/money';

defineOptions({
    layout: AdminLayout,
});

const props = defineProps({
    entries: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        required: true,
    },
    competitions: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            competition: '',
            payment_status: '',
        }),
    },
});

const search = ref(props.filters.search || '');
const competition = ref(props.filters.competition || '');
const paymentStatus = ref(props.filters.payment_status || '');

let searchTimeout = null;

function applyFilters() {
    router.get(
        route('admin.entries.index'),
        {
            search: search.value || undefined,
            competition: competition.value || undefined,
            payment_status: paymentStatus.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
}

// Debounced search.
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 400);
});

// Apply dropdown filters immediately.
watch([competition, paymentStatus], applyFilters);

onBeforeUnmount(() => {
    clearTimeout(searchTimeout);
});

const hasFilters = computed(() =>
    Boolean(search.value || competition.value || paymentStatus.value)
);

function clearFilters() {
    clearTimeout(searchTimeout);

    search.value = '';
    competition.value = '';
    paymentStatus.value = '';

    router.get(
        route('admin.entries.index'),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
}

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

function formatEntryDate(date) {
    return date ? formatDate(date) : '—';
}

function formatOrderTotal(amount, currency) {
    if (amount === null || amount === undefined || !currency) {
        return '—';
    }

    return formatMoney(amount, currency);
}

function entryUrl(entry) {
    return route('admin.entries.show', entry.encrypted_id);
}

function orderUrl(entry) {
    return entry.order_encrypted_id
        ? route('admin.orders.show', entry.order_encrypted_id)
        : null;
}
</script>

<template>
    <Head title="Entries Management" />

    <div class="mx-auto w-full max-w-[1600px] space-y-6">

        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-400">
                    Entry Management
                </p>

                <h1 class="mt-1 text-2xl font-bold text-white">
                    Entries
                </h1>

                <p class="mt-1 text-sm text-neutral-400">
                    View and monitor customer entries across competitions.
                </p>
            </div>

            <div class="inline-flex w-fit items-center gap-2 rounded-full border border-neutral-800 bg-[#121212] px-4 py-2 text-xs font-medium text-neutral-400">
                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                Assigned tickets
            </div>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            <!-- Total Entries -->
            <div class="rounded-2xl border border-neutral-800 bg-[#121212] p-5">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-neutral-400">
                        Total Entries
                    </p>

                    <div class="rounded-xl bg-amber-500/10 p-2.5 text-amber-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M6 5h12M6 9h12M6 13h8m-8 4h8" />
                        </svg>
                    </div>
                </div>

                <p class="mt-4 text-3xl font-bold text-white">
                    {{ stats.total_entries ?? 0 }}
                </p>

                <p class="mt-1 text-xs text-neutral-500">
                    All assigned tickets
                </p>
            </div>

            <!-- Paid Entries -->
            <div class="rounded-2xl border border-neutral-800 bg-[#121212] p-5">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-neutral-400">
                        Paid Entries
                    </p>

                    <div class="rounded-xl bg-emerald-500/10 p-2.5 text-emerald-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>

                <p class="mt-4 text-3xl font-bold text-white">
                    {{ stats.paid_entries ?? 0 }}
                </p>

                <p class="mt-1 text-xs text-neutral-500">
                    Linked to paid orders
                </p>
            </div>

            <!-- Pending Entries -->
            <div class="rounded-2xl border border-neutral-800 bg-[#121212] p-5">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-neutral-400">
                        Pending Payment
                    </p>

                    <div class="rounded-xl bg-amber-500/10 p-2.5 text-amber-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                <p class="mt-4 text-3xl font-bold text-white">
                    {{ stats.pending_entries ?? 0 }}
                </p>

                <p class="mt-1 text-xs text-neutral-500">
                    Linked to pending orders
                </p>
            </div>

            <!-- Other Payment States -->
            <div class="rounded-2xl border border-neutral-800 bg-[#121212] p-5">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-neutral-400">
                        Other Payment States
                    </p>

                    <div class="rounded-xl bg-red-500/10 p-2.5 text-red-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                </div>

                <p class="mt-4 text-3xl font-bold text-white">
                    {{ stats.other_payment_entries ?? 0 }}
                </p>

                <p class="mt-1 text-xs text-neutral-500">
                    Failed, refunded or disputed
                </p>
            </div>
        </div>

        <!-- Entries Table -->
        <div class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">

            <!-- Section Header & Filters -->
            <div class="border-b border-neutral-800 p-5">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">

                    <div>
                        <h2 class="text-base font-semibold text-white">
                            All Entries
                        </h2>

                        <p class="mt-1 text-sm text-neutral-500">
                            Customer entries and their associated orders.
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">

                        <!-- Search -->
                        <div class="relative">
                            <svg
                                class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-500"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>

                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search entries..."
                                class="w-full rounded-xl border border-neutral-800 bg-neutral-950 py-2.5 pl-10 pr-4 text-sm text-white placeholder-neutral-500 outline-none transition focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/30 sm:w-64"
                            />
                        </div>

                        <!-- Competition Filter -->
                        <select
                            v-model="competition"
                            class="rounded-xl border border-neutral-800 bg-neutral-950 px-3.5 py-2.5 text-sm text-neutral-300 outline-none transition focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/30"
                        >
                            <option value="">
                                All Competitions
                            </option>

                            <option
                                v-for="item in competitions"
                                :key="item.encrypted_id"
                                :value="item.encrypted_id"
                            >
                                {{ item.title }}
                            </option>
                        </select>

                        <!-- Payment Filter -->
                        <select
                            v-model="paymentStatus"
                            class="rounded-xl border border-neutral-800 bg-neutral-950 px-3.5 py-2.5 text-sm text-neutral-300 outline-none transition focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/30"
                        >
                            <option value="">All Payment Statuses</option>
                            <option value="paid">Paid</option>
                            <option value="pending">Pending</option>
                            <option value="failed">Failed</option>
                            <option value="refunded">Refunded</option>
                            <option value="disputed">Disputed</option>
                        </select>
                    </div>
                </div>

                <div v-if="hasFilters" class="mt-4">
                    <button
                        type="button"
                        @click="clearFilters"
                        class="text-xs font-semibold text-amber-400 transition hover:text-amber-300"
                    >
                        Clear all filters
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1050px] text-left text-sm">

                    <thead class="border-b border-neutral-800 bg-neutral-950/60 text-[11px] uppercase tracking-wider text-neutral-500">
                        <tr>
                            <th class="px-5 py-4 font-semibold">Entry / Ticket</th>
                            <th class="px-5 py-4 font-semibold">Competition</th>
                            <th class="px-5 py-4 font-semibold">Customer</th>
                            <th class="px-5 py-4 font-semibold">Order</th>
                            <th class="px-5 py-4 font-semibold">Order Total</th>
                            <th class="px-5 py-4 font-semibold">Payment</th>
                            <th class="px-5 py-4 font-semibold">Entry Date</th>
                            <th class="px-5 py-4 text-right font-semibold">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-neutral-800/80">

                        <tr
                            v-for="entry in entries.data"
                            :key="entry.encrypted_id"
                            class="transition-colors hover:bg-white/[0.025]"
                        >
                            <!-- Ticket -->
                            <td class="px-5 py-4">
                                <Link
                                    :href="entryUrl(entry)"
                                    class="font-semibold text-amber-400 transition hover:text-amber-300"
                                >
                                    #{{ entry.ticket_number }}
                                </Link>
                            </td>

                            <!-- Competition -->
                            <td class="px-5 py-4">
                                <p class="max-w-[190px] truncate font-medium text-neutral-200">
                                    {{ entry.competition_title || 'Unknown Competition' }}
                                </p>
                            </td>

                            <!-- Customer -->
                            <td class="px-5 py-4">
                                <Link
                                    v-if="entry.customer_encrypted_id"
                                    :href="route('admin.customers.show', entry.customer_encrypted_id)"
                                    class="group"
                                >
                                    <p class="font-medium text-neutral-200 transition group-hover:text-amber-400">
                                        {{ entry.customer_name || 'Customer' }}
                                    </p>

                                    <p class="mt-1 text-xs text-neutral-500">
                                        {{ entry.customer_email || '—' }}
                                    </p>
                                </Link>

                                <div v-else>
                                    <p class="text-neutral-500">
                                        No customer linked
                                    </p>
                                </div>
                            </td>

                            <!-- Order -->
                            <td class="px-5 py-4">
                                <Link
                                    v-if="entry.order_encrypted_id"
                                    :href="orderUrl(entry)"
                                    class="font-medium text-neutral-300 transition hover:text-amber-400"
                                >
                                    {{ entry.order_number || 'View Order' }}
                                </Link>

                                <span v-else class="text-neutral-500">
                                    —
                                </span>
                            </td>

                            <!-- Order Total -->
                            <td class="px-5 py-4 font-medium text-neutral-200">
                                {{ formatOrderTotal(entry.order_total, entry.order_currency) }}
                            </td>

                            <!-- Payment Status -->
                            <td class="px-5 py-4">
                                <span
                                    v-if="entry.payment_status"
                                    class="inline-flex rounded-full border px-2.5 py-1 text-xs font-medium capitalize"
                                    :class="paymentClass(entry.payment_status)"
                                >
                                    {{ entry.payment_status.replaceAll('_', ' ') }}
                                </span>

                                <span v-else class="text-neutral-500">
                                    No payment
                                </span>
                            </td>

                            <!-- Date -->
                            <td class="px-5 py-4 text-neutral-400">
                                {{ formatEntryDate(entry.assigned_at) }}
                            </td>

                            <!-- Action -->
                            <td class="px-5 py-4 text-right">
                                <Link
                                    :href="entryUrl(entry)"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-neutral-700 bg-neutral-900 px-3 py-2 text-xs font-semibold text-neutral-300 transition hover:border-amber-500/40 hover:bg-amber-500/10 hover:text-amber-400"
                                >
                                    View Details

                                    <svg
                                        class="h-3.5 w-3.5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 5l7 7-7 7"
                                        />
                                    </svg>
                                </Link>
                            </td>
                        </tr>

                        <!-- Empty State -->
                        <tr v-if="!entries.data?.length">
                            <td colspan="8" class="px-5 py-16 text-center">
                                <div class="mx-auto flex max-w-sm flex-col items-center">

                                    <div class="mb-4 rounded-2xl bg-neutral-900 p-4">
                                        <svg
                                            class="h-7 w-7 text-neutral-500"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.5"
                                                d="M6 5h12M6 9h12M6 13h8m-8 4h8"
                                            />
                                        </svg>
                                    </div>

                                    <p class="font-semibold text-white">
                                        No entries found
                                    </p>

                                    <p class="mt-1 text-sm text-neutral-500">
                                        Try changing your search or filters.
                                    </p>

                                    <button
                                        v-if="hasFilters"
                                        type="button"
                                        @click="clearFilters"
                                        class="mt-4 text-xs font-semibold text-amber-400 transition hover:text-amber-300"
                                    >
                                        Clear filters
                                    </button>
                                </div>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="entries.links && entries.links.length > 3"
                class="flex flex-col gap-3 border-t border-neutral-800 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <p class="text-xs text-neutral-500">
                    Showing
                    <span class="font-semibold text-neutral-300">{{ entries.from || 0 }}</span>
                    to
                    <span class="font-semibold text-neutral-300">{{ entries.to || 0 }}</span>
                    of
                    <span class="font-semibold text-neutral-300">{{ entries.total || 0 }}</span>
                    entries
                </p>

                <div class="flex flex-wrap items-center gap-1">
                    <template
                        v-for="(link, index) in entries.links"
                        :key="index"
                    >
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            preserve-scroll
                            class="rounded-lg border px-3 py-2 text-xs font-medium transition"
                            :class="link.active
                                ? 'border-amber-500/30 bg-amber-500/10 text-amber-400'
                                : 'border-neutral-800 bg-neutral-950 text-neutral-400 hover:border-amber-500/40 hover:text-amber-400'"
                            v-html="link.label"
                        />

                        <span
                            v-else
                            class="rounded-lg border border-neutral-800 bg-neutral-950 px-3 py-2 text-xs text-neutral-600"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>

        </div>
    </div>
</template>
