<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import UserLayout from '../../../Layouts/UserLayout.vue'

defineOptions({ layout: UserLayout })

const props = defineProps({
    transaction: { type: Object, required: true },
    order: { type: Object, default: null },
    items: { type: Array, default: () => [] },
    tickets: { type: Array, default: () => [] },
})

const status = computed(() =>
    props.transaction?.status
    ?? props.transaction?.payment_status
    ?? props.order?.payment_status
    ?? props.order?.status
    ?? 'unknown'
)

const formatLabel = (value, fallback = '—') => {
    if (value === null || value === undefined || value === '') return fallback
    return String(value).replaceAll('_', ' ').replaceAll('-', ' ')
        .replace(/\b\w/g, (letter) => letter.toUpperCase())
}

const formatDate = (value) => {
    if (!value) return '—'
    const date = new Date(value)
    if (Number.isNaN(date.getTime())) return '—'
    return new Intl.DateTimeFormat('en', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    }).format(date)
}

const formatMoney = (value, currency = 'USD') => {
    const amount = Number(value ?? 0)
    const safeAmount = Number.isFinite(amount) ? amount : 0
    const safeCurrency = typeof currency === 'string' && currency.trim()
        ? currency.trim().toUpperCase() : 'USD'
    try {
        return new Intl.NumberFormat('en', {
            style: 'currency', currency: safeCurrency,
            minimumFractionDigits: 2, maximumFractionDigits: 2,
        }).format(safeAmount)
    } catch {
        return `${safeCurrency} ${safeAmount.toFixed(2)}`
    }
}

const statusClasses = computed(() => {
    const value = String(status.value).toLowerCase()
    if (['paid', 'completed', 'successful', 'succeeded', 'success'].includes(value)) {
        return 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400'
    }
    if (['pending', 'processing', 'requires_action', 'requires_payment_method'].includes(value)) {
        return 'border-amber-500/20 bg-amber-500/10 text-amber-400'
    }
    if (['failed', 'cancelled', 'canceled', 'refunded', 'disputed'].includes(value)) {
        return 'border-red-500/20 bg-red-500/10 text-red-400'
    }
    return 'border-amber-500/20 bg-amber-500/5 text-amber-100/70'
})

const publicReference = computed(() =>
    props.transaction?.reference
    ?? props.transaction?.transaction_number
    ?? `Payment #${props.transaction?.id ?? '—'}`
)
const orderNumber = computed(() =>
    props.order?.order_number
    ?? props.transaction?.order_number
    ?? (props.order?.id || props.transaction?.order_id
        ? `Order #${props.order?.id ?? props.transaction?.order_id}` : '—')
)
const currency = computed(() => props.transaction?.currency ?? props.order?.currency ?? 'USD')
const amount = computed(() =>
    props.transaction?.amount ?? props.transaction?.total
    ?? props.order?.total ?? props.order?.subtotal ?? 0
)
const paymentMethod = computed(() =>
    props.transaction?.payment_method ?? props.transaction?.method
    ?? props.transaction?.provider ?? null
)
const paymentDate = computed(() =>
    props.transaction?.paid_at ?? props.transaction?.created_at
    ?? props.transaction?.updated_at ?? props.order?.paid_at ?? props.order?.created_at
)
const itemName = (item) =>
    item?.competition_name ?? item?.competition?.name ?? item?.name ?? item?.title ?? 'Competition entry'
const itemQuantity = (item) => item?.quantity ?? item?.entries_count ?? item?.ticket_quantity ?? 1
const itemPrice = (item) => item?.total ?? item?.line_total ?? item?.subtotal ?? item?.price ?? null
const ticketNumber = (ticket) =>
    ticket?.ticket_number ?? ticket?.number ?? ticket?.entry_number ?? ticket?.code ?? `Ticket #${ticket?.id ?? '—'}`
</script>

<template>
    <Head :title="`Transaction ${publicReference}`" />

    <div class="space-y-6 p-4 text-amber-50 sm:p-6">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <Link href="/user/transactions" class="mb-3 inline-flex items-center gap-2 text-sm text-amber-100/60 transition hover:text-amber-400">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Back to Transactions
                </Link>
                <h1 class="text-2xl font-bold text-amber-400">Transaction Details</h1>
                <p class="mt-1 text-sm text-amber-100/50">Review the payment and order information associated with this transaction.</p>
            </div>
            <span class="inline-flex w-fit items-center rounded-full border px-3 py-1.5 text-xs font-semibold" :class="statusClasses">
                {{ formatLabel(status, 'Unknown') }}
            </span>
        </header>

        <section class="overflow-hidden rounded-2xl border border-amber-500/20 bg-black">
            <div class="border-b border-amber-500/20 bg-amber-500/5 px-5 py-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-100/50">Payment summary</p>
                <p class="mt-2 break-all text-lg font-bold text-amber-100">{{ publicReference }}</p>
            </div>
            <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 lg:grid-cols-4">
                <div><p class="text-xs text-amber-100/40">Amount</p><p class="mt-2 text-xl font-bold text-amber-400">{{ formatMoney(amount, currency) }}</p></div>
                <div><p class="text-xs text-amber-100/40">Payment date</p><p class="mt-2 text-sm font-medium text-amber-100">{{ formatDate(paymentDate) }}</p></div>
                <div><p class="text-xs text-amber-100/40">Payment method</p><p class="mt-2 text-sm font-medium text-amber-100">{{ formatLabel(paymentMethod, 'Not available') }}</p></div>
                <div><p class="text-xs text-amber-100/40">Order number</p><p class="mt-2 break-all text-sm font-medium text-amber-100">{{ orderNumber }}</p></div>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-amber-500/20 bg-black">
            <div class="border-b border-amber-500/20 px-5 py-4">
                <h2 class="font-semibold text-amber-100">Order information</h2>
                <p class="mt-1 text-xs text-amber-100/40">Summary of the order associated with this payment.</p>
            </div>
            <dl class="grid grid-cols-1 gap-x-6 gap-y-5 p-5 sm:grid-cols-2 lg:grid-cols-3">
                <div><dt class="text-xs text-amber-100/40">Order number</dt><dd class="mt-1 break-all text-sm font-medium text-amber-100">{{ orderNumber }}</dd></div>
                <div><dt class="text-xs text-amber-100/40">Order date</dt><dd class="mt-1 text-sm font-medium text-amber-100">{{ formatDate(order?.created_at) }}</dd></div>
                <div><dt class="text-xs text-amber-100/40">Order status</dt><dd class="mt-1 text-sm font-medium text-amber-100">{{ formatLabel(order?.status, 'Not available') }}</dd></div>
                <div><dt class="text-xs text-amber-100/40">Payment status</dt><dd class="mt-1 text-sm font-medium text-amber-100">{{ formatLabel(transaction?.status ?? transaction?.payment_status ?? order?.payment_status, 'Unknown') }}</dd></div>
                <div><dt class="text-xs text-amber-100/40">Items in order</dt><dd class="mt-1 text-sm font-medium text-amber-100">{{ items.length }}</dd></div>
                <div><dt class="text-xs text-amber-100/40">Order total</dt><dd class="mt-1 text-sm font-medium text-amber-100">{{ formatMoney(order?.total ?? order?.subtotal ?? amount, order?.currency ?? currency) }}</dd></div>
            </dl>
        </section>

        <section class="overflow-hidden rounded-2xl border border-amber-500/20 bg-black">
            <div class="border-b border-amber-500/20 px-5 py-4">
                <h2 class="font-semibold text-amber-100">Order items</h2>
                <p class="mt-1 text-xs text-amber-100/40">Competition entries included in this order.</p>
            </div>
            <div v-if="items.length" class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-amber-500/20 bg-amber-500/5 text-xs uppercase text-amber-100/60">
                        <tr><th scope="col" class="px-5 py-4 font-semibold">Item</th><th scope="col" class="whitespace-nowrap px-5 py-4 font-semibold">Quantity</th><th scope="col" class="whitespace-nowrap px-5 py-4 font-semibold">Item total</th></tr>
                    </thead>
                    <tbody class="divide-y divide-amber-500/10">
                        <tr v-for="(item, index) in items" :key="item.id ?? index" class="transition hover:bg-amber-500/5">
                            <td class="px-5 py-4"><p class="font-medium text-amber-100">{{ itemName(item) }}</p><p v-if="item.competition_id" class="mt-1 text-xs text-amber-100/40">Competition ID: {{ item.competition_id }}</p></td>
                            <td class="whitespace-nowrap px-5 py-4 text-amber-100/70">{{ itemQuantity(item) }}</td>
                            <td class="whitespace-nowrap px-5 py-4 font-medium text-amber-100">{{ itemPrice(item) === null ? '—' : formatMoney(itemPrice(item), item.currency ?? currency) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="px-5 py-8 text-sm text-amber-100/50">Itemized order details are not available for this transaction.</div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-amber-500/20 bg-black">
            <div class="border-b border-amber-500/20 px-5 py-4">
                <h2 class="font-semibold text-amber-100">Tickets and entries</h2>
                <p class="mt-1 text-xs text-amber-100/40">Tickets associated with this order, when available.</p>
            </div>
            <div v-if="tickets.length" class="p-5">
                <div class="flex flex-wrap gap-2">
                    <span v-for="ticket in tickets" :key="ticket.id ?? ticketNumber(ticket)" class="inline-flex items-center rounded-lg border border-amber-500/20 bg-amber-500/5 px-3 py-2 text-sm font-semibold text-amber-300">
                        {{ ticketNumber(ticket) }}
                    </span>
                </div>
            </div>
            <div v-else class="px-5 py-8 text-sm text-amber-100/50">No ticket or entry details are available for this order.</div>
        </section>

        <section class="rounded-2xl border border-amber-500/20 bg-black p-5">
            <div class="flex items-start gap-3">
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-1.5a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM12 16.5h.008v.008H12V16.5Z" />
                </svg>
                <div>
                    <h2 class="font-semibold text-amber-100">Need help with this payment?</h2>
                    <p class="mt-1 text-sm leading-6 text-amber-100/50">Contact customer support and provide the transaction reference shown above. Never share your full card details or security code.</p>
                </div>
            </div>
        </section>
    </div>
</template>
