<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ref, watch } from 'vue'


const props = defineProps({
    claims: {
        type: Object,
        required: true,
    },
    statistics: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            status: '',
        }),
    },
})

const search = ref(props.filters.search || '')
const status = ref(props.filters.status || '')

let searchTimeout = null

const applyFilters = () => {
    clearTimeout(searchTimeout)

    searchTimeout = setTimeout(() => {
        router.get(
            route('admin.prize-claims.index'),
            {
                search: search.value || undefined,
                status: status.value || undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        )
    }, 350)
}

watch(search, applyFilters)
watch(status, applyFilters)

const formatDate = (date) => {
    if (!date) return '—'

    return new Date(date).toLocaleDateString('en-US', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    })
}

const formatCurrency = (value, currency) => {
    if (value === null || value === undefined) return '—'

    return `${currency || ''} ${Number(value).toLocaleString()}`
}

const statusConfig = (claim) => {
    if (claim.status === 'won' && claim.claimed_at) {
        return {
            label: 'Pending Review',
            class: 'border-amber-500/20 bg-amber-500/10 text-amber-400',
        }
    }

    const statuses = {
        claimed: {
            label: 'Approved',
            class: 'border-blue-500/20 bg-blue-500/10 text-blue-400',
        },
        fulfilled: {
            label: 'Fulfilled',
            class: 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400',
        },
        cancelled: {
            label: 'Rejected',
            class: 'border-red-500/20 bg-red-500/10 text-red-400',
        },
    }

    return statuses[claim.status] || {
        label: claim.status,
        class: 'border-neutral-700 bg-neutral-800 text-neutral-400',
    }
}

const clearFilters = () => {
    search.value = ''
    status.value = ''
}
</script>

<template>

    <Head title="Prize Claims" />

    <AdminLayout>
        <div class="min-h-screen space-y-6 bg-[#0b0b0b] p-4 text-white sm:p-6">

            <!-- Header -->
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <div class="flex items-center gap-2">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/10">
                            <svg class="h-5 w-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M9 12l2 2 4-4m5-1a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>

                        <h1 class="text-2xl font-bold">Prize Claims</h1>
                    </div>

                    <p class="mt-2 text-sm text-neutral-400">
                        Review and manage customer prize claim submissions.
                    </p>
                </div>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">

                <div class="rounded-xl border border-neutral-800 bg-[#121212] p-5">
                    <p class="text-sm text-neutral-400">Total Claims</p>
                    <p class="mt-3 text-3xl font-bold text-white">
                        {{ statistics.total || 0 }}
                    </p>
                    <p class="mt-1 text-xs text-neutral-500">All submitted claims</p>
                </div>

                <div class="rounded-xl border border-amber-500/20 bg-[#121212] p-5">
                    <p class="text-sm text-neutral-400">Pending Review</p>
                    <p class="mt-3 text-3xl font-bold text-amber-400">
                        {{ statistics.pending_review || 0 }}
                    </p>
                    <p class="mt-1 text-xs text-neutral-500">Awaiting admin review</p>
                </div>

                <div class="rounded-xl border border-blue-500/20 bg-[#121212] p-5">
                    <p class="text-sm text-neutral-400">Approved</p>
                    <p class="mt-3 text-3xl font-bold text-blue-400">
                        {{ statistics.approved || 0 }}
                    </p>
                    <p class="mt-1 text-xs text-neutral-500">Claims approved</p>
                </div>

                <div class="rounded-xl border border-emerald-500/20 bg-[#121212] p-5">
                    <p class="text-sm text-neutral-400">Fulfilled</p>
                    <p class="mt-3 text-3xl font-bold text-emerald-400">
                        {{ statistics.fulfilled || 0 }}
                    </p>
                    <p class="mt-1 text-xs text-neutral-500">Prizes delivered</p>
                </div>

                <div class="rounded-xl border border-red-500/20 bg-[#121212] p-5">
                    <p class="text-sm text-neutral-400">Rejected</p>
                    <p class="mt-3 text-3xl font-bold text-red-400">
                        {{ statistics.rejected || 0 }}
                    </p>
                    <p class="mt-1 text-xs text-neutral-500">Claims not approved</p>
                </div>

            </div>

            <!-- Claims Table -->
            <div class="overflow-hidden rounded-xl border border-neutral-800 bg-[#121212]">

                <!-- Filters -->
                <div
                    class="flex flex-col gap-3 border-b border-neutral-800 p-4 lg:flex-row lg:items-center lg:justify-between">

                    <div>
                        <h2 class="font-semibold text-white">Submitted Claims</h2>
                        <p class="mt-1 text-xs text-neutral-500">
                            Review customer requests and prize fulfilment status.
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">

                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-500" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="m21 21-4.35-4.35M19 10.5a8.5 8.5 0 11-17 0 8.5 8.5 0 0117 0z" />
                            </svg>

                            <input v-model="search" type="text" placeholder="Search claims..."
                                class="w-full rounded-lg border border-neutral-700 bg-[#0b0b0b] py-2.5 pl-9 pr-3 text-sm text-white outline-none transition placeholder:text-neutral-600 focus:border-amber-500 sm:w-64" />
                        </div>

                        <select v-model="status"
                            class="rounded-lg border border-neutral-700 bg-[#0b0b0b] px-3 py-2.5 text-sm text-neutral-300 outline-none focus:border-amber-500">
                            <option value="">All Statuses</option>
                            <option value="won">Pending Review</option>
                            <option value="claimed">Approved</option>
                            <option value="fulfilled">Fulfilled</option>
                            <option value="cancelled">Rejected</option>
                        </select>

                        <button v-if="search || status" @click="clearFilters" type="button"
                            class="rounded-lg border border-neutral-700 px-3 py-2 text-sm text-neutral-400 transition hover:bg-neutral-800 hover:text-white">
                            Clear
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">


                    <table class="w-full min-w-[1050px] text-left">
                        <thead class="border-b border-neutral-800 bg-[#171717]">
                            <tr class="text-xs uppercase tracking-wider text-neutral-500">
                                <th class="px-5 py-4 font-semibold">Prize</th>
                                <th class="px-5 py-4 font-semibold">Customer</th>
                                <th class="px-5 py-4 font-semibold">Competition</th>
                                <th class="px-5 py-4 font-semibold">Ticket</th>
                                <th class="px-5 py-4 font-semibold">Claim Date</th>
                                <th class="px-5 py-4 font-semibold">Status</th>
                                <th class="px-5 py-4 text-right font-semibold">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-neutral-800">
                            <tr v-for="claim in claims.data" :key="claim.id" class="transition hover:bg-white/[0.02]">
                                <!-- Prize -->
                                <td class="px-5 py-4">
                                    <p class="text-sm font-semibold text-white">
                                        {{ claim.prize_name }}
                                    </p>
                                    <p class="mt-1 text-xs text-neutral-500">
                                        {{ formatCurrency(claim.prize_value, claim.currency) }}
                                    </p>
                                </td>


                                <!-- Customer -->
                                <td class="px-5 py-4">
                                    <Link :href="route('admin.customers.show', claim.customer_encrypted_id)"
                                        class="text-sm font-semibold text-neutral-200 transition hover:text-amber-400">
                                        {{ claim.customer_name }}
                                    </Link>
                                </td>

                                <!-- Competition -->
                                <td class="px-5 py-4">
                                    <p class="max-w-[190px] truncate text-sm text-neutral-300">
                                        {{ claim.competition_title }}
                                    </p>
                                </td>

                                <!-- Ticket -->
                                <td class="px-5 py-4">
                                    <span
                                        class="rounded-md border border-neutral-700 bg-neutral-800/60 px-2.5 py-1 font-mono text-xs text-amber-400">
                                        {{ claim.ticket_number }}
                                    </span>
                                </td>

                                <!-- Claim Date -->
                                <td class="px-5 py-4">
                                    <p class="text-sm text-neutral-300">
                                        {{ formatDate(claim.claimed_at) }}
                                    </p>
                                </td>

                                <!-- Status -->
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold"
                                        :class="statusConfig(claim).class">
                                        {{ statusConfig(claim).label }}
                                    </span>
                                </td>

                                <!-- Action -->
                                <td class="px-5 py-4 text-right">
                                    <Link :href="route('admin.prize-claims.show', claim.id)"
                                        class="inline-flex items-center gap-2 rounded-lg border border-amber-500/30 bg-amber-500/10 px-4 py-2 text-xs font-semibold text-amber-400 transition duration-200 hover:border-amber-400/50 hover:bg-amber-500/20 hover:text-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-500/40">
                                        View Details

                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </Link>
                                </td>
                            </tr>

                            <!-- Empty State -->
                            <tr v-if="!claims.data.length">
                                <td colspan="7" class="px-5 py-16 text-center">
                                    <div
                                        class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-neutral-800">
                                        <svg class="h-6 w-6 text-neutral-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M9 12l2 2 4-4m5-1a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>

                                    <h3 class="mt-4 font-semibold text-white">
                                        No prize claims found
                                    </h3>

                                    <p class="mt-1 text-sm text-neutral-500">
                                        Customer-submitted prize claims will appear here.
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div>

                <!-- Pagination -->
                <div v-if="claims.links && claims.links.length > 3"
                    class="flex flex-col gap-3 border-t border-neutral-800 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs text-neutral-500">
                        Showing {{ claims.from || 0 }} to {{ claims.to || 0 }}
                        of {{ claims.total }} claims
                    </p>

                    <div class="flex flex-wrap gap-1">
                        <template v-for="(link, index) in claims.links" :key="index">
                            <Link v-if="link.url" :href="link.url" preserve-scroll
                                class="rounded-lg border px-3 py-2 text-xs transition" :class="link.active
                                    ? 'border-amber-500/30 bg-amber-500/10 text-amber-400'
                                    : 'border-neutral-800 text-neutral-400 hover:bg-neutral-800 hover:text-white'">
                                <span v-html="link.label"></span>
                            </Link>

                            <span v-else
                                class="cursor-not-allowed rounded-lg border border-neutral-800 px-3 py-2 text-xs text-neutral-700"
                                v-html="link.label"></span>
                        </template>
                    </div>
                </div>

            </div>

        </div>
    </AdminLayout>
</template>
