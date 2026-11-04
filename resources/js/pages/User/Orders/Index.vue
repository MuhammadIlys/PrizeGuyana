<script setup>
import { computed, reactive, watch, onBeforeUnmount } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import UserLayout from '../../../Layouts/UserLayout.vue';

defineOptions({
    layout: UserLayout,
});

const props = defineProps({
    orders: {
        type: Object,
        default: () => ({
            data: [],
            links: [],
            current_page: 1,
            last_page: 1,
            from: 0,
            to: 0,
            total: 0,
        }),
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            status: '',
        }),
    },
});

const page = usePage();

const filters = reactive({
    search: props.filters?.search ?? '',
    status: props.filters?.status ?? '',
});

const userName = computed(() => {
    const user = page.props.auth?.user ?? {};
    return user.first_name || user.name || '';
});

let searchTimer = null;

watch(
    () => filters.search,
    () => {
        if (searchTimer) clearTimeout(searchTimer);
        searchTimer = setTimeout(applyFilters, 350);
    },
);

watch(
    () => filters.status,
    () => applyFilters(),
);

onBeforeUnmount(() => {
    if (searchTimer) clearTimeout(searchTimer);
});

function applyFilters() {
    router.get(
        '/user/orders',
        {
            search: filters.search || undefined,
            status: filters.status || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function clearFilters() {
    filters.search = '';
    filters.status = '';

    if (searchTimer) clearTimeout(searchTimer);
    applyFilters();
}

function formatStatus(status) {
    return String(status ?? 'unknown')
        .replaceAll('_', ' ')
        .replace(/\b\w/g, (letter) => letter.toUpperCase());
}

function statusClasses(status) {
    const value = String(status ?? '').toLowerCase();

    if (['paid', 'completed', 'succeeded'].includes(value)) {
        return 'border-green-500/20 bg-green-500/10 text-green-400';
    }

    if (['pending', 'processing'].includes(value)) {
        return 'border-amber-500/20 bg-amber-500/10 text-amber-300';
    }

    if (['failed', 'cancelled', 'canceled'].includes(value)) {
        return 'border-red-500/20 bg-red-500/10 text-red-400';
    }

    return 'border-amber-500/10 bg-amber-500/5 text-amber-100/50';
}

function formatDate(value) {
    if (!value) return '—';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) return '—';

    return new Intl.DateTimeFormat(undefined, {
        year: 'numeric',
        month: 'short',
        day: '2-digit',
    }).format(date);
}

function formatMoney(amount, currency) {
    const numericAmount = Number(amount ?? 0);
    const currencyCode = String(currency ?? '').toUpperCase();

    if (!Number.isFinite(numericAmount)) {
        return `${amount ?? '0.00'} ${currencyCode}`.trim();
    }

    return `${numericAmount.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })} ${currencyCode}`.trim();
}
</script>

<template>
    <Head title="My Orders" />

    <div class="space-y-6 p-4 text-amber-50 sm:p-6">
        <!-- Page heading -->
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h1 class="text-2xl font-bold text-amber-400">
                    My Orders
                </h1>

                <p class="mt-1 text-sm text-amber-100/50">
                    <template v-if="userName">Welcome back, {{ userName }}. </template>
                    View your competition orders and payment status.
                </p>
            </div>

            <div class="inline-flex items-center gap-3 rounded-xl border border-amber-500/20 bg-black px-4 py-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg border border-amber-500/20 bg-amber-500/10 text-amber-400">
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M7 3.75h7l4.25 4.5v12A.75.75 0 0 1 17.5 21h-11a.75.75 0 0 1-.75-.75V4.5A.75.75 0 0 1 6.5 3.75Z"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M14 4v4.5h4.25M8.5 13h7M8.5 16.5h7"
                        />
                    </svg>
                </div>

                <div>
                    <p class="text-xs font-medium text-amber-100/50">Total orders</p>
                    <p class="mt-0.5 text-xl font-bold tabular-nums text-amber-100">
                        {{ orders.total ?? orders.data?.length ?? 0 }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="flex flex-col justify-between gap-3 sm:flex-row">
            <input
                v-model="filters.search"
                type="search"
                placeholder="Search order number..."
                aria-label="Search orders by order number"
                class="w-full rounded-xl border border-amber-500/20 bg-black px-4 py-3 text-sm text-amber-50 outline-none placeholder:text-amber-100/30 focus:border-amber-500 sm:max-w-sm"
            />

            <div class="flex flex-col gap-2 sm:flex-row">
                <select
                    v-model="filters.status"
                    aria-label="Filter by payment status"
                    class="rounded-xl border border-amber-500/20 bg-black px-4 py-3 text-sm text-amber-100 outline-none focus:border-amber-500"
                >
                    <option value="">All payment statuses</option>
                    <option value="pending">Pending</option>
                    <option value="paid">Paid</option>
                    <option value="failed">Failed</option>
                    <option value="cancelled">Cancelled</option>
                </select>

                <button
                    v-if="filters.search || filters.status"
                    type="button"
                    @click="clearFilters"
                    class="rounded-xl border border-amber-500/20 px-4 py-3 text-sm font-semibold text-amber-400 transition hover:bg-amber-500/10"
                >
                    Clear filters
                </button>
            </div>
        </div>

        <!-- Orders table -->
        <section class="overflow-hidden rounded-2xl border border-amber-500/20 bg-black">
            <div v-if="orders.data?.length" class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-amber-500/20 bg-amber-500/5 text-xs uppercase text-amber-100/60">
                        <tr>
                            <th class="whitespace-nowrap px-5 py-4">Order</th>
                            <th class="whitespace-nowrap px-5 py-4">Date</th>
                            <th class="whitespace-nowrap px-5 py-4">Items</th>
                            <th class="whitespace-nowrap px-5 py-4">Total</th>
                            <th class="whitespace-nowrap px-5 py-4">Payment Status</th>
                            <th class="whitespace-nowrap px-5 py-4">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-amber-500/10">
                        <tr
                            v-for="order in orders.data"
                            :key="order.id"
                            class="transition hover:bg-amber-500/5"
                        >
                            <td class="whitespace-nowrap px-5 py-4">
                                <p class="font-semibold text-amber-100">
                                    {{ order.order_number }}
                                </p>
                                <p class="mt-1 text-xs text-amber-100/40">
                                    #{{ order.id }}
                                </p>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-amber-100/70">
                                {{ formatDate(order.created_at) }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-amber-100/80">
                                {{ order.items_count }}
                                {{ order.items_count === 1 ? 'item' : 'items' }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 font-semibold text-amber-100/80">
                                {{ formatMoney(order.total, order.currency) }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4">
                                <span
                                    class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold capitalize"
                                    :class="statusClasses(order.payment_status)"
                                >
                                    {{ formatStatus(order.payment_status) }}
                                </span>
                            </td>
                            <td>
                                <Link
    :href="route('user.orders.show', order.id)"
    class="border border-amber-500/20 px-3 py-2 text-xs font-semibold text-amber-400 transition hover:bg-amber-500/10"
>
    View Details
</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty state -->
            <div v-else class="px-5 py-16 text-center sm:py-20">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-amber-500/20 bg-amber-500/5 text-amber-400">
                    <svg
                        class="h-7 w-7"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.6"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M7 3.75h7l4.25 4.5v12A.75.75 0 0 1 17.5 21h-11a.75.75 0 0 1-.75-.75V4.5A.75.75 0 0 1 6.5 3.75Z"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M14 4v4.5h4.25M8.5 13h7M8.5 16.5h4"
                        />
                    </svg>
                </div>

                <h3 class="mt-5 text-base font-semibold text-amber-100">
                    {{ filters.search || filters.status ? 'No matching orders' : 'No orders yet' }}
                </h3>

                <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-amber-100/50">
                    {{
                        filters.search || filters.status
                            ? 'Try changing your search or clearing the selected status filter.'
                            : 'Your competition orders will appear here once you place an order.'
                    }}
                </p>

                <button
                    v-if="filters.search || filters.status"
                    type="button"
                    @click="clearFilters"
                    class="mt-5 rounded-xl border border-amber-500/20 px-4 py-2.5 text-sm font-semibold text-amber-400 transition hover:bg-amber-500/10"
                >
                    Clear filters
                </button>
            </div>

            <!-- Pagination -->
            <div
                v-if="orders.links?.length && orders.last_page > 1"
                class="flex flex-col justify-between gap-4 border-t border-amber-500/20 px-5 py-4 sm:flex-row sm:items-center"
            >
                <p class="text-xs text-amber-100/50">
                    Showing
                    <span class="font-medium text-amber-100/80">{{ orders.from ?? 0 }}</span>
                    –
                    <span class="font-medium text-amber-100/80">{{ orders.to ?? 0 }}</span>
                    of
                    <span class="font-medium text-amber-100/80">{{ orders.total ?? 0 }}</span>
                    orders
                </p>

                <div class="flex flex-wrap gap-2">
                    <template
                        v-for="(link, index) in orders.links"
                        :key="`${index}-${link.label}`"
                    >
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            preserve-scroll
                            class="rounded-lg border px-3 py-2 text-xs transition"
                            :class="
                                link.active
                                    ? 'border-amber-500 bg-amber-500 text-black'
                                    : 'border-amber-500/20 text-amber-100 hover:bg-amber-500/10'
                            "
                            v-html="link.label"
                        />

                        <span
                            v-else
                            class="rounded-lg border border-amber-500/10 px-3 py-2 text-xs text-amber-100/30"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </section>
    </div>
</template>
