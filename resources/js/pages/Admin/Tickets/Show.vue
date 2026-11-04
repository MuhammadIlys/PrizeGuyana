
<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import { formatDate } from '../../../helpers/date';
import { formatMoney } from '../../../helpers/money';

defineOptions({
    layout: AdminLayout,
});

const props = defineProps({
    ticket: {
        type: Object,
        required: true,
    },
});

function statusClass(status) {
    const classes = {
        available: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
        assigned: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        unavailable: 'bg-gray-500/10 text-gray-400 border-gray-500/20',
    };

    return classes[status] || 'bg-gray-500/10 text-gray-400 border-gray-500/20';
}

function paymentStatusClass(status) {
    const classes = {
        paid: 'bg-emerald-500/10 text-emerald-400',
        pending: 'bg-amber-500/10 text-amber-400',
        failed: 'bg-red-500/10 text-red-400',
        refunded: 'bg-purple-500/10 text-purple-400',
    };

    return classes[status] || 'bg-gray-500/10 text-gray-400';
}

function displayDate(date) {
    return date ? formatDate(date) : '—';
}

function displayMoney(amount, currency) {
    if (amount === null || amount === undefined || !currency) {
        return '—';
    }

    return formatMoney(amount, currency);
}

function copyValue(value) {
    if (value === null || value === undefined || value === '') return;

    navigator.clipboard?.writeText(String(value));
}
</script>

<template>
    <Head :title="`Ticket #${ticket.ticket_number}`" />

    <div class="space-y-6 text-gray-100">

        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="mb-2 flex items-center gap-2 text-sm text-gray-500">
                    <Link
                        :href="route('admin.tickets.index')"
                        class="transition hover:text-amber-400"
                    >
                        Tickets
                    </Link>
                    <span>/</span>
                    <span class="text-gray-300">Ticket Details</span>
                </div>

                <h1 class="text-2xl font-bold text-white">
                    Ticket #{{ ticket.ticket_number }}
                </h1>

                <p class="mt-1 text-sm text-gray-400">
                    View ticket allocation and purchase information.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <span
                    class="inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-semibold capitalize"
                    :class="statusClass(ticket.status)"
                >
                    {{ ticket.status }}
                </span>

                <Link
                    :href="route('admin.tickets.index')"
                    class="rounded-lg border border-gray-700 px-4 py-2.5 text-sm font-medium text-gray-300 transition hover:border-gray-500 hover:bg-gray-800 hover:text-white"
                >
                    Back to Tickets
                </Link>
            </div>
        </div>

        <!-- Ticket Overview -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            <div class="rounded-xl border border-gray-800 bg-gray-900 p-5">
                <p class="text-sm text-gray-400">Ticket Number</p>
                <div class="mt-3 flex items-center justify-between gap-2">
                    <p class="text-2xl font-bold text-amber-400">
                        #{{ ticket.ticket_number }}
                    </p>
                    <button
                        type="button"
                        @click="copyValue(ticket.ticket_number)"
                        title="Copy ticket number"
                        class="rounded-lg p-2 text-gray-500 transition hover:bg-gray-800 hover:text-white"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M8 8V5a2 2 0 012-2h9a2 2 0 012 2v9a2 2 0 01-2 2h-3M5 8h9a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2v-9a2 2 0 012-2z" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="rounded-xl border border-gray-800 bg-gray-900 p-5">
                <p class="text-sm text-gray-400">Ticket Status</p>
                <div class="mt-3">
                    <span
                        class="inline-flex rounded-full border px-3 py-1.5 text-sm font-semibold capitalize"
                        :class="statusClass(ticket.status)"
                    >
                        {{ ticket.status }}
                    </span>
                </div>
            </div>

            <div class="rounded-xl border border-gray-800 bg-gray-900 p-5">
                <p class="text-sm text-gray-400">Customer Assignment</p>
                <p class="mt-3 text-xl font-bold text-white">
                    {{ ticket.user_id ? 'Assigned' : 'Not Assigned' }}
                </p>
                <p class="mt-1 text-xs text-gray-500">
                    {{ ticket.user_id ? 'Linked to a customer account' : 'No customer linked' }}
                </p>
            </div>

            <div class="rounded-xl border border-gray-800 bg-gray-900 p-5">
                <p class="text-sm text-gray-400">Order Reference</p>
                <p class="mt-3 text-xl font-bold text-white">
                    {{ ticket.order_number || '—' }}
                </p>
                <p class="mt-1 text-xs text-gray-500">
                    {{ ticket.order_id ? `Order ID: ${ticket.order_id}` : 'No order linked' }}
                </p>
            </div>

        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

            <!-- Left Column -->
            <div class="space-y-6 xl:col-span-2">

                <!-- Competition Information -->
                <div class="overflow-hidden rounded-xl border border-gray-800 bg-gray-900">
                    <div class="border-b border-gray-800 px-5 py-4">
                        <h2 class="font-semibold text-white">Competition Information</h2>
                    </div>

                    <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                                Competition
                            </p>
                            <p class="mt-2 text-lg font-semibold text-white">
                                {{ ticket.competition_title || 'Competition not found' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                                Competition ID
                            </p>
                            <p class="mt-2 text-sm text-gray-200">
                                {{ ticket.competition_id }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                                Competition Slug
                            </p>
                            <p class="mt-2 break-all text-sm text-gray-200">
                                {{ ticket.competition_slug || '—' }}
                            </p>
                        </div>

                        <div v-if="ticket.competition_slug" class="sm:col-span-2">
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                                Competition URL Slug
                            </p>
                            <p class="mt-2 text-sm text-gray-300">
                                {{ ticket.competition_slug }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="overflow-hidden rounded-xl border border-gray-800 bg-gray-900">
                    <div class="flex items-center justify-between border-b border-gray-800 px-5 py-4">
                        <h2 class="font-semibold text-white">Customer Information</h2>

                        <Link
                            v-if="ticket.user_id"
                            :href="route('admin.customers.show', ticket.user_id)"
                            class="text-xs font-medium text-amber-400 transition hover:text-amber-300"
                        >
                            View Customer
                        </Link>
                    </div>

                    <div v-if="ticket.user_id" class="p-5">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-amber-500/10 text-lg font-bold text-amber-400">
                                {{ (ticket.customer_name || ticket.customer_email || 'C').charAt(0).toUpperCase() }}
                            </div>

                            <div class="min-w-0">
                                <p class="font-semibold text-white">
                                    {{ ticket.customer_name || 'Customer' }}
                                </p>
                                <p class="mt-1 break-all text-sm text-gray-400">
                                    {{ ticket.customer_email || 'No email available' }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 border-t border-gray-800 pt-4">
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                                Customer ID
                            </p>
                            <p class="mt-2 text-sm text-gray-200">
                                {{ ticket.user_id }}
                            </p>
                        </div>
                    </div>

                    <div v-else class="p-5">
                        <p class="text-sm text-gray-400">
                            This ticket is not currently linked to a customer.
                        </p>
                    </div>
                </div>

                <!-- Order Information -->
                <div class="overflow-hidden rounded-xl border border-gray-800 bg-gray-900">
                    <div class="flex items-center justify-between border-b border-gray-800 px-5 py-4">
                        <h2 class="font-semibold text-white">Order Information</h2>

                        <Link
                            v-if="ticket.order_id"
                            :href="route('admin.orders.show', ticket.order_id)"
                            class="text-xs font-medium text-amber-400 transition hover:text-amber-300"
                        >
                            View Order
                        </Link>
                    </div>

                    <div v-if="ticket.order_id" class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                                Order Number
                            </p>
                            <p class="mt-2 font-semibold text-white">
                                {{ ticket.order_number || '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                                Order ID
                            </p>
                            <p class="mt-2 text-sm text-gray-200">
                                {{ ticket.order_id }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                                Order Status
                            </p>
                            <p class="mt-2 text-sm capitalize text-gray-200">
                                {{ ticket.order_status || '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                                Payment Status
                            </p>
                            <div class="mt-2">
                                <span
                                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold capitalize"
                                    :class="paymentStatusClass(ticket.payment_status)"
                                >
                                    {{ ticket.payment_status || 'Unknown' }}
                                </span>
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                                Order Total
                            </p>
                            <p class="mt-2 text-xl font-bold text-white">
                                {{ displayMoney(ticket.order_total, ticket.order_currency) }}
                            </p>
                        </div>
                    </div>

                    <div v-else class="p-5">
                        <p class="text-sm text-gray-400">
                            No order is currently linked to this ticket.
                        </p>
                    </div>
                </div>

            </div>

            <!-- Right Column -->
            <div class="space-y-6">

                <!-- Assignment Details -->
                <div class="overflow-hidden rounded-xl border border-gray-800 bg-gray-900">
                    <div class="border-b border-gray-800 px-5 py-4">
                        <h2 class="font-semibold text-white">Assignment Details</h2>
                    </div>

                    <div class="space-y-5 p-5">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                                Assignment Status
                            </p>
                            <div class="mt-2">
                                <span
                                    class="inline-flex rounded-full border px-3 py-1.5 text-xs font-semibold capitalize"
                                    :class="statusClass(ticket.status)"
                                >
                                    {{ ticket.status }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                                Assigned At
                            </p>
                            <p class="mt-2 text-sm text-gray-200">
                                {{ displayDate(ticket.assigned_at) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                                Customer ID
                            </p>
                            <p class="mt-2 text-sm text-gray-200">
                                {{ ticket.user_id || '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                                Order ID
                            </p>
                            <p class="mt-2 text-sm text-gray-200">
                                {{ ticket.order_id || '—' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Record Information -->
                <div class="overflow-hidden rounded-xl border border-gray-800 bg-gray-900">
                    <div class="border-b border-gray-800 px-5 py-4">
                        <h2 class="font-semibold text-white">Record Information</h2>
                    </div>

                    <div class="space-y-5 p-5">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                                Ticket Record ID
                            </p>
                            <p class="mt-2 text-sm text-gray-200">
                                {{ ticket.id }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                                Created At
                            </p>
                            <p class="mt-2 text-sm text-gray-200">
                                {{ displayDate(ticket.created_at) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                                Last Updated
                            </p>
                            <p class="mt-2 text-sm text-gray-200">
                                {{ displayDate(ticket.updated_at) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Notice -->
                <div class="rounded-xl border border-amber-500/20 bg-amber-500/5 p-4">
                    <div class="flex gap-3">
                        <svg class="h-5 w-5 shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M12 9v3m0 4h.01M10.3 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.7 3.86a2 2 0 00-3.4 0z" />
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-amber-300">
                                Read-only ticket record
                            </p>
                            <p class="mt-1 text-xs leading-5 text-gray-400">
                                Ticket allocation and status changes are not available from this page.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
