<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import UserLayout from '@/Layouts/UserLayout.vue'

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
    items: {
        type: Array,
        default: () => [],
    },
    tickets: {
        type: Array,
        default: () => [],
    },
    transactions: {
        type: Array,
        default: () => [],
    },
})

const orderStatus = computed(() => String(props.order?.status ?? 'unknown').toLowerCase())
const paymentStatus = computed(() => String(props.order?.payment_status ?? 'unknown').toLowerCase())

const statusClass = (status) => {
    const value = String(status ?? '').toLowerCase()

    if (['paid', 'completed', 'success', 'successful', 'active', 'issued'].includes(value)) {
        return 'border-emerald-500/25 bg-emerald-500/10 text-emerald-300'
    }

    if (['pending', 'processing', 'reserved'].includes(value)) {
        return 'border-amber-500/25 bg-amber-500/10 text-amber-300'
    }

    if (['failed', 'cancelled', 'canceled', 'refunded', 'expired'].includes(value)) {
        return 'border-rose-500/25 bg-rose-500/10 text-rose-300'
    }

    return 'border-sky-500/25 bg-sky-500/10 text-sky-300'
}

const formatDate = (value) => {
    if (!value) return '—'

    const date = new Date(value)
    if (Number.isNaN(date.getTime())) return value

    return new Intl.DateTimeFormat(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date)
}

const money = (amount, currency = props.order?.currency) => {
    const value = Number(amount ?? 0)
    const code = String(currency ?? '').toUpperCase()

    if (!Number.isFinite(value)) return '—'

    try {
        return new Intl.NumberFormat(undefined, {
            style: 'currency',
            currency: code || 'USD',
        }).format(value)
    } catch {
        return `${code ? `${code} ` : ''}${value.toFixed(2)}`
    }
}

const itemName = (item) =>
    item?.competition_title ??
    item?.competition_name ??
    item?.title ??
    item?.name ??
    item?.description ??
    `Order item #${item?.id ?? ''}`

const itemQuantity = (item) => Number(item?.quantity ?? item?.qty ?? 1)

const itemTotal = (item) => {
    if (item?.total !== undefined && item?.total !== null) return item.total
    if (item?.line_total !== undefined && item?.line_total !== null) return item.line_total

    const unitPrice = Number(item?.unit_price ?? item?.price ?? 0)
    return unitPrice * itemQuantity(item)
}
</script>

<template>
    <UserLayout>
        <div class="min-h-screen bg-black px-4 py-8 text-amber-50 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-6xl space-y-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <Link
                            :href="route('user.orders.index')"
                            class="mb-3 inline-flex items-center gap-2 text-sm text-amber-100/50 transition hover:text-amber-300"
                        >
                            <span aria-hidden="true">←</span>
                            Back to Orders
                        </Link>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-amber-100/40">
                            Order details
                        </p>
                        <h1 class="mt-2 text-2xl font-bold text-amber-400 sm:text-3xl">
                            {{ order.order_number || `Order #${order.id}` }}
                        </h1>
                        <p class="mt-2 text-sm text-amber-100/50">
                            Placed {{ formatDate(order.created_at) }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <span
                            class="inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-semibold capitalize"
                            :class="statusClass(orderStatus)"
                        >
                            Order: {{ order.status || 'Unknown' }}
                        </span>
                        <span
                            class="inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-semibold capitalize"
                            :class="statusClass(paymentStatus)"
                        >
                            Payment: {{ order.payment_status || 'Unknown' }}
                        </span>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-2xl border border-amber-500/20 bg-amber-500/[0.04] p-5">
                        <p class="text-sm text-amber-100/50">Order total</p>
                        <p class="mt-2 text-2xl font-bold text-amber-300">
                            {{ money(order.total ?? order.subtotal, order.currency) }}
                        </p>
                    </div>
                    <div class="rounded-2xl border border-amber-500/20 bg-amber-500/[0.04] p-5">
                        <p class="text-sm text-amber-100/50">Items / entries</p>
                        <p class="mt-2 text-2xl font-bold text-amber-100">
                            {{ order.items_count ?? items.reduce((sum, item) => sum + itemQuantity(item), 0) }}
                        </p>
                    </div>
                    <div class="rounded-2xl border border-amber-500/20 bg-amber-500/[0.04] p-5">
                        <p class="text-sm text-amber-100/50">Payment status</p>
                        <p class="mt-2 text-lg font-semibold capitalize text-amber-100">
                            {{ order.payment_status || 'Unknown' }}
                        </p>
                    </div>
                    <div class="rounded-2xl border border-amber-500/20 bg-amber-500/[0.04] p-5">
                        <p class="text-sm text-amber-100/50">Paid at</p>
                        <p class="mt-2 text-sm font-semibold text-amber-100">
                            {{ formatDate(order.paid_at) }}
                        </p>
                    </div>
                </div>

                <section class="overflow-hidden rounded-2xl border border-amber-500/20 bg-black">
                    <div class="border-b border-amber-500/20 px-5 py-4 sm:px-6">
                        <h2 class="text-lg font-semibold text-amber-300">Purchased items</h2>
                        <p class="mt-1 text-sm text-amber-100/40">
                            Items associated with this order.
                        </p>
                    </div>

                    <div v-if="items.length" class="overflow-x-auto">
                        <table class="w-full min-w-[620px] text-left text-sm">
                            <thead class="border-b border-amber-500/20 bg-amber-500/5 text-xs uppercase text-amber-100/60">
                                <tr>
                                    <th class="px-5 py-4 font-semibold">Item</th>
                                    <th class="px-5 py-4 font-semibold">Quantity</th>
                                    <th class="px-5 py-4 font-semibold">Unit price</th>
                                    <th class="px-5 py-4 text-right font-semibold">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-amber-500/10">
                                <tr v-for="item in items" :key="item.id" class="transition hover:bg-amber-500/5">
                                    <td class="px-5 py-4">
                                        <p class="font-medium text-amber-100">{{ itemName(item) }}</p>
                                        <p v-if="item.competition_id" class="mt-1 text-xs text-amber-100/40">
                                            Competition #{{ item.competition_id }}
                                        </p>
                                    </td>
                                    <td class="px-5 py-4 text-amber-100/70">{{ itemQuantity(item) }}</td>
                                    <td class="px-5 py-4 text-amber-100/70">
                                        {{ money(item.unit_price ?? item.price ?? 0, item.currency || order.currency) }}
                                    </td>
                                    <td class="px-5 py-4 text-right font-semibold text-amber-200">
                                        {{ money(itemTotal(item), item.currency || order.currency) }}
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="border-t border-amber-500/20 bg-amber-500/[0.03]">
                                <tr v-if="order.subtotal !== undefined">
                                    <td colspan="3" class="px-5 py-3 text-right text-amber-100/50">Subtotal</td>
                                    <td class="px-5 py-3 text-right text-amber-100/80">
                                        {{ money(order.subtotal, order.currency) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-5 py-4 text-right font-semibold text-amber-100">Order total</td>
                                    <td class="px-5 py-4 text-right text-base font-bold text-amber-300">
                                        {{ money(order.total ?? order.subtotal, order.currency) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div v-else class="px-6 py-12 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full border border-amber-500/20 bg-amber-500/5 text-amber-300">
                            <span class="text-xl" aria-hidden="true">▤</span>
                        </div>
                        <h3 class="mt-4 font-semibold text-amber-100">No item details available</h3>
                        <p class="mx-auto mt-2 max-w-md text-sm text-amber-100/45">
                            The order exists, but no associated order items were returned. If this order represents competition entries, connect the relevant entry or ticket records in the controller.
                        </p>
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-amber-500/20 bg-black">
                    <div class="border-b border-amber-500/20 px-5 py-4 sm:px-6">
                        <h2 class="text-lg font-semibold text-amber-300">Tickets and entries</h2>
                        <p class="mt-1 text-sm text-amber-100/40">
                            Entry references linked to this purchase, when available.
                        </p>
                    </div>

                    <div v-if="tickets.length" class="divide-y divide-amber-500/10">
                        <div v-for="ticket in tickets" :key="ticket.id" class="flex flex-col gap-2 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-medium text-amber-100">
                                    {{ ticket.ticket_number || ticket.entry_number || ticket.reference || `Entry #${ticket.id}` }}
                                </p>
                                <p v-if="ticket.competition_title || ticket.competition_name" class="mt-1 text-sm text-amber-100/45">
                                    {{ ticket.competition_title || ticket.competition_name }}
                                </p>
                            </div>
                            <span
                                v-if="ticket.status"
                                class="inline-flex w-fit rounded-full border px-3 py-1 text-xs font-semibold capitalize"
                                :class="statusClass(ticket.status)"
                            >
                                {{ ticket.status }}
                            </span>
                        </div>
                    </div>
                    <div v-else class="px-5 py-6 text-sm text-amber-100/45">
                        No ticket or entry records were supplied for this order.
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-amber-500/20 bg-black">
                    <div class="border-b border-amber-500/20 px-5 py-4 sm:px-6">
                        <h2 class="text-lg font-semibold text-amber-300">Payment attempts</h2>
                        <p class="mt-1 text-sm text-amber-100/40">
                            Payment records associated with this order.
                        </p>
                    </div>

                    <div v-if="transactions.length" class="overflow-x-auto">
                        <table class="w-full min-w-[560px] text-left text-sm">
                            <thead class="border-b border-amber-500/20 bg-amber-500/5 text-xs uppercase text-amber-100/60">
                                <tr>
                                    <th class="px-5 py-4 font-semibold">Reference</th>
                                    <th class="px-5 py-4 font-semibold">Method</th>
                                    <th class="px-5 py-4 font-semibold">Date</th>
                                    <th class="px-5 py-4 font-semibold">Status</th>
                                    <th class="px-5 py-4 text-right font-semibold">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-amber-500/10">
                                <tr v-for="payment in transactions" :key="payment.id" class="hover:bg-amber-500/5">
                                    <td class="px-5 py-4">
                                        <Link
                                            :href="route('user.transactions.show', payment.id)"
                                            class="font-medium text-amber-400 hover:text-amber-300"
                                        >
                                            {{ payment.reference || `Payment #${payment.id}` }}
                                        </Link>
                                    </td>
                                    <td class="px-5 py-4 text-amber-100/70">{{ payment.provider || '—' }}</td>
                                    <td class="px-5 py-4 text-amber-100/60">{{ formatDate(payment.created_at) }}</td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full border px-2.5 py-1 text-xs font-semibold capitalize" :class="statusClass(payment.status)">
                                            {{ payment.status || 'Unknown' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-right font-semibold text-amber-200">
                                        {{ money(payment.amount, payment.currency) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p v-else class="px-5 py-6 text-sm text-amber-100/45">
                        No payment attempts were supplied for this order.
                    </p>
                </section>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <Link
                        :href="route('user.orders.index')"
                        class="inline-flex items-center justify-center rounded-xl border border-amber-500/20 px-5 py-3 text-sm font-semibold text-amber-300 transition hover:bg-amber-500/10"
                    >
                        Back to all orders
                    </Link>
                    <Link
                        :href="route('user.transactions.index')"
                        class="inline-flex items-center justify-center rounded-xl bg-amber-500 px-5 py-3 text-sm font-bold text-black transition hover:bg-amber-400"
                    >
                        View transactions
                    </Link>
                </div>
            </div>
        </div>
    </UserLayout>
</template>
