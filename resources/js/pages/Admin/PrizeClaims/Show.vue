
<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { computed, ref } from 'vue'

const props = defineProps({
    claim: {
        type: Object,
        required: true,
    },
})

const showRejectModal = ref(false)
const showConfirmationModal = ref(false)
const confirmationAction = ref(null)

const approveForm = useForm({})
const rejectForm = useForm({
    reason: '',
})
const fulfillForm = useForm({})

const statusConfig = computed(() => {
    const statuses = {
        won: {
            label: 'Pending Review',
            class: 'bg-amber-500/10 text-amber-400 ring-amber-500/20',
            description: 'The customer has submitted this claim and it is awaiting admin review.',
        },
        claimed: {
            label: 'Approved',
            class: 'bg-blue-500/10 text-blue-400 ring-blue-500/20',
            description: 'This claim has been approved and is awaiting prize fulfilment.',
        },
        fulfilled: {
            label: 'Fulfilled',
            class: 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/20',
            description: 'The prize has been successfully delivered to the customer.',
        },
        cancelled: {
            label: 'Rejected',
            class: 'bg-red-500/10 text-red-400 ring-red-500/20',
            description: 'This claim has been rejected.',
        },
    }

    return statuses[props.claim.status] || {
        label: props.claim.status,
        class: 'bg-gray-500/10 text-gray-400 ring-gray-500/20',
        description: 'Claim status information.',
    }
})

const canReview = computed(() => props.claim.status === 'won')
const canFulfill = computed(() => props.claim.status === 'claimed')

const formatDate = (date) => {
    if (!date) return 'Not available'

    const parsedDate = new Date(date)

    if (Number.isNaN(parsedDate.getTime())) return 'Not available'

    return new Intl.DateTimeFormat('en-US', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(parsedDate)
}

const formatCurrency = (value, currency = 'USD') => {
    if (value === null || value === undefined) return 'Not specified'

    try {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency || 'USD',
        }).format(Number(value))
    } catch {
        return `${currency || 'USD'} ${value}`
    }
}

const submitApprove = () => {
    approveForm.post(
        route('admin.prize-claims.approve', {
            id: props.claim.id,
        }),
        {
            preserveScroll: true,
            onSuccess: () => {
                showConfirmationModal.value = false
                confirmationAction.value = null
            },
        }
    )
}

const submitReject = () => {
    rejectForm.post(
        route('admin.prize-claims.reject', {
            id: props.claim.id,
        }),
        {
            preserveScroll: true,
            onSuccess: () => {
                showRejectModal.value = false
                rejectForm.reset()
                rejectForm.clearErrors()
            },
        }
    )
}

const submitFulfill = () => {
    fulfillForm.post(
        route('admin.prize-claims.fulfill', {
            id: props.claim.id,
        }),
        {
            preserveScroll: true,
            onSuccess: () => {
                showConfirmationModal.value = false
                confirmationAction.value = null
            },
        }
    )
}

const openConfirmation = (action) => {
    confirmationAction.value = action
    showConfirmationModal.value = true
}

const confirmAction = () => {
    if (confirmationAction.value === 'approve') {
        submitApprove()
    } else if (confirmationAction.value === 'fulfill') {
        submitFulfill()
    }
}

const closeRejectModal = () => {
    if (rejectForm.processing) return

    showRejectModal.value = false
    rejectForm.reset()
    rejectForm.clearErrors()
}

const closeConfirmationModal = () => {
    if (approveForm.processing || fulfillForm.processing) return

    showConfirmationModal.value = false
    confirmationAction.value = null
}

const isConfirmationProcessing = computed(() => {
    return approveForm.processing || fulfillForm.processing
})

const confirmationContent = computed(() => {
    if (confirmationAction.value === 'approve') {
        return {
            title: 'Approve Prize Claim?',
            description: 'This will approve the customer’s claim. You can then proceed with prize fulfilment.',
            button: 'Approve Claim',
            buttonClass: 'bg-blue-600 hover:bg-blue-500',
        }
    }

    return {
        title: 'Mark Prize as Fulfilled?',
        description: 'Confirm that the prize has been successfully delivered to the customer. This action will complete the claim.',
        button: 'Confirm Fulfilment',
        buttonClass: 'bg-emerald-600 hover:bg-emerald-500',
    }
})
</script>

<template>
    <Head title="Prize Claim Details" />

    <AdminLayout>
        <div class="space-y-6 pb-10">

            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <Link
                        :href="route('admin.prize-claims.index')"
                        class="mb-3 inline-flex items-center gap-2 text-sm text-gray-400 transition hover:text-amber-400"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Prize Claims
                    </Link>

                    <h1 class="text-2xl font-bold text-white sm:text-3xl">
                        Prize Claim Details
                    </h1>

                    <p class="mt-1 text-sm text-gray-400">
                        Review the winning entry and customer claim information.
                    </p>
                </div>

                <span
                    class="inline-flex w-fit items-center rounded-full px-4 py-2 text-sm font-semibold ring-1"
                    :class="statusConfig.class"
                >
                    {{ statusConfig.label }}
                </span>
            </div>

            <!-- Status Overview -->
            <div class="rounded-xl border border-gray-800 bg-gray-900 p-5">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-500/10">
                        <svg class="h-5 w-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M12 22a10 10 0 110-20 10 10 0 010 20z" />
                        </svg>
                    </div>

                    <div>
                        <h2 class="font-semibold text-white">
                            {{ statusConfig.label }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-400">
                            {{ statusConfig.description }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Main Grid -->
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

                <!-- Left Column -->
                <div class="space-y-6 xl:col-span-2">

                    <!-- Prize Information -->
                    <section class="overflow-hidden rounded-xl border border-gray-800 bg-gray-900">
                        <div class="border-b border-gray-800 px-5 py-4">
                            <h2 class="font-semibold text-white">Prize Information</h2>
                            <p class="mt-1 text-xs text-gray-500">
                                Details of the prize won by the customer.
                            </p>
                        </div>

                        <div class="grid gap-5 p-5 sm:grid-cols-2">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Prize Name
                                </p>
                                <p class="mt-2 text-lg font-semibold text-white">
                                    {{ claim.prize_name || 'N/A' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Prize Type
                                </p>
                                <p class="mt-2 text-sm font-medium capitalize text-gray-200">
                                    {{ claim.prize_type?.replaceAll('_', ' ') || 'N/A' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Prize Value
                                </p>
                                <p class="mt-2 text-lg font-semibold text-amber-400">
                                    {{ formatCurrency(claim.prize_value, claim.currency) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Competition
                                </p>
                                <p class="mt-2 text-sm font-medium text-gray-200">
                                    {{ claim.competition_title || 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Customer Information -->
                    <section class="overflow-hidden rounded-xl border border-gray-800 bg-gray-900">
                        <div class="border-b border-gray-800 px-5 py-4">
                            <h2 class="font-semibold text-white">Customer Information</h2>
                            <p class="mt-1 text-xs text-gray-500">
                                Account details of the prize winner.
                            </p>
                        </div>

                        <div class="p-5">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-amber-500/10 text-xl font-bold text-amber-400">
                                    {{ claim.customer_name?.charAt(0)?.toUpperCase() || 'C' }}
                                </div>

                                <div class="min-w-0 flex-1">
                                    <Link
                                        :href="route('admin.customers.show', {
                                            customer: claim.customer_encrypted_id
                                        })"
                                        class="font-semibold text-white transition hover:text-amber-400"
                                    >
                                        {{ claim.customer_name || 'Unknown Customer' }}
                                    </Link>

                                    <p class="mt-1 break-all text-sm text-gray-400">
                                        {{ claim.customer_email || 'No email available' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Winning Entry -->
                    <section class="overflow-hidden rounded-xl border border-gray-800 bg-gray-900">
                        <div class="border-b border-gray-800 px-5 py-4">
                            <h2 class="font-semibold text-white">Winning Entry</h2>
                            <p class="mt-1 text-xs text-gray-500">
                                Ticket and purchase information.
                            </p>
                        </div>

                        <div class="grid gap-5 p-5 sm:grid-cols-2">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Ticket Number
                                </p>
                                <p class="mt-2 inline-flex rounded-lg border border-amber-500/20 bg-amber-500/10 px-3 py-2 font-mono text-lg font-bold tracking-wider text-amber-400">
                                    {{ claim.ticket_number || 'N/A' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Winning Method
                                </p>
                                <p class="mt-2 text-sm font-medium capitalize text-gray-200">
                                    {{ claim.winning_method?.replaceAll('_', ' ') || 'N/A' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Order Reference
                                </p>
                                <p class="mt-2 text-sm font-medium text-gray-200">
                                    {{ claim.order_id ? `#${claim.order_id}` : 'N/A' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Winning Date
                                </p>
                                <p class="mt-2 text-sm text-gray-200">
                                    {{ formatDate(claim.won_at) }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Admin Notes -->
                    <section class="overflow-hidden rounded-xl border border-gray-800 bg-gray-900">
                        <div class="border-b border-gray-800 px-5 py-4">
                            <h2 class="font-semibold text-white">Claim Notes</h2>
                            <p class="mt-1 text-xs text-gray-500">
                                Internal notes and rejection information.
                            </p>
                        </div>

                        <div class="p-5">
                            <p v-if="claim.notes" class="whitespace-pre-line text-sm leading-6 text-gray-300">
                                {{ claim.notes }}
                            </p>

                            <p v-else class="text-sm italic text-gray-500">
                                No notes have been added yet.
                            </p>
                        </div>
                    </section>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">

                    <!-- Claim Timeline -->
                    <section class="rounded-xl border border-gray-800 bg-gray-900 p-5">
                        <h2 class="font-semibold text-white">Claim Timeline</h2>

                        <div class="mt-6 space-y-6">

                            <!-- Prize Won -->
                            <div class="relative flex gap-3">
                                <div class="relative flex flex-col items-center">
                                    <div class="z-10 flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <div class="absolute top-8 h-full w-px bg-gray-700"></div>
                                </div>

                                <div class="pb-6">
                                    <p class="text-sm font-medium text-white">Prize Won</p>
                                    <p class="mt-1 text-xs text-gray-400">
                                        {{ formatDate(claim.won_at) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Claim Submitted -->
                            <div class="relative flex gap-3">
                                <div class="relative flex flex-col items-center">
                                    <div class="z-10 flex h-8 w-8 items-center justify-center rounded-full bg-amber-500/10 text-amber-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="absolute top-8 h-full w-px bg-gray-700"></div>
                                </div>

                                <div class="pb-6">
                                    <p class="text-sm font-medium text-white">Claim Submitted</p>
                                    <p class="mt-1 text-xs text-gray-400">
                                        {{ formatDate(claim.claimed_at) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Claim Reviewed -->
                            <div class="flex gap-3">
                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full"
                                    :class="['claimed', 'fulfilled', 'cancelled'].includes(claim.status)
                                        ? claim.status === 'cancelled'
                                            ? 'bg-red-500/10 text-red-400'
                                            : 'bg-blue-500/10 text-blue-400'
                                        : 'bg-gray-800 text-gray-500'"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4" />
                                    </svg>
                                </div>

                                <div>
                                    <p class="text-sm font-medium text-white">Claim Reviewed</p>
                                    <p class="mt-1 text-xs text-gray-400">
                                        {{ claim.status === 'claimed' || claim.status === 'fulfilled'
                                            ? 'Approved'
                                            : claim.status === 'cancelled'
                                                ? 'Rejected'
                                                : 'Awaiting review' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Fulfilled -->
                            <div class="flex gap-3">
                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full"
                                    :class="claim.fulfilled_at
                                        ? 'bg-emerald-500/10 text-emerald-400'
                                        : 'bg-gray-800 text-gray-500'"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>

                                <div>
                                    <p class="text-sm font-medium text-white">Prize Fulfilled</p>
                                    <p class="mt-1 text-xs text-gray-400">
                                        {{ formatDate(claim.fulfilled_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Claim Summary -->
                    <section class="rounded-xl border border-gray-800 bg-gray-900 p-5">
                        <h2 class="font-semibold text-white">Claim Summary</h2>

                        <div class="mt-5 space-y-4">
                            <div class="flex justify-between gap-3">
                                <span class="text-sm text-gray-400">Claim ID</span>
                                <span class="break-all text-right font-mono text-xs text-gray-300">
                                    {{ claim.display_id }}
                                </span>
                            </div>

                            <div class="flex justify-between gap-3">
                                <span class="text-sm text-gray-400">Current Status</span>
                                <span class="text-sm font-medium text-white">
                                    {{ statusConfig.label }}
                                </span>
                            </div>

                            <div class="flex justify-between gap-3">
                                <span class="text-sm text-gray-400">Claim Submitted</span>
                                <span class="text-right text-sm text-gray-300">
                                    {{ formatDate(claim.claimed_at) }}
                                </span>
                            </div>

                            <div class="flex justify-between gap-3">
                                <span class="text-sm text-gray-400">Fulfilled Date</span>
                                <span class="text-right text-sm text-gray-300">
                                    {{ formatDate(claim.fulfilled_at) }}
                                </span>
                            </div>
                        </div>
                    </section>

                    <!-- Claim Management Actions -->
                    <section class="overflow-hidden rounded-xl border border-gray-800 bg-gray-900">
                        <div class="border-b border-gray-800 px-5 py-4">
                            <h2 class="font-semibold text-white">Manage Claim</h2>
                            <p class="mt-1 text-xs text-gray-500">
                                Available actions for this prize claim.
                            </p>
                        </div>

                        <div class="space-y-3 p-5">

                            <!-- Pending Review Actions -->
                            <template v-if="canReview">
                                <button
                                    type="button"
                                    :disabled="isConfirmationProcessing"
                                    @click="openConfirmation('approve')"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    Approve Claim
                                </button>

                                <button
                                    type="button"
                                    :disabled="rejectForm.processing"
                                    @click="showRejectModal = true"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm font-semibold text-red-400 transition hover:border-red-500/50 hover:bg-red-500/20 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Reject Claim
                                </button>

                                <p class="pt-1 text-xs leading-5 text-gray-500">
                                    Review the customer's details before approving or rejecting this claim.
                                </p>
                            </template>

                            <!-- Approved Action -->
                            <template v-else-if="canFulfill">
                                <button
                                    type="button"
                                    :disabled="isConfirmationProcessing"
                                    @click="openConfirmation('fulfill')"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-500 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    Mark as Fulfilled
                                </button>

                                <p class="pt-1 text-xs leading-5 text-gray-500">
                                    Only mark this prize as fulfilled after confirming successful delivery.
                                </p>
                            </template>

                            <!-- Completed -->
                            <div v-else-if="claim.status === 'fulfilled'"
                                class="rounded-lg border border-emerald-500/20 bg-emerald-500/5 p-4">
                                <div class="flex items-center gap-2 text-emerald-400">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-sm font-semibold">Claim Completed</span>
                                </div>
                                <p class="mt-2 text-xs leading-5 text-gray-400">
                                    This prize has been marked as successfully fulfilled.
                                </p>
                            </div>

                            <!-- Rejected -->
                            <div v-else-if="claim.status === 'cancelled'"
                                class="rounded-lg border border-red-500/20 bg-red-500/5 p-4">
                                <div class="flex items-center gap-2 text-red-400">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    <span class="text-sm font-semibold">Claim Rejected</span>
                                </div>
                                <p class="mt-2 text-xs leading-5 text-gray-400">
                                    This claim has been rejected. No further actions are available.
                                </p>
                            </div>

                        </div>
                    </section>
                </div>
            </div>
        </div>

        <!-- Approve / Fulfill Confirmation Modal -->
        <div
            v-if="showConfirmationModal"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/70 p-4 backdrop-blur-sm"
            @click.self="closeConfirmationModal"
        >
            <div class="w-full max-w-md rounded-2xl border border-gray-700 bg-gray-900 p-6 shadow-2xl">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl"
                    :class="confirmationAction === 'approve'
                        ? 'bg-blue-500/10 text-blue-400'
                        : 'bg-emerald-500/10 text-emerald-400'"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                <h2 class="mt-5 text-xl font-bold text-white">
                    {{ confirmationContent.title }}
                </h2>

                <p class="mt-2 text-sm leading-6 text-gray-400">
                    {{ confirmationContent.description }}
                </p>

                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        :disabled="isConfirmationProcessing"
                        @click="closeConfirmationModal"
                        class="rounded-lg border border-gray-700 px-4 py-2.5 text-sm font-medium text-gray-300 transition hover:bg-gray-800 disabled:opacity-50"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        :disabled="isConfirmationProcessing"
                        @click="confirmAction"
                        class="rounded-lg px-4 py-2.5 text-sm font-semibold text-white transition disabled:cursor-not-allowed disabled:opacity-50"
                        :class="confirmationContent.buttonClass"
                    >
                        <span v-if="isConfirmationProcessing">Processing...</span>
                        <span v-else>{{ confirmationContent.button }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Reject Claim Modal -->
        <div
            v-if="showRejectModal"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/70 p-4 backdrop-blur-sm"
            @click.self="closeRejectModal"
        >
            <div class="w-full max-w-lg rounded-2xl border border-gray-700 bg-gray-900 p-6 shadow-2xl">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-500/10 text-red-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>

                <h2 class="mt-5 text-xl font-bold text-white">
                    Reject Prize Claim
                </h2>

                <p class="mt-2 text-sm leading-6 text-gray-400">
                    Please provide a clear reason for rejecting this customer's claim. This reason will be saved in the claim notes.
                </p>

                <form @submit.prevent="submitReject" class="mt-6 space-y-4">
                    <div>
                        <label for="rejection_reason" class="mb-2 block text-sm font-medium text-gray-300">
                            Rejection Reason <span class="text-red-400">*</span>
                        </label>

                        <textarea
                            id="rejection_reason"
                            v-model="rejectForm.reason"
                            rows="4"
                            maxlength="1000"
                            placeholder="Explain why this claim is being rejected..."
                            class="w-full rounded-lg border border-gray-700 bg-gray-950 px-4 py-3 text-sm text-white placeholder-gray-500 outline-none transition focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                            :class="{ 'border-red-500': rejectForm.errors.reason }"
                        ></textarea>

                        <div class="mt-1 flex items-center justify-between gap-3">
                            <p v-if="rejectForm.errors.reason" class="text-xs text-red-400">
                                {{ rejectForm.errors.reason }}
                            </p>
                            <p v-else class="text-xs text-gray-500">
                                A rejection reason is required.
                            </p>

                            <span class="shrink-0 text-xs text-gray-500">
                                {{ rejectForm.reason.length }}/1000
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-gray-800 pt-5 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            :disabled="rejectForm.processing"
                            @click="closeRejectModal"
                            class="rounded-lg border border-gray-700 px-4 py-2.5 text-sm font-medium text-gray-300 transition hover:bg-gray-800 disabled:opacity-50"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            :disabled="rejectForm.processing || !rejectForm.reason.trim()"
                            class="rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-500 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <span v-if="rejectForm.processing">Rejecting...</span>
                            <span v-else>Confirm Rejection</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </AdminLayout>
</template>
