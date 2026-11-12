
<script setup>
import { computed, ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    winners: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            method: 'all',
            status: 'all',
        }),
    },
})

const search = ref(props.filters.search || '')
const method = ref(props.filters.method || 'all')
const status = ref(props.filters.status || 'all')

let searchTimeout

watch(search, () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(applyFilters, 400)
})

watch([method, status], applyFilters)

function applyFilters() {
    router.get(route('admin.winners.index'), {
        search: search.value || undefined,
        method: method.value !== 'all' ? method.value : undefined,
        status: status.value !== 'all' ? status.value : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

function clearFilters() {
    search.value = ''
    method.value = 'all'
    status.value = 'all'
    applyFilters()
}

const hasFilters = computed(() =>
    search.value !== '' || method.value !== 'all' || status.value !== 'all'
)

function statusLabel(value) {
    const labels = {
        pending: 'Awaiting Claim',
        under_review: 'Under Review',
        approved: 'Approved',
        rejected: 'Rejected',
        fulfilled: 'Fulfilled',
    }

    return labels[value] || value
}

function statusClass(value) {
    const classes = {
        pending: 'bg-amber-500/10 text-amber-400 ring-amber-500/20',
        under_review: 'bg-blue-500/10 text-blue-400 ring-blue-500/20',
        approved: 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/20',
        rejected: 'bg-red-500/10 text-red-400 ring-red-500/20',
        fulfilled: 'bg-purple-500/10 text-purple-400 ring-purple-500/20',
    }

    return classes[value] || 'bg-slate-500/10 text-slate-400 ring-slate-500/20'
}

function methodLabel(value) {
    return value === 'instant_win' ? 'Instant Win' : 'Scheduled Draw'
}

function formatDate(value) {
    if (!value) return '—'

    return new Date(value).toLocaleDateString('en-US', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    })
}

function formatNumber(value) {
    return Number(value || 0).toLocaleString()
}
</script>

<template>
    <Head title="Winners" />

    <AdminLayout>
        <div class="min-h-screen space-y-6 pb-10">

            <!-- Header -->
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <div class="flex items-center gap-2 text-sm text-slate-400">
                        <span>Management</span>
                        <span>/</span>
                        <span class="text-amber-400">Winners</span>
                    </div>

                    <h1 class="mt-2 text-2xl font-bold tracking-tight text-white sm:text-3xl">
                        Winners
                    </h1>

                    <p class="mt-1 text-sm text-slate-400">
                        View all competition winners and monitor their prize claim progress.
                    </p>
                </div>

                <div class="flex items-center gap-2 rounded-lg border border-slate-700 bg-slate-800/60 px-3 py-2 text-sm text-slate-300">
                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                    Read-only overview
                </div>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <div class="rounded-xl border border-slate-700/70 bg-slate-800/60 p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-slate-400">Total Winners</p>
                        <span class="rounded-lg bg-amber-500/10 p-2 text-amber-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-white">
                        {{ formatNumber(stats.total) }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">All winning tickets</p>
                </div>

                <div class="rounded-xl border border-slate-700/70 bg-slate-800/60 p-5">
                    <p class="text-sm text-slate-400">Instant Wins</p>
                    <p class="mt-3 text-3xl font-bold text-white">
                        {{ formatNumber(stats.instant_win) }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">Instant win prizes</p>
                </div>

                <div class="rounded-xl border border-slate-700/70 bg-slate-800/60 p-5">
                    <p class="text-sm text-slate-400">Draw Winners</p>
                    <p class="mt-3 text-3xl font-bold text-white">
                        {{ formatNumber(stats.draw) }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">Scheduled draw prizes</p>
                </div>

                <div class="rounded-xl border border-slate-700/70 bg-slate-800/60 p-5">
                    <p class="text-sm text-slate-400">Awaiting Claims</p>
                    <p class="mt-3 text-3xl font-bold text-amber-400">
                        {{ formatNumber(stats.pending_claims) }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">Winners not yet claimed</p>
                </div>

                <div class="rounded-xl border border-slate-700/70 bg-slate-800/60 p-5">
                    <p class="text-sm text-slate-400">Fulfilled</p>
                    <p class="mt-3 text-3xl font-bold text-emerald-400">
                        {{ formatNumber(stats.fulfilled) }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">Prizes fulfilled</p>
                </div>
            </div>

            <!-- Winners Table -->
            <div class="overflow-hidden rounded-xl border border-slate-700/70 bg-slate-800/40">

                <!-- Table Header -->
                <div class="border-b border-slate-700/70 p-5">
                    <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">
                        <div>
                            <h2 class="text-lg font-semibold text-white">Winner Records</h2>
                            <p class="mt-1 text-sm text-slate-400">
                                Search and filter all awarded prizes.
                            </p>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row">
                            <div class="relative">
                                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <circle cx="11" cy="11" r="7" stroke-width="1.8" />
                                    <path stroke-linecap="round" stroke-width="1.8" d="m16 16 4 4" />
                                </svg>
                                <input
                                    v-model="search"
                                    type="search"
                                    placeholder="Search winners..."
                                    class="w-full rounded-lg border border-slate-700 bg-slate-900/70 py-2.5 pl-9 pr-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-amber-500 sm:w-64"
                                />
                            </div>

                            <select
                                v-model="method"
                                class="rounded-lg border border-slate-700 bg-slate-900/70 px-3 py-2.5 text-sm text-slate-300 outline-none focus:border-amber-500"
                            >
                                <option value="all">All Methods</option>
                                <option value="instant_win">Instant Win</option>
                                <option value="draw">Scheduled Draw</option>
                            </select>

                            <select
                                v-model="status"
                                class="rounded-lg border border-slate-700 bg-slate-900/70 px-3 py-2.5 text-sm text-slate-300 outline-none focus:border-amber-500"
                            >
                                <option value="all">All Statuses</option>
                                <option value="pending">Awaiting Claim</option>
                                <option value="under_review">Under Review</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="fulfilled">Fulfilled</option>
                            </select>
                        </div>
                    </div>

                    <div v-if="hasFilters" class="mt-3 flex items-center justify-between">
                        <p class="text-xs text-slate-500">Filters are applied automatically.</p>
                        <button
                            type="button"
                            @click="clearFilters"
                            class="text-xs font-medium text-amber-400 transition hover:text-amber-300"
                        >
                            Clear filters
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[950px] text-left">
                        <thead class="border-b border-slate-700/70 bg-slate-900/40">
                            <tr class="text-xs uppercase tracking-wider text-slate-500">
                                <th class="px-5 py-4 font-medium">Winner</th>
                                <th class="px-5 py-4 font-medium">Competition</th>
                                <th class="px-5 py-4 font-medium">Prize</th>
                                <th class="px-5 py-4 font-medium">Ticket</th>
                                <th class="px-5 py-4 font-medium">Method</th>
                                <th class="px-5 py-4 font-medium">Claim Status</th>
                                <th class="px-5 py-4 font-medium">Won Date</th>
                                <th class="px-5 py-4 text-right font-medium">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-700/50">
                            <tr
                                v-for="winner in winners.data"
                                :key="winner.id"
                                class="transition hover:bg-slate-700/20"
                            >
                                <td class="px-5 py-4">
                                    <div class="font-medium text-white">
                                        {{ winner.customer_name }}
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ winner.customer_email }}
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="max-w-[190px] truncate text-sm text-slate-200">
                                        {{ winner.competition_title }}
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="max-w-[180px] truncate text-sm font-medium text-white">
                                        {{ winner.prize_name }}
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="rounded-md border border-slate-700 bg-slate-900 px-2.5 py-1 font-mono text-xs text-amber-400">
                                        {{ winner.ticket_number }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        class="text-xs font-medium"
                                        :class="winner.winning_method === 'instant_win' ? 'text-purple-400' : 'text-blue-400'"
                                    >
                                        {{ methodLabel(winner.winning_method) }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset"
                                        :class="statusClass(winner.claim_status)"
                                    >
                                        {{ statusLabel(winner.claim_status) }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-400">
                                    {{ formatDate(winner.won_at) }}
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <Link
                                        :href="route('admin.winners.show', winner.id)"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-700 px-3 py-2 text-xs font-medium text-slate-300 transition hover:border-amber-500/50 hover:bg-amber-500/10 hover:text-amber-400"
                                    >
                                        View Details
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m9 18 6-6-6-6" />
                                        </svg>
                                    </Link>
                                </td>
                            </tr>

                            <tr v-if="!winners.data?.length">
                                <td colspan="8" class="px-5 py-16 text-center">
                                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-700/40 text-slate-400">
                                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>

                                    <h3 class="mt-4 font-semibold text-white">
                                        No winners found
                                    </h3>
                                    <p class="mt-1 text-sm text-slate-500">
                                        Winners will appear here once prizes have been awarded.
                                    </p>

                                    <button
                                        v-if="hasFilters"
                                        type="button"
                                        @click="clearFilters"
                                        class="mt-4 text-sm font-medium text-amber-400 hover:text-amber-300"
                                    >
                                        Clear filters
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="winners.links?.length > 3"
                    class="flex flex-col justify-between gap-4 border-t border-slate-700/70 px-5 py-4 sm:flex-row sm:items-center"
                >
                    <p class="text-sm text-slate-500">
                        Showing
                        <span class="font-medium text-slate-300">{{ winners.from || 0 }}</span>
                        to
                        <span class="font-medium text-slate-300">{{ winners.to || 0 }}</span>
                        of
                        <span class="font-medium text-slate-300">{{ winners.total || 0 }}</span>
                        winners
                    </p>

                    <div class="flex flex-wrap items-center gap-1">
                        <template v-for="(link, index) in winners.links" :key="index">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                preserve-scroll
                                class="rounded-lg border px-3 py-2 text-xs transition"
                                :class="link.active
                                    ? 'border-amber-500 bg-amber-500 text-slate-950'
                                    : 'border-slate-700 text-slate-400 hover:border-slate-500 hover:text-white'"
                            >
                                <span v-html="link.label"></span>
                            </Link>

                            <span
                                v-else
                                class="rounded-lg border border-slate-800 px-3 py-2 text-xs text-slate-600"
                            >
                                <span v-html="link.label"></span>
                            </span>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
