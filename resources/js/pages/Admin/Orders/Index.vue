
<script setup>
import { computed, ref, watch, onBeforeUnmount } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { formatDate } from '../../../helpers/date'

defineOptions({
    layout: AdminLayout,
})

const props = defineProps({
    orders: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            status: '',
            payment_status: '',
        }),
    },
})

/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

const search = ref(props.filters.search || '')
const status = ref(props.filters.status || '')
const paymentStatus = ref(props.filters.payment_status || '')

const isFiltering = ref(false)

let searchTimeout = null

const hasActiveFilters = computed(() => {
    return Boolean(
        search.value ||
        status.value ||
        paymentStatus.value
    )
})

function applyFilters() {
    isFiltering.value = true

    router.get(
        route('admin.orders.index'),
        {
            search: search.value || undefined,
            status: status.value || undefined,
            payment_status: paymentStatus.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onFinish: () => {
                isFiltering.value = false
            },
        }
    )
}

watch(search, () => {
    clearTimeout(searchTimeout)

    searchTimeout = setTimeout(() => {
        applyFilters()
    }, 400)
})

watch([status, paymentStatus], () => {
    clearTimeout(searchTimeout)
    applyFilters()
})

onBeforeUnmount(() => {
    clearTimeout(searchTimeout)
})

function clearFilters() {
    clearTimeout(searchTimeout)

    search.value = ''
    status.value = ''
    paymentStatus.value = ''

    applyFilters()
}

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

/*
|--------------------------------------------------------------------------
| Status Styling
|--------------------------------------------------------------------------
*/

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

function formatStatus(value) {
    if (!value) return 'Unknown'

    return value
        .replace(/_/g, ' ')
        .replace(/\b\w/g, char => char.toUpperCase())
}

/*
|--------------------------------------------------------------------------
| Navigation
|--------------------------------------------------------------------------
*/

function viewOrder(order) {
    if (!order?.encrypted_id) return

    router.visit(
        route('admin.orders.show', order.encrypted_id)
    )
}

function viewCustomer(order) {
    if (!order?.customer_encrypted_id) return

    router.visit(
        route('admin.customers.show', order.customer_encrypted_id)
    )
}

function goToPage(url) {
    if (!url) return

    router.visit(url, {
        preserveScroll: true,
        preserveState: true,
    })
}
</script>

<template>
    <Head title="Orders Management" />

    <div class="space-y-6 pb-10">

        <!-- Page Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium uppercase tracking-[0.2em] text-amber-500">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                    Management
                </div>

                <h1 class="mt-2 text-2xl font-bold tracking-tight text-white sm:text-3xl">
                    Orders
                </h1>

                <p class="mt-2 text-sm text-neutral-400">
                    Monitor customer orders, payments and transactions.
                </p>
            </div>

            <div class="flex items-center gap-2 self-start rounded-lg border border-neutral-800 bg-[#121212] px-3 py-2 text-xs text-neutral-400">
                <svg class="h-4 w-4 text-amber-500" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.7">
                    <path d="M8 2v4M16 2v4M3 10h18" stroke-linecap="round" />
                    <rect x="3" y="4" width="18" height="17" rx="2" />
                </svg>

                <span>Updated {{ formatDateTime(new Date()) }}</span>
            </div>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            <!-- Total Orders -->
            <div class="rounded-2xl border border-neutral-800 bg-[#121212] p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-neutral-400">
                            Total Orders
                        </p>

                        <h2 class="mt-3 text-3xl font-bold tracking-tight text-white">
                            {{ Number(stats.total_orders || 0).toLocaleString() }}
                        </h2>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-500/10 text-amber-400">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.7">
                            <path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"
                                stroke-linecap="round" />
                        </svg>
                    </div>
                </div>

                <p class="mt-3 text-xs text-neutral-500">
                    All customer orders
                </p>
            </div>

            <!-- Paid Orders -->
            <div class="rounded-2xl border border-neutral-800 bg-[#121212] p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-neutral-400">
                            Paid Orders
                        </p>

                        <h2 class="mt-3 text-3xl font-bold tracking-tight text-emerald-400">
                            {{ Number(stats.paid_orders || 0).toLocaleString() }}
                        </h2>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.7">
                            <path d="m5 12 4 4L19 6" stroke-linecap="round" stroke-linejoin="round" />
                            <circle cx="12" cy="12" r="10" />
                        </svg>
                    </div>
                </div>

                <p class="mt-3 text-xs text-neutral-500">
                    Successfully paid orders
                </p>
            </div>

            <!-- Pending Orders -->
            <div class="rounded-2xl border border-neutral-800 bg-[#121212] p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-neutral-400">
                            Pending Payments
                        </p>

                        <h2 class="mt-3 text-3xl font-bold tracking-tight text-amber-400">
                            {{ Number(stats.pending_orders || 0).toLocaleString() }}
                        </h2>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-500/10 text-amber-400">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.7">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 6v6l4 2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                </div>

                <p class="mt-3 text-xs text-neutral-500">
                    Awaiting payment confirmation
                </p>
            </div>

            <!-- Cancelled Orders -->
            <div class="rounded-2xl border border-neutral-800 bg-[#121212] p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-neutral-400">
                            Cancelled Orders
                        </p>

                        <h2 class="mt-3 text-3xl font-bold tracking-tight text-red-400">
                            {{ Number(stats.cancelled_orders || 0).toLocaleString() }}
                        </h2>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-red-500/10 text-red-400">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.7">
                            <circle cx="12" cy="12" r="10" />
                            <path d="m15 9-6 6m0-6 6 6" stroke-linecap="round" />
                        </svg>
                    </div>
                </div>

                <p class="mt-3 text-xs text-neutral-500">
                    Orders marked as cancelled
                </p>
            </div>
        </div>

        <!-- Orders Table Container -->
        <div class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">

            <!-- Table Header -->
            <div class="border-b border-neutral-800 p-5">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">

                    <div>
                        <h2 class="text-lg font-semibold text-white">
                            All Orders
                        </h2>

                        <p class="mt-1 text-sm text-neutral-500">
                            Review customer purchases and payment status.
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">

                        <!-- Search -->
                        <div class="relative sm:w-64">
                            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-500"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                <circle cx="11" cy="11" r="7" />
                                <path d="m16 16 4 4" stroke-linecap="round" />
                            </svg>

                            <input
                                v-model="search"
                                type="search"
                                placeholder="Search orders or customers..."
                                class="h-10 w-full rounded-lg border border-neutral-800 bg-neutral-950 pl-9 pr-3 text-sm text-white placeholder-neutral-500 outline-none transition focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/20"
                            />
                        </div>

                        <!-- Order Status -->
                        <select
                            v-model="status"
                            class="h-10 rounded-lg border border-neutral-800 bg-neutral-950 px-3 text-sm text-neutral-300 outline-none transition focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/20"
                        >
                            <option value="">All Order Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="failed">Failed</option>
                        </select>

                        <!-- Payment Status -->
                        <select
                            v-model="paymentStatus"
                            class="h-10 rounded-lg border border-neutral-800 bg-neutral-950 px-3 text-sm text-neutral-300 outline-none transition focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/20"
                        >
                            <option value="">All Payment Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="failed">Failed</option>
                            <option value="refunded">Refunded</option>
                            <option value="partially_refunded">Partially Refunded</option>
                        </select>

                        <!-- Clear Filters -->
                        <button
                            v-if="hasActiveFilters"
                            type="button"
                            @click="clearFilters"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-neutral-700 bg-neutral-900 px-3 text-sm font-medium text-neutral-300 transition hover:border-amber-500/40 hover:bg-amber-500/10 hover:text-amber-400"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.7">
                                <path d="m18 6-12 12M6 6l12 12" stroke-linecap="round" />
                            </svg>

                            Clear
                        </button>
                    </div>
                </div>

                <div v-if="isFiltering" class="mt-3 flex items-center gap-2 text-xs text-amber-400">
                    <span class="h-3 w-3 animate-spin rounded-full border-2 border-amber-400/30 border-t-amber-400"></span>
                    Updating results...
                </div>
            </div>

            <!-- Orders Table -->
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1050px] text-left">

                    <thead class="border-b border-neutral-800 bg-neutral-950/60">
                        <tr>
                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-neutral-500">
                                Order
                            </th>

                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-neutral-500">
                                Customer
                            </th>

                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-neutral-500">
                                Items
                            </th>

                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-neutral-500">
                                Total
                            </th>

                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-neutral-500">
                                Order Status
                            </th>

                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-neutral-500">
                                Payment
                            </th>

                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-neutral-500">
                                Date
                            </th>

                            <th class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wider text-neutral-500">
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-neutral-800/80">

                        <tr
                            v-for="order in orders.data"
                            :key="order.encrypted_id"
                            @click="viewOrder(order)"
                            class="group cursor-pointer transition hover:bg-white/[0.025]"
                        >
                            <!-- Order Number -->
                            <td class="px-5 py-4">
                                <div class="font-semibold text-amber-400 transition group-hover:text-amber-300">
                                    {{ order.order_number }}
                                </div>

                                <div class="mt-1 text-xs text-neutral-500">
                                    Customer order
                                </div>
                            </td>

                            <!-- Customer -->
                            <td class="px-5 py-4">
                                <button
                                    type="button"
                                    @click.stop="viewCustomer(order)"
                                    class="text-left"
                                    :disabled="!order.customer_encrypted_id"
                                >
                                    <div class="font-medium text-neutral-200 transition hover:text-amber-400">
                                        {{ order.customer_name }}
                                    </div>

                                    <div class="mt-1 text-xs text-neutral-500">
                                        {{ order.customer_email }}
                                    </div>
                                </button>
                            </td>

                            <!-- Items -->
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center rounded-md border border-neutral-700 bg-neutral-900 px-2.5 py-1 text-xs font-medium text-neutral-300">
                                    {{ Number(order.items_count || 0).toLocaleString() }}
                                    {{ Number(order.items_count || 0) === 1 ? 'item' : 'items' }}
                                </span>
                            </td>

                            <!-- Total -->
                            <td class="px-5 py-4">
                                <span class="font-semibold tabular-nums text-white">
                                    {{ formatCurrency(order.total, order.currency) }}
                                </span>

                                <div class="mt-1 text-xs text-neutral-500">
                                    {{ order.currency }}
                                </div>
                            </td>

                            <!-- Order Status -->
                            <td class="px-5 py-4">
                                <span
                                    :class="getOrderStatusClass(order.status)"
                                    class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset"
                                >
                                    {{ formatStatus(order.status) }}
                                </span>
                            </td>

                            <!-- Payment Status -->
                            <td class="px-5 py-4">
                                <span
                                    :class="getPaymentStatusClass(order.payment_status)"
                                    class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset"
                                >
                                    {{ formatStatus(order.payment_status) }}
                                </span>
                            </td>

                            <!-- Date -->
                            <td class="px-5 py-4">
                                <div class="text-sm text-neutral-300">
                                    {{ formatDate(order.created_at) }}
                                </div>

                                <div class="mt-1 text-xs text-neutral-500">
                                    {{ order.paid_at ? 'Paid ' + formatDate(order.paid_at) : 'Not paid' }}
                                </div>
                            </td>

                            <!-- Action -->
                            <td class="px-5 py-4 text-right">
                                <button
                                    type="button"
                                    @click.stop="viewOrder(order)"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-neutral-700 bg-neutral-900 text-neutral-400 transition hover:border-amber-500/40 hover:bg-amber-500/10 hover:text-amber-400"
                                    title="View order details"
                                    :aria-label="`View order ${order.order_number}`"
                                >
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.7">
                                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"
                                            stroke-linejoin="round" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                </button>
                            </td>
                        </tr>

                        <!-- Empty State -->
                        <tr v-if="!orders.data || orders.data.length === 0">
                            <td colspan="8" class="px-5 py-20 text-center">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-neutral-800 bg-neutral-900 text-neutral-500">
                                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.5">
                                        <path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"
                                            stroke-linecap="round" />
                                    </svg>
                                </div>

                                <h3 class="mt-4 text-base font-semibold text-white">
                                    No orders found
                                </h3>

                                <p class="mx-auto mt-2 max-w-sm text-sm text-neutral-500">
                                    {{ hasActiveFilters
                                        ? 'No orders match your current search or filters. Try adjusting your criteria.'
                                        : 'Customer orders will appear here once purchases are made.' }}
                                </p>

                                <button
                                    v-if="hasActiveFilters"
                                    type="button"
                                    @click="clearFilters"
                                    class="mt-5 text-sm font-medium text-amber-400 transition hover:text-amber-300"
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
                v-if="orders.data && orders.data.length"
                class="flex flex-col gap-4 border-t border-neutral-800 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <p class="text-sm text-neutral-500">
                    Showing
                    <span class="font-medium text-neutral-300">{{ orders.from || 0 }}</span>
                    to
                    <span class="font-medium text-neutral-300">{{ orders.to || 0 }}</span>
                    of
                    <span class="font-medium text-neutral-300">{{ orders.total || 0 }}</span>
                    orders
                </p>

                <div class="flex flex-wrap items-center gap-1">
                    <button
                        v-for="(link, index) in orders.links"
                        :key="index"
                        type="button"
                        :disabled="!link.url || link.active"
                        @click="goToPage(link.url)"
                        v-html="link.label"
                        :class="[
                            'flex h-9 min-w-9 items-center justify-center rounded-lg border px-3 text-sm transition',
                            link.active
                                ? 'border-amber-500/30 bg-amber-500/10 font-semibold text-amber-400'
                                : link.url
                                    ? 'border-neutral-700 bg-neutral-900 text-neutral-400 hover:border-amber-500/40 hover:bg-amber-500/10 hover:text-amber-400'
                                    : 'cursor-not-allowed border-neutral-800 text-neutral-600 opacity-50'
                        ]"
                    ></button>
                </div>
            </div>

        </div>
    </div>
</template>
