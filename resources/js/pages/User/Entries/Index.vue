<script setup>
import { computed, ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import UserLayout from '@/Layouts/UserLayout.vue'

const props = defineProps({
    entries: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({ search: '' }),
    },
})

const search = ref(props.filters?.search ?? '')

watch(
    () => props.filters?.search,
    (value) => {
        search.value = value ?? ''
    }
)

const rows = computed(() => props.entries?.data ?? [])

const totalEntries = computed(() =>
    rows.value.reduce((total, entry) => total + Number(entry.entries_count ?? 0), 0)
)

function applyFilters() {
    router.get(
        route('user.entries.index'),
        { search: search.value.trim() || undefined },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    )
}

function clearFilters() {
    search.value = ''
    router.get(
        route('user.entries.index'),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    )
}

function formatDate(value) {
    if (!value) return 'Not specified'

    const date = new Date(value)
    if (Number.isNaN(date.getTime())) return 'Not specified'

    return new Intl.DateTimeFormat(undefined, {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    }).format(date)
}

function competitionStatus(status, endsAt) {
    const normalized = String(status ?? '').toLowerCase()

    if (['ended', 'completed', 'closed', 'drawn', 'cancelled'].includes(normalized)) {
        return normalized === 'completed' || normalized === 'drawn' ? 'Ended' :
            normalized.charAt(0).toUpperCase() + normalized.slice(1)
    }

    if (endsAt) {
        const endDate = new Date(endsAt)
        if (!Number.isNaN(endDate.getTime()) && endDate < new Date()) return 'Ended'
    }

    if (['active', 'published', 'live'].includes(normalized)) return 'Active'

    return normalized
        ? normalized.charAt(0).toUpperCase() + normalized.slice(1)
        : 'Status unavailable'
}

function statusClass(status, endsAt) {
    const label = competitionStatus(status, endsAt).toLowerCase()

    if (label === 'active') return 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300'
    if (label === 'ended' || label === 'completed' || label === 'drawn') {
        return 'border-slate-500/20 bg-slate-500/10 text-slate-300'
    }
    if (label === 'cancelled') return 'border-red-500/20 bg-red-500/10 text-red-300'

    return 'border-amber-500/20 bg-amber-500/10 text-amber-200'
}
</script>

<template>

    <Head title="My Entries" />

    <UserLayout>
        <div class="min-w-0 space-y-6 text-amber-50">
            <!-- Page heading -->
            <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-amber-400/70">
                        Your participation
                    </p>
                    <h1 class="text-2xl font-bold tracking-tight text-amber-400 sm:text-3xl">
                        My Entries
                    </h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-amber-100/50">
                        View the competitions you've entered and keep track of your entries in one place.
                    </p>
                </div>

                <div
                    class="inline-flex w-fit items-center gap-2 rounded-lg border border-amber-500/15 bg-amber-500/[0.04] px-3 py-2 text-sm text-amber-100/70">
                    <svg class="h-4 w-4 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.7">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" />
                    </svg>
                    <span><span class="font-semibold text-amber-100">{{ entries.total ?? rows.length }}</span>
                        competitions</span>
                </div>
            </header>

            <!-- Summary -->
            <section class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="rounded-xl border border-amber-500/15 bg-[#0b0b0b] p-4 sm:p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm text-amber-100/50">Competitions entered</p>
                            <p class="mt-2 text-2xl font-bold tabular-nums text-amber-100">
                                {{ entries.total ?? rows.length }}
                            </p>
                        </div>
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl border border-amber-500/15 bg-amber-500/[0.06] text-amber-400">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8 21h8m-4-4v4m-7-17h14a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h6" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-amber-100/35">Competitions shown in your entries list</p>
                </div>

                <div class="rounded-xl border border-amber-500/15 bg-[#0b0b0b] p-4 sm:p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm text-amber-100/50">Entries on this page</p>
                            <p class="mt-2 text-2xl font-bold tabular-nums text-amber-100">
                                {{ totalEntries }}
                            </p>
                        </div>
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl border border-amber-500/15 bg-amber-500/[0.06] text-amber-400">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m9-9H3" />
                                <rect x="4" y="4" width="16" height="16" rx="3" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-amber-100/35">Assigned, non-cancelled entries on the current page</p>
                </div>
            </section>

            <!-- Search -->
            <section class="rounded-xl border border-amber-500/15 bg-[#0b0b0b] p-4 sm:p-5">
                <form class="grid grid-cols-1 items-end gap-3 sm:grid-cols-[minmax(0,1fr)_auto_auto]"
                    @submit.prevent="applyFilters">
                    <label class="block min-w-0">
                        <span class="mb-2 block text-xs font-semibold tracking-wide text-amber-100/60">
                            Search competitions
                        </span>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-amber-100/35"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.8">
                                <circle cx="11" cy="11" r="7" />
                                <path stroke-linecap="round" d="m16 16 4 4" />
                            </svg>
                            <input v-model="search" type="search" name="search"
                                placeholder="Search by competition name..."
                                class="h-11 w-full rounded-lg border border-amber-500/15 bg-black pl-10 pr-3 text-sm text-amber-50 outline-none transition placeholder:text-amber-100/30 hover:border-amber-500/30 focus:border-amber-500/70 focus:ring-2 focus:ring-amber-500/10" />
                        </div>
                    </label>

                    <button type="submit"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-amber-500 px-5 text-sm font-semibold text-black transition hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-500/40">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M7 12h10m-7 6h4" />
                        </svg>
                        Search
                    </button>

                    <button type="button" @click="clearFilters"
                        class="inline-flex h-11 items-center justify-center rounded-lg border border-amber-500/20 px-5 text-sm font-medium text-amber-100/70 transition hover:border-amber-500/40 hover:bg-amber-500/5 hover:text-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                        Clear
                    </button>
                </form>
            </section>

            <!-- Entries list -->
            <section class="overflow-hidden rounded-xl border border-amber-500/20 bg-black">
                <div
                    class="flex flex-col gap-2 border-b border-amber-500/15 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                    <div>
                        <h2 class="text-base font-semibold text-amber-100">Your competitions</h2>
                        <p class="mt-1 text-xs text-amber-100/40">Each competition is listed once, with your entry
                            totals.</p>
                    </div>
                    <p v-if="entries.total" class="text-xs text-amber-100/45">
                        Showing {{ entries.from ?? 1 }}–{{ entries.to ?? rows.length }} of {{ entries.total }}
                    </p>
                </div>

                <div v-if="rows.length" class="divide-y divide-amber-500/10">
                    <article v-for="entry in rows" :key="entry.competition_id"
                        class="flex flex-col gap-4 p-4 transition hover:bg-amber-500/[0.025] sm:p-5 lg:flex-row lg:items-center">
                        <div class="flex min-w-0 flex-1 items-center gap-4">
                            <div
                                class="h-20 w-24 shrink-0 overflow-hidden rounded-lg border border-amber-500/15 bg-[#101010] sm:h-24 sm:w-32">
                                <img v-if="entry.main_image" :src="entry.main_image" :alt="entry.title"
                                    class="h-full w-full object-cover" loading="lazy" />
                                <div v-else
                                    class="flex h-full w-full flex-col items-center justify-center gap-1 text-amber-500/40">
                                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="1.4">
                                        <rect x="3" y="4" width="18" height="16" rx="2" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m3 15 5-5 4 4 3-3 6 6" />
                                    </svg>
                                    <span class="text-[10px] uppercase tracking-wider">Competition</span>
                                </div>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="mb-2 flex flex-wrap items-center gap-2">
                                    <span
                                        :class="['inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide', statusClass(entry.competition_status, entry.ends_at)]">
                                        {{ competitionStatus(entry.competition_status, entry.ends_at) }}
                                    </span>
                                    <span class="text-xs text-amber-100/35">Competition #{{ entry.competition_id
                                        }}</span>
                                </div>
                                <h3 class="truncate text-base font-semibold text-amber-100 sm:text-lg">
                                    {{ entry.title }}
                                </h3>
                                <p class="mt-1 text-xs text-amber-100/45">
                                    Closes {{ formatDate(entry.ends_at) }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="grid grid-cols-3 gap-2 border-t border-amber-500/10 pt-3 sm:max-w-sm sm:flex-1 sm:border-0 sm:pt-0 lg:max-w-md">
                            <div class="rounded-lg border border-amber-500/10 bg-amber-500/[0.03] px-3 py-3">
                                <p class="text-[10px] font-medium uppercase tracking-wide text-amber-100/40">Total</p>
                                <p class="mt-1 text-lg font-bold tabular-nums text-amber-300">{{ entry.entries_count ??
                                    0 }}</p>
                            </div>
                            <div class="rounded-lg border border-emerald-500/10 bg-emerald-500/[0.03] px-3 py-3">
                                <p class="text-[10px] font-medium uppercase tracking-wide text-amber-100/40">Active</p>
                                <p class="mt-1 text-lg font-bold tabular-nums text-emerald-300">{{ entry.active_entries
                                    ?? 0 }}</p>
                            </div>
                            <div class="rounded-lg border border-slate-500/10 bg-slate-500/[0.03] px-3 py-3">
                                <p class="text-[10px] font-medium uppercase tracking-wide text-amber-100/40">Used</p>
                                <p class="mt-1 text-lg font-bold tabular-nums text-slate-300">{{ entry.used_entries ?? 0
                                    }}</p>
                            </div>
                        </div>
                    </article>
                </div>

                <!-- Empty state -->
                <div v-else class="px-5 py-14 text-center sm:py-16">
                    <div
                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-amber-500/15 bg-amber-500/[0.05] text-amber-400">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-base font-semibold text-amber-100">No entries found</h3>
                    <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-amber-100/45">
                        {{ search ? 'Try another competition name or clear your search.' : 'When you enter a competition, it will appear here.' }}
                    </p>
                    <button v-if="search" type="button" @click="clearFilters"
                        class="mt-5 inline-flex h-10 items-center justify-center rounded-lg border border-amber-500/25 px-4 text-sm font-semibold text-amber-300 transition hover:bg-amber-500/10">
                        Clear search
                    </button>
                </div>

                <!-- Pagination -->
                <div v-if="entries.links && entries.links.length > 3"
                    class="flex flex-col gap-3 border-t border-amber-500/15 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                    <p class="text-xs text-amber-100/40">
                        Page {{ entries.current_page }} of {{ entries.last_page }}
                    </p>
                    <nav class="flex flex-wrap items-center gap-1.5" aria-label="Entries pagination">
                        <template v-for="(link, index) in entries.links" :key="index">
                            <span v-if="!link.url"
                                class="inline-flex h-9 min-w-9 cursor-not-allowed items-center justify-center rounded-lg border border-amber-500/10 px-3 text-xs text-amber-100/20"
                                v-html="link.label" />
                            <Link v-else :href="link.url" preserve-scroll
                                class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border px-3 text-xs transition"
                                :class="link.active
                                    ? 'border-amber-500 bg-amber-500 font-bold text-black'
                                    : 'border-amber-500/15 text-amber-100/60 hover:border-amber-500/40 hover:bg-amber-500/5 hover:text-amber-300'"
                                v-html="link.label" />
                        </template>
                    </nav>
                </div>
            </section>
        </div>
    </UserLayout>
</template>
