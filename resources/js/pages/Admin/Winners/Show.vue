
<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    winner: {
        type: Object,
        required: true,
    },
})

const statusConfig = {
    pending: {
        label: 'Awaiting Claim',
        class: 'bg-amber-500/10 text-amber-400 ring-amber-500/20',
        description: 'The winner has not submitted a prize claim yet.',
    },
    under_review: {
        label: 'Under Review',
        class: 'bg-blue-500/10 text-blue-400 ring-blue-500/20',
        description: 'The submitted prize claim is awaiting admin review.',
    },
    approved: {
        label: 'Approved',
        class: 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/20',
        description: 'The prize claim has been approved.',
    },
    rejected: {
        label: 'Rejected',
        class: 'bg-red-500/10 text-red-400 ring-red-500/20',
        description: 'The prize claim was rejected.',
    },
    fulfilled: {
        label: 'Fulfilled',
        class: 'bg-purple-500/10 text-purple-400 ring-purple-500/20',
        description: 'The prize has been marked as fulfilled.',
    },
}

const currentStatus = computed(() =>
    statusConfig[props.winner.claim_status] || {
        label: props.winner.claim_status || 'Unknown',
        class: 'bg-slate-500/10 text-slate-400 ring-slate-500/20',
        description: 'No status description available.',
    }
)

const timeline = computed(() => [
    {
        label: 'Prize Won',
        date: props.winner.won_at,
        description: 'This ticket was awarded the prize.',
        complete: !!props.winner.won_at,
    },
    {
        label: 'Claim Submitted',
        date: props.winner.claimed_at,
        description: 'The winner submitted their prize claim.',
        complete: !!props.winner.claimed_at,
    },
    {
        label: 'Prize Fulfilled',
        date: props.winner.fulfilled_at,
        description: 'The prize was marked as fulfilled.',
        complete: !!props.winner.fulfilled_at,
    },
])

function formatDate(value) {
    if (!value) return '—'

    return new Date(value).toLocaleString('en-US', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

function formatCurrency(value, code = 'USD') {
    if (value === null || value === undefined || value === '') return '—'

    try {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: code || 'USD',
            maximumFractionDigits: 2,
        }).format(Number(value))
    } catch {
        return `${code || 'USD'} ${value}`
    }
}

function methodLabel(value) {
    return value === 'instant_win' ? 'Instant Win' : 'Scheduled Draw'
}

function titleCase(value) {
    if (!value) return '—'

    return value
        .replace(/_/g, ' ')
        .replace(/\b\w/g, char => char.toUpperCase())
}
</script>

<template>
    <Head title="Winner Details" />

    <AdminLayout>
        <div class="min-h-screen space-y-6 pb-10">

            <!-- Header -->
            <div>
                <div class="flex flex-wrap items-center gap-2 text-sm text-slate-400">
                    <Link
                        :href="route('admin.winners.index')"
                        class="transition hover:text-amber-400"
                    >
                        Winners
                    </Link>
                    <span>/</span>
                    <span class="text-amber-400">Winner Details</span>
                </div>

                <div class="mt-4 flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                            Winner Details
                        </h1>
                        <p class="mt-1 text-sm text-slate-400">
                            Complete information about this winning ticket and prize.
                        </p>
                    </div>

                    <Link
                        :href="route('admin.winners.index')"
                        class="inline-flex w-fit items-center gap-2 rounded-lg border border-slate-700 px-4 py-2.5 text-sm font-medium text-slate-300 transition hover:border-slate-500 hover:bg-slate-800 hover:text-white"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m15 18-6-6 6-6" />
                        </svg>
                        Back to Winners
                    </Link>
                </div>
            </div>

            <!-- Winner Summary -->
            <div class="overflow-hidden rounded-xl border border-amber-500/20 bg-gradient-to-br from-amber-500/10 via-slate-800/70 to-slate-800/70">
                <div class="flex flex-col justify-between gap-5 p-6 sm:flex-row sm:items-center">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/10 text-amber-400">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8 21h8m-4-4v4m-6-17h12v5a6 6 0 01-12 0V4zm0 2H4v2a4 4 0 004 4m8-6h4v2a4 4 0 01-4 4" />
                            </svg>
                        </div>

                        <div>
                            <p class="text-sm text-amber-400">Winning Ticket</p>
                            <h2 class="mt-1 text-2xl font-bold text-white">
                                #{{ winner.ticket.number || '—' }}
                            </h2>
                            <p class="mt-1 text-sm text-slate-400">
                                {{ winner.prize.name }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-2 sm:text-right">
                        <span
                            class="inline-flex rounded-full px-3 py-1.5 text-xs font-semibold ring-1 ring-inset"
                            :class="currentStatus.class"
                        >
                            {{ currentStatus.label }}
                        </span>
                        <p class="text-xs text-slate-400">
                            {{ currentStatus.description }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

                <!-- Main Details -->
                <div class="space-y-6 xl:col-span-2">

                    <!-- Customer -->
                    <section class="overflow-hidden rounded-xl border border-slate-700/70 bg-slate-800/40">
                        <div class="border-b border-slate-700/70 px-5 py-4">
                            <h3 class="font-semibold text-white">Winner Information</h3>
                            <p class="mt-1 text-xs text-slate-500">
                                Customer associated with this winning ticket.
                            </p>
                        </div>

                        <div class="grid gap-5 p-5 sm:grid-cols-2">
                            <div>
                                <p class="text-xs text-slate-500">Full Name</p>
                                <p class="mt-1 font-medium text-white">
                                    {{ winner.customer.name || '—' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-500">Email Address</p>
                                <p class="mt-1 break-all font-medium text-slate-200">
                                    {{ winner.customer.email || '—' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-500">Phone Number</p>
                                <p class="mt-1 font-medium text-slate-200">
                                    {{ winner.customer.phone || '—' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-500">Country</p>
                                <p class="mt-1 font-medium text-slate-200">
                                    {{ winner.customer.country || '—' }}
                                </p>
                            </div>

                            <!-- <div>
                                <p class="text-xs text-slate-500">Customer Reference</p>
                                <p class="mt-1 break-all font-mono text-xs text-slate-300">
                                    {{ winner.customer.id || '—' }}
                                </p>
                            </div> -->
                        </div>
                    </section>

                    <!-- Competition -->
                    <section class="overflow-hidden rounded-xl border border-slate-700/70 bg-slate-800/40">
                        <div class="border-b border-slate-700/70 px-5 py-4">
                            <h3 class="font-semibold text-white">Competition Information</h3>
                            <p class="mt-1 text-xs text-slate-500">
                                Competition in which this ticket won.
                            </p>
                        </div>

                        <div class="grid gap-5 p-5 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <p class="text-xs text-slate-500">Competition Name</p>
                                <p class="mt-1 font-semibold text-white">
                                    {{ winner.competition.title || '—' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-500">Competition Status</p>
                                <p class="mt-1 font-medium text-slate-200">
                                    {{ titleCase(winner.competition.status) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-500">Competition Currency</p>
                                <p class="mt-1 font-medium text-slate-200">
                                    {{ winner.competition.currency || '—' }}
                                </p>
                            </div>

                            <!-- <div class="sm:col-span-2">
                                <p class="text-xs text-slate-500">Competition Reference</p>
                                <p class="mt-1 break-all font-mono text-xs text-slate-300">
                                    {{ winner.competition.id || '—' }}
                                </p>
                            </div> -->
                        </div>
                    </section>

                    <!-- Prize -->
                    <section class="overflow-hidden rounded-xl border border-slate-700/70 bg-slate-800/40">
                        <div class="border-b border-slate-700/70 px-5 py-4">
                            <h3 class="font-semibold text-white">Prize Information</h3>
                            <p class="mt-1 text-xs text-slate-500">
                                Details of the prize awarded to this winner.
                            </p>
                        </div>

                        <div class="flex flex-col gap-5 p-5 sm:flex-row">
                            <div
                                v-if="winner.prize.image"
                                class="h-40 w-full shrink-0 overflow-hidden rounded-lg border border-slate-700 bg-slate-900 sm:w-44"
                            >
                                <img
                                    :src="winner.prize.image"
                                    :alt="winner.prize.name"
                                    class="h-full w-full object-cover"
                                />
                            </div>

                            <div class="min-w-0 flex-1">
                                <h4 class="text-lg font-semibold text-white">
                                    {{ winner.prize.name || '—' }}
                                </h4>

                                <p v-if="winner.prize.description" class="mt-2 text-sm leading-6 text-slate-400">
                                    {{ winner.prize.description }}
                                </p>

                                <div class="mt-5 grid grid-cols-2 gap-5">
                                    <div>
                                        <p class="text-xs text-slate-500">Prize Value</p>
                                        <p class="mt-1 text-lg font-semibold text-amber-400">
                                            {{ formatCurrency(winner.prize.value, winner.prize.currency) }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-xs text-slate-500">Prize Type</p>
                                        <p class="mt-1 font-medium text-slate-200">
                                            {{ titleCase(winner.prize.type) }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-xs text-slate-500">Prize Currency</p>
                                        <p class="mt-1 font-medium text-slate-200">
                                            {{ winner.prize.currency || '—' }}
                                        </p>
                                    </div>

                                    <!-- <div>
                                        <p class="text-xs text-slate-500">Prize Reference</p>
                                        <p class="mt-1 font-mono text-xs text-slate-300">
                                            {{ winner.prize_id || '—' }}
                                        </p>
                                    </div> -->
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Ticket and Order -->
                    <section class="overflow-hidden rounded-xl border border-slate-700/70 bg-slate-800/40">
                        <div class="border-b border-slate-700/70 px-5 py-4">
                            <h3 class="font-semibold text-white">Ticket & Order Information</h3>
                            <p class="mt-1 text-xs text-slate-500">
                                Purchase reference and winning ticket details.
                            </p>
                        </div>

                        <div class="grid gap-5 p-5 sm:grid-cols-2">
                            <div>
                                <p class="text-xs text-slate-500">Ticket Number</p>
                                <p class="mt-1 font-mono font-semibold text-amber-400">
                                    #{{ winner.ticket.number || '—' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-500">Ticket Database ID</p>
                                <p class="mt-1 font-medium text-slate-200">
                                    {{ winner.ticket.id || '—' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-500">Order Reference</p>
                                <p class="mt-1 font-medium text-slate-200">
                                    {{ winner.order_id ? `#${winner.order_id}` : '—' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-500">Winning Record ID</p>
                                <p class="mt-1 font-medium text-slate-200">
                                    {{ winner.display_id || '—' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-500">Winning Method</p>
                                <p class="mt-1 font-medium text-slate-200">
                                    {{ methodLabel(winner.winning_method) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-500">Record Status</p>
                                <p class="mt-1 font-medium text-slate-200">
                                    {{ titleCase(winner.status) }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Claim Notes -->
                    <section
                        v-if="winner.rejection_reason || winner.notes"
                        class="overflow-hidden rounded-xl border border-slate-700/70 bg-slate-800/40"
                    >
                        <div class="border-b border-slate-700/70 px-5 py-4">
                            <h3 class="font-semibold text-white">Claim Notes</h3>
                            <p class="mt-1 text-xs text-slate-500">
                                Notes associated with this prize claim.
                            </p>
                        </div>

                        <div class="space-y-5 p-5">
                            <div v-if="winner.rejection_reason">
                                <p class="text-xs font-medium text-red-400">Rejection Reason</p>
                                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-300">
                                    {{ winner.rejection_reason }}
                                </p>
                            </div>

                            <div v-if="winner.notes">
                                <p class="text-xs font-medium text-slate-400">Admin Notes</p>
                                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-300">
                                    {{ winner.notes }}
                                </p>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Right Sidebar -->
                <div class="space-y-6">

                    <!-- Claim Status -->
                    <section class="rounded-xl border border-slate-700/70 bg-slate-800/40 p-5">
                        <h3 class="font-semibold text-white">Claim Status</h3>

                        <div class="mt-4 rounded-lg border border-slate-700 bg-slate-900/50 p-4">
                            <span
                                class="inline-flex rounded-full px-3 py-1.5 text-xs font-semibold ring-1 ring-inset"
                                :class="currentStatus.class"
                            >
                                {{ currentStatus.label }}
                            </span>

                            <p class="mt-3 text-sm leading-6 text-slate-400">
                                {{ currentStatus.description }}
                            </p>
                        </div>

                        <div class="mt-5 space-y-4">
                            <div class="flex justify-between gap-3 text-sm">
                                <span class="text-slate-500">Won On</span>
                                <span class="text-right text-slate-300">
                                    {{ formatDate(winner.won_at) }}
                                </span>
                            </div>

                            <div class="flex justify-between gap-3 text-sm">
                                <span class="text-slate-500">Claim Submitted</span>
                                <span class="text-right text-slate-300">
                                    {{ formatDate(winner.claimed_at) }}
                                </span>
                            </div>

                            <div class="flex justify-between gap-3 text-sm">
                                <span class="text-slate-500">Fulfilled On</span>
                                <span class="text-right text-slate-300">
                                    {{ formatDate(winner.fulfilled_at) }}
                                </span>
                            </div>
                        </div>
                    </section>

                    <!-- Timeline -->
                    <section class="rounded-xl border border-slate-700/70 bg-slate-800/40 p-5">
                        <h3 class="font-semibold text-white">Winner Timeline</h3>
                        <p class="mt-1 text-xs text-slate-500">
                            Progress of this prize.
                        </p>

                        <div class="mt-6 space-y-6">
                            <div
                                v-for="(item, index) in timeline"
                                :key="item.label"
                                class="relative flex gap-3"
                            >
                                <div class="relative flex flex-col items-center">
                                    <div
                                        class="z-10 flex h-7 w-7 shrink-0 items-center justify-center rounded-full"
                                        :class="item.complete
                                            ? 'bg-emerald-500/15 text-emerald-400'
                                            : 'bg-slate-700 text-slate-500'"
                                    >
                                        <svg
                                            v-if="item.complete"
                                            class="h-4 w-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 4 4L19 6" />
                                        </svg>
                                        <span v-else class="h-2 w-2 rounded-full bg-current"></span>
                                    </div>

                                    <div
                                        v-if="index !== timeline.length - 1"
                                        class="absolute top-7 h-full w-px bg-slate-700"
                                    ></div>
                                </div>

                                <div class="min-w-0 pb-1">
                                    <p
                                        class="text-sm font-medium"
                                        :class="item.complete ? 'text-white' : 'text-slate-500'"
                                    >
                                        {{ item.label }}
                                    </p>

                                    <p class="mt-1 text-xs leading-5 text-slate-500">
                                        {{ item.description }}
                                    </p>

                                    <p v-if="item.date" class="mt-1 text-xs text-slate-400">
                                        {{ formatDate(item.date) }}
                                    </p>
                                    <p v-else class="mt-1 text-xs text-slate-600">
                                        Not yet completed
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Admin Guidance -->
                    <section class="rounded-xl border border-blue-500/20 bg-blue-500/5 p-5">
                        <div class="flex items-center gap-2 text-blue-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <circle cx="12" cy="12" r="9" stroke-width="1.7" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 11v5m0-8h.01" />
                            </svg>
                            <h3 class="text-sm font-semibold">Admin Information</h3>
                        </div>

                        <p class="mt-3 text-sm leading-6 text-slate-400">
                            This page is for monitoring winner records. To review, approve,
                            reject, or fulfil a prize claim, use the Prize Claims module.
                        </p>

                        <Link
                            :href="route('admin.prize-claims.index')"
                            class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-blue-400 transition hover:text-blue-300"
                        >
                            Go to Prize Claims
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 12h14m-6-6 6 6-6 6" />
                            </svg>
                        </Link>
                    </section>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
