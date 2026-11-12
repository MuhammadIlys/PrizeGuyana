
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import RevenueAreaChart from '@/Components/Admin/RevenueAreaChart.vue';

defineOptions({
    layout: AdminLayout,
});

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({}),
    },
    revenueByCurrency: {
        type: Array,
        default: () => [],
    },
    revenueTrend: {
        type: Array,
        default: () => [],
    },
    paymentCounts: {
        type: Array,
        default: () => [],
    },
    recentOrders: {
        type: Array,
        default: () => [],
    },
    recentCustomers: {
        type: Array,
        default: () => [],
    },
    topCompetitions: {
        type: Array,
        default: () => [],
    },
    upcomingDraws: {
        type: Array,
        default: () => [],
    },
});

/*
|--------------------------------------------------------------------------
| Dashboard Overview Cards
|--------------------------------------------------------------------------
*/

const overviewCards = [
    {
        title: 'Total Customers',
        value: () => props.stats.total_customers ?? 0,
        description: 'Registered customers',
        icon: 'users',
    },
    {
        title: 'Total Competitions',
        value: () => props.stats.total_competitions ?? 0,
        description: 'All competitions',
        icon: 'trophy',
    },
    {
        title: 'Active Competitions',
        value: () => props.stats.active_competitions ?? 0,
        description: 'Currently running',
        icon: 'activity',
    },
    {
        title: 'Paid Entries',
        value: () => props.stats.paid_entries ?? 0,
        description: 'Entries from paid orders',
        icon: 'ticket',
    },
    {
        title: 'Available Tickets',
        value: () => props.stats.available_tickets ?? 0,
        description: 'Ready for allocation',
        icon: 'ticket',
    },
    {
        title: 'Total Orders',
        value: () => props.stats.total_orders ?? 0,
        description: 'All customer orders',
        icon: 'shopping-cart',
    },
    {
        title: 'Paid Orders',
        value: () => props.stats.paid_orders ?? 0,
        description: 'Successfully paid orders',
        icon: 'check-circle',
    },
    {
        title: 'Pending Orders',
        value: () => props.stats.pending_orders ?? 0,
        description: 'Awaiting payment',
        icon: 'clock',
    },
];

const competitionStats = [
    {
        label: 'Published',
        value: () => props.stats.published_competitions ?? 0,
    },
    {
        label: 'Upcoming',
        value: () => props.stats.upcoming_competitions ?? 0,
    },
    {
        label: 'Completed',
        value: () => props.stats.completed_competitions ?? 0,
    },
];

/*
|--------------------------------------------------------------------------
| Encrypted Navigation
|--------------------------------------------------------------------------
*/

function competitionUrl(competition) {
    return route('admin.competitions.show', competition.encrypted_id);
}

function orderUrl(order) {
    return route('admin.orders.show', order.encrypted_id);
}

function customerUrl(customer) {
    return route('admin.customers.show', customer.encrypted_id);
}

/*
|--------------------------------------------------------------------------
| Formatting Helpers
|--------------------------------------------------------------------------
*/

function formatNumber(value) {
    return Number(value ?? 0).toLocaleString();
}

function paymentStatusClass(status) {
    const classes = {
        paid: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
        succeeded: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
        completed: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
        pending: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
        processing: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        failed: 'bg-red-500/10 text-red-400 border-red-500/20',
        cancelled: 'bg-neutral-500/10 text-neutral-400 border-neutral-500/20',
        refunded: 'bg-purple-500/10 text-purple-400 border-purple-500/20',
    };

    return classes[status?.toLowerCase()] ??
        'bg-neutral-500/10 text-neutral-400 border-neutral-500/20';
}

function formatStatus(status) {
    if (!status) return 'Unknown';

    return status
        .replaceAll('_', ' ')
        .replace(/\b\w/g, character => character.toUpperCase());
}

function entryPercentage(competition) {
    const total = Number(competition.total_entries ?? 0);
    const sold = Number(competition.sold_entries ?? 0);

    if (total <= 0) return 0;

    return Math.min(100, Math.round((sold / total) * 100));
}
</script>

<template>
    <Head title="Admin Dashboard" />

    <div class="space-y-6">

        <!-- Welcome Banner -->
        <section
            class="relative overflow-hidden rounded-2xl border border-amber-500/20 bg-[#121212] px-6 py-8 shadow-lg sm:px-8">

            <div class="absolute -right-10 -top-16 h-48 w-48 rounded-full bg-amber-500/10 blur-3xl"></div>

            <div class="relative z-10 flex flex-col justify-between gap-6 sm:flex-row sm:items-center">

                <div>
                    <span
                        class="mb-4 inline-flex items-center gap-2 rounded-full border border-amber-500/20 bg-amber-500/10 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-amber-400">

                        <span class="h-2 w-2 rounded-full bg-amber-400"></span>

                        Admin Dashboard
                    </span>

                    <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                        Welcome back, Admin!
                    </h1>

                    <p class="mt-3 max-w-xl text-sm leading-6 text-neutral-400">
                        Manage competitions, monitor customer activity, track payments,
                        and oversee platform performance from one central dashboard.
                    </p>
                </div>

                <Link
                    :href="route('admin.competitions.create')"
                    class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-amber-400/30 bg-amber-500 px-5 py-3 text-sm font-semibold text-neutral-950 shadow-lg shadow-amber-500/10 transition hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 focus:ring-offset-neutral-950">

                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 4v16m8-8H4" />
                    </svg>

                    Create Competition
                </Link>

            </div>
        </section>

        <!-- Platform Overview -->
        <section>

            <div class="mb-5">
                <h2 class="text-lg font-bold text-white">
                    Platform Overview
                </h2>
                <p class="mt-1 text-xs text-neutral-500">
                    Overall statistics across PrizeGuyana
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

                <div
                    v-for="card in overviewCards"
                    :key="card.title"
                    class="rounded-2xl border border-neutral-800 bg-[#121212] p-5 transition hover:border-amber-500/30">

                    <div class="flex items-center justify-between">

                        <span class="text-sm font-medium text-neutral-400">
                            {{ card.title }}
                        </span>

                        <span
                            class="flex h-10 w-10 items-center justify-center rounded-xl border border-amber-500/15 bg-amber-500/10 text-amber-400">

                            <!-- Users -->
                            <svg v-if="card.icon === 'users'"
                                class="h-5 w-5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M17 20h5v-2a4 4 0 00-5-3.87M9 20H2v-2a4 4 0 017.75-1M16 20v-2a4 4 0 00-8 0v2m8-11a4 4 0 11-8 0 4 4 0 018 0zm5-3a4 4 0 11-8 0" />
                            </svg>

                            <!-- Trophy -->
                            <svg v-else-if="card.icon === 'trophy'"
                                class="h-5 w-5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M8 21h8m-4-4v4m-5-18h10v5a5 5 0 01-10 0V3zm10 2h4v2a4 4 0 01-4 4M7 5H3v2a4 4 0 004 4" />
                            </svg>

                            <!-- Activity -->
                            <svg v-else-if="card.icon === 'activity'"
                                class="h-5 w-5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="1.8" d="M3 12h4l3-8 4 16 3-8h4" />
                            </svg>

                            <!-- Ticket -->
                            <svg v-else-if="card.icon === 'ticket'"
                                class="h-5 w-5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M2 9a3 3 0 013-3h14a3 3 0 013 3v1a2 2 0 000 4v1a3 3 0 01-3 3H5a3 3 0 01-3-3v-1a2 2 0 000-4V9z" />
                            </svg>

                            <!-- Shopping cart -->
                            <svg v-else-if="card.icon === 'shopping-cart'"
                                class="h-5 w-5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M3 3h2l2.5 12h11l2-8H6m3 12a1 1 0 100 2 1 1 0 000-2zm8 0a1 1 0 100 2 1 1 0 000-2z" />
                            </svg>

                            <!-- Check circle -->
                            <svg v-else-if="card.icon === 'check-circle'"
                                class="h-5 w-5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>

                            <!-- Clock -->
                            <svg v-else
                                class="h-5 w-5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9" stroke-width="1.8" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="1.8" d="M12 7v5l3 2" />
                            </svg>

                        </span>
                    </div>

                    <div class="mt-5">
                        <h3 class="text-3xl font-bold tracking-tight text-white">
                            {{ formatNumber(card.value()) }}
                        </h3>

                        <p class="mt-1 text-xs text-neutral-500">
                            {{ card.description }}
                        </p>
                    </div>
                </div>

            </div>
        </section>

        <!-- Competition and Revenue Overview -->
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-1">

            <!-- Revenue Overview -->
            <section class="rounded-2xl border border-neutral-800 bg-[#121212] p-6">

                <div class="mb-6">
                    <h2 class="text-lg font-bold text-white">
                        Revenue Overview
                    </h2>
                    <p class="mt-1 text-xs text-neutral-500">
                        Lifetime revenue and daily revenue over the last 30 days
                    </p>
                </div>

                <!-- Lifetime Revenue by Currency -->
                <div v-if="revenueByCurrency.length" class="mb-6 space-y-3">

                    <div
                        v-for="revenue in revenueByCurrency"
                        :key="revenue.currency"
                        class="flex items-center justify-between gap-4 rounded-xl border border-neutral-800 bg-neutral-950 p-4">

                        <div>
                            <p class="text-xs text-neutral-500">
                                Currency
                            </p>
                            <p class="mt-1 font-semibold text-white">
                                {{ revenue.currency }}
                            </p>
                        </div>

                        <div class="text-right">
                            <p class="text-xs text-neutral-500">
                                Lifetime Revenue
                            </p>
                            <p class="mt-1 text-xl font-bold text-amber-400">
                                {{ revenue.total }} {{ revenue.currency }}
                            </p>
                        </div>

                    </div>

                </div>

                <div v-else
                    class="mb-6 rounded-xl border border-dashed border-neutral-800 px-4 py-6 text-center">
                    <p class="text-sm text-neutral-500">
                        No paid orders recorded yet.
                    </p>
                </div>

                <!-- Daily Revenue Area Chart -->
                <div class="border-t border-neutral-800 pt-5">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <h3 class="text-sm font-semibold text-white">
                                Daily Revenue
                            </h3>
                            <p class="mt-1 text-xs text-neutral-500">
                                Last 30 days · currencies shown separately
                            </p>
                        </div>

                        <span class="rounded-lg bg-neutral-900 px-3 py-1.5 text-xs text-neutral-400">
                            {{ [...new Set(revenueTrend.map(item => item.currency))].length }} currencies
                        </span>
                    </div>

                    <RevenueAreaChart :revenue-trend="revenueTrend" />
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3">

                    <div class="rounded-xl border border-neutral-800 bg-neutral-950 p-4">
                        <p class="text-xs text-neutral-500">
                            Failed Orders
                        </p>
                        <p class="mt-2 text-2xl font-bold text-red-400">
                            {{ formatNumber(stats.failed_orders) }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-neutral-800 bg-neutral-950 p-4">
                        <p class="text-xs text-neutral-500">
                            Total Payments
                        </p>
                        <p class="mt-2 text-2xl font-bold text-white">
                            {{ formatNumber(stats.total_payments) }}
                        </p>
                    </div>

                </div>

            </section>
            
            <!-- Competition Overview -->
            <section class="rounded-2xl border border-neutral-800 bg-[#121212] p-6">

                <div class="mb-6">
                    <h2 class="text-lg font-bold text-white">
                        Competition Overview
                    </h2>
                    <p class="mt-1 text-xs text-neutral-500">
                        Competition status and ticket inventory
                    </p>
                </div>

                <div class="grid grid-cols-3 gap-3">

                    <div
                        v-for="item in competitionStats"
                        :key="item.label"
                        class="rounded-xl border border-neutral-800 bg-neutral-950 p-4">

                        <p class="text-xs text-neutral-500">
                            {{ item.label }}
                        </p>

                        <p class="mt-2 text-2xl font-bold text-white">
                            {{ formatNumber(item.value()) }}
                        </p>
                    </div>

                </div>

                <div class="mt-6 grid grid-cols-2 gap-3">

                    <div class="rounded-xl border border-neutral-800 bg-neutral-950 p-4">
                        <p class="text-xs text-neutral-500">
                            Total Generated Tickets
                        </p>
                        <p class="mt-2 text-2xl font-bold text-white">
                            {{ formatNumber(stats.total_tickets) }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-neutral-800 bg-neutral-950 p-4">
                        <p class="text-xs text-neutral-500">
                            Allocated Tickets
                        </p>
                        <p class="mt-2 text-2xl font-bold text-amber-400">
                            {{ formatNumber(stats.assigned_tickets) }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-neutral-800 bg-neutral-950 p-4">
                        <p class="text-xs text-neutral-500">
                            Paid Entries
                        </p>
                        <p class="mt-2 text-2xl font-bold text-emerald-400">
                            {{ formatNumber(stats.paid_entries) }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-neutral-800 bg-neutral-950 p-4">
                        <p class="text-xs text-neutral-500">
                            Available Tickets
                        </p>
                        <p class="mt-2 text-2xl font-bold text-white">
                            {{ formatNumber(stats.available_tickets) }}
                        </p>
                    </div>

                </div>

            </section>

        </div>

        <!-- Top Competitions -->
        <section class="rounded-2xl border border-neutral-800 bg-[#121212] p-6">

            <div class="mb-6">
                <h2 class="text-lg font-bold text-white">
                    Top Competitions
                </h2>
                <p class="mt-1 text-xs text-neutral-500">
                    Competitions ranked by paid entries
                </p>
            </div>

            <div v-if="topCompetitions.length" class="space-y-5">

                <Link
                    v-for="competition in topCompetitions"
                    :key="competition.encrypted_id"
                    :href="competitionUrl(competition)"
                    class="group flex flex-col gap-4 rounded-xl p-3 transition hover:bg-white/[0.025] sm:flex-row sm:items-center">

                    <div class="flex min-w-0 flex-1 items-center gap-4">

                        <div
                            class="h-14 w-14 shrink-0 overflow-hidden rounded-xl border border-neutral-800 bg-neutral-950">

                            <img
                                v-if="competition.main_image"
                                :src="competition.main_image"
                                :alt="competition.title"
                                class="h-full w-full object-cover"
                            />

                            <div v-else
                                class="flex h-full items-center justify-center text-amber-400">
                                <span class="text-lg font-bold">PG</span>
                            </div>

                        </div>

                        <div class="min-w-0 flex-1">

                            <h3 class="truncate text-sm font-semibold text-white transition group-hover:text-amber-400">
                                {{ competition.title }}
                            </h3>

                            <p class="mt-1 text-xs text-neutral-500">
                                {{ formatNumber(competition.sold_entries) }} paid /
                                {{ formatNumber(competition.total_entries) }} total entries
                            </p>

                            <p class="mt-1 text-xs text-neutral-600">
                                {{ formatNumber(competition.allocated_entries) }} allocated
                            </p>

                            <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-neutral-800">
                                <div
                                    class="h-full rounded-full bg-amber-500 transition-all"
                                    :style="{ width: `${entryPercentage(competition)}%` }">
                                </div>
                            </div>

                        </div>

                    </div>

                    <div class="shrink-0 sm:text-right">
                        <p class="text-lg font-bold text-amber-400">
                            {{ entryPercentage(competition) }}%
                        </p>
                        <p class="text-xs text-neutral-500">
                            Paid-entry progress
                        </p>
                    </div>

                    <svg class="hidden h-5 w-5 shrink-0 text-neutral-600 transition group-hover:text-amber-400 sm:block"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>

                </Link>

            </div>

            <div v-else
                class="rounded-xl border border-dashed border-neutral-800 px-4 py-10 text-center">
                <p class="text-sm text-neutral-500">
                    No competition records available.
                </p>
            </div>

        </section>

        <!-- Recent Orders and Customers -->
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

            <!-- Recent Orders -->
            <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">

                <div class="border-b border-neutral-800 px-6 py-5">
                    <h2 class="text-lg font-bold text-white">
                        Recent Orders
                    </h2>
                    <p class="mt-1 text-xs text-neutral-500">
                        Latest customer orders
                    </p>
                </div>

                <div v-if="recentOrders.length" class="overflow-x-auto">

                    <table class="w-full text-left text-sm">

                        <thead class="border-b border-neutral-800 bg-neutral-950/60 text-xs uppercase text-neutral-500">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Order</th>
                                <th class="px-5 py-3 font-semibold">Customer</th>
                                <th class="px-5 py-3 font-semibold">Total</th>
                                <th class="px-5 py-3 font-semibold">Status</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-neutral-800/80">

                            <tr
                                v-for="order in recentOrders"
                                :key="order.encrypted_id"
                                class="transition hover:bg-white/[0.025]">

                                <td class="whitespace-nowrap px-5 py-4">
                                    <Link
                                        :href="orderUrl(order)"
                                        class="font-semibold text-white transition hover:text-amber-400">
                                        {{ order.order_number }}
                                    </Link>

                                    <p class="mt-1 text-xs text-neutral-500">
                                        {{ order.date }}
                                    </p>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="max-w-[140px] truncate font-medium text-neutral-300">
                                        {{ order.customer_name }}
                                    </p>
                                    <p class="mt-1 max-w-[140px] truncate text-xs text-neutral-500">
                                        {{ order.customer_email }}
                                    </p>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 font-medium text-white">
                                    {{ order.total }} {{ order.currency }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    <span
                                        class="inline-flex rounded-full border px-2.5 py-1 text-[10px] font-semibold"
                                        :class="paymentStatusClass(order.payment_status)">
                                        {{ formatStatus(order.payment_status) }}
                                    </span>
                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

                <div v-else class="px-6 py-12 text-center">
                    <p class="text-sm text-neutral-500">
                        No orders have been recorded yet.
                    </p>
                </div>

            </section>

            <!-- Recent Customers -->
            <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">

                <div class="border-b border-neutral-800 px-6 py-5">
                    <h2 class="text-lg font-bold text-white">
                        Recent Customers
                    </h2>
                    <p class="mt-1 text-xs text-neutral-500">
                        Latest customer registrations
                    </p>
                </div>

                <div v-if="recentCustomers.length" class="divide-y divide-neutral-800/80">

                    <Link
                        v-for="customer in recentCustomers"
                        :key="customer.encrypted_id"
                        :href="customerUrl(customer)"
                        class="group flex items-center gap-4 px-6 py-4 transition hover:bg-white/[0.025]">

                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/10 text-sm font-bold text-amber-400">

                            {{ customer.name?.charAt(0)?.toUpperCase() ?? 'U' }}

                        </div>

                        <div class="min-w-0 flex-1">

                            <p class="truncate text-sm font-semibold text-white transition group-hover:text-amber-400">
                                {{ customer.name }}
                            </p>

                            <p class="mt-1 truncate text-xs text-neutral-500">
                                {{ customer.email }}
                            </p>

                        </div>

                        <div class="shrink-0 text-right">
                            <p class="text-[10px] uppercase tracking-wide text-neutral-600">
                                Joined
                            </p>
                            <p class="mt-1 text-xs text-neutral-400">
                                {{ customer.joined_at }}
                            </p>
                        </div>

                        <svg class="h-4 w-4 shrink-0 text-neutral-600 transition group-hover:text-amber-400"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>

                    </Link>

                </div>

                <div v-else class="px-6 py-12 text-center">
                    <p class="text-sm text-neutral-500">
                        No customers registered yet.
                    </p>
                </div>

            </section>

        </div>

        <!-- Upcoming Draws and Payment Status -->
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

            <!-- Upcoming Draws -->
            <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">

                <div class="border-b border-neutral-800 px-6 py-5">
                    <h2 class="text-lg font-bold text-white">
                        Upcoming Draws
                    </h2>
                    <p class="mt-1 text-xs text-neutral-500">
                        Scheduled competition draws
                    </p>
                </div>

                <div v-if="upcomingDraws.length" class="divide-y divide-neutral-800/80">

                    <Link
                        v-for="draw in upcomingDraws"
                        :key="draw.encrypted_id"
                        :href="competitionUrl(draw)"
                        class="group flex items-center justify-between gap-4 px-6 py-4 transition hover:bg-white/[0.025]">

                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-white transition group-hover:text-amber-400">
                                {{ draw.title }}
                            </p>

                            <p class="mt-1 text-xs text-neutral-500">
                                {{ draw.draw_at }}
                            </p>

                            <p class="mt-1 text-xs text-neutral-600">
                                {{ formatNumber(draw.allocated_entries) }} allocated
                            </p>
                        </div>

                        <div class="shrink-0 text-right">
                            <p class="text-lg font-bold text-amber-400">
                                {{ formatNumber(draw.sold_entries) }}
                            </p>
                            <p class="text-[10px] text-neutral-500">
                                Paid entries
                            </p>
                        </div>

                        <svg class="h-4 w-4 shrink-0 text-neutral-600 transition group-hover:text-amber-400"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>

                    </Link>

                </div>

                <div v-else class="px-6 py-12 text-center">
                    <p class="text-sm text-neutral-500">
                        No upcoming draws scheduled.
                    </p>
                </div>

            </section>

            <!-- Payment Status -->
            <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">

                <div class="border-b border-neutral-800 px-6 py-5">
                    <h2 class="text-lg font-bold text-white">
                        Payment Status
                    </h2>
                    <p class="mt-1 text-xs text-neutral-500">
                        Breakdown of recorded payment statuses
                    </p>
                </div>

                <div v-if="paymentCounts.length" class="space-y-3 p-6">

                    <div
                        v-for="payment in paymentCounts"
                        :key="payment.status"
                        class="flex items-center justify-between gap-4 rounded-xl border border-neutral-800 bg-neutral-950 p-4">

                        <span
                            class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold"
                            :class="paymentStatusClass(payment.status)">
                            {{ formatStatus(payment.status) }}
                        </span>

                        <span class="text-lg font-bold text-white">
                            {{ formatNumber(payment.total) }}
                        </span>

                    </div>

                </div>

                <div v-else class="px-6 py-12 text-center">
                    <p class="text-sm text-neutral-500">
                        No payment records available yet.
                    </p>
                </div>

            </section>

        </div>

    </div>
</template>
