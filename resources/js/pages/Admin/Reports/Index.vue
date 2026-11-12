
<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import RevenueAreaChart from '@/Components/Admin/RevenueAreaChart.vue'

const props = defineProps({
    statistics: {
        type: Object,
        default: () => ({
            revenue_by_currency: [],
            average_order_value: [],
            paid_orders: 0,
            tickets_sold: 0,
            total_customers: 0,
            new_customers: 0,
        }),
    },
    payment_statuses: {
        type: Array,
        default: () => [],
    },
    revenue_trend: {
        type: Array,
        default: () => [],
    },
    competition_performance: {
        type: Array,
        default: () => [],
    },
    recent_orders: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({
            period: '30days',
            start_date: null,
            end_date: null,
        }),
    },
})

const selectedPeriod = computed(() => props.filters.period || '30days')

const periods = [
    { label: 'Today', value: 'today' },
    { label: 'Last 7 days', value: '7days' },
    { label: 'Last 30 days', value: '30days' },
    { label: 'This month', value: 'this_month' },
    { label: 'This year', value: 'this_year' },
]

const applyPeriod = (period) => {
    router.get(
        route('admin.reports.index'),
        { period },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    )
}

const formatMoney = (amount, currency = 'USD') => {
    try {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency || 'USD',
            maximumFractionDigits: 2,
        }).format(Number(amount || 0))
    } catch {
        return `${currency || 'USD'} ${Number(amount || 0).toFixed(2)}`
    }
}

const formatNumber = (value) => {
    return new Intl.NumberFormat('en-US').format(Number(value || 0))
}

const formatPercentage = (value) => {
    return `${Number(value || 0).toFixed(1)}%`
}

const formatDate = (date) => {
    if (!date) return '—'

    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    })
}

const formatDateTime = (date) => {
    if (!date) return '—'

    return new Date(date).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

const averageOrderValues = computed(() => {
    return props.statistics.average_order_value || []
})

const totalPayments = computed(() =>
    props.payment_statuses.reduce(
        (total, item) => total + Number(item.total || 0),
        0
    )
)

const paymentStatusColor = (status) => {
    const colors = {
        paid: 'bg-emerald-500',
        succeeded: 'bg-emerald-500',
        pending: 'bg-amber-400',
        failed: 'bg-rose-500',
        cancelled: 'bg-slate-500',
        refunded: 'bg-sky-500',
    }

    return colors[String(status).toLowerCase()] || 'bg-violet-500'
}

const paymentStatusLabel = (status) => {
    return String(status || 'unknown')
        .replaceAll('_', ' ')
        .replace(/\b\w/g, (char) => char.toUpperCase())
}

/*
|--------------------------------------------------------------------------
| Revenue Charts
|--------------------------------------------------------------------------
| Each currency gets its own scale.
| Different currencies are never compared against one shared maximum.
*/

const chartCurrencies = computed(() => [
    ...new Set(
        props.revenue_trend
            .map((item) => item.currency)
            .filter(Boolean)
    ),
])

const chartDates = computed(() => {
    return [...new Set(
        props.revenue_trend.map((item) => item.date)
    )].sort()
})

const revenueCharts = computed(() => {
    return chartCurrencies.value.map((currency) => {
        const values = chartDates.value.map((date) => {
            const entry = props.revenue_trend.find(
                (item) =>
                    item.date === date &&
                    item.currency === currency
            )

            return {
                date,
                label: new Date(`${date}T00:00:00`).toLocaleDateString(
                    'en-US',
                    {
                        month: 'short',
                        day: 'numeric',
                    }
                ),
                revenue: Number(entry?.revenue || 0),
            }
        })

        const maximum = Math.max(
            1,
            ...values.map((item) => item.revenue)
        )

        return {
            currency,
            maximum,
            values: values.map((item) => ({
                ...item,
                height: (item.revenue / maximum) * 100,
            })),
        }
    })
})

const hasRevenue = computed(() => props.revenue_trend.length > 0)

/*
|--------------------------------------------------------------------------
| Competition Performance
|--------------------------------------------------------------------------
*/

const getProgressWidth = (value) => {
    return Math.min(100, Math.max(0, Number(value || 0)))
}

const competitionWinnerTotal = (competition) => {
    return (
        Number(competition.instant_win_winners || 0) +
        Number(competition.draw_winners || 0)
    )
}
</script>

<template>
    <AdminLayout>
        <div class="min-h-screen space-y-6 bg-slate-950 p-4 text-slate-100 sm:p-6 lg:p-8">

            <!-- Header -->
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-amber-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                        Analytics & Insights
                    </div>

                    <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                        Reports Overview
                    </h1>

                    <p class="mt-1 text-sm text-slate-400">
                        Monitor revenue, ticket sales, customers and competition performance.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs text-slate-500">
                        Reporting period
                    </span>

                    <select
                        :value="selectedPeriod"
                        @change="applyPeriod($event.target.value)"
                        class="rounded-xl border border-slate-700 bg-slate-900 px-4 py-2.5 text-sm text-white outline-none transition focus:border-amber-400"
                    >
                        <option
                            v-for="period in periods"
                            :key="period.value"
                            :value="period.value"
                        >
                            {{ period.label }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Date Range -->
            <div class="flex flex-wrap items-center gap-2 text-xs text-slate-400">
                <span class="rounded-lg border border-slate-800 bg-slate-900 px-3 py-2">
                    <span class="text-slate-500">From:</span>
                    {{ formatDate(filters.start_date) }}
                </span>

                <span class="rounded-lg border border-slate-800 bg-slate-900 px-3 py-2">
                    <span class="text-slate-500">To:</span>
                    {{ formatDate(filters.end_date) }}
                </span>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">

                <!-- Revenue -->
                <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-400">
                                Paid Revenue
                            </p>

                            <div
                                v-if="statistics.revenue_by_currency?.length"
                                class="mt-3 space-y-1"
                            >
                                <p
                                    v-for="item in statistics.revenue_by_currency"
                                    :key="item.currency"
                                    class="text-xl font-bold tracking-tight text-white"
                                >
                                    {{ formatMoney(item.revenue, item.currency) }}
                                </p>
                            </div>

                            <p v-else class="mt-3 text-2xl font-bold text-white">
                                {{ formatMoney(0) }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-emerald-500/10 p-3 text-emerald-400">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-4-9h6a2 2 0 010 4H10a2 2 0 000 4h6" />
                                <circle cx="12" cy="12" r="9" />
                            </svg>
                        </div>
                    </div>

                    <p class="mt-4 text-xs text-slate-500">
                        Revenue from successfully paid orders
                    </p>
                </div>

                <!-- Paid Orders -->
                <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-400">
                                Paid Orders
                            </p>

                            <p class="mt-3 text-3xl font-bold text-white">
                                {{ formatNumber(statistics.paid_orders) }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-blue-500/10 p-3 text-blue-400">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16v14H4zM8 9h8M8 13h5" />
                            </svg>
                        </div>
                    </div>

                    <p class="mt-4 text-xs text-slate-500">
                        Successful customer orders
                    </p>
                </div>

                <!-- Average Order Value -->
                <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-400">
                                Average Order Value
                            </p>

                            <div
                                v-if="averageOrderValues.length"
                                class="mt-3 space-y-1"
                            >
                                <p
                                    v-for="item in averageOrderValues"
                                    :key="item.currency"
                                    class="text-xl font-bold text-white"
                                >
                                    {{ formatMoney(item.average_order_value, item.currency) }}
                                </p>
                            </div>

                            <p v-else class="mt-3 text-2xl font-bold text-white">
                                —
                            </p>
                        </div>

                        <div class="rounded-xl bg-violet-500/10 p-3 text-violet-400">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h10M4 17h7" />
                                <circle cx="18" cy="16" r="3" />
                            </svg>
                        </div>
                    </div>

                    <p class="mt-4 text-xs text-slate-500">
                        Average value per paid order
                    </p>
                </div>

                <!-- Tickets -->
                <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-400">
                                Tickets Sold
                            </p>

                            <p class="mt-3 text-3xl font-bold text-white">
                                {{ formatNumber(statistics.tickets_sold) }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-amber-500/10 p-3 text-amber-400">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7a2 2 0 012-2h12a2 2 0 012 2v3a2 2 0 000 4v3a2 2 0 01-2 2H6a2 2 0 01-2-2v-3a2 2 0 000-4V7zM9 8v2m0 4v2" />
                            </svg>
                        </div>
                    </div>

                    <p class="mt-4 text-xs text-slate-500">
                        Assigned tickets from paid orders
                    </p>
                </div>

                <!-- Customers -->
                <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-400">
                                Total Customers
                            </p>

                            <p class="mt-3 text-3xl font-bold text-white">
                                {{ formatNumber(statistics.total_customers) }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-cyan-500/10 p-3 text-cyan-400">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="9" cy="8" r="3" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 19a6 6 0 0112 0M16 11a3 3 0 100-6m2 8a5 5 0 013 6" />
                            </svg>
                        </div>
                    </div>

                    <p class="mt-4 text-xs text-slate-500">
                        {{ formatNumber(statistics.new_customers) }} new registrations in this period
                    </p>
                </div>
            </div>

            <!-- Revenue Analytics + Payment Status -->
            <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">

                <!-- Revenue Analytics -->
                <section class="rounded-2xl border border-slate-800 bg-slate-900 p-5 xl:col-span-2">
    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-base font-semibold text-white">
                Revenue Analytics
            </h2>

            <p class="mt-1 text-xs text-slate-500">
                Daily revenue from paid orders, separated by currency
            </p>
        </div>

        <span class="rounded-lg bg-slate-800 px-3 py-1.5 text-xs text-slate-400">
            {{ chartCurrencies.length }} currencies
        </span>
    </div>

    <RevenueAreaChart :revenue-trend="revenue_trend" />
</section>

                <!-- Payment Status -->
                <section class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                    <div>
                        <h2 class="text-base font-semibold text-white">
                            Payment Status
                        </h2>

                        <p class="mt-1 text-xs text-slate-500">
                            Payment records created during this period
                        </p>
                    </div>

                    <div v-if="payment_statuses.length" class="mt-6 space-y-5">
                        <div
                            v-for="item in payment_statuses"
                            :key="item.status"
                        >
                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-sm text-slate-300">
                                    {{ paymentStatusLabel(item.status) }}
                                </span>

                                <span class="text-sm font-semibold text-white">
                                    {{ formatNumber(item.total) }}
                                </span>
                            </div>

                            <div class="h-2 overflow-hidden rounded-full bg-slate-800">
                                <div
                                    class="h-full rounded-full transition-all"
                                    :class="paymentStatusColor(item.status)"
                                    :style="{
                                        width: `${totalPayments ? (item.total / totalPayments) * 100 : 0}%`
                                    }"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="flex h-52 items-center justify-center text-sm text-slate-500">
                        No payment records for this period.
                    </div>

                    <div class="mt-6 border-t border-slate-800 pt-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-400">Total payment records</span>

                            <span class="font-semibold text-white">
                                {{ formatNumber(totalPayments) }}
                            </span>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Competition Performance -->
            <section class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900">
                <div class="flex flex-col justify-between gap-2 border-b border-slate-800 p-5 sm:flex-row sm:items-center">
                    <div>
                        <h2 class="text-base font-semibold text-white">
                            Competition Performance
                        </h2>

                        <p class="mt-1 text-xs text-slate-500">
                            Sales, capacity, participation and winners
                        </p>
                    </div>

                    <span class="rounded-lg bg-slate-800 px-3 py-1.5 text-xs text-slate-400">
                        Top 10 by revenue
                    </span>
                </div>

                <div v-if="competition_performance.length" class="overflow-x-auto">
                    <table class="w-full min-w-[1150px] text-left">
                        <thead class="bg-slate-950/50 text-[11px] uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-5 py-4 font-semibold">Competition</th>
                                <th class="px-5 py-4 font-semibold">Period Sales</th>
                                <th class="px-5 py-4 font-semibold">Revenue</th>
                                <th class="px-5 py-4 font-semibold">Participants</th>
                                <th class="px-5 py-4 font-semibold">Tickets Sold</th>
                                <th class="px-5 py-4 font-semibold">Remaining</th>
                                <th class="px-5 py-4 font-semibold">Sales Progress</th>
                                <th class="px-5 py-4 font-semibold">Instant Wins</th>
                                <th class="px-5 py-4 font-semibold">Draw Winners</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-800">
                            <tr
                                v-for="(competition, index) in competition_performance"
                                :key="`${competition.competition_id}-${competition.currency}`"
                                class="transition hover:bg-slate-800/40"
                            >
                                <!-- Competition -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-800 text-xs font-semibold text-slate-400">
                                            {{ index + 1 }}
                                        </span>

                                        <span class="max-w-[200px] text-sm font-medium text-white">
                                            {{ competition.title }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Period Sales -->
                                <td class="px-5 py-4 text-sm text-slate-300">
                                    {{ formatNumber(competition.tickets_sold) }}
                                </td>

                                <!-- Revenue -->
                                <td class="px-5 py-4 text-sm font-semibold text-emerald-400">
                                    {{ formatMoney(competition.revenue, competition.currency) }}
                                </td>

                                <!-- Participants -->
                                <td class="px-5 py-4 text-sm text-slate-300">
                                    {{ formatNumber(competition.participants) }}
                                </td>

                                <!-- Lifetime Sold -->
                                <td class="px-5 py-4 text-sm text-slate-300">
                                    {{ formatNumber(competition.lifetime_tickets_sold) }}
                                    <span class="text-xs text-slate-500">
                                        / {{ formatNumber(competition.total_tickets) }}
                                    </span>
                                </td>

                                <!-- Remaining -->
                                <td class="px-5 py-4 text-sm text-slate-300">
                                    {{ formatNumber(competition.remaining_tickets) }}
                                </td>

                                <!-- Progress -->
                                <td class="px-5 py-4">
                                    <div class="min-w-[130px]">
                                        <div class="mb-2 flex items-center justify-between gap-2">
                                            <span class="text-xs font-semibold text-white">
                                                {{ formatPercentage(competition.sales_progress) }}
                                            </span>
                                        </div>

                                        <div class="h-1.5 overflow-hidden rounded-full bg-slate-800">
                                            <div
                                                class="h-full rounded-full bg-amber-400 transition-all"
                                                :style="{
                                                    width: `${getProgressWidth(competition.sales_progress)}%`
                                                }"
                                            ></div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Instant Win Winners -->
                                <td class="px-5 py-4 text-sm text-slate-300">
                                    {{ formatNumber(competition.instant_win_winners) }}
                                </td>

                                <!-- Draw Winners -->
                                <td class="px-5 py-4 text-sm text-slate-300">
                                    {{ formatNumber(competition.draw_winners) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="px-5 py-12 text-center">
                    <p class="text-sm text-slate-400">
                        No competition sales found for this period.
                    </p>
                </div>

                <div class="border-t border-slate-800 px-5 py-3">
                    <p class="text-xs text-slate-500">
                        Period Sales and Revenue follow the selected reporting period.
                        Ticket capacity, participants, progress and winners represent current/lifetime figures.
                    </p>
                </div>
            </section>

            <!-- Recent Paid Orders -->
            <section class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900">
                <div class="flex items-center justify-between border-b border-slate-800 p-5">
                    <div>
                        <h2 class="text-base font-semibold text-white">
                            Recent Paid Orders
                        </h2>

                        <p class="mt-1 text-xs text-slate-500">
                            Latest successful customer transactions
                        </p>
                    </div>

                    <span class="rounded-lg bg-emerald-500/10 px-3 py-1.5 text-xs font-medium text-emerald-400">
                        Paid
                    </span>
                </div>

                <div v-if="recent_orders.length" class="overflow-x-auto">
                    <table class="w-full min-w-[700px] text-left">
                        <thead class="bg-slate-950/50 text-[11px] uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-5 py-4 font-semibold">Order</th>
                                <th class="px-5 py-4 font-semibold">Customer</th>
                                <th class="px-5 py-4 font-semibold">Amount</th>
                                <th class="px-5 py-4 font-semibold">Payment Date</th>
                                <th class="px-5 py-4 font-semibold">Status</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-800">
                            <tr
                                v-for="order in recent_orders"
                                :key="order.id"
                                class="transition hover:bg-slate-800/40"
                            >
                                <td class="px-5 py-4">
                                    <span class="text-sm font-semibold text-amber-400">
                                        {{ order.order_number }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="text-sm font-medium text-white">
                                        {{ order.customer_name || 'Customer' }}
                                    </p>

                                    <p class="mt-0.5 text-xs text-slate-500">
                                        {{ order.customer_email }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-sm font-semibold text-white">
                                    {{ formatMoney(order.total, order.currency) }}
                                </td>

                                <td class="px-5 py-4 text-xs text-slate-400">
                                    {{ formatDateTime(order.paid_at) }}
                                </td>

                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-medium text-emerald-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                        Paid
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="px-5 py-12 text-center">
                    <p class="text-sm text-slate-400">
                        No paid orders found for this period.
                    </p>
                </div>
            </section>

            <!-- Footer -->
            <div class="pb-4 text-center text-xs text-slate-600">
                PrizeGuyana Reports · Data based on recorded platform transactions
            </div>
        </div>
    </AdminLayout>
</template>
