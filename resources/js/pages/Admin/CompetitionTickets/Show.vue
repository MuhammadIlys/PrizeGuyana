
<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { computed, ref } from 'vue'
import axios from 'axios'

defineOptions({
    layout: AdminLayout,
})

const props = defineProps({
    competition: {
        type: Object,
        required: true,
    },
    tickets: {
        type: Object,
        default: () => ({
            data: [],
            links: [],
            total: 0,
            from: 0,
            to: 0,
        }),
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            status: '',
        }),
    },
    prizes: {
        type: Array,
        default: () => [],
    },
    allocation: {
        type: Object,
        default: () => ({
            exists: false,
            total_tickets: 0,
            available_tickets: 0,
            assigned_tickets: 0,
        }),
    },
})

const search = ref(props.filters?.search || '')
const selectedStatus = ref(props.filters?.status || '')

const previewAllocations = ref([])
const previewTotal = ref(0)

const isPreviewing = ref(false)
const isSaving = ref(false)

const allocationError = ref('')
const allocationSuccess = ref('')

const hasPreview = computed(() => previewAllocations.value.length > 0)

const ticketList = computed(() =>
    Array.isArray(props.tickets?.data)
        ? props.tickets.data
        : []
)

const paginationLinks = computed(() =>
    Array.isArray(props.tickets?.links)
        ? props.tickets.links
        : []
)

const totalTickets = computed(() =>
    Number(props.tickets?.total || 0)
)

const currentPageCount = computed(() =>
    ticketList.value.length
)

const statusOptions = [
    'available',
    'assigned',
    'unavailable',
]

const totalInstantWinPrizes = computed(() =>
    props.prizes.length
)

const totalRequiredAllocations = computed(() =>
    props.prizes.reduce(
        (total, prize) => total + Number(prize.quantity || 0),
        0
    )
)

const totalAllocated = computed(() =>
    props.prizes.reduce(
        (total, prize) => total + Number(prize.allocated_quantity || 0),
        0
    )
)

const allPrizesAllocated = computed(() =>
    props.prizes.length > 0 &&
    props.prizes.every(
        prize => Number(prize.allocated_quantity) === Number(prize.quantity)
    )
)

const canPreviewAllocation = computed(() =>
    props.competition.instant_win_enabled &&
    props.prizes.length > 0 &&
    !props.allocation?.exists &&
    !isPreviewing.value &&
    !isSaving.value &&
    props.allocation?.total_tickets === Number(props.competition.total_entries) &&
    totalRequiredAllocations.value > 0 &&
    props.allocation?.available_tickets >= totalRequiredAllocations.value
)

const ticketListUrl = computed(() =>
    route('admin.tickets.show', props.competition.encrypted_id)
)

const previewUrl = computed(() =>
    route('admin.tickets.preview-allocation', props.competition.encrypted_id)
)

const allocateUrl = computed(() =>
    route('admin.tickets.allocate-winning-tickets', props.competition.encrypted_id)
)

const backUrl = computed(() =>
    route('admin.tickets.index')
)

const applyFilters = () => {
    router.get(
        ticketListUrl.value,
        {
            search: search.value || undefined,
            status: selectedStatus.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    )
}

const resetFilters = () => {
    search.value = ''
    selectedStatus.value = ''

    applyFilters()
}

const formatDate = (date) => {
    if (!date) return '—'

    const parsedDate = new Date(date)

    if (Number.isNaN(parsedDate.getTime())) return '—'

    return parsedDate.toLocaleString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

const formatNumber = (value) =>
    Number(value || 0).toLocaleString('en-US')

const formatCurrency = (value, currency = 'USD') => {
    const amount = Number(value || 0)

    return `${currency} ${amount.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`
}

const statusClasses = (status) => {
    const classes = {
        available: 'border-blue-500/20 bg-blue-500/10 text-blue-400',
        assigned: 'border-amber-500/20 bg-amber-500/10 text-amber-400',
        unavailable: 'border-neutral-700 bg-neutral-800 text-neutral-400',
    }

    return classes[String(status || '').toLowerCase()]
        || 'border-neutral-700 bg-neutral-800 text-neutral-400'
}

const winningStatusClasses = (status) => {
    const classes = {
        pending: 'border-amber-500/20 bg-amber-500/10 text-amber-400',
        won: 'border-green-500/20 bg-green-500/10 text-green-400',
        claimed: 'border-blue-500/20 bg-blue-500/10 text-blue-400',
        fulfilled: 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400',
        cancelled: 'border-red-500/20 bg-red-500/10 text-red-400',
    }

    return classes[String(status || '').toLowerCase()]
        || 'border-neutral-700 bg-neutral-800 text-neutral-400'
}

const statusLabel = (status) => {
    if (!status) return '—'

    return String(status)
        .replaceAll('_', ' ')
        .replace(/\b\w/g, character => character.toUpperCase())
}

/**
 * Generate the random Instant Win allocation preview.
 * This action does not save anything to the database.
 */
const generateAllocationPreview = async () => {
    allocationError.value = ''
    allocationSuccess.value = ''

    previewAllocations.value = []
    previewTotal.value = 0

    if (!canPreviewAllocation.value) {
        allocationError.value = 'This competition is not currently eligible for Instant Win allocation.'
        return
    }

    isPreviewing.value = true

    try {
        const response = await axios.post(previewUrl.value)

        if (!response.data?.success) {
            throw new Error(
                response.data?.message || 'Unable to generate allocation preview.'
            )
        }

        previewAllocations.value = response.data.allocations || []
        previewTotal.value = Number(response.data.total_allocations || 0)

        if (!previewAllocations.value.length) {
            throw new Error('No ticket allocations were returned.')
        }
    } catch (error) {
        allocationError.value =
            error.response?.data?.message ||
            error.message ||
            'Something went wrong while generating the preview.'
    } finally {
        isPreviewing.value = false
    }
}

/**
 * Save the confirmed ticket allocations.
 */
const confirmAllocation = () => {
    allocationError.value = ''
    allocationSuccess.value = ''

    if (!hasPreview.value || isSaving.value) {
        return
    }

    const confirmed = window.confirm(
        `You are about to allocate ${formatNumber(previewTotal.value)} winning tickets. These assignments cannot be replaced through this screen. Continue?`
    )

    if (!confirmed) return

    isSaving.value = true

    router.post(
        allocateUrl.value,
        {
            allocations: previewAllocations.value.map(allocation => ({
                prize_id: allocation.prize_id,
                ticket_ids: allocation.ticket_ids,
            })),
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                previewAllocations.value = []
                previewTotal.value = 0
                allocationSuccess.value = 'Instant Win tickets allocated successfully.'
            },

            onError: (errors) => {
                allocationError.value =
                    Object.values(errors).flat()[0] ||
                    'Unable to save Instant Win allocations.'
            },

            onFinish: () => {
                isSaving.value = false
            },
        }
    )
}

const clearPreview = () => {
    previewAllocations.value = []
    previewTotal.value = 0
    allocationError.value = ''
    allocationSuccess.value = ''
}
</script>

<template>
    <Head :title="`${competition.title} | Competition Tickets`" />

    <div class="space-y-6 pb-10">

        <!-- Breadcrumb -->
        <div class="flex flex-wrap items-center gap-2 text-sm">
            <Link
                :href="backUrl"
                class="text-neutral-500 transition hover:text-amber-400"
            >
                Tickets
            </Link>

            <span class="text-neutral-700">/</span>

            <span class="font-medium text-neutral-300">
                {{ competition.title }}
            </span>
        </div>

        <!-- Page Header -->
        <div class="flex flex-col justify-between gap-5 rounded-2xl border border-neutral-800 bg-[#121212] p-5 lg:flex-row lg:items-center">

            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/10 text-amber-400">
                    <svg
                        class="h-6 w-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M4 7.5A2.5 2.5 0 016.5 5h11A2.5 2.5 0 0120 7.5v1a2.5 2.5 0 010 5v1a2.5 2.5 0 01-2.5 2.5h-11A2.5 2.5 0 014 14.5v-1a2.5 2.5 0 000-5v-1z"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.8"
                            stroke-dasharray="2 2"
                            d="M12 7v10"
                        />
                    </svg>
                </div>

                <div>
                    <h1 class="text-xl font-bold text-white">
                        Competition Tickets
                    </h1>

                    <p class="mt-1 text-sm text-neutral-400">
                        Manage tickets and Instant Win allocations for this competition.
                    </p>

                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <span class="text-xs text-neutral-500">
                            Competition:
                        </span>

                        <span class="text-xs font-semibold text-amber-400">
                            {{ competition.title }}
                        </span>

                        <span class="text-neutral-700">•</span>

                        <span class="text-xs capitalize text-neutral-400">
                            {{ competition.status || 'Unknown status' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <Link
                    :href="backUrl"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-neutral-700 px-4 py-2.5 text-sm font-medium text-neutral-300 transition hover:border-neutral-600 hover:bg-neutral-800"
                >
                    <svg
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 19l-7-7 7-7"
                        />
                    </svg>

                    Back to Tickets
                </Link>

                <Link
                    v-if="!allocation?.exists"
                    :href="route('admin.tickets.generate', competition.encrypted_id)"
                    method="post"
                    as="button"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-semibold text-black transition hover:bg-amber-400"
                >
                    <svg
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 5v14m-7-7h14"
                        />
                    </svg>

                    Generate Tickets
                </Link>
            </div>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">

            <div class="rounded-xl border border-neutral-800 bg-[#151515] p-5">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-neutral-400">
                        Total Tickets
                    </p>

                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-500/10 text-amber-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M4 7.5A2.5 2.5 0 016.5 5h11A2.5 2.5 0 0120 7.5v1a2.5 2.5 0 010 5v1a2.5 2.5 0 01-2.5 2.5h-11A2.5 2.5 0 014 14.5v-1a2.5 2.5 0 000-5v-1z"
                            />
                        </svg>
                    </div>
                </div>

                <h3 class="mt-3 text-2xl font-bold text-white">
                    {{ formatNumber(allocation.total_tickets) }}
                </h3>

                <p class="mt-1 text-xs text-neutral-500">
                    Generated tickets
                </p>
            </div>

            <div class="rounded-xl border border-neutral-800 bg-[#151515] p-5">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-neutral-400">
                        Available Tickets
                    </p>

                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-500/10 text-blue-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M4 12h16M12 4v16"
                            />
                        </svg>
                    </div>
                </div>

                <h3 class="mt-3 text-2xl font-bold text-white">
                    {{ formatNumber(allocation.available_tickets) }}
                </h3>

                <p class="mt-1 text-xs text-neutral-500">
                    Not yet assigned to customers
                </p>
            </div>

            <div class="rounded-xl border border-neutral-800 bg-[#151515] p-5">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-neutral-400">
                        Assigned Tickets
                    </p>

                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-green-500/10 text-green-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="m5 12 4 4L19 6"
                            />
                        </svg>
                    </div>
                </div>

                <h3 class="mt-3 text-2xl font-bold text-white">
                    {{ formatNumber(allocation.assigned_tickets) }}
                </h3>

                <p class="mt-1 text-xs text-neutral-500">
                    Assigned to customers
                </p>
            </div>

        </div>

        <!-- Instant Win Allocation -->
        <div
            v-if="competition.instant_win_enabled"
            class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]"
        >

            <!-- Section Header -->
            <div class="flex flex-col justify-between gap-4 border-b border-neutral-800 p-5 lg:flex-row lg:items-center">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-lg font-bold text-white">
                            Instant Win Allocation
                        </h2>

                        <span
                            v-if="allocation.exists"
                            class="rounded-full border border-green-500/20 bg-green-500/10 px-2.5 py-1 text-xs font-semibold text-green-400"
                        >
                            Allocated
                        </span>

                        <span
                            v-else
                            class="rounded-full border border-amber-500/20 bg-amber-500/10 px-2.5 py-1 text-xs font-semibold text-amber-400"
                        >
                            Not Allocated
                        </span>
                    </div>

                    <p class="mt-1 text-sm text-neutral-500">
                        Assign winning ticket numbers to the competition's Instant Win prizes before launch.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs text-neutral-500">
                        {{ formatNumber(totalInstantWinPrizes) }} active prizes
                    </span>

                    <span class="text-neutral-700">•</span>

                    <span class="text-xs text-neutral-500">
                        {{ formatNumber(totalRequiredAllocations) }} winning tickets required
                    </span>
                </div>
            </div>

            <div class="space-y-5 p-5">

                <!-- Instant Win Disabled / No Prizes -->
                <div
                    v-if="!props.prizes.length"
                    class="rounded-xl border border-neutral-800 bg-[#151515] px-5 py-10 text-center"
                >
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-neutral-800 text-neutral-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M12 8v4m0 4h.01M4.93 19h14.14a1.5 1.5 0 001.3-2.25L13.3 4.5a1.5 1.5 0 00-2.6 0L3.63 16.75A1.5 1.5 0 004.93 19z"
                            />
                        </svg>
                    </div>

                    <h3 class="mt-3 text-sm font-semibold text-white">
                        No active Instant Win prizes
                    </h3>

                    <p class="mt-1 text-sm text-neutral-500">
                        Add and activate Instant Win prizes in the competition's prize configuration first.
                    </p>
                </div>

                <template v-else>

                    <!-- Prize Summary -->
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        <div
                            v-for="prize in prizes"
                            :key="prize.id"
                            class="rounded-xl border border-neutral-800 bg-[#151515] p-4"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-white">
                                        {{ prize.name }}
                                    </p>

                                    <p class="mt-1 text-xs text-neutral-500">
                                        {{ prize.prize_type || 'Prize' }}
                                    </p>
                                </div>

                                <span class="shrink-0 rounded-lg bg-amber-500/10 px-2.5 py-1 text-xs font-bold text-amber-400">
                                    ×{{ formatNumber(prize.quantity) }}
                                </span>
                            </div>

                            <div class="mt-4 flex items-center justify-between border-t border-neutral-800 pt-3">
                                <span class="text-xs text-neutral-500">
                                    Allocated
                                </span>

                                <span class="text-sm font-semibold text-white">
                                    {{ formatNumber(prize.allocated_quantity) }}
                                    <span class="text-neutral-500">
                                        / {{ formatNumber(prize.quantity) }}
                                    </span>
                                </span>
                            </div>

                            <p
                                v-if="prize.prize_value"
                                class="mt-2 text-xs text-neutral-500"
                            >
                                {{ formatCurrency(prize.prize_value, prize.currency) }}
                            </p>
                        </div>
                    </div>

                    <!-- Allocation Progress -->
                    <div class="rounded-xl border border-neutral-800 bg-[#151515] p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-white">
                                    Allocation Progress
                                </p>

                                <p class="mt-1 text-xs text-neutral-500">
                                    {{ formatNumber(allocation.exists ? totalAllocated : 0) }}
                                    of
                                    {{ formatNumber(totalRequiredAllocations) }}
                                    winning tickets allocated
                                </p>
                            </div>

                            <span class="text-sm font-bold text-amber-400">
                                {{
                                    totalRequiredAllocations > 0
                                        ? Math.round(
                                            (totalAllocated / totalRequiredAllocations) * 100
                                        )
                                        : 0
                                }}%
                            </span>
                        </div>

                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-neutral-800">
                            <div
                                class="h-full rounded-full bg-amber-500 transition-all duration-300"
                                :style="{
                                    width: `${
                                        totalRequiredAllocations > 0
                                            ? Math.min(
                                                (totalAllocated / totalRequiredAllocations) * 100,
                                                100
                                            )
                                            : 0
                                    }%`
                                }"
                            ></div>
                        </div>
                    </div>

                    <!-- Already Allocated -->
                    <div
                        v-if="allocation.exists"
                        class="rounded-xl border border-green-500/20 bg-green-500/5 p-4"
                    >
                        <div class="flex items-start gap-3">
                            <svg
                                class="mt-0.5 h-5 w-5 shrink-0 text-green-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="m5 12 4 4L19 6"
                                />
                            </svg>

                            <div>
                                <p class="text-sm font-semibold text-green-400">
                                    Instant Win allocations have been created
                                </p>

                                <p class="mt-1 text-xs leading-relaxed text-neutral-400">
                                    Winning ticket assignments are stored and cannot be replaced through this screen.
                                    Matching will happen after successful customer payments.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Eligibility Warning -->
                    <div
                        v-else-if="allocation.total_tickets !== Number(competition.total_entries)"
                        class="rounded-xl border border-amber-500/20 bg-amber-500/5 p-4"
                    >
                        <p class="text-sm font-semibold text-amber-400">
                            Ticket generation is incomplete
                        </p>

                        <p class="mt-1 text-xs leading-relaxed text-neutral-400">
                            Generate all competition tickets before allocating Instant Win prizes.
                            Expected:
                            {{ formatNumber(competition.total_entries) }},
                            generated:
                            {{ formatNumber(allocation.total_tickets) }}.
                        </p>
                    </div>

                    <div
                        v-else-if="allocation.available_tickets < totalRequiredAllocations"
                        class="rounded-xl border border-red-500/20 bg-red-500/5 p-4"
                    >
                        <p class="text-sm font-semibold text-red-400">
                            Not enough available tickets
                        </p>

                        <p class="mt-1 text-xs text-neutral-400">
                            Required:
                            {{ formatNumber(totalRequiredAllocations) }}.
                            Available:
                            {{ formatNumber(allocation.available_tickets) }}.
                        </p>
                    </div>

                    <div
                        v-else-if="['active', 'completed', 'cancelled'].includes(competition.status)"
                        class="rounded-xl border border-amber-500/20 bg-amber-500/5 p-4"
                    >
                        <p class="text-sm font-semibold text-amber-400">
                            Allocation is unavailable
                        </p>

                        <p class="mt-1 text-xs text-neutral-400">
                            Instant Win allocations must be prepared before the competition becomes active.
                        </p>
                    </div>

                    <!-- Preview / Save Actions -->
                    <div
                        v-if="!allocation.exists"
                        class="flex flex-col justify-between gap-4 rounded-xl border border-neutral-800 bg-[#151515] p-4 lg:flex-row lg:items-center"
                    >
                        <div>
                            <p class="text-sm font-semibold text-white">
                                Prepare winning ticket assignments
                            </p>

                            <p class="mt-1 max-w-2xl text-xs leading-relaxed text-neutral-500">
                                The system will randomly select unique ticket numbers for each active Instant Win prize.
                                Preview the assignments before saving them permanently.
                            </p>
                        </div>

                        <button
                            type="button"
                            @click="generateAllocationPreview"
                            :disabled="!canPreviewAllocation"
                            class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-amber-500 px-5 py-3 text-sm font-semibold text-black transition hover:bg-amber-400 disabled:cursor-not-allowed disabled:opacity-40"
                        >
                            <svg
                                v-if="isPreviewing"
                                class="h-4 w-4 animate-spin"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                />
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                                />
                            </svg>

                            <svg
                                v-else
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M12 3v3m0 12v3m9-9h-3M6 12H3m15.36-6.36-2.12 2.12M7.76 16.24l-2.12 2.12m12.72 0-2.12-2.12M7.76 7.76 5.64 5.64"
                                />
                            </svg>

                            {{ isPreviewing ? 'Generating Preview...' : 'Generate Allocation Preview' }}
                        </button>
                    </div>

                    <!-- Preview Results -->
                    <div
                        v-if="hasPreview"
                        class="space-y-4"
                    >
                        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                            <div>
                                <h3 class="text-base font-bold text-white">
                                    Allocation Preview
                                </h3>

                                <p class="mt-1 text-xs text-neutral-500">
                                    Review the selected ticket numbers before confirming.
                                </p>
                            </div>

                            <span class="rounded-lg border border-amber-500/20 bg-amber-500/10 px-3 py-2 text-xs font-semibold text-amber-400">
                                {{ formatNumber(previewTotal) }} winning tickets selected
                            </span>
                        </div>

                        <div
                            v-for="allocationItem in previewAllocations"
                            :key="allocationItem.prize_id"
                            class="overflow-hidden rounded-xl border border-neutral-800 bg-[#151515]"
                        >
                            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-neutral-800 px-4 py-3">
                                <div>
                                    <p class="text-sm font-semibold text-white">
                                        {{ allocationItem.prize_name }}
                                    </p>

                                    <p class="mt-1 text-xs text-neutral-500">
                                        {{ formatNumber(allocationItem.quantity) }} winning ticket(s)
                                    </p>
                                </div>

                                <span class="rounded-lg bg-amber-500/10 px-2.5 py-1 text-xs font-semibold text-amber-400">
                                    Prize #{{ allocationItem.prize_id }}
                                </span>
                            </div>

                            <div class="flex flex-wrap gap-2 p-4">
                                <span
                                    v-for="ticket in allocationItem.tickets"
                                    :key="ticket.id"
                                    class="inline-flex items-center rounded-lg border border-neutral-700 bg-[#121212] px-3 py-2 font-mono text-xs font-semibold text-amber-400"
                                >
                                    {{ ticket.ticket_number }}
                                </span>
                            </div>
                        </div>

                        <div class="rounded-xl border border-amber-500/20 bg-amber-500/5 p-4">
                            <p class="text-sm font-semibold text-amber-400">
                                Please review before confirming
                            </p>

                            <p class="mt-1 text-xs leading-relaxed text-neutral-400">
                                Saving these assignments will permanently associate the selected tickets with their prizes.
                                A ticket cannot be assigned to another prize afterward.
                            </p>
                        </div>

                        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                            <button
                                type="button"
                                @click="clearPreview"
                                :disabled="isSaving"
                                class="rounded-lg border border-neutral-700 px-5 py-2.5 text-sm font-medium text-neutral-300 transition hover:bg-neutral-800 disabled:opacity-50"
                            >
                                Discard Preview
                            </button>

                            <button
                                type="button"
                                @click="confirmAllocation"
                                :disabled="isSaving"
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-500 px-5 py-2.5 text-sm font-semibold text-black transition hover:bg-amber-400 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <svg
                                    v-if="isSaving"
                                    class="h-4 w-4 animate-spin"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    />
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                                    />
                                </svg>

                                {{ isSaving ? 'Saving Allocations...' : 'Confirm & Save Allocations' }}
                            </button>
                        </div>
                    </div>

                </template>

                <!-- Error Message -->
                <div
                    v-if="allocationError"
                    class="rounded-xl border border-red-500/20 bg-red-500/5 p-4"
                >
                    <p class="text-sm font-semibold text-red-400">
                        Allocation Error
                    </p>

                    <p class="mt-1 text-xs text-neutral-400">
                        {{ allocationError }}
                    </p>
                </div>

                <!-- Success Message -->
                <div
                    v-if="allocationSuccess"
                    class="rounded-xl border border-green-500/20 bg-green-500/5 p-4"
                >
                    <p class="text-sm font-semibold text-green-400">
                        {{ allocationSuccess }}
                    </p>
                </div>

            </div>
        </div>

        <!-- Tickets Table -->
        <div class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">

            <!-- Table Header -->
            <div class="flex flex-col justify-between gap-4 border-b border-neutral-800 p-5 lg:flex-row lg:items-center">

                <div>
                    <h2 class="text-base font-bold text-white">
                        Ticket Records
                    </h2>

                    <p class="mt-1 text-sm text-neutral-500">
                        Search and review tickets associated with this competition.
                    </p>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row">

                    <!-- Search -->
                    <form
                        @submit.prevent="applyFilters"
                        class="flex gap-2"
                    >
                        <div class="relative">
                            <svg
                                class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-500"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    cx="11"
                                    cy="11"
                                    r="7"
                                    stroke-width="1.8"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-width="1.8"
                                    d="m16 16 4 4"
                                />
                            </svg>

                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search ticket number..."
                                class="w-full rounded-lg border border-neutral-700 bg-[#151515] py-2.5 pl-9 pr-3 text-sm text-white placeholder-neutral-500 outline-none transition focus:border-amber-500 sm:w-64"
                            />
                        </div>

                        <button
                            type="submit"
                            class="rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-semibold text-black transition hover:bg-amber-400"
                        >
                            Search
                        </button>
                    </form>

                    <!-- Status Filter -->
                    <select
                        v-model="selectedStatus"
                        @change="applyFilters"
                        class="rounded-lg border border-neutral-700 bg-[#151515] px-3 py-2.5 text-sm text-neutral-300 outline-none transition focus:border-amber-500"
                    >
                        <option value="">
                            All Statuses
                        </option>

                        <option
                            v-for="status in statusOptions"
                            :key="status"
                            :value="status"
                        >
                            {{ statusLabel(status) }}
                        </option>
                    </select>

                    <button
                        v-if="search || selectedStatus"
                        type="button"
                        @click="resetFilters"
                        class="rounded-lg border border-neutral-700 px-3 py-2.5 text-sm font-medium text-neutral-400 transition hover:bg-neutral-800 hover:text-white"
                    >
                        Reset
                    </button>

                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1250px] text-left">

                    <thead>
                        <tr class="border-b border-neutral-800 bg-[#151515] text-xs uppercase tracking-wider text-neutral-500">
                            <th class="px-5 py-4 font-semibold">
                                Ticket Number
                            </th>

                            <th class="px-5 py-4 font-semibold">
                                Ticket Status
                            </th>

                            <th class="px-5 py-4 font-semibold">
                                Winning Prize
                            </th>

                            <th class="px-5 py-4 font-semibold">
                                Winning Method
                            </th>

                            <th class="px-5 py-4 font-semibold">
                                Allocation Status
                            </th>

                            <th class="px-5 py-4 font-semibold">
                                Customer ID
                            </th>

                            <th class="px-5 py-4 font-semibold">
                                Order ID
                            </th>

                            <th class="px-5 py-4 font-semibold">
                                Assigned At
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="ticket in ticketList"
                            :key="ticket.id"
                            class="border-b border-neutral-800/70 transition hover:bg-white/[0.025]"
                        >
                            <!-- Ticket Number -->
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-amber-500/20 bg-amber-500/10 text-amber-400">
                                        <svg
                                            class="h-4 w-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.8"
                                                d="M4 7.5A2.5 2.5 0 016.5 5h11A2.5 2.5 0 0120 7.5v1a2.5 2.5 0 010 5v1a2.5 2.5 0 01-2.5 2.5h-11A2.5 2.5 0 014 14.5v-1a2.5 2.5 0 000-5v-1z"
                                            />
                                        </svg>
                                    </div>

                                    <span class="text-sm font-semibold text-white">
                                        {{ ticket.ticket_number || '—' }}
                                    </span>
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold"
                                    :class="statusClasses(ticket.status)"
                                >
                                    {{ statusLabel(ticket.status) }}
                                </span>
                            </td>

                            <!-- Winning Prize -->
                            <td class="px-5 py-4">
                                <span
                                    v-if="ticket.winning_prize_name"
                                    class="text-sm font-medium text-amber-400"
                                >
                                    {{ ticket.winning_prize_name }}
                                </span>
                                <span v-else class="text-sm text-neutral-600">
                                    —
                                </span>
                            </td>

                            <!-- Winning Method -->
                            <td class="px-5 py-4">
                                <span
                                    v-if="ticket.winning_method"
                                    class="inline-flex items-center rounded-full border border-purple-500/20 bg-purple-500/10 px-2.5 py-1 text-xs font-semibold capitalize text-purple-400"
                                >
                                    {{ statusLabel(ticket.winning_method) }}
                                </span>
                                <span v-else class="text-sm text-neutral-600">
                                    —
                                </span>
                            </td>

                            <!-- Allocation Status -->
                            <td class="px-5 py-4">
                                <span
                                    v-if="ticket.winning_status"
                                    class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold"
                                    :class="winningStatusClasses(ticket.winning_status)"
                                >
                                    {{ statusLabel(ticket.winning_status) }}
                                </span>
                                <span v-else class="text-sm text-neutral-600">
                                    Not a winning ticket
                                </span>
                            </td>

                            <!-- Customer -->
                            <td class="px-5 py-4">
                                <span class="text-sm text-neutral-300">
                                    {{ ticket.user_id || '—' }}
                                </span>
                            </td>

                            <!-- Order -->
                            <td class="px-5 py-4">
                                <span class="text-sm text-neutral-300">
                                    {{ ticket.order_id || '—' }}
                                </span>
                            </td>

                            <!-- Assigned Date -->
                            <td class="px-5 py-4">
                                <span class="text-sm text-neutral-400">
                                    {{ formatDate(ticket.assigned_at) }}
                                </span>
                            </td>
                        </tr>

                        <!-- Empty State -->
                        <tr v-if="ticketList.length === 0">
                            <td
                                colspan="8"
                                class="px-5 py-16 text-center"
                            >
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-neutral-800 bg-[#151515] text-neutral-500">
                                    <svg
                                        class="h-7 w-7"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.6"
                                            d="M4 7.5A2.5 2.5 0 016.5 5h11A2.5 2.5 0 0120 7.5v1a2.5 2.5 0 010 5v1a2.5 2.5 0 01-2.5 2.5h-11A2.5 2.5 0 014 14.5v-1a2.5 2.5 0 000-5v-1z"
                                        />
                                    </svg>
                                </div>

                                <h3 class="mt-4 text-sm font-semibold text-white">
                                    No tickets found
                                </h3>

                                <p class="mt-1 text-sm text-neutral-500">
                                    {{
                                        search || selectedStatus
                                            ? 'Try adjusting your search or filters.'
                                            : 'No tickets have been generated for this competition yet.'
                                    }}
                                </p>

                                <button
                                    v-if="search || selectedStatus"
                                    type="button"
                                    @click="resetFilters"
                                    class="mt-4 text-sm font-semibold text-amber-400 transition hover:text-amber-300"
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
                v-if="paginationLinks.length"
                class="flex flex-col justify-between gap-4 border-t border-neutral-800 px-5 py-4 sm:flex-row sm:items-center"
            >
                <p class="text-sm text-neutral-500">
                    Showing
                    <span class="font-semibold text-neutral-300">
                        {{ formatNumber(props.tickets?.from) }}
                    </span>
                    to
                    <span class="font-semibold text-neutral-300">
                        {{ formatNumber(props.tickets?.to) }}
                    </span>
                    of
                    <span class="font-semibold text-neutral-300">
                        {{ formatNumber(totalTickets) }}
                    </span>
                    tickets
                </p>

                <div class="flex flex-wrap items-center gap-1">
                    <template
                        v-for="(link, index) in paginationLinks"
                        :key="index"
                    >
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            preserve-scroll
                            class="flex h-9 min-w-9 items-center justify-center rounded-lg border px-3 text-sm transition"
                            :class="link.active
                                ? 'border-amber-500 bg-amber-500 text-black font-semibold'
                                : 'border-neutral-800 bg-[#151515] text-neutral-400 hover:border-neutral-600 hover:text-white'"
                        >
                            <span v-html="link.label"></span>
                        </Link>

                        <span
                            v-else
                            class="flex h-9 min-w-9 cursor-not-allowed items-center justify-center rounded-lg border border-neutral-800 bg-[#151515] px-3 text-sm text-neutral-600"
                            v-html="link.label"
                        ></span>
                    </template>
                </div>
            </div>

        </div>

    </div>
</template>
