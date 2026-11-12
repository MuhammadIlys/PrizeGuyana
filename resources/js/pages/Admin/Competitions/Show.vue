<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { formatDate } from '../../../helpers/date'
import { Head, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
    competition: {
        type: Object,
        required: true,
    },
    category: {
        type: Object,
        default: null,
    },
    questions: {
        type: Array,
        default: () => [],
    },
    prizes: {
        type: Array,
        default: () => [],
    },
})

const isFinishingDraw = ref(false)

const canFinishDraw = computed(() =>
    String(props.competition?.status || '').toLowerCase() === 'active'
)

const finishDraw = () => {
    if (!canFinishDraw.value || isFinishingDraw.value) return

    const confirmed = window.confirm(
        'Are you sure you want to finish this competition? The system will randomly select the scheduled draw winners and close the competition. This action cannot be undone.'
    )

    if (!confirmed) return

    isFinishingDraw.value = true

    router.post(
        route('admin.tickets.finish-draw', props.competition.encrypted_id),
        {},
        {
            preserveScroll: true,
            onError: (errors) => {
                const message = Object.values(errors).flat()[0]
                if (message) window.alert(message)
            },
            onFinish: () => {
                isFinishingDraw.value = false
            },
        }
    )
}

const statusStyles = {
    draft: 'rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-black',
    published: 'rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-black',
    active: 'rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-black',
    completed: 'rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-black',
    cancelled: 'rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-black',
}

const getStatusClass = (status) =>
    statusStyles[status] ??
    'border-amber-500/30 bg-amber-500/10 text-amber-400'

const displayValue = (value) =>
    value !== null && value !== undefined && value !== ''
        ? value
        : '—'

const formatMoney = (amount) => {
    if (amount === null || amount === undefined || amount === '') {
        return '—'
    }

    return `${props.competition.currency ?? 'USD'} ${Number(amount).toLocaleString(
        'en-US',
        {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }
    )}`
}

const formatPrizeMoney = (amount, currency = 'USD') => {
    if (amount === null || amount === undefined || amount === '') return '—'

    return `${currency} ${Number(amount).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`
}

const yesNo = (value) => value ? 'Enabled' : 'Disabled'

const yesNoClass = (value) =>
    value
        ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400'
        : 'border-white/10 bg-white/5 text-amber-100/50'
</script>

<template>

    <Head title="Competition detail"></Head>
    <AdminLayout>
        <div class="mx-auto max-w-7xl space-y-7 p-4 text-amber-50 sm:p-6 lg:p-8">

            <!-- Page header -->
            <header class="flex flex-col justify-between gap-5 sm:flex-row sm:items-center">
                <div>
                    <div class="mb-3 flex items-center gap-2 text-xs text-amber-100/40">
                        <a :href="route('admin.competitions.index')" class="transition hover:text-amber-400">
                            Competitions
                        </a>
                        <span>/</span>
                        <span class="text-amber-200">Details</span>
                    </div>

                    <h1 class="text-2xl font-bold tracking-tight text-amber-400 sm:text-3xl">
                        Competition Details
                    </h1>

                    <p class="mt-2 text-sm text-amber-100/50">
                        Review competition settings, schedule, media, and metadata.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a :href="route('admin.competitions.index')"
                        class="rounded-xl border border-amber-500/25 px-4 py-2.5 text-sm font-semibold text-amber-100 transition hover:bg-amber-500/10">
                        ← Back to Competitions
                    </a>

                    <button
                        v-if="canFinishDraw"
                        type="button"
                        @click="finishDraw"
                        :disabled="isFinishingDraw"
                        class="rounded-xl border border-red-500/30 bg-red-500/10 px-5 py-2.5 text-sm font-bold text-red-300 transition hover:bg-red-500/20 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{ isFinishingDraw ? 'Finishing Competition...' : 'Finish Competition' }}
                    </button>

                    <a :href="route('admin.competitions.edit', {
                        competition: competition.encrypted_id
                    })"
                        class="rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-bold text-black transition hover:bg-amber-400">
                        Edit Competition ↗
                    </a>
                </div>
            </header>

            <!-- Hero / Overview -->
            <section
                class="overflow-hidden rounded-2xl border border-amber-500/20 bg-black shadow-xl shadow-amber-500/5">
                <div class="grid grid-cols-1 lg:grid-cols-5">
                    <div class="relative min-h-64 bg-amber-500/5 lg:col-span-2">
                        <img v-if="competition.main_image" :src="`/storage/${competition.main_image}`"
                            :alt="competition.title" class="absolute inset-0 h-full w-full object-cover" />

                        <div v-else class="flex min-h-64 items-center justify-center text-sm text-amber-100/40">
                            No competition image available
                        </div>

                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent"></div>

                        <div class="absolute bottom-4 left-4 flex flex-wrap gap-2">
                            <span
                                class="rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-black"
                                :class="getStatusClass(competition.status)">
                                {{ competition.status || 'Unknown' }}
                            </span>

                            <span v-if="competition.is_featured"
                                class="rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-black">
                                ★ Featured
                            </span>
                            <!-- Instant Win Badge -->
                            <span v-if="competition.instant_win_enabled"
                                class="rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-black">
                                ⚡︎ Instant Win
                            </span>
                            <!-- Qualification Badge -->
                            <span v-if="competition.qualification_enabled"
                                class="rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-black">
                                ✓ Qualification Required
                            </span>
                        </div>
                    </div>

                    <div class="space-y-6 p-6 sm:p-8 lg:col-span-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-500/70">
                                Competition Overview
                            </p>

                            <h2 class="mt-3 break-words text-2xl font-bold leading-tight text-white sm:text-3xl">
                                {{ competition.title }}
                            </h2>

                            <p class="mt-3 whitespace-pre-line text-sm leading-7 text-amber-100/60">
                                {{ competition.short_description || 'No short description provided.' }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-5 border-t border-amber-500/15 pt-5 sm:grid-cols-2">
                            <div>
                                <p class="text-xs text-amber-100/40">Internal Reference</p>
                                <p class="mt-1 break-all font-semibold text-amber-200">
                                    {{ displayValue(competition.internal_reference) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-amber-100/40">Competition Slug</p>
                                <p class="mt-1 break-all font-medium text-white">
                                    {{ displayValue(competition.slug) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-amber-100/40">Category</p>
                                <p class="mt-1 font-semibold text-white">
                                    {{ category?.name || 'Uncategorized' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-amber-100/40">Record ID</p>
                                <p class="mt-1 font-semibold text-white">
                                    #{{ competition.id }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Key metrics -->
            <section>
                <div class="mb-4">
                    <h2 class="text-lg font-bold text-white">Entry Overview</h2>
                    <p class="mt-1 text-sm text-amber-100/40">
                        Pricing and entry capacity configured for this competition.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-2xl border border-amber-500/20 bg-black p-5">
                        <p class="text-sm text-amber-100/50">Entry Price</p>
                        <p class="mt-3 break-words text-2xl font-bold text-amber-400">
                            {{ formatMoney(competition.entry_price) }}
                        </p>
                        <p class="mt-2 text-xs text-amber-100/35">Per entry</p>
                    </div>

                    <div class="rounded-2xl border border-amber-500/20 bg-black p-5">
                        <p class="text-sm text-amber-100/50">Total Entries</p>
                        <p class="mt-3 text-2xl font-bold text-white">
                            {{ Number(competition.total_entries || 0).toLocaleString() }}
                        </p>
                        <p class="mt-2 text-xs text-amber-100/35">Configured capacity</p>
                    </div>

                    <div class="rounded-2xl border border-amber-500/20 bg-black p-5">
                        <p class="text-sm text-amber-100/50">Max Entries / Customer</p>
                        <p class="mt-3 text-2xl font-bold text-white">
                            {{ displayValue(competition.max_entries_per_customer) }}
                        </p>
                        <p class="mt-2 text-xs text-amber-100/35">Purchase limit</p>
                    </div>

                    <div class="rounded-2xl border border-amber-500/20 bg-black p-5">
                        <p class="text-sm text-amber-100/50">Minimum Age</p>
                        <p class="mt-3 text-2xl font-bold text-white">
                            {{ displayValue(competition.minimum_age) }}
                            <span class="text-sm font-medium text-amber-100/40">years</span>
                        </p>
                        <p class="mt-2 text-xs text-amber-100/35">Participant requirement</p>
                    </div>
                </div>
            </section>

            <!-- Competition Prizes -->
            <section>
                <div class="mb-4">
                    <h2 class="text-lg font-bold text-white">Competition Prizes</h2>
                    <p class="mt-1 text-sm text-amber-100/40">
                        Main and small prizes configured for this competition, including how each prize is awarded.
                    </p>
                </div>

                <div v-if="prizes.length" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <article v-for="prize in prizes" :key="prize.id"
                        class="overflow-hidden rounded-2xl border border-amber-500/20 bg-black">
                        <div class="relative flex h-48 items-center justify-center bg-amber-500/[0.04]">
                            <img v-if="prize.image_path" :src="`/storage/${prize.image_path}`" :alt="prize.name"
                                class="h-full w-full object-cover" />
                            <span v-else class="text-sm text-amber-100/35">No prize image available</span>
                            <div class="absolute left-3 top-3 flex flex-wrap gap-2">
                                <span class="rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-black">
                                    {{ prize.prize_role === 'main' ? 'Main Prize' : 'Small Prize' }}
                                </span>
                                <span class="rounded-lg border border-amber-500/25 bg-black/80 px-3 py-1.5 text-xs font-semibold text-amber-200">
                                    {{ prize.winning_method === 'instant_win' ? 'Instant Win' : 'Scheduled Draw' }}
                                </span>
                            </div>
                        </div>

                        <div class="space-y-4 p-5">
                            <div>
                                <h3 class="break-words text-lg font-bold text-white">{{ prize.name }}</h3>
                                <p class="mt-1 text-xs capitalize text-amber-100/45">
                                    {{ prize.prize_type === 'cash' ? 'Cash Prize' : 'Physical Prize' }}
                                </p>
                                <p v-if="prize.description" class="mt-3 whitespace-pre-line break-words text-sm leading-6 text-amber-100/60">
                                    {{ prize.description }}
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-3 border-t border-amber-500/10 pt-4">
                                <div>
                                    <p class="text-xs text-amber-100/40">Prize Value</p>
                                    <p class="mt-1 break-words font-semibold text-amber-400">
                                        {{ formatPrizeMoney(prize.prize_value, prize.currency || 'USD') }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-amber-100/40">Quantity</p>
                                    <p class="mt-1 font-semibold text-white">{{ prize.quantity }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-amber-100/40">Status</p>
                                    <span class="mt-1 inline-flex rounded-lg border px-2.5 py-1 text-xs font-semibold"
                                        :class="prize.status === 'active'
                                            ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400'
                                            : 'border-white/10 bg-white/5 text-amber-100/50'">
                                        {{ prize.status || 'Unknown' }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-xs text-amber-100/40">Display Order</p>
                                    <p class="mt-1 font-semibold text-white">{{ displayValue(prize.sort_order) }}</p>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>

                <div v-else class="rounded-2xl border border-dashed border-amber-500/20 bg-black p-8 text-center">
                    <p class="font-medium text-amber-100/70">No prizes added</p>
                    <p class="mt-2 text-sm text-amber-100/40">
                        No prizes have been configured for this competition yet.
                    </p>
                </div>
            </section>

            <!-- Schedule and rules -->
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

                <section class="rounded-2xl border border-amber-500/20 bg-black p-5 sm:p-6">
                    <div class="mb-5">
                        <h2 class="text-lg font-bold text-amber-400">Competition Schedule</h2>
                        <p class="mt-1 text-sm text-amber-100/40">
                            Configured start, closing, and draw dates.
                        </p>
                    </div>

                    <div class="space-y-0">
                        <div class="flex gap-4 border-b border-amber-500/10 py-4">
                            <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-emerald-400"></span>
                            <div class="min-w-0">
                                <p class="text-sm text-amber-100/50">Start Date</p>
                                <p class="mt-1 break-words font-semibold text-white">
                                    {{ formatDate(competition.starts_at) }}
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-4 border-b border-amber-500/10 py-4">
                            <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-amber-400"></span>
                            <div class="min-w-0">
                                <p class="text-sm text-amber-100/50">Closing Date</p>
                                <p class="mt-1 break-words font-semibold text-white">
                                    {{ formatDate(competition.ends_at) }}
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-4 py-4">
                            <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-red-400"></span>
                            <div class="min-w-0">
                                <p class="text-sm text-amber-100/50">Draw Date</p>
                                <p class="mt-1 break-words font-semibold text-white">
                                    {{ formatDate(competition.draw_at) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-amber-500/20 bg-black p-5 sm:p-6">
                    <div class="mb-5">
                        <h2 class="text-lg font-bold text-amber-400">Rules & Entry Options</h2>
                        <p class="mt-1 text-sm text-amber-100/40">
                            Configured participation and draw settings.
                        </p>
                    </div>

                    <div class="divide-y divide-amber-500/10">
                        <div v-for="option in [
                            {
                                label: 'Featured Competition',
                                value: competition.is_featured
                            },
                            {
                                label: 'Qualification Required',
                                value: competition.qualification_enabled
                            },
                            {
                                label: 'Instant Win Enabled',
                                value: competition.instant_win_enabled
                            }
                        ]" :key="option.label" class="flex items-center justify-between gap-4 py-4">
                            <span class="text-sm font-medium text-white">
                                {{ option.label }}
                            </span>

                            <span class="rounded-lg border px-3 py-1.5 text-xs font-semibold"
                                :class="yesNoClass(option.value)">
                                {{ yesNo(option.value) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-4 py-4">
                            <span class="text-sm font-medium text-white">Entry Allocation</span>
                            <span class="text-right text-sm font-semibold capitalize text-amber-200">
                                {{ displayValue(competition.entry_allocation_method) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-4 py-4">
                            <span class="text-sm font-medium text-white">Draw Method</span>
                            <span class="text-right text-sm font-semibold capitalize text-amber-200">
                                {{ displayValue(competition.draw_method) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-4 py-4">
                            <span class="text-sm font-medium text-white">Terms ID</span>
                            <span class="text-sm font-semibold text-amber-200">
                                {{ displayValue(competition.terms_id) }}
                            </span>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Qualification Questions -->
            <section>
                <div class="mb-4">
                    <h2 class="text-lg font-bold text-white">Qualification Questions</h2>
                    <p class="mt-1 text-sm text-amber-100/40">
                        Questions selected for this competition and their configured answers.
                    </p>
                </div>

                <div v-if="questions.length" class="space-y-4">
                    <div v-for="(question, index) in questions" :key="question.id"
                        class="rounded-2xl border border-amber-500/20 bg-black p-5 sm:p-6">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="flex min-w-0 gap-3">
                                <span
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-500/10 text-sm font-bold text-amber-400">
                                    {{ index + 1 }}
                                </span>

                                <div class="min-w-0">
                                    <h3 class="break-words font-semibold text-white">
                                        {{ question.title }}
                                    </h3>
                                    <p class="mt-2 whitespace-pre-line break-words text-sm leading-6 text-amber-100/70">
                                        {{ question.question }}
                                    </p>
                                </div>
                            </div>

                            <span
                                class="w-fit shrink-0 rounded-lg border border-amber-500/20 bg-amber-500/10 px-3 py-1 text-xs font-medium text-amber-300">
                                Question {{ index + 1 }}
                            </span>
                        </div>

                        <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div v-for="option in [
                                { key: 'A', value: question.option_a },
                                { key: 'B', value: question.option_b },
                                { key: 'C', value: question.option_c },
                                { key: 'D', value: question.option_d },
                            ].filter(option => option.value)" :key="option.key"
                                class="flex items-start gap-3 rounded-xl border p-3" :class="String(question.correct_answer).toUpperCase() === option.key
                                    ? 'border-emerald-500/30 bg-emerald-500/5'
                                    : 'border-white/10 bg-white/[0.02]'">
                                <span
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-xs font-bold"
                                    :class="String(question.correct_answer).toUpperCase() === option.key
                                        ? 'bg-emerald-500/15 text-emerald-400'
                                        : 'bg-white/5 text-amber-100/50'">
                                    {{ option.key }}
                                </span>

                                <div class="min-w-0 flex-1">
                                    <p class="break-words text-sm text-amber-50/90">
                                        {{ option.value }}
                                    </p>

                                    <p v-if="String(question.correct_answer).toUpperCase() === option.key"
                                        class="mt-1 text-xs font-medium text-emerald-400">
                                        Correct Answer
                                    </p>
                                </div>

                                <icon v-if="String(question.correct_answer).toUpperCase() === option.key"
                                    name="check-circle" size="sm" color="success" />
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="rounded-2xl border border-dashed border-amber-500/20 bg-black p-8 text-center">
                    <p class="font-medium text-amber-100/70">
                        No qualification questions selected
                    </p>
                    <p class="mt-2 text-sm text-amber-100/40">
                        No questions have been assigned to this competition yet.
                    </p>
                </div>
            </section>

            <!-- Description -->
            <section class="rounded-2xl border border-amber-500/20 bg-black p-5 sm:p-6">
                <div class="mb-5">
                    <h2 class="text-lg font-bold text-amber-400">Full Description</h2>
                    <p class="mt-1 text-sm text-amber-100/40">
                        The complete competition description.
                    </p>
                </div>

                <div class="rounded-xl border border-amber-500/10 bg-amber-500/[0.03] p-5">
                    <p class="whitespace-pre-line break-words text-sm leading-7 text-amber-100/80">
                        {{ competition.description || 'No full description provided.' }}
                    </p>
                </div>
            </section>

            <!-- SEO -->
            <!-- <section class="rounded-2xl border border-amber-500/20 bg-black p-5 sm:p-6">
                <div class="mb-5">
                    <h2 class="text-lg font-bold text-amber-400">SEO Metadata</h2>
                    <p class="mt-1 text-sm text-amber-100/40">
                        Search engine title and description.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <div class="rounded-xl border border-amber-500/10 bg-amber-500/[0.03] p-4">
                        <p class="text-xs font-semibold uppercase tracking-wider text-amber-100/40">
                            Meta Title
                        </p>
                        <p class="mt-3 break-words text-sm font-semibold text-white">
                            {{ displayValue(competition.meta_title) }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-amber-500/10 bg-amber-500/[0.03] p-4">
                        <p class="text-xs font-semibold uppercase tracking-wider text-amber-100/40">
                            Meta Description
                        </p>
                        <p class="mt-3 whitespace-pre-line break-words text-sm leading-6 text-amber-100/70">
                            {{ displayValue(competition.meta_description) }}
                        </p>
                    </div>
                </div>
            </section> -->

            <!-- Video -->
            <section class="rounded-2xl border border-amber-500/20 bg-black p-5 sm:p-6">
                <div class="mb-5">
                    <h2 class="text-lg font-bold text-amber-400">Promotional Video</h2>
                    <p class="mt-1 text-sm text-amber-100/40">
                        Video associated with this competition.
                    </p>
                </div>

                <div v-if="competition.video_path" class="max-w-3xl">
                    <video :src="`/storage/${competition.video_path}`" controls preload="metadata"
                        class="max-h-[480px] w-full rounded-xl border border-amber-500/20 bg-black">
                        Your browser does not support video playback.
                    </video>
                    <p class="mt-3 break-all text-xs text-amber-100/40">
                        {{ competition.video_path }}
                    </p>
                </div>

                <div v-else
                    class="rounded-xl border border-dashed border-amber-500/20 bg-amber-500/[0.03] p-8 text-center">
                    <p class="font-medium text-amber-100/60">No promotional video uploaded</p>
                    <p class="mt-2 text-sm text-amber-100/35">
                        Add a video from the edit page if required.
                    </p>
                </div>
            </section>

            <!-- Record information -->
            <section class="rounded-2xl border border-amber-500/20 bg-black p-5 sm:p-6">
                <div class="mb-5">
                    <h2 class="text-lg font-bold text-amber-400">Record Information</h2>
                    <p class="mt-1 text-sm text-amber-100/40">
                        Creation, update, and publication timestamps.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <p class="text-xs text-amber-100/40">Created At</p>
                        <p class="mt-2 text-sm font-medium text-white">
                            {{ formatDate(competition.created_at) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-amber-100/40">Last Updated</p>
                        <p class="mt-2 text-sm font-medium text-white">
                            {{ formatDate(competition.updated_at) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-amber-100/40">Published At</p>
                        <p class="mt-2 text-sm font-medium text-white">
                            {{ formatDate(competition.published_at) }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- Bottom actions -->
            <footer
                class="flex flex-col justify-between gap-4 border-t border-amber-500/15 pt-5 sm:flex-row sm:items-center">
                <p class="text-xs text-amber-100/35">
                    Competition record #{{ competition.id }}
                </p>

                <div class="flex flex-wrap gap-3">
                    <a :href="route('admin.competitions.index')"
                        class="rounded-xl border border-amber-500/25 px-5 py-2.5 text-sm font-semibold text-amber-100 transition hover:bg-amber-500/10">
                        Back to List
                    </a>

                    <a :href="route('admin.competitions.edit', {
                        competition: competition.encrypted_id
                    })"
                        class="rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-bold text-black transition hover:bg-amber-400">
                        Edit Competition
                    </a>
                </div>
            </footer>

        </div>
    </AdminLayout>
</template>
