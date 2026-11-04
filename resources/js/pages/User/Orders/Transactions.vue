<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import UserLayout from '../../../Layouts/UserLayout.vue'

defineOptions({
    layout: UserLayout,
})

const props = defineProps({
    transactions: {
        type: Object,
        default: () => ({
            data: [],
            links: [],
            total: 0,
            current_page: 1,
            last_page: 1,
        }),
    },
})

const transactionRows = computed(() => props.transactions?.data ?? [])

const totalTransactions = computed(() => {
    return props.transactions?.total ?? transactionRows.value.length
})

const formatDate = (value) => {
    if (!value) return '—'

    const date = new Date(value)

    if (Number.isNaN(date.getTime())) return '—'

    return new Intl.DateTimeFormat('en', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date)
}

const formatMoney = (value, currency = 'GYD') => {
    const amount = Number(value ?? 0)
    const safeAmount = Number.isFinite(amount) ? amount : 0
    const safeCurrency = typeof currency === 'string' && currency.trim()
        ? currency.trim().toUpperCase()
        : 'GYD'

    try {
        return new Intl.NumberFormat('en', {
            style: 'currency',
            currency: safeCurrency,
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(safeAmount)
    } catch {
        return `${safeCurrency} ${safeAmount.toFixed(2)}`
    }
}

const formatLabel = (value, fallback = '—') => {
    if (value === null || value === undefined || value === '') return fallback

    return String(value)
        .replaceAll('_', ' ')
        .replaceAll('-', ' ')
        .replace(/\b\w/g, (letter) => letter.toUpperCase())
}

const transactionStatus = (transaction) => {
    return transaction?.status
        ?? transaction?.payment_status
        ?? transaction?.order_payment_status
        ?? 'unknown'
}

const statusClasses = (status) => {
    const value = String(status ?? '').toLowerCase()

    if (['paid', 'completed', 'successful', 'succeeded', 'success'].includes(value)) {
        return 'border border-emerald-500/20 bg-emerald-500/10 text-emerald-400'
    }

    if (['pending', 'processing', 'requires_action', 'requires_payment_method'].includes(value)) {
        return 'border border-amber-500/20 bg-amber-500/10 text-amber-400'
    }

    if (['failed', 'cancelled', 'canceled', 'refunded', 'void', 'voided'].includes(value)) {
        return 'border border-red-500/20 bg-red-500/10 text-red-400'
    }

    return 'border border-amber-500/20 bg-amber-500/5 text-amber-100/70'
}

const transactionReference = (transaction) => {
    return transaction?.reference
        ?? `Payment #${transaction?.id}`
}

const paymentMethod = (transaction) => {
    return transaction?.payment_method
        ?? transaction?.method
        ?? transaction?.provider
        ?? null
}
</script>

<template>

    <Head title="Transactions" />

    <div class="space-y-6 p-4 text-amber-50 sm:p-6">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-amber-400">Transactions</h1>
                <p class="mt-1 text-sm text-amber-100/50">
                    View your payment records and transaction statuses.
                </p>
            </div>

            <Link href="/user/orders"
                class="inline-flex w-fit items-center justify-center rounded-lg border border-amber-500/20 px-4 py-2 text-sm font-semibold text-amber-400 transition hover:bg-amber-500/10">
                View My Orders
            </Link>
        </header>

        <section class="rounded-2xl border border-amber-500/20 bg-black p-5">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm text-amber-100/50">Total transactions</p>
                    <p class="mt-2 text-3xl font-bold text-amber-400">
                        {{ totalTransactions }}
                    </p>
                    <p class="mt-1 text-xs text-amber-100/40">
                        Payment records associated with your account
                    </p>
                </div>

                <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/5">
                    <svg class="h-6 w-6 text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 7.5h16.5m-15-3h13.5A1.5 1.5 0 0 1 20.25 6v12a1.5 1.5 0 0 1-1.5 1.5H5.25A1.5 1.5 0 0 1 3.75 18V6a1.5 1.5 0 0 1 1.5-1.5ZM3.75 11.25h16.5" />
                    </svg>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-amber-500/20 bg-black">
            <div class="border-b border-amber-500/20 px-5 py-4">
                <h2 class="font-semibold text-amber-100">Payment History</h2>
                <p class="mt-1 text-xs text-amber-100/40">
                    Transaction details reported by the payment system.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-amber-500/20 bg-amber-500/5 text-xs uppercase text-amber-100/60">
                        <tr>
                            <th scope="col" class="whitespace-nowrap px-5 py-4 font-semibold">Transaction</th>
                            <th scope="col" class="whitespace-nowrap px-5 py-4 font-semibold">Order</th>
                            <th scope="col" class="whitespace-nowrap px-5 py-4 font-semibold">Date</th>
                            <th scope="col" class="whitespace-nowrap px-5 py-4 font-semibold">Method</th>
                            <th scope="col" class="whitespace-nowrap px-5 py-4 font-semibold">Amount</th>
                            <th scope="col" class="whitespace-nowrap px-5 py-4 font-semibold">Status</th>
                            <th scope="col" class="whitespace-nowrap px-5 py-4 font-semibold">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-amber-500/10">
                        <tr v-for="transaction in transactionRows" :key="transaction.id"
                            class="transition hover:bg-amber-500/5">
                            <td class="whitespace-nowrap px-5 py-4">
                                <p class="font-semibold text-amber-100">
                                    {{ transactionReference(transaction) }} </p>
                                <p class="mt-1 text-xs text-amber-100/40">
                                    Record ID: {{ transaction.id ?? '—' }}
                                </p>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4">
                                <span class="font-medium text-amber-100/80">
                                    {{ transaction.order_number || (transaction.order_id ? `Order
                                    #${transaction.order_id}` : '—') }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-amber-100/70">
                                {{ formatDate(transaction.paid_at || transaction.created_at || transaction.updated_at)
                                }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-amber-100/70">
                                {{ formatLabel(paymentMethod(transaction), 'Not specified') }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 font-semibold text-amber-100">
                                {{ formatMoney(transaction.amount ?? transaction.total ?? transaction.order_total,
                                    transaction.currency) }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                                    :class="statusClasses(transactionStatus(transaction))">
                                    {{ formatLabel(transactionStatus(transaction), 'Unknown') }}
                                </span>
                            </td>
                            <td>
                                <Link :href="route('user.transactions.show', transaction.id)"
                                    class="border border-amber-500/20 px-3 py-2 text-xs font-semibold text-amber-400 transition hover:bg-amber-500/10">
                                    View Details
                                </Link>
                            </td>
                        </tr>

                        <tr v-if="transactionRows.length === 0">
                            <td colspan="6" class="px-5 py-14 text-center">
                                <div class="mx-auto flex max-w-sm flex-col items-center">
                                    <div
                                        class="flex h-14 w-14 items-center justify-center rounded-full border border-amber-500/20 bg-amber-500/5">
                                        <svg class="h-7 w-7 text-amber-400/70" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"
                                            aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3.75 7.5h16.5m-15-3h13.5A1.5 1.5 0 0 1 20.25 6v12a1.5 1.5 0 0 1-1.5 1.5H5.25A1.5 1.5 0 0 1 3.75 18V6a1.5 1.5 0 0 1 1.5-1.5ZM3.75 11.25h16.5" />
                                        </svg>
                                    </div>
                                    <h3 class="mt-4 font-semibold text-amber-100">No transactions yet</h3>
                                    <p class="mt-2 text-sm text-amber-100/50">
                                        Your payment records will appear here when available.
                                    </p>
                                    <Link href="/user/orders"
                                        class="mt-5 inline-flex items-center justify-center rounded-lg border border-amber-500/20 px-4 py-2 text-sm font-semibold text-amber-400 transition hover:bg-amber-500/10">
                                        View My Orders
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="transactions?.links?.length > 3"
                class="flex flex-col gap-3 border-t border-amber-500/20 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-amber-100/40">
                    Page {{ transactions.current_page ?? 1 }} of {{ transactions.last_page ?? 1 }}
                </p>

                <nav class="flex flex-wrap items-center gap-1" aria-label="Transaction pagination">
                    <template v-for="(link, index) in transactions.links" :key="`${index}-${link.label}`">
                        <span v-if="!link.url"
                            class="rounded-lg border border-amber-500/10 px-3 py-2 text-xs text-amber-100/25"
                            v-html="link.label" />

                        <Link v-else :href="link.url" preserve-scroll
                            class="rounded-lg border px-3 py-2 text-xs font-semibold transition" :class="link.active
                                ? 'border-amber-500 bg-amber-500 text-black'
                                : 'border-amber-500/20 text-amber-100/60 hover:bg-amber-500/10 hover:text-amber-400'"
                            v-html="link.label" />
                    </template>
                </nav>
            </div>
        </section>
    </div>
</template>
