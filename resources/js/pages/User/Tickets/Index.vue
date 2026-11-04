<script setup>
import { computed, ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import UserLayout from '@/Layouts/UserLayout.vue'

const props = defineProps({
    tickets: { type: Object, required: true },
    filters: { type: Object, default: () => ({ search: '', status: '' }) },
})

const search = ref(props.filters?.search ?? '')
const status = ref(props.filters?.status ?? '')

watch(() => props.filters, (filters) => {
    search.value = filters?.search ?? ''
    status.value = filters?.status ?? ''
}, { deep: true })

const ticketRows = computed(() => props.tickets?.data ?? [])
const paginationLinks = computed(() => props.tickets?.links ?? [])
const totalTickets = computed(() => Number(props.tickets?.total ?? ticketRows.value.length))
const from = computed(() => props.tickets?.from ?? 0)
const to = computed(() => props.tickets?.to ?? 0)

const applyFilters = () => router.get(route('user.tickets.index'), {
    search: search.value.trim() || undefined,
    status: status.value || undefined,
}, { preserveState: true, preserveScroll: true, replace: true })

const clearFilters = () => {
    search.value = ''
    status.value = ''
    router.get(route('user.tickets.index'), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

const statusClass = (value) => {
    const normalized = String(value ?? '').toLowerCase()
    if (['assigned', 'active', 'issued'].includes(normalized)) return 'border-emerald-500/25 bg-emerald-500/10 text-emerald-300'
    if (['available', 'pending', 'reserved'].includes(normalized)) return 'border-amber-500/25 bg-amber-500/10 text-amber-300'
    if (['used', 'redeemed', 'completed'].includes(normalized)) return 'border-sky-500/25 bg-sky-500/10 text-sky-300'
    if (['cancelled', 'canceled', 'expired', 'failed'].includes(normalized)) return 'border-rose-500/25 bg-rose-500/10 text-rose-300'
    return 'border-amber-500/20 bg-amber-500/5 text-amber-100/60'
}

const formatDate = (value) => {
    if (!value) return '—'
    const date = new Date(value)
    if (Number.isNaN(date.getTime())) return String(value)
    return new Intl.DateTimeFormat(undefined, {
        year: 'numeric', month: 'short', day: 'numeric',
    }).format(date)
}

const paginationLabel = (label) => String(label)
    .replace('&laquo;', '«')
    .replace('&raquo;', '»')
    .replace(/<[^>]*>/g, '')
    .trim()
</script>

<template>

    <Head title="My Tickets" />

    <UserLayout>
        <div class="min-h-screen bg-black px-4 py-8 text-amber-50 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl space-y-6">
                <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-amber-100/40">Your account</p>
                        <h1 class="mt-2 text-2xl font-bold text-amber-400 sm:text-3xl">My Tickets</h1>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-amber-100/55">
                            View your competition tickets, check their status, and keep track of your entries in one
                            place.
                        </p>
                    </div>
                    <Link :href="route('user.dashboard')"
                        class="inline-flex w-fit items-center justify-center gap-2 rounded-xl border border-amber-500/20 px-4 py-2.5 text-sm font-semibold text-amber-300 transition hover:bg-amber-500/10">
                        <span aria-hidden="true">←</span> Dashboard
                    </Link>
                </header>

                <div class="grid gap-4 sm:grid-cols-2">
                    <section class="rounded-2xl border border-amber-500/20 bg-amber-500/[0.04] p-5">
                        <p class="text-sm text-amber-100/50">Tickets matching filters</p>
                        <p class="mt-2 text-3xl font-bold tabular-nums text-amber-300">{{ totalTickets.toLocaleString()
                        }}</p>
                        <p class="mt-3 text-xs text-amber-100/40">Total records matching your current search and status.
                        </p>
                    </section>
                    <section class="rounded-2xl border border-amber-500/20 bg-black p-5">
                        <h2 class="text-sm font-semibold text-amber-100">Looking for a purchase?</h2>
                        <p class="mt-1 text-sm leading-6 text-amber-100/50">
                            If a ticket is missing, check your order and payment status. Tickets appear here when linked
                            to your account.
                        </p>
                        <Link :href="route('user.orders.index')"
                            class="mt-3 inline-flex text-sm font-semibold text-amber-400 transition hover:text-amber-300">
                            View my orders <span class="ml-1" aria-hidden="true">→</span>
                        </Link>
                    </section>
                </div>

               <section class="rounded-xl border border-amber-500/15 bg-[#0b0b0b] p-4 sm:p-5">
    <form
        class="grid grid-cols-1 items-end gap-4 md:grid-cols-[minmax(0,1fr)_200px_auto] xl:grid-cols-[minmax(0,1fr)_220px_auto_auto]"
        @submit.prevent="applyFilters"
    >
        <!-- Search -->
        <label class="block min-w-0">
            <span class="mb-2 block text-xs font-semibold tracking-wide text-amber-100/60">
                Search tickets
            </span>

            <div class="relative">
                <svg
                    class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-amber-100/35"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <circle cx="11" cy="11" r="7" />
                    <path stroke-linecap="round" d="m16 16 4 4" />
                </svg>

                <input
                    v-model="search"
                    type="search"
                    name="search"
                    placeholder="Search by ticket number..."
                    class="h-11 w-full rounded-lg border border-amber-500/15 bg-black pl-10 pr-3 text-sm text-amber-50 outline-none transition placeholder:text-amber-100/30 hover:border-amber-500/30 focus:border-amber-500/70 focus:ring-2 focus:ring-amber-500/10"
                />
            </div>
        </label>

        <!-- Status -->
        <label class="block min-w-0">
            <span class="mb-2 block text-xs font-semibold tracking-wide text-amber-100/60">
                Ticket status
            </span>

            <select
                v-model="status"
                name="status"
                class="h-11 w-full rounded-lg border border-amber-500/15 bg-black px-3 text-sm text-amber-50 outline-none transition hover:border-amber-500/30 focus:border-amber-500/70 focus:ring-2 focus:ring-amber-500/10"
            >
                <option value="">All tickets</option>
                <option value="active">Active</option>
                <option value="used">Used</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </label>

        <!-- Apply -->
        <button
            type="submit"
            class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-amber-500 px-5 text-sm font-semibold text-black transition hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-4 w-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M4 6h16M7 12h10m-7 6h4"
                />
            </svg>
            Apply
        </button>

        <!-- Clear -->
        <button
            type="button"
            @click="clearFilters"
            class="inline-flex h-11 items-center justify-center rounded-lg border border-amber-500/20 px-5 text-sm font-medium text-amber-100/70 transition hover:border-amber-500/40 hover:bg-amber-500/5 hover:text-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-500/20"
        >
            Clear
        </button>
    </form>
</section>

                <section class="overflow-hidden rounded-2xl border border-amber-500/20 bg-black">
                    <div
                        class="flex flex-col gap-2 border-b border-amber-500/20 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div>
                            <h2 class="text-lg font-semibold text-amber-300">Your ticket list</h2>
                            <p class="mt-1 text-sm text-amber-100/40">
                                <template v-if="totalTickets">Showing {{ from }}–{{ to }} of {{ totalTickets }}
                                    tickets</template>
                                <template v-else>No tickets found</template>
                            </p>
                        </div>
                        <span class="text-xs text-amber-100/35">Competition ticket records</span>
                    </div>

                    <div v-if="ticketRows.length" class="overflow-x-auto">
                        <table class="w-full min-w-[740px] text-left text-sm">
                            <thead
                                class="border-b border-amber-500/20 bg-amber-500/5 text-xs uppercase tracking-wider text-amber-100/55">
                                <tr>
                                    <th class="px-5 py-4 font-semibold">Ticket number</th>
                                    <th class="px-5 py-4 font-semibold">Competition</th>
                                    <th class="px-5 py-4 font-semibold">Assigned date</th>
                                    <th class="px-5 py-4 font-semibold">Status</th>
                                    <th class="px-5 py-4 text-right font-semibold">Order</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-amber-500/10">
                                <tr v-for="ticket in ticketRows" :key="ticket.id"
                                    class="transition hover:bg-amber-500/[0.04]">
                                    <td class="px-5 py-4">
                                        <span class="font-semibold text-amber-100">{{ ticket.ticket_number || `Ticket
                                            #${ticket.id}`
                                        }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-amber-100/75">
                                        {{ ticket.competition_title || ticket.competition_name || (ticket.competition_id
                                            ? `Competition
                                        #${ticket.competition_id}` : 'Competition details unavailable') }}
                                    </td>
                                    <td class="px-5 py-4 text-amber-100/60">{{ formatDate(ticket.assigned_at) }}</td>
                                    <td class="px-5 py-4">
                                        <span
                                            class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold capitalize"
                                            :class="statusClass(ticket.status)">{{ ticket.status || 'Unknown' }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <Link v-if="ticket.order_id" :href="route('user.orders.show', ticket.order_id)"
                                            class="font-semibold text-amber-400 transition hover:text-amber-300">
                                            View order →
                                        </Link>
                                        <span v-else class="text-amber-100/30">—</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="px-5 py-14 text-center sm:px-8">
                        <div
                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-amber-500/20 bg-amber-500/5 text-amber-300">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                aria-hidden="true">
                                <path
                                    d="M4 7a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 000 4v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2a2 2 0 000-4V7z"
                                    stroke-width="1.7" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-base font-semibold text-amber-100">
                            {{ search || status ? 'No tickets match your filters' : 'No tickets yet' }}
                        </h3>
                        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-amber-100/45">
                            {{ search || status ? 'Try another search or clear the selected filters.' : 'When tickets are assigned to your account, they will appear here. You can review your purchases in Orders.' }}
                        </p>
                        <div class="mt-5 flex flex-wrap justify-center gap-3">
                            <button v-if="search || status" type="button" @click="clearFilters"
                                class="rounded-xl border border-amber-500/20 px-4 py-2.5 text-sm font-semibold text-amber-300 transition hover:bg-amber-500/10">Clear
                                filters</button>
                            <Link :href="route('user.orders.index')"
                                class="rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-bold text-black transition hover:bg-amber-400">
                                View my orders</Link>
                        </div>
                    </div>

                    <div v-if="paginationLinks.length > 3"
                        class="flex flex-col gap-3 border-t border-amber-500/20 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <p class="text-xs text-amber-100/40">Page {{ tickets.current_page }} of {{ tickets.last_page }}
                        </p>
                        <nav class="flex flex-wrap items-center gap-1.5" aria-label="Ticket pagination">
                            <template v-for="(link, index) in paginationLinks" :key="`${index}-${link.label}`">
                                <span v-if="!link.url"
                                    class="inline-flex min-w-9 cursor-not-allowed items-center justify-center rounded-lg border border-amber-500/10 px-3 py-2 text-xs text-amber-100/20"
                                    v-html="paginationLabel(link.label)" />
                                <Link v-else :href="link.url" preserve-scroll
                                    class="inline-flex min-w-9 items-center justify-center rounded-lg border px-3 py-2 text-xs font-semibold transition"
                                    :class="link.active ? 'border-amber-500 bg-amber-500 text-black' : 'border-amber-500/20 text-amber-200/70 hover:bg-amber-500/10 hover:text-amber-100'"
                                    v-html="paginationLabel(link.label)" />
                            </template>
                        </nav>
                    </div>
                </section>
            </div>
        </div>
    </UserLayout>
</template>
