<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import UserLayout from '../../../Layouts/UserLayout.vue'

defineOptions({ layout: UserLayout })

const props = defineProps({
    orders: {
        type: Object,
        default: () => ({ data: [], links: [], total: 0, current_page: 1, last_page: 1 }),
    },
})

const orderRows = computed(() => props.orders?.data ?? [])

const formatDate = (value) => {
    if (!value) return '—'
    const date = new Date(value)
    if (Number.isNaN(date.getTime())) return '—'
    return new Intl.DateTimeFormat('en', {
        day: '2-digit', month: 'short', year: 'numeric',
    }).format(date)
}

const formatMoney = (value, currency = 'GYD') => {
    const amount = Number(value ?? 0)
    try {
        return new Intl.NumberFormat('en', {
            style: 'currency',
            currency: currency || 'GYD',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(Number.isFinite(amount) ? amount : 0)
    } catch {
        return `${currency || 'GYD'} ${Number.isFinite(amount) ? amount.toFixed(2) : '0.00'}`
    }
}

const formatStatus = (status) => String(status || 'paid')
    .replaceAll('_', ' ')
    .replace(/\b\w/g, (letter) => letter.toUpperCase())

const statusClasses = (status) => {
    const value = String(status ?? 'paid').toLowerCase()
    if (['paid', 'completed', 'successful', 'succeeded'].includes(value)) {
        return 'border border-emerald-500/20 bg-emerald-500/10 text-emerald-400'
    }
    if (['pending', 'processing'].includes(value)) {
        return 'border border-amber-500/20 bg-amber-500/10 text-amber-400'
    }
    if (['failed', 'cancelled', 'canceled', 'refunded'].includes(value)) {
        return 'border border-red-500/20 bg-red-500/10 text-red-400'
    }
    return 'border border-amber-500/20 bg-amber-500/5 text-amber-100/70'
}
</script>

<template>
    <Head title="Order History" />

    <div class="space-y-6 p-4 text-amber-50 sm:p-6">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-amber-400">Order History</h1>
                <p class="mt-1 text-sm text-amber-100/50">
                    Review your completed orders and their payment details.
                </p>
            </div>

            <Link
                href="/user/orders"
                class="inline-flex w-fit items-center justify-center rounded-lg border border-amber-500/20 px-4 py-2 text-sm font-semibold text-amber-400 transition hover:bg-amber-500/10"
            >
                View All Orders
            </Link>
        </header>

        <section class="rounded-2xl border border-amber-500/20 bg-black p-5">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm text-amber-100/50">Completed orders</p>
                    <p class="mt-2 text-3xl font-bold text-amber-400">
                        {{ orders?.total ?? orderRows.length }}
                    </p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/5">
                    <svg class="h-6 w-6 text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M7.5 3.75h9A2.25 2.25 0 0 1 18.75 6v14.25L12 16.5l-6.75 3.75V6A2.25 2.25 0 0 1 7.5 3.75Z" />
                    </svg>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-amber-500/20 bg-black">
            <div class="border-b border-amber-500/20 px-5 py-4">
                <h2 class="font-semibold text-amber-100">Completed Orders</h2>
                <p class="mt-1 text-xs text-amber-100/40">Only successfully paid orders are shown here.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-amber-500/20 bg-amber-500/5 text-xs uppercase text-amber-100/60">
                        <tr>
                            <th scope="col" class="whitespace-nowrap px-5 py-4 font-semibold">Order</th>
                            <th scope="col" class="whitespace-nowrap px-5 py-4 font-semibold">Order Date</th>
                            <th scope="col" class="whitespace-nowrap px-5 py-4 font-semibold">Items</th>
                            <th scope="col" class="whitespace-nowrap px-5 py-4 font-semibold">Total</th>
                            <th scope="col" class="whitespace-nowrap px-5 py-4 font-semibold">Payment</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-amber-500/10">
                        <tr v-for="order in orderRows" :key="order.id" class="transition hover:bg-amber-500/5">
                            <td class="whitespace-nowrap px-5 py-4">
                                <p class="font-semibold text-amber-100">{{ order.order_number || `Order #${order.id}` }}</p>
                                <p class="mt-1 text-xs text-amber-100/40">ID: {{ order.id }}</p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-amber-100/70">
                                {{ formatDate(order.paid_at || order.created_at) }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-amber-100/70">
                                {{ order.items_count ?? order.items?.length ?? 0 }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 font-semibold text-amber-100">
                                {{ formatMoney(order.total ?? order.subtotal, order.currency) }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusClasses(order.payment_status || order.status)">
                                    {{ formatStatus(order.payment_status || order.status || 'paid') }}
                                </span>
                            </td>
                        </tr>

                        <tr v-if="orderRows.length === 0">
                            <td colspan="5" class="px-5 py-14 text-center">
                                <div class="mx-auto flex max-w-sm flex-col items-center">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-full border border-amber-500/20 bg-amber-500/5">
                                        <svg class="h-7 w-7 text-amber-400/70" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M7.5 3.75h9A2.25 2.25 0 0 1 18.75 6v14.25L12 16.5l-6.75 3.75V6A2.25 2.25 0 0 1 7.5 3.75Z" />
                                        </svg>
                                    </div>
                                    <h3 class="mt-4 font-semibold text-amber-100">No completed orders yet</h3>
                                    <p class="mt-2 text-sm text-amber-100/50">
                                        Once an order is successfully paid, it will appear in your order history.
                                    </p>
                                    <Link href="/user/orders" class="mt-5 inline-flex items-center justify-center rounded-lg border border-amber-500/20 px-4 py-2 text-sm font-semibold text-amber-400 transition hover:bg-amber-500/10">
                                        Browse My Orders
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="orders?.links?.length > 3" class="flex flex-col gap-3 border-t border-amber-500/20 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-amber-100/40">Page {{ orders.current_page ?? 1 }} of {{ orders.last_page ?? 1 }}</p>
                <nav class="flex flex-wrap items-center gap-1" aria-label="Order history pagination">
                    <template v-for="(link, index) in orders.links" :key="`${index}-${link.label}`">
                        <span v-if="!link.url" class="rounded-lg border border-amber-500/10 px-3 py-2 text-xs text-amber-100/25" v-html="link.label" />
                        <Link
                            v-else
                            :href="link.url"
                            preserve-scroll
                            class="rounded-lg border px-3 py-2 text-xs font-semibold transition"
                            :class="link.active
                                ? 'border-amber-500 bg-amber-500 text-black'
                                : 'border-amber-500/20 text-amber-100/60 hover:bg-amber-500/10 hover:text-amber-400'"
                            v-html="link.label"
                        />
                    </template>
                </nav>
            </div>
        </section>
    </div>
</template>
