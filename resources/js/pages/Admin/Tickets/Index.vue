
<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import { formatDate } from '../../../helpers/date';

defineOptions({
    layout: AdminLayout,
});

const props = defineProps({
    tickets: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        required: true,
    },
    competitions: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            competition: '',
            status: '',
        }),
    },
});

const search = ref(props.filters.search || '');
const competition = ref(props.filters.competition || '');
const status = ref(props.filters.status || '');

let searchTimeout = null;

function applyFilters() {
    router.get(
        route('admin.tickets.index'),
        {
            search: search.value || undefined,
            competition: competition.value || undefined,
            status: status.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
}

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 400);
});

watch([competition, status], applyFilters);

function clearFilters() {
    search.value = '';
    competition.value = '';
    status.value = '';
}

const hasFilters = computed(() =>
    Boolean(search.value || competition.value || status.value)
);

function statusClass(value) {
    const classes = {
        available: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
        assigned: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        unavailable: 'bg-gray-500/10 text-gray-400 border-gray-500/20',
    };

    return classes[value] || 'bg-gray-500/10 text-gray-400 border-gray-500/20';
}

function formatTicketDate(date) {
    return date ? formatDate(date) : '—';
}
</script>

<template>
    <Head title="Tickets Management" />

    <div class="space-y-6 text-gray-100">

        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">Tickets Management</h1>
                <p class="mt-1 text-sm text-gray-400">
                    Manage and monitor competition tickets across PrizeGuyana.
                </p>
            </div>

            <div class="flex items-center gap-2 text-sm text-gray-400">
                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                Ticket inventory overview
            </div>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            <div class="rounded-xl border border-gray-800 bg-gray-900 p-5">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-400">Total Tickets</p>
                    <div class="rounded-lg bg-amber-500/10 p-2 text-amber-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M4 7V5a1 1 0 011-1h14a1 1 0 011 1v2M4 7h16M4 7v12a1 1 0 001 1h14a1 1 0 001-1V7M9 11h6M9 15h6" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-3xl font-bold text-white">
                    {{ stats.total_tickets ?? 0 }}
                </p>
                <p class="mt-1 text-xs text-gray-500">All competition tickets</p>
            </div>

            <div class="rounded-xl border border-gray-800 bg-gray-900 p-5">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-400">Available</p>
                    <div class="rounded-lg bg-emerald-500/10 p-2 text-emerald-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-3xl font-bold text-emerald-400">
                    {{ stats.available_tickets ?? 0 }}
                </p>
                <p class="mt-1 text-xs text-gray-500">Ready for purchase</p>
            </div>

            <div class="rounded-xl border border-gray-800 bg-gray-900 p-5">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-400">Assigned</p>
                    <div class="rounded-lg bg-blue-500/10 p-2 text-blue-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2m12-13a4 4 0 11-8 0 4 4 0 018 0zm6 13v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-3xl font-bold text-blue-400">
                    {{ stats.assigned_tickets ?? 0 }}
                </p>
                <p class="mt-1 text-xs text-gray-500">Allocated to customers</p>
            </div>

            <div class="rounded-xl border border-gray-800 bg-gray-900 p-5">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-400">Unavailable</p>
                    <div class="rounded-lg bg-gray-500/10 p-2 text-gray-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-3xl font-bold text-gray-300">
                    {{ stats.unavailable_tickets ?? 0 }}
                </p>
                <p class="mt-1 text-xs text-gray-500">Not available for purchase</p>
            </div>

        </div>

        <!-- Tickets Table -->
        <div class="overflow-hidden rounded-xl border border-gray-800 bg-gray-900">

            <div class="border-b border-gray-800 p-5">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-white">All Tickets</h2>
                        <p class="mt-1 text-sm text-gray-400">
                            View and track individual competition tickets.
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-500"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search tickets..."
                                class="w-full rounded-lg border border-gray-700 bg-gray-800 py-2.5 pl-10 pr-4 text-sm text-white placeholder-gray-500 outline-none transition focus:border-amber-500 sm:w-64"
                            />
                        </div>

                        <select
                            v-model="competition"
                            class="rounded-lg border border-gray-700 bg-gray-800 px-3 py-2.5 text-sm text-gray-300 outline-none focus:border-amber-500"
                        >
                            <option value="">All Competitions</option>
                            <option
                                v-for="item in competitions"
                                :key="item.id"
                                :value="String(item.id)"
                            >
                                {{ item.title }}
                            </option>
                        </select>

                        <select
                            v-model="status"
                            class="rounded-lg border border-gray-700 bg-gray-800 px-3 py-2.5 text-sm text-gray-300 outline-none focus:border-amber-500"
                        >
                            <option value="">All Statuses</option>
                            <option value="available">Available</option>
                            <option value="assigned">Assigned</option>
                            <option value="unavailable">Unavailable</option>
                        </select>
                    </div>
                </div>

                <div v-if="hasFilters" class="mt-3">
                    <button
                        type="button"
                        @click="clearFilters"
                        class="text-xs font-medium text-amber-400 transition hover:text-amber-300"
                    >
                        Clear all filters
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead class="border-b border-gray-800 bg-gray-950/50 text-xs uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="px-5 py-4 font-medium">Ticket</th>
                            <th class="px-5 py-4 font-medium">Competition</th>
                            <th class="px-5 py-4 font-medium">Customer</th>
                            <th class="px-5 py-4 font-medium">Order</th>
                            <th class="px-5 py-4 font-medium">Status</th>
                            <th class="px-5 py-4 font-medium">Assigned Date</th>
                            <th class="px-5 py-4 text-right font-medium">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-800/80">
                        <tr
                            v-for="ticket in tickets.data"
                            :key="ticket.id"
                            class="transition hover:bg-gray-800/40"
                        >
                            <td class="px-5 py-4">
                                <span class="font-semibold text-amber-400">
                                    #{{ ticket.ticket_number }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                <span class="font-medium text-gray-200">
                                    {{ ticket.competition_title || 'Unknown Competition' }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                <div v-if="ticket.user_id">
                                    <p class="font-medium text-gray-200">
                                        {{ ticket.customer_name || 'Customer' }}
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ ticket.customer_email || '—' }}
                                    </p>
                                </div>
                                <span v-else class="text-gray-500">Not assigned</span>
                            </td>

                            <td class="px-5 py-4">
                                <span v-if="ticket.order_number" class="text-gray-300">
                                    {{ ticket.order_number }}
                                </span>
                                <span v-else class="text-gray-500">—</span>
                            </td>

                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex rounded-full border px-2.5 py-1 text-xs font-medium capitalize"
                                    :class="statusClass(ticket.status)"
                                >
                                    {{ ticket.status }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-gray-400">
                                {{ formatTicketDate(ticket.assigned_at) }}
                            </td>

                            <td class="px-5 py-4 text-right">
                                <Link
                                    :href="route('admin.tickets.show', ticket.id)"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-700 px-3 py-2 text-xs font-medium text-gray-300 transition hover:border-amber-500/50 hover:bg-amber-500/10 hover:text-amber-400"
                                >
                                    View Details
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </Link>
                            </td>
                        </tr>

                        <tr v-if="!tickets.data?.length">
                            <td colspan="7" class="px-5 py-16 text-center">
                                <div class="mx-auto flex max-w-sm flex-col items-center">
                                    <div class="mb-3 rounded-full bg-gray-800 p-4">
                                        <svg class="h-7 w-7 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M4 7V5a1 1 0 011-1h14a1 1 0 011 1v2M4 7h16M4 7v12a1 1 0 001 1h14a1 1 0 001-1V7" />
                                        </svg>
                                    </div>
                                    <p class="font-medium text-gray-300">No tickets found</p>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Try changing your search or filters.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="tickets.links && tickets.links.length > 3"
                class="flex flex-col gap-3 border-t border-gray-800 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <p class="text-xs text-gray-500">
                    Showing
                    <span class="font-medium text-gray-300">{{ tickets.from || 0 }}</span>
                    to
                    <span class="font-medium text-gray-300">{{ tickets.to || 0 }}</span>
                    of
                    <span class="font-medium text-gray-300">{{ tickets.total || 0 }}</span>
                    tickets
                </p>

                <div class="flex flex-wrap items-center gap-1">
                    <template v-for="(link, index) in tickets.links" :key="index">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            preserve-scroll
                            class="rounded-lg border px-3 py-2 text-xs transition"
                            :class="link.active
                                ? 'border-amber-500 bg-amber-500 text-gray-950 font-semibold'
                                : 'border-gray-700 text-gray-400 hover:border-gray-500 hover:text-white'"
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="rounded-lg border border-gray-800 px-3 py-2 text-xs text-gray-600"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>

        </div>
    </div>
</template>
