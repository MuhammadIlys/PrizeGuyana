
<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

import { formatDate } from '../../../helpers/date';
import { formatMoney } from '../../../helpers/money';

defineOptions({
    layout: AdminLayout,
});

const props = defineProps({
    customer: {
        type: Object,
        required: true,
    },

    stats: {
        type: Object,
        default: () => ({
            total_orders: 0,
            paid_orders: 0,
            pending_orders: 0,
            total_entries: 0,
            total_payments: 0,
        }),
    },

    spendingByCurrency: {
        type: Array,
        default: () => [],
    },

    orders: {
        type: Array,
        default: () => [],
    },

    payments: {
        type: Array,
        default: () => [],
    },

    tickets: {
        type: Array,
        default: () => [],
    },
});

const initials = computed(() => {
    return (props.customer.name || 'Customer')
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('') || 'C';
});

const totalSpent = computed(() => {
    return props.spendingByCurrency;
});

const accountStatus = computed(() => {
    return props.customer.email_verified
        ? 'Verified'
        : 'Unverified';
});

const statCards = computed(() => [
    {
        label: 'Total Orders',
        value: Number(props.stats.total_orders ?? 0).toLocaleString(),
        description: 'All customer orders',
        icon: 'orders',
        color: 'amber',
    },
    {
        label: 'Paid Orders',
        value: Number(props.stats.paid_orders ?? 0).toLocaleString(),
        description: 'Successfully paid orders',
        icon: 'paid',
        color: 'emerald',
    },
    {
        label: 'Pending Orders',
        value: Number(props.stats.pending_orders ?? 0).toLocaleString(),
        description: 'Awaiting payment',
        icon: 'pending',
        color: 'orange',
    },
    {
        label: 'Total Entries',
        value: Number(props.stats.total_entries ?? 0).toLocaleString(),
        description: 'Purchased ticket records',
        icon: 'tickets',
        color: 'blue',
    },
]);

function statusClass(status) {
    const value = String(status ?? '').toLowerCase();

    if (['paid', 'completed', 'assigned'].includes(value)) {
        return 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400';
    }

    if (['pending', 'processing', 'available'].includes(value)) {
        return 'border-amber-500/20 bg-amber-500/10 text-amber-400';
    }

    if (['failed', 'cancelled', 'refunded', 'unavailable'].includes(value)) {
        return 'border-red-500/20 bg-red-500/10 text-red-400';
    }

    return 'border-neutral-700 bg-neutral-800 text-neutral-400';
}

function statusLabel(status) {
    if (!status) return 'Unknown';

    return String(status)
        .replaceAll('_', ' ')
        .replace(/\b\w/g, (letter) => letter.toUpperCase());
}

function displayValue(value) {
    return value !== null && value !== undefined && value !== ''
        ? value
        : '—';
}

function orderUrl(order) {
    return route('admin.orders.show', order.encrypted_id);
}

function competitionUrl(encryptedId) {
    return encryptedId
        ? route('admin.competitions.show', encryptedId)
        : null;
}
</script>

<template>
    <Head :title="`${customer.name} - Customer Details`" />

    <div class="mx-auto w-full max-w-[1600px] space-y-6">

        <!-- Page Header -->
        <header class="flex flex-col justify-between gap-5 sm:flex-row sm:items-center">

            <div>
                <div class="mb-3 flex items-center gap-2 text-xs text-neutral-500">
                    <Link
                        :href="route('admin.customers.index')"
                        class="transition hover:text-amber-400"
                    >
                        Customers
                    </Link>

                    <span>/</span>

                    <span class="text-neutral-300">
                        Customer Details
                    </span>
                </div>

                <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                    Customer Details
                </h1>

                <p class="mt-2 text-sm text-neutral-400">
                    View customer information, orders, payments and competition entries.
                </p>
            </div>

            <Link
                :href="route('admin.customers.index')"
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-neutral-700 bg-neutral-900 px-4 py-2.5 text-sm font-semibold text-neutral-300 transition hover:border-amber-500/40 hover:bg-amber-500/10 hover:text-amber-400"
            >
                <svg
                    class="h-4 w-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 19l-7-7 7-7"
                    />
                </svg>

                Back to Customers
            </Link>

        </header>

        <!-- Customer Profile -->
        <section class="relative overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">

            <div class="absolute left-0 right-0 top-0 h-1 bg-gradient-to-r from-transparent via-amber-500 to-transparent opacity-70"></div>

            <div class="p-6 sm:p-8">

                <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">

                    <!-- Customer Identity -->
                    <div class="flex items-center gap-4">

                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl border border-amber-500/20 bg-amber-500/10 text-xl font-bold text-amber-400 sm:h-20 sm:w-20 sm:text-2xl">
                            {{ initials }}
                        </div>

                        <div class="min-w-0">

                            <h2 class="text-xl font-bold text-white sm:text-2xl">
                                {{ customer.name }}
                            </h2>

                            <p class="mt-1 break-all text-sm text-neutral-400">
                                {{ customer.email }}
                            </p>

                            <div class="mt-3 flex flex-wrap items-center gap-2">

                                <span
                                    class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1 text-[11px] font-bold"
                                    :class="customer.email_verified
                                        ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400'
                                        : 'border-amber-500/20 bg-amber-500/10 text-amber-400'"
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full"
                                        :class="customer.email_verified ? 'bg-emerald-400' : 'bg-amber-400'"
                                    ></span>

                                    {{ accountStatus }}
                                </span>

                                <span class="rounded-lg border border-neutral-700 bg-neutral-900 px-2.5 py-1 text-[11px] font-medium text-neutral-400">
                                    Registered Customer
                                </span>

                            </div>

                        </div>

                    </div>

                    <!-- Registration Information -->
                    <div class="rounded-xl border border-neutral-800 bg-neutral-950 px-5 py-4 sm:min-w-52">

                        <p class="text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                            Customer Since
                        </p>

                        <p class="mt-2 text-sm font-semibold text-white">
                            {{ formatDate(customer.created_at) }}
                        </p>

                        <p class="mt-1 text-xs text-neutral-500">
                            Registration date
                        </p>

                    </div>

                </div>

            </div>

        </section>

        <!-- Statistics -->
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            <div
                v-for="stat in statCards"
                :key="stat.label"
                class="rounded-2xl border border-neutral-800 bg-[#121212] p-5 transition hover:border-amber-500/20"
            >

                <div class="flex items-start justify-between">

                    <div>
                        <p class="text-xs font-semibold text-neutral-400">
                            {{ stat.label }}
                        </p>

                        <p class="mt-3 text-2xl font-bold tracking-tight text-white">
                            {{ stat.value }}
                        </p>

                        <p class="mt-1 text-xs text-neutral-500">
                            {{ stat.description }}
                        </p>
                    </div>

                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl"
                        :class="{
                            'bg-amber-500/10 text-amber-400': stat.color === 'amber',
                            'bg-emerald-500/10 text-emerald-400': stat.color === 'emerald',
                            'bg-orange-500/10 text-orange-400': stat.color === 'orange',
                            'bg-blue-500/10 text-blue-400': stat.color === 'blue',
                        }"
                    >

                        <svg
                            v-if="stat.icon === 'orders'"
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.7"
                                d="M7 3h10a2 2 0 012 2v14l-2-1.5L15 19l-3-1.5L9 19l-2-1.5L5 19V5a2 2 0 012-2z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.7"
                                d="M9 8h6M9 12h6"
                            />
                        </svg>

                        <svg
                            v-else-if="stat.icon === 'paid'"
                            class="h-5 w-5"
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
                            <circle cx="12" cy="12" r="9" stroke-width="1.7" />
                        </svg>

                        <svg
                            v-else-if="stat.icon === 'pending'"
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <circle cx="12" cy="12" r="9" stroke-width="1.7" />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.7"
                                d="M12 7v5l3 2"
                            />
                        </svg>

                        <svg
                            v-else
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.7"
                                d="M4 7h16M4 12h16M4 17h16M7 4v16M17 4v16"
                            />
                        </svg>

                    </div>

                </div>

            </div>

        </section>

        <!-- Customer Information & Spending -->
        <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            <!-- Personal Information -->
            <div class="rounded-2xl border border-neutral-800 bg-[#121212] p-5 sm:p-6 lg:col-span-2">

                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-white">
                            Personal Information
                        </h3>

                        <p class="mt-1 text-xs text-neutral-500">
                            Registered customer account details
                        </p>
                    </div>

                    <div class="rounded-lg bg-amber-500/10 p-2 text-amber-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.7"
                                d="M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m6-10a4 4 0 100-8 4 4 0 000 8zm10 10v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"
                            />
                        </svg>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                            Full Name
                        </p>
                        <p class="mt-2 text-sm font-medium text-white">
                            {{ displayValue(customer.name) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                            Email Address
                        </p>
                        <p class="mt-2 break-all text-sm font-medium text-white">
                            {{ displayValue(customer.email) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                            Phone Number
                        </p>
                        <p class="mt-2 text-sm font-medium text-white">
                            {{ displayValue(customer.phone_number) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                            Country
                        </p>
                        <p class="mt-2 text-sm font-medium text-white">
                            {{ displayValue(customer.country) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                            Email Verification
                        </p>
                        <p
                            class="mt-2 text-sm font-semibold"
                            :class="customer.email_verified ? 'text-emerald-400' : 'text-amber-400'"
                        >
                            {{ accountStatus }}
                        </p>
                    </div>

                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                            Registration Date
                        </p>
                        <p class="mt-2 text-sm font-medium text-white">
                            {{ formatDate(customer.created_at) }}
                        </p>
                    </div>

                </div>

            </div>

            <!-- Spending Summary -->
            <div class="rounded-2xl border border-amber-500/20 bg-[#121212] p-5 sm:p-6">

                <div class="mb-5">
                    <h3 class="text-base font-bold text-white">
                        Spending Summary
                    </h3>

                    <p class="mt-1 text-xs text-neutral-500">
                        Total successfully paid orders
                    </p>
                </div>

                <div
                    v-if="totalSpent.length"
                    class="space-y-3"
                >

                    <div
                        v-for="item in totalSpent"
                        :key="item.currency"
                        class="rounded-xl border border-neutral-800 bg-neutral-950 p-4"
                    >
                        <p class="text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                            {{ item.currency }} Spending
                        </p>

                        <p class="mt-2 text-2xl font-bold tracking-tight text-amber-400">
                            {{ formatMoney(item.total, item.currency) }}
                        </p>
                    </div>

                </div>

                <div
                    v-else
                    class="rounded-xl border border-dashed border-neutral-800 bg-neutral-950 px-4 py-8 text-center"
                >
                    <p class="text-sm font-medium text-neutral-400">
                        No paid orders yet
                    </p>

                    <p class="mt-1 text-xs text-neutral-600">
                        Customer spending will appear here after successful payments.
                    </p>
                </div>

                <div class="mt-5 border-t border-neutral-800 pt-4">

                    <div class="flex items-center justify-between text-xs">
                        <span class="text-neutral-500">
                            Total Payments
                        </span>

                        <span class="font-semibold text-white">
                            {{ Number(stats.total_payments ?? 0).toLocaleString() }}
                        </span>
                    </div>

                    <div class="mt-3 flex items-center justify-between text-xs">
                        <span class="text-neutral-500">
                            Paid Orders
                        </span>

                        <span class="font-semibold text-emerald-400">
                            {{ Number(stats.paid_orders ?? 0).toLocaleString() }}
                        </span>
                    </div>

                </div>

            </div>

        </section>

        <!-- Order History -->
        <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">

            <div class="flex flex-col justify-between gap-3 border-b border-neutral-800 p-5 sm:flex-row sm:items-center sm:px-6">

                <div>
                    <h3 class="text-base font-bold text-white">
                        Order History
                    </h3>

                    <p class="mt-1 text-xs text-neutral-500">
                        All orders placed by this customer
                    </p>
                </div>

                <span class="w-fit rounded-lg border border-neutral-700 bg-neutral-900 px-3 py-1.5 text-xs font-semibold text-neutral-300">
                    {{ orders.length }} Orders
                </span>

            </div>

            <div v-if="orders.length" class="overflow-x-auto">

                <table class="w-full min-w-[850px] text-left text-sm">

                    <thead class="border-b border-neutral-800 bg-neutral-950/60 text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                        <tr>
                            <th class="px-6 py-4">Order</th>
                            <th class="px-6 py-4">Items</th>
                            <th class="px-6 py-4">Order Status</th>
                            <th class="px-6 py-4">Payment</th>
                            <th class="px-6 py-4">Total</th>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4 text-right">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-neutral-800/80">

                        <tr
                            v-for="order in orders"
                            :key="order.encrypted_id"
                            class="transition hover:bg-white/[0.025]"
                        >

                            <td class="px-6 py-4">
                                <Link
                                    :href="orderUrl(order)"
                                    class="font-semibold text-amber-400 transition hover:text-amber-300"
                                >
                                    {{ order.order_number }}
                                </Link>
                            </td>

                            <td class="px-6 py-4">
                                <span class="text-sm text-white">
                                    {{ order.items_count }}
                                </span>

                                <span class="ml-1 text-xs text-neutral-500">
                                    items
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex rounded-lg border px-2.5 py-1 text-[10px] font-bold"
                                    :class="statusClass(order.status)"
                                >
                                    {{ statusLabel(order.status) }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex rounded-lg border px-2.5 py-1 text-[10px] font-bold"
                                    :class="statusClass(order.payment_status)"
                                >
                                    {{ statusLabel(order.payment_status) }}
                                </span>
                            </td>

                            <td class="px-6 py-4 font-semibold text-white">
                                {{ formatMoney(order.total, order.currency) }}
                            </td>

                            <td class="px-6 py-4 text-xs text-neutral-400">
                                {{ formatDate(order.created_at) }}
                            </td>

                            <td class="px-6 py-4 text-right">
                                <Link
                                    :href="orderUrl(order)"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-neutral-700 bg-neutral-900 px-3 py-2 text-xs font-semibold text-neutral-300 transition hover:border-amber-500/30 hover:bg-amber-500/10 hover:text-amber-400"
                                >
                                    View Order

                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M7 17L17 7M7 7h10v10"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </Link>
                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

            <div v-else class="px-6 py-12 text-center">
                <p class="text-sm font-semibold text-neutral-300">
                    No orders found
                </p>
                <p class="mt-1 text-xs text-neutral-500">
                    This customer has not placed any orders yet.
                </p>
            </div>

        </section>

        <!-- Purchased Competitions -->
        <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">

            <div class="border-b border-neutral-800 p-5 sm:px-6">
                <h3 class="text-base font-bold text-white">
                    Purchased Competitions
                </h3>

                <p class="mt-1 text-xs text-neutral-500">
                    Competition items included in customer orders
                </p>
            </div>

            <div v-if="orders.some(order => order.items?.length)" class="overflow-x-auto">

                <table class="w-full min-w-[750px] text-left text-sm">

                    <thead class="border-b border-neutral-800 bg-neutral-950/60 text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                        <tr>
                            <th class="px-6 py-4">Competition</th>
                            <th class="px-6 py-4">Order</th>
                            <th class="px-6 py-4">Quantity</th>
                            <th class="px-6 py-4">Unit Price</th>
                            <th class="px-6 py-4">Line Total</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-neutral-800/80">

                        <template
                            v-for="order in orders"
                            :key="`items-${order.encrypted_id}`"
                        >

                            <tr
                                v-for="(item, index) in order.items"
                                :key="item.encrypted_id || `${order.encrypted_id}-${index}`"
                                class="transition hover:bg-white/[0.025]"
                            >

                                <td class="px-6 py-4">
                                    <Link
                                        v-if="item.competition_encrypted_id"
                                        :href="competitionUrl(item.competition_encrypted_id)"
                                        class="font-semibold text-amber-400 transition hover:text-amber-300"
                                    >
                                        {{ item.competition_title }}
                                    </Link>

                                    <p
                                        v-else
                                        class="font-semibold text-white"
                                    >
                                        {{ item.competition_title }}
                                    </p>
                                </td>

                                <td class="px-6 py-4">
                                    <Link
                                        :href="orderUrl(order)"
                                        class="text-xs font-semibold text-amber-400 hover:text-amber-300"
                                    >
                                        {{ order.order_number }}
                                    </Link>
                                </td>

                                <td class="px-6 py-4 font-medium text-white">
                                    {{ item.quantity }}
                                </td>

                                <td class="px-6 py-4 text-neutral-300">
                                    {{ formatMoney(item.unit_price, item.currency) }}
                                </td>

                                <td class="px-6 py-4 font-semibold text-amber-400">
                                    {{ formatMoney(item.line_total, item.currency) }}
                                </td>

                            </tr>

                        </template>

                    </tbody>

                </table>

            </div>

            <div v-else class="px-6 py-10 text-center text-sm text-neutral-500">
                No purchased competition items found.
            </div>

        </section>

        <!-- Payment History -->
        <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">

            <div class="flex flex-col justify-between gap-3 border-b border-neutral-800 p-5 sm:flex-row sm:items-center sm:px-6">

                <div>
                    <h3 class="text-base font-bold text-white">
                        Payment History
                    </h3>

                    <p class="mt-1 text-xs text-neutral-500">
                        Payment transactions associated with customer orders
                    </p>
                </div>

                <span class="w-fit rounded-lg border border-neutral-700 bg-neutral-900 px-3 py-1.5 text-xs font-semibold text-neutral-300">
                    {{ payments.length }} Payments
                </span>

            </div>

            <div v-if="payments.length" class="overflow-x-auto">

                <table class="w-full min-w-[850px] text-left text-sm">

                    <thead class="border-b border-neutral-800 bg-neutral-950/60 text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                        <tr>
                            <th class="px-6 py-4">Payment Reference</th>
                            <th class="px-6 py-4">Order</th>
                            <th class="px-6 py-4">Provider</th>
                            <th class="px-6 py-4">Amount</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Paid At</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-neutral-800/80">

                        <tr
                            v-for="payment in payments"
                            :key="payment.encrypted_id"
                            class="transition hover:bg-white/[0.025]"
                        >

                            <td class="px-6 py-4">
                                <span class="text-xs font-medium text-neutral-400">
                                    Payment Transaction
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <Link
                                    :href="route('admin.orders.show', payment.order_encrypted_id)"
                                    class="text-xs font-semibold text-amber-400 hover:text-amber-300"
                                >
                                    {{ payment.order_number }}
                                </Link>
                            </td>

                            <td class="px-6 py-4">
                                <span class="text-xs font-semibold uppercase text-white">
                                    {{ payment.provider || '—' }}
                                </span>
                            </td>

                            <td class="px-6 py-4 font-semibold text-white">
                                {{ formatMoney(payment.amount, payment.currency) }}
                            </td>

                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex rounded-lg border px-2.5 py-1 text-[10px] font-bold"
                                    :class="statusClass(payment.status)"
                                >
                                    {{ statusLabel(payment.status) }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-xs text-neutral-400">
                                {{ payment.paid_at ? formatDate(payment.paid_at) : '—' }}
                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

            <div v-else class="px-6 py-12 text-center">
                <p class="text-sm font-semibold text-neutral-300">
                    No payment records found
                </p>

                <p class="mt-1 text-xs text-neutral-500">
                    Customer payment transactions will appear here.
                </p>
            </div>

        </section>

        <!-- Customer Tickets -->
        <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">

            <div class="flex flex-col justify-between gap-3 border-b border-neutral-800 p-5 sm:flex-row sm:items-center sm:px-6">

                <div>
                    <h3 class="text-base font-bold text-white">
                        Customer Entries & Tickets
                    </h3>

                    <p class="mt-1 text-xs text-neutral-500">
                        Tickets assigned to this customer's orders
                    </p>
                </div>

                <span class="w-fit rounded-lg border border-neutral-700 bg-neutral-900 px-3 py-1.5 text-xs font-semibold text-neutral-300">
                    {{ tickets.length }} Tickets
                </span>

            </div>

            <div v-if="tickets.length" class="overflow-x-auto">

                <table class="w-full min-w-[850px] text-left text-sm">

                    <thead class="border-b border-neutral-800 bg-neutral-950/60 text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                        <tr>
                            <th class="px-6 py-4">Ticket Number</th>
                            <th class="px-6 py-4">Competition</th>
                            <th class="px-6 py-4">Order</th>
                            <th class="px-6 py-4">Ticket Status</th>
                            <th class="px-6 py-4">Assigned At</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-neutral-800/80">

                        <tr
                            v-for="ticket in tickets"
                            :key="ticket.encrypted_id"
                            class="transition hover:bg-white/[0.025]"
                        >

                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-lg border border-amber-500/20 bg-amber-500/10 px-3 py-1.5 font-mono text-xs font-bold text-amber-400">
                                    {{ ticket.ticket_number }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <Link
                                    v-if="ticket.competition_encrypted_id"
                                    :href="competitionUrl(ticket.competition_encrypted_id)"
                                    class="font-semibold text-amber-400 transition hover:text-amber-300"
                                >
                                    {{ ticket.competition_title }}
                                </Link>

                                <p
                                    v-else
                                    class="font-semibold text-white"
                                >
                                    {{ ticket.competition_title }}
                                </p>
                            </td>

                            <td class="px-6 py-4">
                                <Link
                                    v-if="ticket.order_encrypted_id"
                                    :href="route('admin.orders.show', ticket.order_encrypted_id)"
                                    class="text-xs font-semibold text-amber-400 hover:text-amber-300"
                                >
                                    {{ ticket.order_number || 'View Order' }}
                                </Link>

                                <span v-else class="text-xs text-neutral-500">
                                    —
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex rounded-lg border px-2.5 py-1 text-[10px] font-bold"
                                    :class="statusClass(ticket.status)"
                                >
                                    {{ statusLabel(ticket.status) }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-xs text-neutral-400">
                                {{ ticket.assigned_at ? formatDate(ticket.assigned_at) : '—' }}
                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

            <div v-else class="px-6 py-12 text-center">
                <p class="text-sm font-semibold text-neutral-300">
                    No tickets found
                </p>

                <p class="mt-1 text-xs text-neutral-500">
                    Purchased competition tickets will appear here.
                </p>
            </div>

        </section>

        <!-- Footer -->
        <div class="flex flex-col justify-between gap-3 border-t border-neutral-800 pt-5 sm:flex-row sm:items-center">

            <p class="text-xs text-neutral-500">
                Customer profile
            </p>

            <Link
                :href="route('admin.customers.index')"
                class="inline-flex items-center justify-center rounded-lg border border-neutral-700 bg-neutral-900 px-4 py-2.5 text-xs font-semibold text-neutral-300 transition hover:border-amber-500/30 hover:bg-amber-500/10 hover:text-amber-400"
            >
                Back to Customer List
            </Link>

        </div>

    </div>
</template>
