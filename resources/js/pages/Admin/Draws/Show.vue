
<script setup>
import { computed, ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    competition: {
        type: Object,
        required: true,
    },
    prizes: {
        type: Array,
        default: () => [],
    },
    winners: {
        type: Array,
        default: () => [],
    },
    statistics: {
        type: Object,
        default: () => ({
            total_prizes: 0,
            required_winners: 0,
            selected_winners: 0,
            eligible_tickets: 0,
        }),
    },
})

const winnerSearch = ref('')
const statusFilter = ref('all')

const isCompleted = computed(() => props.competition.status === 'completed')

const drawProgress = computed(() => {
    if (!props.statistics.required_winners) return 0

    return Math.min(
        100,
        Math.round(
            (props.statistics.selected_winners /
                props.statistics.required_winners) * 100
        )
    )
})

const filteredWinners = computed(() => {
    return props.winners.filter((winner) => {
        const search = winnerSearch.value.trim().toLowerCase()

        const matchesSearch =
            !search ||
            winner.prize_name?.toLowerCase().includes(search) ||
            winner.winner_name?.toLowerCase().includes(search) ||
            winner.ticket_number?.toLowerCase().includes(search)

        const matchesStatus =
            statusFilter.value === 'all' ||
            winner.status === statusFilter.value

        return matchesSearch && matchesStatus
    })
})

const statusStyles = (status) => {
    const styles = {
        active: 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/20',
        completed: 'bg-blue-500/10 text-blue-400 ring-blue-500/20',
        draft: 'bg-amber-500/10 text-amber-400 ring-amber-500/20',
        cancelled: 'bg-red-500/10 text-red-400 ring-red-500/20',
        won: 'bg-amber-500/10 text-amber-400 ring-amber-500/20',
        claimed: 'bg-blue-500/10 text-blue-400 ring-blue-500/20',
        fulfilled: 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/20',
    }

    return styles[status] || 'bg-slate-500/10 text-slate-400 ring-slate-500/20'
}

const statusLabel = (status) => {
    if (status === 'cancelled') return 'Rejected'
    return status?.charAt(0).toUpperCase() + status?.slice(1)
}

const formatDate = (date) => {
    if (!date) return '—'

    return new Date(date).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

const formatAmount = (value, currency) => {
    if (value === null || value === undefined) return '—'

    return `${currency || 'USD'} ${Number(value).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`
}
</script>

<template>
    <Head :title="`Draw - ${competition.title}`" />

    <AdminLayout>
        <div class="min-h-screen space-y-7 bg-slate-950 px-4 py-6 text-slate-100 sm:px-6 lg:px-8">

            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <Link
                        :href="route('admin.draws.index')"
                        class="mb-4 inline-flex items-center gap-2 text-sm text-slate-400 transition hover:text-amber-400"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m15 18-6-6 6-6" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Back to Draws
                    </Link>

                    <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-amber-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                        Draw Management
                    </div>

                    <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                        {{ competition.title }}
                    </h1>

                    <p class="mt-2 text-sm text-slate-400">
                        Monitor scheduled draw results, prize allocation and winner claim progress.
                    </p>
                </div>

                <span
                    class="inline-flex w-fit items-center rounded-full px-3 py-1.5 text-xs font-semibold ring-1 ring-inset"
                    :class="statusStyles(competition.status)"
                >
                    {{ statusLabel(competition.status) }}
                </span>
            </div>

            <!-- Completion Notice -->
            <div
                v-if="isCompleted"
                class="flex items-start gap-3 rounded-xl border border-blue-500/20 bg-blue-500/5 p-4"
            >
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="9" />
                    <path d="m8 12 3 3 5-6" stroke-linecap="round" stroke-linejoin="round" />
                </svg>

                <div>
                    <p class="text-sm font-semibold text-blue-300">Draw completed</p>
                    <p class="mt-1 text-xs leading-5 text-slate-400">
                        The scheduled draw has been completed and its winners have been permanently recorded.
                    </p>
                    <p v-if="competition.completed_at" class="mt-2 text-xs text-slate-500">
                        Completed: {{ formatDate(competition.completed_at) }}
                    </p>
                </div>
            </div>

            <div
                v-else
                class="flex items-start gap-3 rounded-xl border border-amber-500/20 bg-amber-500/5 p-4"
            >
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="9" />
                    <path d="M12 8v5m0 3h.01" stroke-linecap="round" />
                </svg>

                <div>
                    <p class="text-sm font-semibold text-amber-300">Draw awaiting completion</p>
                    <p class="mt-1 text-xs leading-5 text-slate-400">
                        Draw execution is managed through Competition / Ticket Management.
                        This page is for monitoring draw results and winners.
                    </p>
                </div>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                    <p class="text-sm text-slate-400">Active Draw Prizes</p>
                    <p class="mt-3 text-3xl font-bold text-white">{{ statistics.total_prizes }}</p>
                    <p class="mt-1 text-xs text-slate-500">Configured prize categories</p>
                </div>

                <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                    <p class="text-sm text-slate-400">Required Winners</p>
                    <p class="mt-3 text-3xl font-bold text-white">{{ statistics.required_winners }}</p>
                    <p class="mt-1 text-xs text-slate-500">Based on prize quantities</p>
                </div>

                <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                    <p class="text-sm text-slate-400">Remaining Eligible Tickets</p>
                    <p class="mt-3 text-3xl font-bold text-emerald-400">{{ statistics.eligible_tickets }}</p>
                    <p class="mt-1 text-xs text-slate-500">Purchased and not previously won</p>
                </div>

                <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                    <p class="text-sm text-slate-400">Selected Winners</p>
                    <p class="mt-3 text-3xl font-bold text-amber-400">{{ statistics.selected_winners }}</p>
                    <p class="mt-1 text-xs text-slate-500">Saved scheduled draw winners</p>
                </div>
            </div>

            <!-- Draw Progress -->
            <section class="rounded-2xl border border-slate-800 bg-slate-900 p-5 sm:p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="font-semibold text-white">Draw Progress</h2>
                        <p class="mt-1 text-xs text-slate-500">
                            Winner selection against configured prize quantities.
                        </p>
                    </div>

                    <span class="text-sm font-bold text-amber-400">
                        {{ drawProgress }}%
                    </span>
                </div>

                <div class="mt-5 h-2 overflow-hidden rounded-full bg-slate-800">
                    <div
                        class="h-full rounded-full bg-amber-500 transition-all duration-500"
                        :style="{ width: `${drawProgress}%` }"
                    ></div>
                </div>

                <div class="mt-3 flex justify-between text-xs text-slate-500">
                    <span>{{ statistics.selected_winners }} winners selected</span>
                    <span>{{ statistics.required_winners }} required</span>
                </div>
            </section>

            <!-- Prize Breakdown -->
            <section class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900">
                <div class="border-b border-slate-800 px-5 py-5">
                    <h2 class="font-semibold text-white">Scheduled Draw Prizes</h2>
                    <p class="mt-1 text-xs text-slate-500">
                        Active prizes and their winner allocation progress.
                    </p>
                </div>

                <div v-if="prizes.length" class="overflow-x-auto">
                    <table class="w-full min-w-[700px] text-left">
                        <thead class="bg-slate-950/70">
                            <tr class="text-[11px] uppercase tracking-wider text-slate-500">
                                <th class="px-5 py-4">Prize</th>
                                <th class="px-5 py-4">Prize Type</th>
                                <th class="px-5 py-4">Value</th>
                                <th class="px-5 py-4">Quantity</th>
                                <th class="px-5 py-4">Winners Selected</th>
                                <th class="px-5 py-4">Progress</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-800">
                            <tr v-for="prize in prizes" :key="prize.id" class="hover:bg-slate-800/30">
                                <td class="px-5 py-4">
                                    <p class="text-sm font-semibold text-white">{{ prize.name }}</p>
                                    <p v-if="prize.description" class="mt-1 max-w-xs truncate text-xs text-slate-500">
                                        {{ prize.description }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-sm capitalize text-slate-300">
                                    {{ prize.prize_type?.replaceAll('_', ' ') || '—' }}
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-300">
                                    {{ formatAmount(prize.prize_value, prize.currency) }}
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-300">
                                    {{ prize.quantity }}
                                </td>

                                <td class="px-5 py-4 text-sm font-semibold text-amber-400">
                                    {{ prize.winner_count }} / {{ prize.quantity }}
                                </td>

                                <td class="px-5 py-4">
                                    <div class="h-1.5 w-24 overflow-hidden rounded-full bg-slate-800">
                                        <div
                                            class="h-full rounded-full bg-amber-500"
                                            :style="{
                                                width: `${prize.quantity
                                                    ? Math.min(100, (prize.winner_count / prize.quantity) * 100)
                                                    : 0}%`
                                            }"
                                        ></div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="px-5 py-12 text-center">
                    <p class="text-sm font-medium text-slate-300">No active scheduled draw prizes</p>
                    <p class="mt-1 text-xs text-slate-500">
                        No active scheduled draw prizes are configured for this competition.
                    </p>
                </div>
            </section>

            <!-- Winners -->
            <section class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900">
                <div class="flex flex-col gap-4 border-b border-slate-800 p-5 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="font-semibold text-white">Scheduled Draw Winners</h2>
                        <p class="mt-1 text-xs text-slate-500">
                            Review selected winners and their prize claim progress.
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <input
                            v-model="winnerSearch"
                            type="text"
                            placeholder="Search winner, ticket or prize..."
                            class="rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-sm text-white outline-none placeholder:text-slate-500 focus:border-amber-500 sm:w-64"
                        />

                        <select
                            v-model="statusFilter"
                            class="rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-sm text-slate-300 outline-none focus:border-amber-500"
                        >
                            <option value="all">All Claim Statuses</option>
                            <option value="won">Not Claimed</option>
                            <option value="claimed">Claimed</option>
                            <option value="fulfilled">Fulfilled</option>
                            <option value="cancelled">Rejected</option>
                        </select>
                    </div>
                </div>

                <div v-if="filteredWinners.length" class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-left">
                        <thead class="bg-slate-950/70">
                            <tr class="text-[11px] uppercase tracking-wider text-slate-500">
                                <th class="px-5 py-4">Winner</th>
                                <th class="px-5 py-4">Prize</th>
                                <th class="px-5 py-4">Ticket</th>
                                <th class="px-5 py-4">Order</th>
                                <th class="px-5 py-4">Won At</th>
                                <th class="px-5 py-4">Claim Status</th>
                                <th class="px-5 py-4">Fulfilled At</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-800">
                            <tr
                                v-for="winner in filteredWinners"
                                :key="winner.id"
                                class="hover:bg-slate-800/30"
                            >
                                <td class="px-5 py-4">
                                    <p class="text-sm font-semibold text-white">{{ winner.winner_name }}</p>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="text-sm text-slate-200">{{ winner.prize_name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ formatAmount(winner.prize_value, winner.currency) }}
                                    </p>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="rounded-md border border-slate-700 bg-slate-950 px-2.5 py-1 font-mono text-xs text-amber-400">
                                        {{ winner.ticket_number }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-400">
                                    #{{ winner.order_id || '—' }}
                                </td>

                                <td class="px-5 py-4 text-xs text-slate-400">
                                    {{ formatDate(winner.won_at) }}
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset"
                                        :class="statusStyles(winner.status)"
                                    >
                                        {{ statusLabel(winner.status) }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-xs text-slate-400">
                                    {{ formatDate(winner.fulfilled_at) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="px-5 py-12 text-center">
                    <p class="text-sm font-medium text-slate-300">
                        {{ winners.length ? 'No matching winners' : 'No winners selected yet' }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ winners.length
                            ? 'Try changing your search or claim status filter.'
                            : 'Winners will appear here after the scheduled draw is completed.' }}
                    </p>
                </div>

                <div class="border-t border-slate-800 px-5 py-4">
                    <p class="text-xs text-slate-500">
                        Showing {{ filteredWinners.length }} of {{ winners.length }} winner records
                    </p>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>
