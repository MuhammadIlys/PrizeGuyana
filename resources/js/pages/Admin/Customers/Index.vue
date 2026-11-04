
<script setup>
import { ref, watch, onBeforeUnmount } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

import { formatDate } from '../../../helpers/date';
import { formatMoney } from '../../../helpers/money';

defineOptions({
    layout: AdminLayout,
});

const props = defineProps({
    customers: {
        type: Object,
        default: () => ({
            data: [],
            links: [],
            current_page: 1,
            last_page: 1,
            from: 0,
            to: 0,
            total: 0,
        }),
    },

    stats: {
        type: Object,
        default: () => ({
            total_customers: 0,
            new_this_month: 0,
            customers_with_orders: 0,
        }),
    },

    countries: {
        type: Array,
        default: () => [],
    },

    filters: {
        type: Object,
        default: () => ({
            search: '',
            country: '',
        }),
    },
});

const search = ref(props.filters?.search ?? '');
const country = ref(props.filters?.country ?? '');

let searchTimer = null;

watch(search, () => {
    clearTimeout(searchTimer);

    searchTimer = setTimeout(() => {
        applyFilters();
    }, 400);
});

watch(country, () => {
    applyFilters();
});

onBeforeUnmount(() => {
    clearTimeout(searchTimer);
});

function applyFilters() {
    router.get(
        route('admin.customers.index'),
        {
            search: search.value || undefined,
            country: country.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
}

function clearFilters() {
    clearTimeout(searchTimer);

    search.value = '';
    country.value = '';

    applyFilters();
}

function customerName(customer) {
    return customer.name || 'Unnamed Customer';
}

function customerInitials(customer) {
    return customerName(customer)
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('') || 'C';
}

function formatCountry(value) {
    return value || 'Not provided';
}

function statusClass(verified) {
    return verified
        ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400'
        : 'border-amber-500/20 bg-amber-500/10 text-amber-400';
}

function formatSpending(spending) {
    if (!Array.isArray(spending) || spending.length === 0) {
        return '—';
    }

    return spending
        .map((item) => formatMoney(item.total, item.currency))
        .join(' / ');
}
</script>

<template>
    <Head title="Customer Management" />

    <div class="mx-auto w-full max-w-[1600px] space-y-6">

        <!-- Page Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-amber-500">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                    User Management
                </div>

                <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                    Customers
                </h1>

                <p class="mt-2 text-sm text-neutral-400">
                    View customer accounts, purchase activity, entries, and spending.
                </p>
            </div>

            <div class="flex items-center gap-2 rounded-xl border border-neutral-800 bg-[#121212] px-4 py-3">
                <svg
                    class="h-5 w-5 text-amber-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.7"
                        d="M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m16 0v-2a4 4 0 00-3-3.87M14 3.13a4 4 0 010 7.75M14 7a4 4 0 11-8 0 4 4 0 018 0z"
                    />
                </svg>

                <span class="text-sm font-semibold text-white">
                    {{ Number(customers.total ?? 0).toLocaleString() }} Customers
                </span>
            </div>
        </div>

        <!-- Statistics -->
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">

            <!-- Total Customers -->
            <div class="rounded-2xl border border-neutral-800 bg-[#121212] p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-neutral-400">
                            Total Customers
                        </p>

                        <h2 class="mt-3 text-3xl font-bold tracking-tight text-white">
                            {{ Number(stats.total_customers ?? 0).toLocaleString() }}
                        </h2>

                        <p class="mt-2 text-xs text-neutral-500">
                            Registered customer accounts
                        </p>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/10">
                        <svg
                            class="h-5 w-5 text-amber-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.7"
                                d="M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m16 0v-2a4 4 0 00-3-3.87M14 3.13a4 4 0 010 7.75M14 7a4 4 0 11-8 0 4 4 0 018 0z"
                            />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- New Customers -->
            <div class="rounded-2xl border border-neutral-800 bg-[#121212] p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-neutral-400">
                            New This Month
                        </p>

                        <h2 class="mt-3 text-3xl font-bold tracking-tight text-white">
                            {{ Number(stats.new_this_month ?? 0).toLocaleString() }}
                        </h2>

                        <p class="mt-2 text-xs text-neutral-500">
                            Customers registered this month
                        </p>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-blue-500/20 bg-blue-500/10">
                        <svg
                            class="h-5 w-5 text-blue-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.7"
                                d="M12 4v16m8-8H4"
                            />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Customers With Orders -->
            <div class="rounded-2xl border border-neutral-800 bg-[#121212] p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-neutral-400">
                            Customers With Orders
                        </p>

                        <h2 class="mt-3 text-3xl font-bold tracking-tight text-white">
                            {{ Number(stats.customers_with_orders ?? 0).toLocaleString() }}
                        </h2>

                        <p class="mt-2 text-xs text-neutral-500">
                            Customers who have placed orders
                        </p>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-emerald-500/20 bg-emerald-500/10">
                        <svg
                            class="h-5 w-5 text-emerald-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.7"
                                d="M5 12l4 4L19 6"
                            />
                        </svg>
                    </div>
                </div>
            </div>

        </section>

        <!-- Customer List -->
        <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">

            <!-- Section Header -->
            <div class="flex flex-col gap-3 border-b border-neutral-800 px-5 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-bold text-white">
                        All Customers
                    </h2>

                    <p class="mt-1 text-xs text-neutral-500">
                        Search and review registered customer accounts.
                    </p>
                </div>

                <span class="text-xs text-neutral-500">
                    {{ customers.from ?? 0 }}–{{ customers.to ?? 0 }}
                    of {{ customers.total ?? 0 }} records
                </span>
            </div>

            <!-- Filters -->
            <div class="grid grid-cols-1 gap-3 border-b border-neutral-800 p-5 md:grid-cols-[1fr_220px_auto]">

                <!-- Search -->
                <div class="relative">
                    <svg
                        class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-500"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <circle cx="11" cy="11" r="7" stroke-width="1.7" />
                        <path
                            d="m16 16 4 4"
                            stroke-width="1.7"
                            stroke-linecap="round"
                        />
                    </svg>

                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search by name or email..."
                        class="w-full rounded-xl border border-neutral-800 bg-neutral-950 py-2.5 pl-10 pr-4 text-sm text-white outline-none transition placeholder:text-neutral-600 focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/30"
                    />
                </div>

                <!-- Country Filter -->
                <select
                    v-model="country"
                    class="w-full rounded-xl border border-neutral-800 bg-neutral-950 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/30"
                >
                    <option value="">All Countries</option>

                    <option
                        v-for="item in countries"
                        :key="item"
                        :value="item"
                    >
                        {{ item }}
                    </option>
                </select>

                <!-- Clear Filters -->
                <button
                    type="button"
                    @click="clearFilters"
                    :disabled="!search && !country"
                    class="rounded-xl border border-neutral-800 px-4 py-2.5 text-sm font-semibold text-neutral-300 transition hover:border-neutral-700 hover:bg-neutral-800 hover:text-white disabled:cursor-not-allowed disabled:opacity-40"
                >
                    Clear Filters
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1100px] text-left">

                    <thead class="border-b border-neutral-800 bg-neutral-950/60">
                        <tr>
                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-neutral-500">
                                Customer
                            </th>

                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-neutral-500">
                                Account
                            </th>

                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-neutral-500">
                                Country
                            </th>

                            <th class="px-5 py-4 text-center text-xs font-semibold uppercase tracking-wider text-neutral-500">
                                Orders
                            </th>

                            <th class="px-5 py-4 text-center text-xs font-semibold uppercase tracking-wider text-neutral-500">
                                Entries
                            </th>

                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-neutral-500">
                                Total Spent
                            </th>

                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-neutral-500">
                                Registered
                            </th>

                            <th class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wider text-neutral-500">
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-neutral-800/80">

                        <tr
                            v-for="customer in customers.data"
                            :key="customer.encrypted_id"
                            class="transition hover:bg-white/[0.025]"
                        >
                            <!-- Customer -->
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/10 text-sm font-bold text-amber-400">
                                        {{ customerInitials(customer) }}
                                    </div>

                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-white">
                                            {{ customerName(customer) }}
                                        </p>

                                        <p class="mt-1 truncate text-xs text-neutral-500">
                                            {{ customer.email }}
                                        </p>

                                        <p
                                            v-if="customer.phone_number"
                                            class="mt-1 text-xs text-neutral-500"
                                        >
                                            {{ customer.phone_number }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- Verification -->
                            <td class="px-5 py-4">
                                <span
                                    :class="[
                                        'inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-semibold',
                                        statusClass(customer.email_verified)
                                    ]"
                                >
                                    <span
                                        :class="[
                                            'mr-1.5 h-1.5 w-1.5 rounded-full',
                                            customer.email_verified
                                                ? 'bg-emerald-400'
                                                : 'bg-amber-400'
                                        ]"
                                    ></span>

                                    {{ customer.email_verified ? 'Verified' : 'Unverified' }}
                                </span>
                            </td>

                            <!-- Country -->
                            <td class="px-5 py-4">
                                <span class="text-sm text-neutral-300">
                                    {{ formatCountry(customer.country) }}
                                </span>
                            </td>

                            <!-- Orders -->
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex min-w-8 items-center justify-center rounded-lg border border-neutral-700 bg-neutral-900 px-2 py-1 text-xs font-semibold text-white">
                                    {{ Number(customer.orders_count ?? 0).toLocaleString() }}
                                </span>
                            </td>

                            <!-- Entries -->
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex min-w-8 items-center justify-center rounded-lg border border-neutral-700 bg-neutral-900 px-2 py-1 text-xs font-semibold text-white">
                                    {{ Number(customer.total_entries ?? 0).toLocaleString() }}
                                </span>
                            </td>

                            <!-- Spending -->
                            <td class="px-5 py-4">
                                <span class="text-sm font-semibold text-amber-400">
                                    {{ formatSpending(customer.spending_by_currency) }}
                                </span>
                            </td>

                            <!-- Registration Date -->
                            <td class="px-5 py-4">
                                <span class="text-sm text-neutral-400">
                                    {{ formatDate(customer.created_at) }}
                                </span>
                            </td>

                            <!-- Action -->
                            <td class="px-5 py-4 text-right">
                                <Link
                                    :href="route('admin.customers.show', customer.encrypted_id)"
                                    class="inline-flex items-center gap-2 rounded-lg border border-neutral-700 bg-neutral-900 px-3 py-2 text-xs font-semibold text-neutral-300 transition hover:border-amber-500/40 hover:bg-amber-500/10 hover:text-amber-400"
                                >
                                    View Details

                                    <svg
                                        class="h-3.5 w-3.5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            d="M5 12h14m-6-6 6 6-6 6"
                                            stroke-width="1.7"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </Link>
                            </td>
                        </tr>

                        <!-- Empty State -->
                        <tr v-if="!customers.data?.length">
                            <td colspan="8" class="px-5 py-16 text-center">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-neutral-800 bg-neutral-900">
                                    <svg
                                        class="h-6 w-6 text-neutral-500"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.7"
                                            d="M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m16 0v-2a4 4 0 00-3-3.87M14 3.13a4 4 0 010 7.75M14 7a4 4 0 11-8 0 4 4 0 018 0z"
                                        />
                                    </svg>
                                </div>

                                <h3 class="mt-4 text-sm font-semibold text-white">
                                    No customers found
                                </h3>

                                <p class="mt-1 text-xs text-neutral-500">
                                    Try changing your search or country filter.
                                </p>

                                <button
                                    v-if="search || country"
                                    type="button"
                                    @click="clearFilters"
                                    class="mt-4 text-xs font-semibold text-amber-400 transition hover:text-amber-300"
                                >
                                    Clear all filters
                                </button>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="customers.links?.length > 3"
                class="flex flex-col gap-4 border-t border-neutral-800 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <p class="text-xs text-neutral-500">
                    Showing
                    <span class="font-semibold text-neutral-300">
                        {{ customers.from ?? 0 }}–{{ customers.to ?? 0 }}
                    </span>
                    of
                    <span class="font-semibold text-neutral-300">
                        {{ customers.total ?? 0 }}
                    </span>
                    customers
                </p>

                <div class="flex flex-wrap items-center gap-1.5">
                    <template
                        v-for="(link, index) in customers.links"
                        :key="index"
                    >
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            preserve-scroll
                            class="flex h-9 min-w-9 items-center justify-center rounded-lg border px-3 text-xs font-semibold transition"
                            :class="link.active
                                ? 'border-amber-500/30 bg-amber-500/10 text-amber-400'
                                : 'border-neutral-800 bg-neutral-950 text-neutral-400 hover:border-neutral-700 hover:bg-neutral-900 hover:text-white'"
                        >
                            <span v-html="link.label"></span>
                        </Link>

                        <span
                            v-else
                            class="flex h-9 min-w-9 cursor-not-allowed items-center justify-center rounded-lg border border-neutral-800/50 px-3 text-xs text-neutral-700"
                        >
                            <span v-html="link.label"></span>
                        </span>
                    </template>
                </div>
            </div>

        </section>
    </div>
</template>
