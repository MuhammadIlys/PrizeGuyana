
<script setup>
import { computed, ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    competitions: {
        type: Array,
        default: () => [],
    },
})

const search = ref('')
const statusFilter = ref('all')

const filteredCompetitions = computed(() => {
    return props.competitions.filter((competition) => {
        const matchesSearch = competition.title
            .toLowerCase()
            .includes(search.value.toLowerCase().trim())

        const matchesStatus =
            statusFilter.value === 'all' ||
            competition.status === statusFilter.value

        return matchesSearch && matchesStatus
    })
})

const statistics = computed(() => ({
    total: props.competitions.length,
    pending: props.competitions.filter(
        (competition) => competition.status === 'active'
    ).length,
    completed: props.competitions.filter(
        (competition) => competition.status === 'completed'
    ).length,
    winners: props.competitions.reduce(
        (total, competition) => total + competition.total_winners,
        0
    ),
}))

const statusStyles = (status) => {
    const styles = {
        active: 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/20',
        completed: 'bg-blue-500/10 text-blue-400 ring-blue-500/20',
        draft: 'bg-amber-500/10 text-amber-400 ring-amber-500/20',
        cancelled: 'bg-red-500/10 text-red-400 ring-red-500/20',
    }

    return styles[status] || 'bg-slate-500/10 text-slate-400 ring-slate-500/20'
}

const statusLabel = (status) => {
    return status.charAt(0).toUpperCase() + status.slice(1)
}

const formatDate = (date) => {
    if (!date) return '—'

    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    })
}
</script>

<template>
    <Head title="Draw Management" />

    <AdminLayout>
        <div class="min-h-screen space-y-7 bg-slate-950 px-4 py-6 text-slate-100 sm:px-6 lg:px-8">

            <!-- Page Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-amber-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                        Competition Management
                    </div>

                    <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                        Draw Management
                    </h1>

                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-400">
                        Manage scheduled competition draws, review winners and monitor draw progress.
                    </p>
                </div>

                <div class="flex items-center gap-2 rounded-xl border border-slate-800 bg-slate-900 px-4 py-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-500/10 text-amber-400">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 7v5l3 2" stroke-linecap="round" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Scheduled Draws</p>
                        <p class="text-sm font-semibold text-white">
                            {{ statistics.total }} Competitions
                        </p>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-400">Total Competitions</p>
                        <span class="rounded-lg bg-slate-800 p-2 text-slate-300">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="3" y="4" width="18" height="16" rx="2" />
                                <path d="M8 8h8M8 12h8M8 16h4" stroke-linecap="round" />
                            </svg>
                        </span>
                    </div>
                    <p class="mt-4 text-3xl font-bold text-white">{{ statistics.total }}</p>
                    <p class="mt-1 text-xs text-slate-500">With scheduled draw prizes or results</p>
                </div>

                <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-400">Active Draws</p>
                        <span class="rounded-lg bg-emerald-500/10 p-2 text-emerald-400">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="12" cy="12" r="9" />
                                <path d="M12 7v5l3 2" stroke-linecap="round" />
                            </svg>
                        </span>
                    </div>
                    <p class="mt-4 text-3xl font-bold text-white">{{ statistics.pending }}</p>
                    <p class="mt-1 text-xs text-slate-500">Awaiting competition completion</p>
                </div>

                <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-400">Completed Draws</p>
                        <span class="rounded-lg bg-blue-500/10 p-2 text-blue-400">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="m5 12 4 4L19 6" stroke-linecap="round" stroke-linejoin="round" />
                                <circle cx="12" cy="12" r="10" />
                            </svg>
                        </span>
                    </div>
                    <p class="mt-4 text-3xl font-bold text-white">{{ statistics.completed }}</p>
                    <p class="mt-1 text-xs text-slate-500">Successfully completed competitions</p>
                </div>

                <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-400">Selected Winners</p>
                        <span class="rounded-lg bg-amber-500/10 p-2 text-amber-400">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3Z" stroke-linejoin="round" />
                            </svg>
                        </span>
                    </div>
                    <p class="mt-4 text-3xl font-bold text-white">{{ statistics.winners }}</p>
                    <p class="mt-1 text-xs text-slate-500">Scheduled draw winner records</p>
                </div>
            </div>

            <!-- Competition List -->
            <section class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900">

                <!-- Toolbar -->
                <div class="flex flex-col gap-4 border-b border-slate-800 p-5 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-white">Scheduled Draw Competitions</h2>
                        <p class="mt-1 text-xs text-slate-500">
                            Review draw readiness and access competition results.
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="7" />
                                <path d="m16 16 4 4" stroke-linecap="round" />
                            </svg>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search competitions..."
                                class="w-full rounded-xl border border-slate-700 bg-slate-950 py-2.5 pl-9 pr-4 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-amber-500 sm:w-64"
                            />
                        </div>

                        <select
                            v-model="statusFilter"
                            class="rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-sm text-slate-300 outline-none focus:border-amber-500"
                        >
                            <option value="all">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="draft">Draft</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>

                <!-- Table -->
                <div v-if="filteredCompetitions.length" class="overflow-x-auto">
                    <table class="w-full min-w-[850px] text-left">
                        <thead class="bg-slate-950/70">
                            <tr class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                <th class="px-6 py-4">Competition</th>
                                <th class="px-6 py-4">Prizes</th>
                                <th class="px-6 py-4">Required Winners</th>
                                <th class="px-6 py-4">Selected Winners</th>
                                <th class="px-6 py-4">End Date</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-800">
                            <tr
                                v-for="competition in filteredCompetitions"
                                :key="competition.encrypted_id"
                                class="transition hover:bg-slate-800/40"
                            >
                                <td class="px-6 py-5">
                                    <div class="font-semibold text-white">
                                        {{ competition.title }}
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        Scheduled draw
                                    </div>
                                </td>

                                <td class="px-6 py-5">
                                    <span class="text-sm font-medium text-slate-200">
                                        {{ competition.total_prizes }}
                                    </span>
                                </td>

                                <td class="px-6 py-5">
                                    <span class="text-sm text-slate-300">
                                        {{ competition.required_winners }}
                                    </span>
                                </td>

                                <td class="px-6 py-5">
                                    <span class="text-sm font-semibold text-amber-400">
                                        {{ competition.total_winners }}
                                    </span>
                                </td>

                                <td class="px-6 py-5 text-sm text-slate-400">
                                    {{ formatDate(competition.ends_at) }}
                                </td>

                                <td class="px-6 py-5">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset"
                                        :class="statusStyles(competition.status)"
                                    >
                                        {{ statusLabel(competition.status) }}
                                    </span>
                                </td>

                                <td class="px-6 py-5 text-right">
                                    <Link
                                        :href="route('admin.draws.show', competition.encrypted_id)"
                                        class="inline-flex items-center gap-2 rounded-lg border border-amber-500/30 bg-amber-500/10 px-3.5 py-2 text-xs font-semibold text-amber-400 transition hover:border-amber-400 hover:bg-amber-500/20"
                                    >
                                        View Draw
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M5 12h14m-6-6 6 6-6 6" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-else class="flex flex-col items-center justify-center px-6 py-20 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl border border-slate-800 bg-slate-950 text-slate-500">
                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 7v5l3 2" stroke-linecap="round" />
                        </svg>
                    </div>

                    <h3 class="mt-5 text-base font-semibold text-white">
                        {{ search || statusFilter !== 'all' ? 'No matching competitions' : 'No scheduled draws yet' }}
                    </h3>

                    <p class="mt-2 max-w-sm text-sm leading-6 text-slate-500">
                        {{ search || statusFilter !== 'all'
                            ? 'Try adjusting your search or status filter.'
                            : 'Competitions with active scheduled draw prizes will appear here.' }}
                    </p>

                    <button
                        v-if="search || statusFilter !== 'all'"
                        type="button"
                        @click="search = ''; statusFilter = 'all'"
                        class="mt-5 text-sm font-semibold text-amber-400 transition hover:text-amber-300"
                    >
                        Clear filters
                    </button>
                </div>

                <!-- Footer -->
                <div class="flex flex-col gap-2 border-t border-slate-800 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs text-slate-500">
                        Showing {{ filteredCompetitions.length }} of {{ competitions.length }} competitions
                    </p>
                    <p class="text-xs text-slate-600">
                        PrizeGuyana Draw Management
                    </p>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>
