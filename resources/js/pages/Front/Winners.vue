<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import FrontLayout from '@/Layouts/FrontLayout.vue'

defineOptions({
    layout: FrontLayout,
})

const props = defineProps({
    winners: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
        }),
    },
})

const search = ref(props.filters.search || '')

const submitSearch = () => {
    router.get(
        route('winners'),
        { search: search.value.trim() || undefined },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    )
}

const clearSearch = () => {
    search.value = ''
    submitSearch()
}

const formatDate = (date) => {
    if (!date) return 'Date unavailable'

    return new Intl.DateTimeFormat('en-US', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(date))
}
</script>

<template>

    <Head title="Winners | PrizeGuyana" />

   
<section class="min-h-screen bg-[#0b0b0b] px-4 py-14 text-white sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">

        <!-- Page Header -->
        <div class="mb-10 border-b border-white/10 pb-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-amber-500/20 bg-amber-500/[0.07] px-3 py-1.5">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-amber-400">
                            Celebrating Our Winners
                        </span>
                    </div>

                    <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl">
                        The Winners
                        <span class="text-amber-500">Circle.</span>
                    </h1>

                    <p class="mt-3 max-w-xl text-sm leading-6 text-neutral-400 sm:text-base">
                        Real people. Real prizes. Discover the lucky winners
                        who have taken home rewards from PrizeGuyana.
                    </p>
                </div>

                <!-- Search -->
                <form
                    @submit.prevent="submitSearch"
                    class="flex w-full max-w-md items-center rounded-xl border border-white/10 bg-[#141414] p-1.5 transition focus-within:border-amber-500/50"
                >
                    <svg class="ml-3 h-5 w-5 shrink-0 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="7" stroke-width="1.7" />
                        <path stroke-linecap="round" stroke-width="1.7" d="m16 16 4 4" />
                    </svg>

                    <input
                        v-model="search"
                        type="search"
                        placeholder="Search winners, prizes or tickets..."
                        class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2.5 text-sm text-white outline-none placeholder:text-neutral-600 focus:ring-0"
                    />

                    <button
                        type="submit"
                        class="rounded-lg bg-amber-500 px-5 py-2.5 text-xs font-bold text-black transition hover:bg-amber-400"
                    >
                        Search
                    </button>
                </form>
            </div>
        </div>

        <!-- Search Filter -->
        <div v-if="filters.search" class="mb-7 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-neutral-400">
                Showing results for
                <span class="font-semibold text-amber-400">"{{ filters.search }}"</span>
            </p>

            <button
                @click="clearSearch"
                class="text-xs font-semibold text-neutral-400 transition hover:text-amber-400"
            >
                Clear search
            </button>
        </div>

        <!-- Winners -->
        <div v-if="winners.data.length" class="grid grid-cols-1 gap-5 lg:grid-cols-2">

            <article
                v-for="winner in winners.data"
                :key="winner.id"
                class="group relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#121212] transition duration-300 hover:border-amber-500/40"
            >
                <!-- Top accent -->
                <div class="absolute left-0 top-0 h-[2px] w-full bg-gradient-to-r from-transparent via-amber-500/70 to-transparent"></div>

                <div class="p-6 sm:p-7">

                    <!-- Competition -->
                    <div class="mb-7 flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-neutral-500">
                                Competition
                            </p>

                            <h2 class="text-lg font-bold leading-snug text-white transition group-hover:text-amber-400 sm:text-xl">
                                {{ winner.competition_title }}
                            </h2>
                        </div>

                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/[0.07]">
                            <svg class="h-5 w-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                    d="M8 21h8M12 17v4M7 4h10v4a5 5 0 01-10 0V4zM5 4h14M5 8a3 3 0 01-3-3h3m14 3a3 3 0 003-3h-3" />
                            </svg>
                        </div>
                    </div>

                    <!-- Winner Identity -->
                    <div class="flex items-center gap-4 border-y border-white/[0.07] py-6">
                        <div class="flex h-[68px] w-[68px] shrink-0 items-center justify-center rounded-2xl border border-amber-400/30 bg-gradient-to-br from-amber-500/20 to-amber-900/10 shadow-inner shadow-amber-500/10">
                            <span class="text-2xl font-extrabold tracking-wide text-amber-300">
                                {{ winner.initials || '?' }}
                            </span>
                        </div>

                        <div class="min-w-0">
                            <p class="mb-1 text-[10px] font-bold uppercase tracking-[0.16em] text-neutral-500">
                                Congratulations to
                            </p>

                            <h3 class="break-words text-lg font-bold leading-tight text-white sm:text-xl">
                                {{ winner.winner_name || 'PrizeGuyana Winner' }}
                            </h3>

                            <p class="mt-1.5 text-xs text-neutral-500">
                                Winning ticket
                                <span class="ml-1 font-bold text-amber-400">
                                    #{{ winner.ticket_number }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <!-- Prize Information -->
                    <div class="mt-6 flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="mb-1.5 text-[10px] font-bold uppercase tracking-[0.16em] text-neutral-500">
                                Prize Won
                            </p>

                            <p class="break-words text-sm font-semibold leading-relaxed text-amber-400 sm:text-base">
                                {{ winner.prize_name || winner.prize_description || 'Competition Prize' }}
                            </p>
                        </div>

                        <div class="shrink-0 text-right">
                            <p class="mb-1.5 text-[10px] font-bold uppercase tracking-[0.16em] text-neutral-500">
                                Winner Since
                            </p>

                            <p class="text-xs font-medium text-neutral-300">
                                {{ formatDate(winner.won_at) }}
                            </p>
                        </div>
                    </div>

                </div>
            </article>
        </div>

        <!-- Empty State -->
        <div
            v-else
            class="rounded-2xl border border-white/10 bg-[#121212] px-6 py-20 text-center"
        >
            <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl border border-amber-500/20 bg-amber-500/[0.07]">
                <svg class="h-8 w-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M8 21h8M12 17v4M7 4h10v4a5 5 0 01-10 0V4zM5 4h14M5 8a3 3 0 01-3-3h3m14 3a3 3 0 003-3h-3" />
                </svg>
            </div>

            <h2 class="text-xl font-bold text-white">No Winners Found</h2>

            <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-neutral-500">
                {{
                    filters.search
                        ? 'Try another winner name, ticket number or competition.'
                        : 'Competition winners will be celebrated here once results are available.'
                }}
            </p>

            <button
                v-if="filters.search"
                @click="clearSearch"
                class="mt-6 rounded-lg bg-amber-500 px-5 py-2.5 text-sm font-bold text-black transition hover:bg-amber-400"
            >
                Clear Search
            </button>
        </div>

        <!-- Pagination -->
        <div
            v-if="winners.links && winners.last_page > 1"
            class="mt-10 flex flex-wrap justify-center gap-2"
        >
            <Link
                v-for="(link, index) in winners.links"
                :key="index"
                :href="link.url || '#'"
                :class="[
                    'rounded-lg border px-4 py-2.5 text-xs font-semibold transition',
                    link.active
                        ? 'border-amber-500 bg-amber-500 text-black'
                        : link.url
                            ? 'border-white/10 bg-[#141414] text-neutral-300 hover:border-amber-500/40 hover:text-amber-400'
                            : 'cursor-not-allowed border-white/5 bg-[#111111] text-neutral-600',
                ]"
                v-html="link.label"
            />
        </div>

    </div>
</section>


</template>
