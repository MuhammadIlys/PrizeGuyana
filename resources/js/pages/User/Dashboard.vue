
<script setup>
import { computed } from 'vue'
import { Head, usePage, Link } from '@inertiajs/vue3'
import UserLayout from '../../Layouts/UserLayout.vue'

defineOptions({
    layout: UserLayout,
})

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            total_orders: 0,
            pending_orders: 0,
            completed_orders: 0,
            total_spent: '0.00',
            total_tickets: 0,
            active_tickets: 0,
        }),
    },

    recentOrders: {
        type: Array,
        default: () => [],
    },

    upcomingDraws: {
        type: Array,
        default: () => [],
    },
})

const page = usePage()

const user = computed(() => page.props.auth?.user ?? {})

const displayName = computed(() => {
    const firstName = user.value.first_name ?? ''
    const lastName = user.value.last_name ?? ''
    const fullName = `${firstName} ${lastName}`.trim()

    return fullName || user.value.name || 'Customer'
})

const initials = computed(() => {
    return displayName.value
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map(part => part.charAt(0).toUpperCase())
        .join('') || 'C'
})

const emailVerified = computed(() => Boolean(user.value.email_verified_at))

const totalOrders = computed(() => Number(props.stats?.total_orders ?? 0))
const pendingOrders = computed(() => Number(props.stats?.pending_orders ?? 0))
const paidOrders = computed(() => Number(props.stats?.completed_orders ?? 0))
const totalSpent = computed(() => props.stats?.total_spent ?? '0.00')
const totalTickets = computed(() => Number(props.stats?.total_tickets ?? 0))
const activeTickets = computed(() => Number(props.stats?.active_tickets ?? 0))

const formattedDate = computed(() => {
    return new Intl.DateTimeFormat(undefined, {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    }).format(new Date())
})

const statsCards = computed(() => [
    {
        label: 'Total orders',
        value: totalOrders.value.toLocaleString(),
        description: 'All orders on your account',
        icon: 'orders',
        color: 'amber',
    },
    {
        label: 'Pending payments',
        value: pendingOrders.value.toLocaleString(),
        description: 'Orders awaiting payment',
        icon: 'clock',
        color: 'orange',
    },
    {
        label: 'Paid orders',
        value: paidOrders.value.toLocaleString(),
        description: 'Successfully paid orders',
        icon: 'check',
        color: 'emerald',
    },
    {
        label: 'Total spent',
        value: totalSpent.value,
        description: 'Across paid orders',
        icon: 'wallet',
        color: 'amber',
    },
    {
        label: 'Total tickets',
        value: totalTickets.value.toLocaleString(),
        description: 'Purchased and assigned tickets',
        icon: 'ticket',
        color: 'amber',
    },
    {
        label: 'Active tickets',
        value: activeTickets.value.toLocaleString(),
        description: 'Tickets currently assigned to you',
        icon: 'ticket-check',
        color: 'emerald',
    },
])

function statusLabel(status) {
    const labels = {
        paid: 'Paid',
        pending: 'Pending',
        processing: 'Processing',
        completed: 'Completed',
        failed: 'Failed',
        cancelled: 'Cancelled',
        expired: 'Expired',
        refunded: 'Refunded',
        partially_refunded: 'Partially refunded',
    }

    const normalized = String(status ?? '').toLowerCase()

    return labels[normalized] ?? (normalized.replaceAll('_', ' ') || 'Unknown')
}

function statusClass(status) {
    const normalized = String(status ?? '').toLowerCase()

    if (['paid', 'completed'].includes(normalized)) {
        return 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400'
    }

    if (['pending', 'processing'].includes(normalized)) {
        return 'border-amber-500/20 bg-amber-500/10 text-amber-400'
    }

    if (['failed', 'cancelled', 'expired'].includes(normalized)) {
        return 'border-red-500/20 bg-red-500/10 text-red-400'
    }

    if (['refunded', 'partially_refunded'].includes(normalized)) {
        return 'border-sky-500/20 bg-sky-500/10 text-sky-400'
    }

    return 'border-neutral-700 bg-neutral-800/70 text-neutral-300'
}
</script>

<template>
    <Head title="My Dashboard" />

    <main class="min-h-screen bg-[#0a0a0a] px-4 py-6 text-neutral-100 sm:px-6 sm:py-8 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-7">

            <!-- Welcome -->
            <section class="relative overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212] p-6 shadow-xl sm:p-8">
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-transparent via-amber-500 to-transparent opacity-80"></div>

                <div class="pointer-events-none absolute -right-16 -top-20 h-56 w-56 rounded-full bg-amber-500/[0.06] blur-3xl"></div>

                <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-400">
                            Customer portal
                        </p>

                        <h1 class="mt-2 text-2xl font-extrabold tracking-tight text-white sm:text-3xl">
                            Welcome back,
                            <span class="break-words text-amber-400">{{ displayName }}</span>
                        </h1>

                        <p class="mt-2 max-w-2xl text-sm leading-6 text-neutral-400">
                            Review your order activity, payment status, tickets, and account information.
                        </p>

                        <p class="mt-3 text-xs text-neutral-500">
                            {{ formattedDate }}
                        </p>
                    </div>

                    <div class="flex shrink-0 items-center gap-3 rounded-xl border border-neutral-800 bg-neutral-900/70 p-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/10 text-sm font-black text-amber-400">
                            {{ initials }}
                        </div>

                        <div class="min-w-0">
                            <p class="max-w-[200px] truncate text-sm font-bold text-white">
                                {{ displayName }}
                            </p>

                            <p class="mt-1 max-w-[200px] truncate text-xs text-neutral-500">
                                {{ user.email || 'No email provided' }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Account status -->
            <section class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <article class="flex items-center gap-4 rounded-2xl border border-neutral-800 bg-[#121212] p-5">
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
                        :class="emailVerified ? 'bg-emerald-500/10 text-emerald-400' : 'bg-amber-500/10 text-amber-400'"
                    >
                        <svg v-if="emailVerified" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="m5 12 4 4L19 6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>

                        <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="9" stroke-width="1.7" />
                            <path d="M12 7v5l3 2" stroke-width="1.7" stroke-linecap="round" />
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500">
                            Email verification
                        </p>

                        <p class="mt-1 text-sm font-bold" :class="emailVerified ? 'text-emerald-400' : 'text-amber-400'">
                            {{ emailVerified ? 'Verified' : 'Not verified' }}
                        </p>

                        <p class="mt-1 text-xs text-neutral-500">
                            {{ emailVerified ? 'Your email address is verified.' : 'Your email address has not been verified yet.' }}
                        </p>
                    </div>
                </article>

                <article class="flex items-center gap-4 rounded-2xl border border-neutral-800 bg-[#121212] p-5">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-sky-500/10 text-sky-400">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M4 5h16v14H4zM4 8l8 5 8-5" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500">
                            Account email
                        </p>

                        <p class="mt-1 truncate text-sm font-bold text-white">
                            {{ user.email || 'Not provided' }}
                        </p>

                        <p class="mt-1 text-xs text-neutral-500">
                            Your account contact address
                        </p>
                    </div>
                </article>
            </section>

            <!-- Statistics -->
            <section>
                <div class="mb-4">
                    <h2 class="text-lg font-bold text-white">
                        Your overview
                    </h2>

                    <p class="mt-1 text-xs text-neutral-500">
                        A summary of your orders, payments, and tickets.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <article
                        v-for="card in statsCards"
                        :key="card.label"
                        class="rounded-2xl border border-neutral-800 bg-[#121212] p-5 transition hover:border-neutral-700"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs font-bold uppercase tracking-wider text-neutral-400">
                                    {{ card.label }}
                                </p>

                                <p
                                    class="mt-3 break-words text-2xl font-black tracking-tight sm:text-3xl"
                                    :class="{
                                        'text-amber-400': card.color === 'amber',
                                        'text-orange-400': card.color === 'orange',
                                        'text-emerald-400': card.color === 'emerald',
                                    }"
                                >
                                    {{ card.value }}
                                </p>
                            </div>

                            <div
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
                                :class="{
                                    'bg-amber-500/10 text-amber-400': card.color === 'amber',
                                    'bg-orange-500/10 text-orange-400': card.color === 'orange',
                                    'bg-emerald-500/10 text-emerald-400': card.color === 'emerald',
                                }"
                            >
                                <svg v-if="card.icon === 'orders'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M8 5h12v16H4V5h4Zm0 0V3h8v2M8 10h8M8 14h8M8 18h5" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>

                                <svg v-else-if="card.icon === 'clock'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <circle cx="12" cy="12" r="9" stroke-width="1.7" />
                                    <path d="M12 7v5l3 2" stroke-width="1.7" stroke-linecap="round" />
                                </svg>

                                <svg v-else-if="card.icon === 'check'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="m5 12 4 4L19 6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    <circle cx="12" cy="12" r="10" stroke-width="1.5" />
                                </svg>

                                <svg v-else-if="card.icon === 'ticket'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 8a2 2 0 0 0 0 4v4a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-4a2 2 0 0 1 0-4V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2Z" />
                                    <path d="M13 4v2m0 3v2m0 3v2m0 3v1" />
                                </svg>

                                <svg v-else-if="card.icon === 'ticket-check'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 8a2 2 0 0 0 0 4v4a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-4a2 2 0 0 1 0-4V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2Z" />
                                    <path d="m9 12 2 2 4-4" />
                                </svg>

                                <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M12 2v20m5-15.5C17 5.1 14.8 4 12 4S7 5.1 7 7s2.2 3 5 3 5 1.1 5 3-2.2 3-5 3-5-1.1-5-2.5" stroke-width="1.7" stroke-linecap="round" />
                                </svg>
                            </div>
                        </div>

                        <p class="mt-4 border-t border-neutral-800 pt-3 text-xs text-neutral-500">
                            {{ card.description }}
                        </p>
                    </article>
                </div>
            </section>

            <!-- Upcoming draws -->
            <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212] shadow-xl">
                <div class="border-b border-neutral-800 p-5 sm:p-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-white">
                                Upcoming draws
                            </h2>

                            <p class="mt-1 text-xs text-neutral-500">
                                Competitions you have entered with upcoming draw dates.
                            </p>
                        </div>

                        <Link
                            :href="route('user.entries.index')"
                            class="inline-flex items-center gap-2 text-xs font-bold text-amber-400 transition hover:text-amber-300"
                        >
                            View my entries
                            <span aria-hidden="true">→</span>
                        </Link>
                    </div>
                </div>

                <div v-if="upcomingDraws.length" class="divide-y divide-neutral-800/80">
                    <article
                        v-for="competition in upcomingDraws"
                        :key="competition.id"
                        class="flex flex-col gap-4 p-5 transition hover:bg-white/[0.02] sm:flex-row sm:items-center sm:p-6"
                    >
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-neutral-800 bg-neutral-900">
                            <img
                                v-if="competition.main_image"
                                :src="competition.main_image"
                                :alt="competition.title"
                                class="h-full w-full object-cover"
                                loading="lazy"
                            />

                            <svg v-else class="h-7 w-7 text-amber-400/50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="3" width="18" height="18" rx="3" />
                                <path d="m3 16 5-5 4 4 3-3 6 6" />
                            </svg>
                        </div>

                        <div class="min-w-0 flex-1">
                            <h3 class="break-words text-sm font-bold text-white">
                                {{ competition.title }}
                            </h3>

                            <p class="mt-2 text-xs text-neutral-400">
                                Draw date:
                                <span class="font-semibold text-amber-400">
                                    {{ competition.draw_at }}
                                </span>
                            </p>
                        </div>

                        <div class="flex shrink-0 items-center justify-between gap-4 sm:flex-col sm:items-end">
                            <span class="text-xs font-semibold text-neutral-300">
                                {{ competition.entries_count }}
                                {{ competition.entries_count === 1 ? 'entry' : 'entries' }}
                            </span>

                            <Link
                                :href="route('user.entries.index')"
                                class="text-xs font-bold text-amber-400 transition hover:text-amber-300"
                            >
                                View entries →
                            </Link>
                        </div>
                    </article>
                </div>

                <div v-else class="px-6 py-10 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl border border-neutral-800 bg-neutral-900 text-neutral-500">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 7v5l3 2" stroke-linecap="round" />
                        </svg>
                    </div>

                    <h3 class="mt-3 text-sm font-bold text-white">
                        No upcoming draws
                    </h3>

                    <p class="mx-auto mt-2 max-w-sm text-xs leading-5 text-neutral-500">
                        Competitions you have entered with upcoming draw dates will appear here.
                    </p>
                </div>
            </section>

            <!-- Recent orders -->
            <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212] shadow-xl">
                <div class="border-b border-neutral-800 p-5 sm:p-6">
                    <div>
                        <h2 class="text-lg font-bold text-white">
                            Recent orders
                        </h2>

                        <p class="mt-1 text-xs text-neutral-500">
                            Showing {{ recentOrders.length }}
                            recent {{ recentOrders.length === 1 ? 'order' : 'orders' }}.
                        </p>
                    </div>
                </div>

                <div v-if="recentOrders.length > 0" class="overflow-x-auto">
                    <table class="w-full min-w-[650px] text-left text-sm">
                        <thead class="bg-[#171717] text-[10px] uppercase tracking-wider text-neutral-500">
                            <tr>
                                <th class="px-5 py-4 font-bold sm:px-6">Order number</th>
                                <th class="px-5 py-4 font-bold">Date</th>
                                <th class="px-5 py-4 text-center font-bold">Items</th>
                                <th class="px-5 py-4 font-bold">Total</th>
                                <th class="px-5 py-4 font-bold">Payment status</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-neutral-800/80">
                            <tr
                                v-for="order in recentOrders"
                                :key="order.id"
                                class="transition hover:bg-white/[0.02]"
                            >
                                <td class="px-5 py-4 sm:px-6">
                                    <span class="font-mono text-xs font-bold text-amber-400">
                                        {{ order.id || '—' }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-xs text-neutral-400">
                                    {{ order.date || '—' }}
                                </td>

                                <td class="px-5 py-4 text-center text-xs text-neutral-300">
                                    {{ Number(order.items ?? 0) }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-xs font-bold text-white">
                                    {{ order.total ?? '—' }}
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex whitespace-nowrap rounded-full border px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wide"
                                        :class="statusClass(order.status)"
                                    >
                                        {{ statusLabel(order.status) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="px-6 py-12 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-neutral-800 bg-neutral-900 text-neutral-500">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M8 5h12v16H4V5h4Zm0 0V3h8v2M8 10h8M8 14h5" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>

                    <h3 class="mt-4 text-sm font-bold text-white">
                        No orders yet
                    </h3>

                    <p class="mx-auto mt-2 max-w-sm text-xs leading-5 text-neutral-500">
                        When you place an order, its details and payment status will appear here.
                    </p>
                </div>

                <div class="border-t border-neutral-800 px-5 py-3 sm:px-6">
                    <p class="text-[11px] leading-5 text-neutral-500">
                        Order information is supplied by your account records.
                    </p>
                </div>
            </section>

            <footer class="flex flex-col gap-2 border-t border-neutral-900 pt-5 text-xs text-neutral-600 sm:flex-row sm:items-center sm:justify-between">
                <p>PrizeGuyana customer dashboard</p>
                <p>Order totals reflect recorded paid orders.</p>
            </footer>
        </div>
    </main>
</template>
