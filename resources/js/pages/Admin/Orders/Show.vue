
<script setup>
import { computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineOptions({
    layout: AdminLayout,
})

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
})

const customer = computed(() => props.order.customer || {})
const items = computed(() => props.order.items || [])
const payments = computed(() => props.order.payments || [])
const tickets = computed(() => props.order.tickets || [])

const totalQuantity = computed(() => {
    return items.value.reduce(
        (total, item) => total + Number(item.quantity || 0),
        0
    )
})

/*
|--------------------------------------------------------------------------
| Formatting
|--------------------------------------------------------------------------
*/

function formatCurrency(amount, currency = 'USD') {
    const value = Number(amount || 0)

    try {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency || 'USD',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(value)
    } catch {
        return `${currency || 'USD'} ${value.toFixed(2)}`
    }
}

function formatDateTime(date) {
    if (!date) return '—'

    const parsedDate = new Date(date)

    if (Number.isNaN(parsedDate.getTime())) return '—'

    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    }).format(parsedDate)
}

function formatStatus(value) {
    if (!value) return 'Unknown'

    return value
        .replace(/_/g, ' ')
        .replace(/\b\w/g, char => char.toUpperCase())
}

/*
|--------------------------------------------------------------------------
| Status Styling
|--------------------------------------------------------------------------
*/

function getOrderStatusClass(value) {
    const styles = {
        completed: 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/20',
        pending: 'bg-amber-500/10 text-amber-400 ring-amber-500/20',
        processing: 'bg-blue-500/10 text-blue-400 ring-blue-500/20',
        cancelled: 'bg-red-500/10 text-red-400 ring-red-500/20',
        failed: 'bg-red-500/10 text-red-400 ring-red-500/20',
    }

    return styles[value] || 'bg-neutral-800 text-neutral-400 ring-neutral-700'
}

function getPaymentStatusClass(value) {
    const styles = {
        paid: 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/20',
        pending: 'bg-amber-500/10 text-amber-400 ring-amber-500/20',
        failed: 'bg-red-500/10 text-red-400 ring-red-500/20',
        refunded: 'bg-blue-500/10 text-blue-400 ring-blue-500/20',
        partially_refunded: 'bg-purple-500/10 text-purple-400 ring-purple-500/20',
    }

    return styles[value] || 'bg-neutral-800 text-neutral-400 ring-neutral-700'
}

function getTicketStatusClass(value) {
    const styles = {
        assigned: 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/20',
        available: 'bg-amber-500/10 text-amber-400 ring-amber-500/20',
        unavailable: 'bg-neutral-700 text-neutral-300 ring-neutral-600',
    }

    return styles[value] || 'bg-neutral-800 text-neutral-400 ring-neutral-700'
}

/*
|--------------------------------------------------------------------------
| Actions
|--------------------------------------------------------------------------
*/

async function copyToClipboard(value) {
    if (!value || !navigator.clipboard) return

    try {
        await navigator.clipboard.writeText(String(value))
    } catch (error) {
        console.error('Unable to copy value:', error)
    }
}

function viewCustomer() {
    if (!customer.value.encrypted_id) return

    router.visit(
        route('admin.customers.show', customer.value.encrypted_id)
    )
}

function viewCompetition(encryptedId) {
    if (!encryptedId) return

    router.visit(
        route('admin.competitions.show', encryptedId)
    )
}
</script>

<template>
    <Head :title="`Order ${order.order_number}`" />

    <div class="space-y-6 pb-10">

        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <Link
                    :href="route('admin.orders.index')"
                    class="inline-flex items-center gap-2 text-sm text-neutral-400 transition hover:text-amber-400"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.8">
                        <path d="m15 18-6-6 6-6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>

                    Back to Orders
                </Link>

                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                        Order Details
                    </h1>

                    <span class="rounded-md border border-amber-500/20 bg-amber-500/10 px-2.5 py-1 text-xs font-semibold text-amber-400">
                        {{ order.order_number }}
                    </span>
                </div>

                <p class="mt-2 text-sm text-neutral-400">
                    Review order information, customer details and payment records.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span
                    :class="getOrderStatusClass(order.status)"
                    class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-semibold ring-1 ring-inset"
                >
                    {{ formatStatus(order.status) }}
                </span>

                <span
                    :class="getPaymentStatusClass(order.payment_status)"
                    class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-semibold ring-1 ring-inset"
                >
                    {{ formatStatus(order.payment_status) }}
                </span>
            </div>
        </div>

        <!-- Overview Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            <!-- Order Total -->
            <div class="rounded-2xl border border-neutral-800 bg-[#121212] p-5">
                <p class="text-sm font-medium text-neutral-400">
                    Order Total
                </p>

                <h2 class="mt-3 text-2xl font-bold tracking-tight text-white">
                    {{ formatCurrency(order.total, order.currency) }}
                </h2>

                <p class="mt-2 text-xs text-neutral-500">
                    {{ order.currency }}
                </p>
            </div>

            <!-- Purchased Items -->
            <div class="rounded-2xl border border-neutral-800 bg-[#121212] p-5">
                <p class="text-sm font-medium text-neutral-400">
                    Purchased Items
                </p>

                <h2 class="mt-3 text-2xl font-bold tracking-tight text-white">
                    {{ items.length }}
                </h2>

                <p class="mt-2 text-xs text-neutral-500">
                    {{ totalQuantity }} total entries purchased
                </p>
            </div>

            <!-- Generated Tickets -->
            <div class="rounded-2xl border border-neutral-800 bg-[#121212] p-5">
                <p class="text-sm font-medium text-neutral-400">
                    Generated Tickets
                </p>

                <h2 class="mt-3 text-2xl font-bold tracking-tight text-emerald-400">
                    {{ tickets.length }}
                </h2>

                <p class="mt-2 text-xs text-neutral-500">
                    Tickets associated with this order
                </p>
            </div>

            <!-- Payment Records -->
            <div class="rounded-2xl border border-neutral-800 bg-[#121212] p-5">
                <p class="text-sm font-medium text-neutral-400">
                    Payment Records
                </p>

                <h2 class="mt-3 text-2xl font-bold tracking-tight text-white">
                    {{ payments.length }}
                </h2>

                <p class="mt-2 text-xs text-neutral-500">
                    Recorded payment attempts
                </p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

            <!-- Left Column -->
            <div class="space-y-6 xl:col-span-2">

                <!-- Purchased Items -->
                <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">

                    <div class="flex items-center justify-between border-b border-neutral-800 px-5 py-4">
                        <div>
                            <h2 class="font-semibold text-white">
                                Purchased Competitions
                            </h2>

                            <p class="mt-1 text-xs text-neutral-500">
                                Competition entries included in this order.
                            </p>
                        </div>

                        <span class="rounded-lg border border-neutral-700 bg-neutral-900 px-2.5 py-1 text-xs text-neutral-400">
                            {{ items.length }} {{ items.length === 1 ? 'item' : 'items' }}
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[550px] text-left">
                            <thead class="border-b border-neutral-800 bg-neutral-950/60">
                                <tr>
                                    <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wider text-neutral-500">
                                        Competition
                                    </th>

                                    <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wider text-neutral-500">
                                        Quantity
                                    </th>

                                    <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wider text-neutral-500">
                                        Unit Price
                                    </th>

                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-neutral-500">
                                        Subtotal
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-neutral-800/80">
                                <tr
                                    v-for="(item, index) in items"
                                    :key="`${item.competition_encrypted_id || item.competition_title}-${index}`"
                                    class="transition hover:bg-white/[0.025]"
                                >
                                    <td class="px-5 py-4">
                                        <button
                                            v-if="item.competition_encrypted_id"
                                            type="button"
                                            @click="viewCompetition(item.competition_encrypted_id)"
                                            class="text-left font-medium text-neutral-200 transition hover:text-amber-400"
                                        >
                                            {{ item.competition_title }}
                                        </button>

                                        <p v-else class="font-medium text-neutral-200">
                                            {{ item.competition_title }}
                                        </p>
                                    </td>

                                    <td class="px-5 py-4 text-sm text-neutral-300">
                                        {{ Number(item.quantity || 0).toLocaleString() }}
                                    </td>

                                    <td class="px-5 py-4 text-sm text-neutral-300">
                                        {{ formatCurrency(item.unit_price, item.currency) }}
                                    </td>

                                    <td class="px-5 py-4 text-right text-sm font-semibold text-white">
                                        {{ formatCurrency(item.line_total, item.currency) }}
                                    </td>
                                </tr>

                                <tr v-if="items.length === 0">
                                    <td colspan="4" class="px-5 py-12 text-center text-sm text-neutral-500">
                                        No order items were found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Order Totals -->
                    <div class="border-t border-neutral-800 bg-neutral-950/40 p-5">
                        <div class="ml-auto max-w-xs space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-neutral-400">Subtotal</span>

                                <span class="font-medium text-neutral-200">
                                    {{ formatCurrency(order.subtotal, order.currency) }}
                                </span>
                            </div>

                            <div class="flex justify-between border-t border-neutral-800 pt-3">
                                <span class="font-semibold text-white">
                                    Order Total
                                </span>

                                <span class="text-lg font-bold text-amber-400">
                                    {{ formatCurrency(order.total, order.currency) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Tickets -->
                <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">

                    <div class="flex items-center justify-between border-b border-neutral-800 px-5 py-4">
                        <div>
                            <h2 class="font-semibold text-white">
                                Generated Tickets
                            </h2>

                            <p class="mt-1 text-xs text-neutral-500">
                                Ticket numbers associated with this order.
                            </p>
                        </div>

                        <span class="rounded-lg border border-neutral-700 bg-neutral-900 px-2.5 py-1 text-xs text-neutral-400">
                            {{ tickets.length }} tickets
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[550px] text-left">
                            <thead class="border-b border-neutral-800 bg-neutral-950/60">
                                <tr>
                                    <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wider text-neutral-500">
                                        Ticket Number
                                    </th>

                                    <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wider text-neutral-500">
                                        Competition
                                    </th>

                                    <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wider text-neutral-500">
                                        Status
                                    </th>

                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-neutral-500">
                                        Assigned Date
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-neutral-800/80">
                                <tr
                                    v-for="ticket in tickets"
                                    :key="ticket.encrypted_id"
                                    class="transition hover:bg-white/[0.025]"
                                >
                                    <td class="px-5 py-4">
                                        <span class="font-mono text-sm font-semibold text-amber-400">
                                            {{ ticket.ticket_number }}
                                        </span>

                                        <button
                                            type="button"
                                            @click="copyToClipboard(ticket.ticket_number)"
                                            class="ml-2 text-neutral-500 transition hover:text-white"
                                            title="Copy ticket number"
                                            aria-label="Copy ticket number"
                                        >
                                            <svg class="inline h-3.5 w-3.5" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="1.7">
                                                <rect x="8" y="8" width="13" height="13" rx="2" />
                                                <path d="M16 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h3" />
                                            </svg>
                                        </button>
                                    </td>

                                    <td class="px-5 py-4 text-sm text-neutral-300">
                                        {{ ticket.competition_title }}
                                    </td>

                                    <td class="px-5 py-4">
                                        <span
                                            :class="getTicketStatusClass(ticket.status)"
                                            class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset"
                                        >
                                            {{ formatStatus(ticket.status) }}
                                        </span>
                                    </td>

                                    <td class="px-5 py-4 text-right text-sm text-neutral-400">
                                        {{ formatDateTime(ticket.assigned_at) }}
                                    </td>
                                </tr>

                                <tr v-if="tickets.length === 0">
                                    <td colspan="4" class="px-5 py-12 text-center">
                                        <p class="text-sm text-neutral-400">
                                            No tickets are associated with this order.
                                        </p>

                                        <p class="mt-1 text-xs text-neutral-500">
                                            Tickets may not have been generated or assigned yet.
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Payment Transactions -->
                <!-- <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">

                    <div class="border-b border-neutral-800 px-5 py-4">
                        <h2 class="font-semibold text-white">
                            Payment Transactions
                        </h2>

                        <p class="mt-1 text-xs text-neutral-500">
                            Payment provider records associated with this order.
                        </p>
                    </div>

                    <div class="divide-y divide-neutral-800/80">

                        <div
                            v-for="payment in payments"
                            :key="payment.encrypted_id"
                            class="space-y-4 p-5"
                        >
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-semibold capitalize text-white">
                                            {{ payment.provider || 'Payment Provider' }}
                                        </span>

                                        <span
                                            :class="getPaymentStatusClass(payment.status)"
                                            class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset"
                                        >
                                            {{ formatStatus(payment.status) }}
                                        </span>
                                    </div>

                                    <p class="mt-2 text-xs text-neutral-500">
                                        Payment transaction
                                    </p>
                                </div>

                                <div class="sm:text-right">
                                    <p class="text-lg font-bold text-white">
                                        {{ formatCurrency(payment.amount, payment.currency) }}
                                    </p>

                                    <p class="mt-1 text-xs text-neutral-500">
                                        {{ formatDateTime(payment.created_at) }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

                                <div
                                    v-if="payment.checkout_session_id"
                                    class="rounded-lg border border-neutral-800 bg-neutral-950 p-3"
                                >
                                    <p class="text-xs text-neutral-500">
                                        Stripe Checkout Session
                                    </p>

                                    <p class="mt-1 break-all font-mono text-xs text-neutral-300">
                                        {{ payment.checkout_session_id }}
                                    </p>

                                    <button
                                        type="button"
                                        @click="copyToClipboard(payment.checkout_session_id)"
                                        class="mt-2 text-xs text-amber-400 transition hover:text-amber-300"
                                    >
                                        Copy session ID
                                    </button>
                                </div>

                                <div
                                    v-if="payment.payment_intent_id"
                                    class="rounded-lg border border-neutral-800 bg-neutral-950 p-3"
                                >
                                    <p class="text-xs text-neutral-500">
                                        Stripe Payment Intent
                                    </p>

                                    <p class="mt-1 break-all font-mono text-xs text-neutral-300">
                                        {{ payment.payment_intent_id }}
                                    </p>

                                    <button
                                        type="button"
                                        @click="copyToClipboard(payment.payment_intent_id)"
                                        class="mt-2 text-xs text-amber-400 transition hover:text-amber-300"
                                    >
                                        Copy payment intent
                                    </button>
                                </div>

                                <div
                                    v-if="payment.paid_at"
                                    class="rounded-lg border border-neutral-800 bg-neutral-950 p-3 sm:col-span-2"
                                >
                                    <p class="text-xs text-neutral-500">
                                        Payment Date
                                    </p>

                                    <p class="mt-1 text-sm text-neutral-200">
                                        {{ formatDateTime(payment.paid_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div v-if="payments.length === 0" class="px-5 py-12 text-center">
                            <p class="text-sm text-neutral-400">
                                No payment transactions found.
                            </p>

                            <p class="mt-1 text-xs text-neutral-500">
                                Payment records will appear here when available.
                            </p>
                        </div>
                    </div>
                </section> -->

            </div>

            <!-- Right Column -->
            <div class="space-y-6">

                <!-- Customer Information -->
                <section class="rounded-2xl border border-neutral-800 bg-[#121212]">

                    <div class="border-b border-neutral-800 px-5 py-4">
                        <h2 class="font-semibold text-white">
                            Customer Information
                        </h2>
                    </div>

                    <div class="p-5">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/10 text-lg font-bold text-amber-400">
                                {{ (customer.name || 'C').charAt(0).toUpperCase() }}
                            </div>

                            <div class="min-w-0">
                                <p class="truncate font-semibold text-white">
                                    {{ customer.name || 'Unknown Customer' }}
                                </p>

                                <p class="mt-1 text-xs text-neutral-500">
                                    Registered customer
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 space-y-4">

                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-neutral-500">
                                    Email Address
                                </p>

                                <p class="mt-1 break-all text-sm text-neutral-300">
                                    {{ customer.email || '—' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-neutral-500">
                                    Phone Number
                                </p>

                                <p class="mt-1 text-sm text-neutral-300">
                                    {{ customer.phone || 'Not provided' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-neutral-500">
                                    Country
                                </p>

                                <p class="mt-1 text-sm text-neutral-300">
                                    {{ customer.country || 'Not provided' }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 border-t border-neutral-800 pt-4">
                            <button
                                v-if="customer.encrypted_id"
                                type="button"
                                @click="viewCustomer"
                                class="flex w-full items-center justify-center gap-2 rounded-lg border border-neutral-700 bg-neutral-900 px-4 py-2.5 text-sm font-medium text-neutral-300 transition hover:border-amber-500/40 hover:bg-amber-500/10 hover:text-amber-400"
                            >
                                View Customer Profile

                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.7">
                                    <path d="M7 17 17 7M7 7h10v10"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </section>

                <!-- Order Information -->
                <section class="rounded-2xl border border-neutral-800 bg-[#121212]">

                    <div class="border-b border-neutral-800 px-5 py-4">
                        <h2 class="font-semibold text-white">
                            Order Information
                        </h2>
                    </div>

                    <div class="space-y-4 p-5">

                        <div>
                            <p class="text-xs text-neutral-500">
                                Order Number
                            </p>

                            <div class="mt-1 flex items-center justify-between gap-2">
                                <span class="break-all text-sm font-semibold text-amber-400">
                                    {{ order.order_number }}
                                </span>

                                <button
                                    type="button"
                                    @click="copyToClipboard(order.order_number)"
                                    class="shrink-0 text-neutral-500 transition hover:text-white"
                                    title="Copy order number"
                                    aria-label="Copy order number"
                                >
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.7">
                                        <rect x="8" y="8" width="13" height="13" rx="2" />
                                        <path d="M16 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h3" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="border-t border-neutral-800 pt-4">
                            <p class="text-xs text-neutral-500">
                                Order Created
                            </p>

                            <p class="mt-1 text-sm text-neutral-300">
                                {{ formatDateTime(order.created_at) }}
                            </p>
                        </div>

                        <div class="border-t border-neutral-800 pt-4">
                            <p class="text-xs text-neutral-500">
                                Last Updated
                            </p>

                            <p class="mt-1 text-sm text-neutral-300">
                                {{ formatDateTime(order.updated_at) }}
                            </p>
                        </div>

                        <div class="border-t border-neutral-800 pt-4">
                            <p class="text-xs text-neutral-500">
                                Payment Completed
                            </p>

                            <p class="mt-1 text-sm text-neutral-300">
                                {{ formatDateTime(order.paid_at) }}
                            </p>
                        </div>

                        <div class="border-t border-neutral-800 pt-4">
                            <p class="text-xs text-neutral-500">
                                Currency
                            </p>

                            <p class="mt-1 text-sm font-medium text-neutral-300">
                                {{ order.currency || 'USD' }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Payment Summary -->
                <section class="rounded-2xl border border-neutral-800 bg-[#121212]">

                    <div class="border-b border-neutral-800 px-5 py-4">
                        <h2 class="font-semibold text-white">
                            Payment Summary
                        </h2>
                    </div>

                    <div class="space-y-4 p-5">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-sm text-neutral-400">
                                Payment Status
                            </span>

                            <span
                                :class="getPaymentStatusClass(order.payment_status)"
                                class="rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset"
                            >
                                {{ formatStatus(order.payment_status) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="text-sm text-neutral-400">
                                Payment Attempts
                            </span>

                            <span class="text-sm font-medium text-white">
                                {{ payments.length }}
                            </span>
                        </div>

                        <div class="border-t border-neutral-800 pt-4">
                            <p class="text-xs text-neutral-500">
                                Order Amount
                            </p>

                            <p class="mt-1 text-2xl font-bold text-amber-400">
                                {{ formatCurrency(order.total, order.currency) }}
                            </p>
                        </div>
                    </div>
                </section>

            </div>
        </div>

    </div>
</template>
