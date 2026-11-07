
<script setup>
import { computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import UserLayout from '@/Layouts/UserLayout.vue'

const props = defineProps({
    prize: {
        type: Object,
        required: true,
    },
})

const claimForm = useForm({})

const imageUrl = computed(() => {
    const image = props.prize?.prize_image

    if (!image) return null

    if (image.startsWith('http://') || image.startsWith('https://')) {
        return image
    }

    if (image.startsWith('/')) {
        return image
    }

    return `/storage/${image.replace(/^storage\//, '')}`
})

const formatDate = (value, emptyText = 'Not yet available') => {
    if (!value) return emptyText

    const date = new Date(value)

    if (Number.isNaN(date.getTime())) return value

    return new Intl.DateTimeFormat(undefined, {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    }).format(date)
}

const claimSubmitted = computed(() => {
    return Boolean(props.prize?.claimed_at)
})

const normalizedStatus = computed(() => {
    return String(props.prize?.claim_status || 'won').toLowerCase()
})

const isReadyToClaim = computed(() => {
    return normalizedStatus.value === 'won' && !claimSubmitted.value
})

const isClaimUnderReview = computed(() => {
    return normalizedStatus.value === 'won' && claimSubmitted.value
})

const statusConfig = computed(() => {
    const statuses = {
        pending: {
            label: 'Awaiting confirmation',
            description: 'This winning ticket has been allocated but has not yet been matched to a purchased ticket.',
            color: 'border-amber-500/30 bg-amber-500/10 text-amber-300',
            dot: 'bg-amber-400',
        },

        won: {
            label: 'Claim available',
            description: 'Congratulations! You have won this prize. Submit your claim request below to begin the review process.',
            color: 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300',
            dot: 'bg-emerald-400',
        },

        claimed: {
            label: 'Claim approved',
            description: 'Your claim has been approved by our team. We will coordinate the prize fulfilment with you.',
            color: 'border-sky-500/30 bg-sky-500/10 text-sky-300',
            dot: 'bg-sky-400',
        },

        processing: {
            label: 'Processing',
            description: 'Your claim has been approved and your prize is currently being prepared.',
            color: 'border-violet-500/30 bg-violet-500/10 text-violet-300',
            dot: 'bg-violet-400',
        },

        fulfilled: {
            label: 'Prize fulfilled',
            description: 'Your prize has been successfully fulfilled.',
            color: 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300',
            dot: 'bg-emerald-400',
        },

        cancelled: {
            label: 'Claim not approved',
            description: 'This claim has been rejected or cancelled. Please contact our support team if you need further information.',
            color: 'border-red-500/30 bg-red-500/10 text-red-300',
            dot: 'bg-red-400',
        },
    }

    if (isClaimUnderReview.value) {
        return {
            label: 'Claim under review',
            description: 'Your claim request has been submitted successfully. Our team is reviewing it and will update you once a decision has been made.',
            color: 'border-sky-500/30 bg-sky-500/10 text-sky-300',
            dot: 'bg-sky-400',
        }
    }

    return statuses[normalizedStatus.value] || statuses.won
})

const winningMethod = computed(() => {
    return String(props.prize?.winning_method || 'instant_win')
        .replaceAll('_', ' ')
        .replace(/\b\w/g, letter => letter.toUpperCase())
})

const submitClaim = () => {
    if (!props.prize?.winning_id || claimForm.processing) {
        return
    }

    claimForm.post(
        route('user.prizes.claim', {
            winningTicket: props.prize.winning_id,
        }),
        {
            preserveScroll: true,
        }
    )
}
</script>

<template>
    <Head :title="`Your Prize - ${prize.prize_name}`" />

    <UserLayout>
        <div class="min-h-screen bg-black px-4 py-8 text-amber-50 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-5xl space-y-7">

                <!-- Back navigation -->
                <div class="flex items-center justify-between">
                    <Link
                        :href="route('user.tickets.index')"
                        class="inline-flex items-center gap-2 text-sm font-medium text-amber-100/50 transition hover:text-amber-300"
                    >
                        <span class="text-lg">←</span>
                        Back to My Tickets
                    </Link>

                    <span class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-100/30">
                        PrizeGuyana
                    </span>
                </div>

                <!-- Winner confirmation -->
                <section class="relative overflow-hidden rounded-3xl border border-emerald-500/20 bg-gradient-to-br from-emerald-500/[0.12] via-[#101510] to-black p-6 sm:p-9">
                    <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-emerald-500/[0.08] blur-3xl"></div>

                    <div class="relative flex flex-col items-center text-center">
                        <div class="mb-5 flex h-16 w-16 items-center justify-center rounded-2xl border border-emerald-400/25 bg-emerald-400/10 text-3xl text-emerald-300 shadow-lg shadow-emerald-950/30">
                            <svg viewBox="0 0 24 24" class="h-9 w-9" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M12 3l2.2 4.5 5 .7-3.6 3.5.85 5-4.45-2.35L7.55 16.7l.85-5L4.8 8.2l5-.7L12 3z" stroke-linejoin="round"/>
                                <path d="M8 20h8" stroke-linecap="round"/>
                            </svg>
                        </div>

                        <span class="rounded-full border border-emerald-400/25 bg-emerald-400/10 px-4 py-1.5 text-xs font-bold uppercase tracking-[0.18em] text-emerald-300">
                            Congratulations · Instant Win
                        </span>

                        <h1 class="mt-5 text-3xl font-bold tracking-tight text-white sm:text-4xl">
                            You've Won!
                        </h1>

                        <p class="mt-3 max-w-xl text-sm leading-7 text-neutral-400 sm:text-base">
                            Your ticket matched a preselected winning entry. Congratulations on winning this prize!
                        </p>
                    </div>
                </section>

                <!-- Prize information -->
                <section class="overflow-hidden rounded-3xl border border-amber-500/20 bg-[#0b0b0b]">
                    <div class="grid md:grid-cols-2">

                        <!-- Prize image -->
                        <div class="relative flex min-h-[280px] items-center justify-center overflow-hidden border-b border-amber-500/10 bg-gradient-to-br from-amber-500/[0.08] to-black p-6 md:min-h-[420px] md:border-b-0 md:border-r">
                            <div
                                v-if="imageUrl"
                                class="flex h-full w-full items-center justify-center"
                            >
                                <img
                                    :src="imageUrl"
                                    :alt="prize.prize_name"
                                    class="max-h-[340px] w-full rounded-xl object-contain"
                                />
                            </div>

                            <div v-else class="flex flex-col items-center text-center">
                                <div class="flex h-28 w-28 items-center justify-center rounded-3xl border border-amber-500/20 bg-amber-500/[0.06]">
                                    <svg viewBox="0 0 24 24" class="h-14 w-14 text-amber-400/60" fill="none" stroke="currentColor" stroke-width="1.2">
                                        <path d="M20 12v8H4v-8m-2-5h20v5H2zm10 0v13M12 7H7.5a2.5 2.5 0 110-5C11 2 12 7 12 7zm0 0h4.5a2.5 2.5 0 100-5C13 2 12 7 12 7z" stroke-linejoin="round" stroke-linecap="round"/>
                                    </svg>
                                </div>

                                <p class="mt-4 text-xs font-medium uppercase tracking-[0.2em] text-amber-100/30">
                                    Your winning reward
                                </p>
                            </div>

                            <div class="absolute left-5 top-5">
                                <span class="rounded-full border border-amber-500/20 bg-black/70 px-3 py-1.5 text-xs font-semibold text-amber-300 backdrop-blur">
                                    {{ winningMethod }}
                                </span>
                            </div>
                        </div>

                        <!-- Prize details -->
                        <div class="flex flex-col justify-center p-6 sm:p-9">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-500/60">
                                Your reward
                            </p>

                            <h2 class="mt-3 text-3xl font-bold leading-tight text-white sm:text-4xl">
                                {{ prize.prize_name }}
                            </h2>

                            <p class="mt-3 text-sm leading-7 text-neutral-400">
                                {{ prize.prize_description || 'You have successfully won this prize through an Instant Win entry.' }}
                            </p>

                            <div class="my-7 h-px bg-gradient-to-r from-amber-500/30 via-amber-500/10 to-transparent"></div>

                            <div class="space-y-5">
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wider text-neutral-500">
                                        Competition
                                    </p>
                                    <p class="mt-1 font-semibold text-amber-100">
                                        {{ prize.competition_title }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wider text-neutral-500">
                                        Winning ticket
                                    </p>
                                    <p class="mt-1 font-mono text-xl font-bold tracking-wider text-amber-400">
                                        {{ prize.ticket_number }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wider text-neutral-500">
                                        Date won
                                    </p>
                                    <p class="mt-1 text-sm font-medium text-neutral-200">
                                        {{ formatDate(prize.won_at, 'Awaiting confirmation') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Claim and fulfilment status -->
                <section class="rounded-3xl border border-amber-500/20 bg-[#0b0b0b] p-6 sm:p-8">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-500/60">
                                Prize claim
                            </p>

                            <h2 class="mt-2 text-xl font-bold text-white">
                                Your reward status
                            </h2>
                        </div>

                        <span
                            class="inline-flex w-fit items-center gap-2 rounded-full border px-4 py-2 text-xs font-bold"
                            :class="statusConfig.color"
                        >
                            <span class="h-2 w-2 rounded-full" :class="statusConfig.dot"></span>
                            {{ statusConfig.label }}
                        </span>
                    </div>

                    <div class="mt-6 rounded-2xl border border-neutral-800 bg-black/60 p-5">
                        <div class="flex items-start gap-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-400">
                                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7">
                                    <circle cx="12" cy="12" r="9"/>
                                    <path d="M12 7v5l3 2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>

                            <div>
                                <h3 class="font-semibold text-amber-100">
                                    {{ statusConfig.label }}
                                </h3>

                                <p class="mt-1 text-sm leading-6 text-neutral-400">
                                    {{ statusConfig.description }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Claim submission action -->
                    <div
                        v-if="isReadyToClaim"
                        class="mt-6 rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.05] p-5 sm:p-6"
                    >
                        <h3 class="text-lg font-bold text-white">
                            Ready to claim your prize?
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-neutral-400">
                            Submit your claim request. Our team will review your details and contact you regarding your prize.
                        </p>

                        <button
                            type="button"
                            :disabled="claimForm.processing"
                            @click="submitClaim"
                            class="mt-5 inline-flex w-full items-center justify-center rounded-xl bg-amber-500 px-6 py-3.5 text-sm font-bold text-black shadow-lg shadow-amber-950/20 transition hover:bg-amber-400 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                        >
                            <span v-if="claimForm.processing">
                                Submitting...
                            </span>
                            <span v-else>
                                Claim This Prize →
                            </span>
                        </button>

                        <p
                            v-if="claimForm.errors.winningTicket"
                            class="mt-3 text-sm text-red-400"
                        >
                            {{ claimForm.errors.winningTicket }}
                        </p>
                    </div>

                    <!-- Claim submitted confirmation -->
                    <div
                        v-else-if="isClaimUnderReview"
                        class="mt-6 rounded-2xl border border-sky-500/20 bg-sky-500/[0.05] p-5 sm:p-6"
                    >
                        <h3 class="text-lg font-bold text-sky-300">
                            Claim successfully submitted
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-neutral-400">
                            Your claim is awaiting admin review. You will be able to see updates here.
                        </p>
                    </div>

                    <!-- Claim and fulfilment dates -->
                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl border border-neutral-800 p-4">
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500">
                                Claim submitted
                            </p>

                            <p class="mt-2 text-sm font-semibold text-neutral-200">
                                {{ formatDate(prize.claimed_at) }}
                            </p>
                        </div>

                        <div class="rounded-xl border border-neutral-800 p-4">
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500">
                                Fulfilment date
                            </p>

                            <p class="mt-2 text-sm font-semibold text-neutral-200">
                                {{ formatDate(prize.fulfilled_at) }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Important information -->
                <section class="rounded-2xl border border-amber-500/15 bg-amber-500/[0.035] p-5 sm:p-6">
                    <div class="flex items-start gap-3">
                        <svg viewBox="0 0 24 24" class="mt-0.5 h-5 w-5 shrink-0 text-amber-400" fill="none" stroke="currentColor" stroke-width="1.7">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M12 11v5m0-8h.01" stroke-linecap="round"/>
                        </svg>

                        <div>
                            <h3 class="text-sm font-bold text-amber-200">
                                What happens next?
                            </h3>

                            <p
                                v-if="isReadyToClaim"
                                class="mt-2 text-sm leading-6 text-amber-100/50"
                            >
                                Click Claim This Prize to submit your request. Our team will review your claim and contact you using the information in your account. Please make sure your contact details are up to date.
                            </p>

                            <p
                                v-else-if="isClaimUnderReview"
                                class="mt-2 text-sm leading-6 text-amber-100/50"
                            >
                                Your claim has been received and is awaiting admin review. Our team will contact you once a decision has been made.
                            </p>

                            <p
                                v-else-if="normalizedStatus === 'cancelled'"
                                class="mt-2 text-sm leading-6 text-amber-100/50"
                            >
                                Your claim was not approved or has been cancelled. Please contact our support team for further information.
                            </p>

                            <p
                                v-else
                                class="mt-2 text-sm leading-6 text-amber-100/50"
                            >
                                Our team is coordinating your prize fulfilment. Please keep your account contact information up to date so we can reach you regarding your reward.
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Footer actions -->
                <div class="flex flex-col gap-3 pb-6 sm:flex-row sm:justify-between">
                    <Link
                        :href="route('user.tickets.index')"
                        class="inline-flex items-center justify-center rounded-xl border border-amber-500/25 px-6 py-3 text-sm font-semibold text-amber-300 transition hover:border-amber-500/50 hover:bg-amber-500/5"
                    >
                        ← Back to My Tickets
                    </Link>

                    <Link
                        :href="route('user.dashboard')"
                        class="inline-flex items-center justify-center rounded-xl bg-amber-500 px-6 py-3 text-sm font-bold text-black shadow-lg shadow-amber-950/20 transition hover:bg-amber-400"
                    >
                        Go to Dashboard →
                    </Link>
                </div>

            </div>
        </div>
    </UserLayout>
</template>
