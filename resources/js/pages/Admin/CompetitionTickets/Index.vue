
<script setup>
import { router, Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineOptions({
    layout: AdminLayout,
})

const props = defineProps({
    competitions: {
        type: Array,
        default: () => [],
    },
})

const generateTickets = (competition) => {
    if (!confirm(
        `Generate ${Number(competition.total_entries).toLocaleString()} tickets for "${competition.title}"? This action cannot be repeated.`
    )) {
        return
    }

    router.post(
        route('admin.tickets.generate', competition.encrypted_id),
        {},
        {
            preserveScroll: true,
        }
    )
}
</script>

<template>
    <Head title="Ticket Generation" />

    <div class="min-h-screen bg-neutral-950 p-4 text-white sm:p-6">
        <div class="mx-auto max-w-[1600px] space-y-6">

            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-white">
                        Ticket Generation
                    </h1>

                    <p class="mt-2 text-sm text-neutral-400">
                        Generate and manage competition ticket inventory.
                    </p>
                </div>

                <div class="rounded-xl border border-neutral-800 bg-[#121212] px-5 py-3">
                    <p class="text-xs font-medium text-neutral-500">
                        Total Competitions
                    </p>

                    <p class="mt-1 text-2xl font-bold text-amber-400">
                        {{ competitions.length }}
                    </p>
                </div>
            </div>

            <!-- Competitions Table -->
            <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">

                <div class="flex flex-col gap-2 border-b border-neutral-800 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="font-semibold text-white">
                            Competition Inventory
                        </h2>

                        <p class="mt-1 text-xs text-neutral-500">
                            View ticket generation and allocation statistics.
                        </p>
                    </div>

                    <span class="w-fit rounded-lg border border-neutral-800 bg-neutral-950 px-3 py-1.5 text-xs font-medium text-neutral-400">
                        {{ competitions.length }} competitions
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-left">
                        <thead class="border-b border-neutral-800 bg-neutral-950/60">
                            <tr class="text-xs font-semibold uppercase tracking-wider text-neutral-500">
                                <th class="px-5 py-4">Competition</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4 text-right">Capacity</th>
                                <th class="px-5 py-4 text-right">Generated</th>
                                <th class="px-5 py-4 text-right">Available</th>
                                <th class="px-5 py-4 text-right">Assigned</th>
                                <th class="px-5 py-4 text-right">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-neutral-800/80">
                            <tr
                                v-for="competition in competitions"
                                :key="competition.encrypted_id"
                                class="transition hover:bg-white/[0.025]"
                            >
                                <!-- Competition -->
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-white">
                                        {{ competition.title }}
                                    </p>

                                    <p class="mt-1 text-xs text-neutral-500">
                                        Competition ticket inventory
                                    </p>
                                </td>

                                <!-- Status -->
                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold capitalize"
                                        :class="{
                                            'border-amber-500/20 bg-amber-500/10 text-amber-400': competition.status === 'draft',
                                            'border-emerald-500/20 bg-emerald-500/10 text-emerald-400': competition.status === 'active',
                                            'border-blue-500/20 bg-blue-500/10 text-blue-400': competition.status === 'published',
                                            'border-neutral-700 bg-neutral-800 text-neutral-400': competition.status === 'completed',
                                            'border-red-500/20 bg-red-500/10 text-red-400': competition.status === 'cancelled',
                                        }"
                                    >
                                        {{ competition.status }}
                                    </span>
                                </td>

                                <!-- Capacity -->
                                <td class="px-5 py-4 text-right text-sm font-medium tabular-nums text-neutral-300">
                                    {{ Number(competition.total_entries).toLocaleString() }}
                                </td>

                                <!-- Generated -->
                                <td class="px-5 py-4 text-right text-sm font-semibold tabular-nums text-amber-400">
                                    {{ Number(competition.generated_tickets).toLocaleString() }}
                                </td>

                                <!-- Available -->
                                <td class="px-5 py-4 text-right text-sm font-medium tabular-nums text-emerald-400">
                                    {{ Number(competition.available_tickets).toLocaleString() }}
                                </td>

                                <!-- Assigned -->
                                <td class="px-5 py-4 text-right text-sm font-medium tabular-nums text-neutral-300">
                                    {{ Number(competition.assigned_tickets).toLocaleString() }}
                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">

                                        <button
                                            v-if="Number(competition.generated_tickets) === 0"
                                            type="button"
                                            @click="generateTickets(competition)"
                                            class="whitespace-nowrap rounded-lg bg-amber-500 px-3.5 py-2 text-xs font-bold text-black transition hover:bg-amber-400"
                                        >
                                            Generate Tickets
                                        </button>

                                        <span
                                            v-else
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-3 py-2 text-xs font-semibold text-emerald-400"
                                        >
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                            Generated
                                        </span>

                                        <Link
                                            v-if="Number(competition.generated_tickets) > 0"
                                            :href="route('admin.tickets.show', competition.encrypted_id)"
                                            class="whitespace-nowrap rounded-lg border border-neutral-700 bg-neutral-900 px-3.5 py-2 text-xs font-semibold text-neutral-300 transition hover:border-amber-500/40 hover:bg-amber-500/10 hover:text-amber-400"
                                        >
                                            View Tickets
                                        </Link>

                                    </div>
                                </td>
                            </tr>

                            <!-- Empty State -->
                            <tr v-if="competitions.length === 0">
                                <td colspan="7" class="px-5 py-16 text-center">
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl border border-neutral-800 bg-neutral-900">
                                        <svg
                                            class="h-6 w-6 text-neutral-500"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.7"
                                                d="M16 4H8a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2ZM9 9h6m-6 4h6m-6 4h3"
                                            />
                                        </svg>
                                    </div>

                                    <h3 class="mt-4 text-sm font-semibold text-white">
                                        No competitions found
                                    </h3>

                                    <p class="mt-1 text-sm text-neutral-500">
                                        Create a competition before generating tickets.
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

        </div>
    </div>
</template>
