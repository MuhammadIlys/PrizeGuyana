
<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({
    prizes: {
        type: Array,
        default: () => [],
    },
})

const deletePrize = (prize) => {
    if (confirm(`Are you sure you want to delete "${prize.name}"?`)) {
        router.delete(route('admin.competition-prizes.destroy', prize.id))
    }
}

const formatValue = (value, currency = 'USD') => {
    if (value === null || value === '') return '—'

    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency,
    }).format(Number(value))
}

const methodLabel = (method) => {
    return method === 'instant_win' ? 'Instant Win' : 'Scheduled Draw'
}
</script>

<template>
    <Head title="All Prizes" />

    <AdminLayout>
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="mb-2 flex items-center gap-2 text-xs text-neutral-500">
                        <span>Prizes</span>
                        <span>/</span>
                        <span class="text-amber-400">All Prizes</span>
                    </div>

                    <h1 class="text-xl font-bold text-white sm:text-2xl">
                        All Prizes
                    </h1>

                    <p class="mt-1 text-sm text-neutral-500">
                        Manage competition prizes, values, and winning methods.
                    </p>
                </div>

                <Link
                    :href="route('admin.competition-prizes.create')"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-bold text-neutral-950 transition hover:bg-amber-400"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14" />
                    </svg>
                    Create Prize
                </Link>
            </div>

            <!-- Summary -->
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-neutral-800 bg-[#121212] p-5">
                    <p class="text-xs font-medium text-neutral-500">Total Prizes</p>
                    <p class="mt-2 text-2xl font-bold text-white">{{ prizes.length }}</p>
                </div>

                <div class="rounded-2xl border border-neutral-800 bg-[#121212] p-5">
                    <p class="text-xs font-medium text-neutral-500">Main Prizes</p>
                    <p class="mt-2 text-2xl font-bold text-amber-400">
                        {{ prizes.filter(prize => prize.prize_role === 'main').length }}
                    </p>
                </div>

                <div class="rounded-2xl border border-neutral-800 bg-[#121212] p-5">
                    <p class="text-xs font-medium text-neutral-500">Small Prizes</p>
                    <p class="mt-2 text-2xl font-bold text-white">
                        {{ prizes.filter(prize => prize.prize_role === 'small').length }}
                    </p>
                </div>
            </div>

            <!-- Prizes Table -->
            <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">
                <div class="flex flex-col gap-2 border-b border-neutral-800 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-white">Prize Management</h2>
                        <p class="mt-1 text-xs text-neutral-500">
                            All prizes created for your competitions.
                        </p>
                    </div>

                    <span class="text-xs text-neutral-500">
                        {{ prizes.length }} {{ prizes.length === 1 ? 'prize' : 'prizes' }}
                    </span>
                </div>

                <div v-if="prizes.length" class="overflow-x-auto">
                    <table class="w-full min-w-[1050px] text-left text-sm">
                        <thead class="border-b border-neutral-800 bg-[#0d0d0d]">
                            <tr>
                                <th class="px-5 py-4 text-xs font-semibold text-neutral-400">Prize</th>
                                <th class="px-5 py-4 text-xs font-semibold text-neutral-400">Competition</th>
                                <th class="px-5 py-4 text-xs font-semibold text-neutral-400">Role / Type</th>
                                <th class="px-5 py-4 text-xs font-semibold text-neutral-400">Winning Method</th>
                                <th class="px-5 py-4 text-xs font-semibold text-neutral-400">Value</th>
                                <th class="px-5 py-4 text-xs font-semibold text-neutral-400">Quantity</th>
                                <th class="px-5 py-4 text-xs font-semibold text-neutral-400">Status</th>
                                <th class="px-5 py-4 text-right text-xs font-semibold text-neutral-400">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-neutral-800/80">
                            <tr
                                v-for="prize in prizes"
                                :key="prize.id"
                                class="transition hover:bg-white/[0.02]"
                            >
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-neutral-800 bg-[#0d0d0d]">
                                            <img
                                                v-if="prize.image_path"
                                                :src="`/storage/${prize.image_path}`"
                                                :alt="prize.name"
                                                class="h-full w-full object-cover"
                                            />
                                            <svg v-else class="h-5 w-5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 5h16v14H4zM8 10h.01M20 15l-5-5-7 7" />
                                            </svg>
                                        </div>

                                        <div>
                                            <p class="font-semibold text-white">{{ prize.name }}</p>
                                            <p class="mt-1 text-xs text-neutral-500">#{{ prize.id }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="text-neutral-300">
                                        {{ prize.competition_title || `Competition #${prize.competition_id}` }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex flex-col items-start gap-1.5">
                                        <span
                                            :class="prize.prize_role === 'main'
                                                ? 'rounded-full border border-amber-500/20 bg-amber-500/10 px-2.5 py-1 text-xs font-medium text-amber-400'
                                                : 'rounded-full border border-neutral-700 bg-neutral-800/60 px-2.5 py-1 text-xs font-medium text-neutral-300'"
                                        >
                                            {{ prize.prize_role === 'main' ? 'Main Prize' : 'Small Prize' }}
                                        </span>
                                        <span class="text-xs capitalize text-neutral-500">
                                            {{ prize.prize_type === 'cash' ? 'Cash' : 'Physical' }}
                                        </span>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        :class="prize.winning_method === 'instant_win'
                                            ? 'rounded-full border border-sky-500/20 bg-sky-500/10 px-2.5 py-1 text-xs font-medium text-sky-400'
                                            : 'rounded-full border border-violet-500/20 bg-violet-500/10 px-2.5 py-1 text-xs font-medium text-violet-400'"
                                    >
                                        {{ methodLabel(prize.winning_method) }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap text-neutral-300">
                                    {{ formatValue(prize.prize_value, prize.currency) }}
                                </td>

                                <td class="px-5 py-4 text-neutral-300">
                                    {{ prize.quantity }}
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        :class="prize.status === 'active'
                                            ? 'rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-1 text-xs font-medium text-emerald-400'
                                            : 'rounded-full border border-neutral-700 bg-neutral-800 px-2.5 py-1 text-xs font-medium text-neutral-400'"
                                    >
                                        {{ prize.status === 'active' ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="route('admin.competition-prizes.edit', prize.id)"
                                            class="rounded-lg border border-neutral-700 px-3 py-2 text-xs font-semibold text-neutral-300 transition hover:border-amber-500/40 hover:text-amber-400"
                                        >
                                            Edit
                                        </Link>

                                        <button
                                            type="button"
                                            @click="deletePrize(prize)"
                                            class="rounded-lg border border-rose-500/20 px-3 py-2 text-xs font-semibold text-rose-400 transition hover:bg-rose-500/10"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-else class="px-5 py-16 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-neutral-800 bg-[#0d0d0d]">
                        <svg class="h-7 w-7 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 8v13m0-13C10.343 8 9 6.657 9 5s1.343-3 3-3 3 1.343 3 3-1.343 3-3 3zm0 0H5.5A2.5 2.5 0 003 10.5v1A2.5 2.5 0 005.5 14H12m0-6h6.5a2.5 2.5 0 012.5 2.5v1a2.5 2.5 0 01-2.5 2.5H12" />
                        </svg>
                    </div>

                    <h3 class="mt-4 text-sm font-bold text-white">No prizes yet</h3>
                    <p class="mt-1 text-sm text-neutral-500">
                        Create your first prize to get started.
                    </p>

                    <Link
                        :href="route('admin.competition-prizes.create')"
                        class="mt-5 inline-flex items-center justify-center rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-bold text-neutral-950 transition hover:bg-amber-400"
                    >
                        Create Your First Prize
                    </Link>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>
