<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ref, watch } from 'vue'
import { Head } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import { formatDate } from '../../../helpers/date'

const props = defineProps({
    competitions: Object,
    filters: Object,
})

const search = ref(props.filters?.search ?? '')
const status = ref(props.filters?.status ?? '')

watch([search, status], () => {
    router.get(route('admin.competitions.index'), {
        search: search.value,
        status: status.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
})

const deleteCompetition = (id) => {
    if (!confirm('Are you sure you want to delete this competition?')) {
        return
    }

    router.delete(route('admin.competitions.destroy', id), {
        preserveScroll: true,
    })
}

const toggleCompetitionStatus = (competition) => {
    let newStatus

    if (competition.status === 'draft') {
        newStatus = 'published'
    } else if (competition.status === 'published') {
        newStatus = 'active'
    } else if (competition.status === 'active') {
        newStatus = 'published'
    } else {
        return
    }

    router.post(
        route('admin.competitions.toggle-status', competition.id),
        { status: newStatus },
        {
            preserveScroll: true,
        }
    )
}

</script>

<template>

    <Head title="Competitions" />
    <AdminLayout>
        <div class="space-y-6 p-4 text-amber-50 sm:p-6">
            <!-- Header -->
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h1 class="text-2xl font-bold text-amber-400">Competitions</h1>
                    <p class="mt-1 text-sm text-amber-100/50">
                        Manage all your competitions.
                    </p>
                </div>

                <a :href="route('admin.competitions.create')"
                    class="rounded-xl bg-amber-500 px-5 py-3 text-center text-sm font-bold text-black transition hover:bg-amber-400">
                    + Create Competition
                </a>
            </div>

            <!-- Filters -->
            <div class="flex flex-col justify-between gap-3 sm:flex-row">
                <input v-model="search" type="search" placeholder="Search competitions..."
                    class="w-full rounded-xl border border-amber-500/20 bg-black px-4 py-3 text-sm text-amber-50 outline-none focus:border-amber-500 sm:max-w-sm" />

                <select v-model="status"
                    class="rounded-xl border border-amber-500/20 bg-black px-4 py-3 text-sm text-amber-100 outline-none focus:border-amber-500">
                    <option value="">All statuses</option>
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-2xl border border-amber-500/20 bg-black">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-amber-500/20 bg-amber-500/5 text-xs uppercase text-amber-100/60">
                            <tr>
                                <th class="px-5 py-4">Competition</th>
                                <th class="px-5 py-4">Entry Price</th>
                                <th class="px-5 py-4">Entries</th>
                                <th class="px-5 py-4">Draw Date</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4">Activate</th>
                                <th class="px-5 py-4 text-right">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-amber-500/10">
                            <tr v-for="competition in competitions.data" :key="competition.id"
                                class="transition hover:bg-amber-500/5">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <img v-if="competition.main_image" :src="`/storage/${competition.main_image}`"
                                            alt=""
                                            class="h-11 w-11 rounded-lg border border-amber-500/20 object-cover" />

                                        <div v-else
                                            class="flex h-11 w-11 items-center justify-center rounded-lg bg-amber-500/10 text-amber-400">
                                            <span class="text-lg">🏆</span>
                                        </div>

                                        <div class="min-w-0">
                                            <p class="max-w-56 truncate font-semibold text-amber-100">
                                                {{ competition.title }}
                                            </p>
                                            <p class="mt-1 text-xs text-amber-100/40">
                                                #{{ competition.id }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-amber-100/80">
                                    {{ competition.currency ?? 'USD' }}
                                    {{ Number(competition.entry_price).toFixed(2) }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-amber-100/80">
                                    {{ competition.total_entries }}
                                </td> 

                                <td class="whitespace-nowrap px-5 py-4 text-amber-100/70">
                                    {{ formatDate(competition.draw_at) }}
                                </td>

                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold capitalize"
                                        :class="{
                                            'bg-green-500/10 text-green-400': competition.status === 'published',
                                            'bg-amber-500/10 text-amber-400': competition.status === 'draft',
                                            'bg-blue-500/10 text-blue-400': competition.status === 'completed',
                                            'bg-red-500/10 text-red-400': competition.status === 'cancelled',
                                        }">
                                        {{ competition.status }}
                                    </span>
                                </td>

                                <!-- Active / Inactive Toggle -->
                                <td class="px-5 py-4">
                                    <button v-if="['draft', 'published', 'active'].includes(competition.status)"
                                        type="button" @click="toggleCompetitionStatus(competition)" :class="competition.status === 'active'
                                            ? 'bg-green-500'
                                            : 'bg-gray-600'"
                                        class="relative inline-flex h-6 w-11 cursor-pointer items-center rounded-full transition-colors duration-200">
                                        <span :class="competition.status === 'active'
                                            ? 'translate-x-6'
                                            : 'translate-x-1'"
                                            class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200"></span>
                                    </button>

                                    <span v-else class="text-xs text-gray-500">—</span>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <a :href="route('admin.competitions.show', competition.encrypted_id)"
                                            class="rounded-lg border border-amber-500/20 px-3 py-2 text-xs font-semibold text-amber-400 transition hover:bg-amber-500/10">
                                            Show
                                        </a>

                                        <a :href="route('admin.competitions.edit', competition.encrypted_id)"
                                            class="rounded-lg border border-amber-500/20 px-3 py-2 text-xs font-semibold text-amber-400 transition hover:bg-amber-500/10">
                                            Edit
                                        </a>

                                        <button type="button" @click="deleteCompetition(competition.encrypted_id)"
                                            class="rounded-lg border border-red-500/20 px-3 py-2 text-xs hover:cursor-pointer font-semibold text-red-400 transition hover:bg-red-500/10">
                                            Delete
                                        </button>
                                    </div>
                                </td>


                            </tr>

                            <tr v-if="!competitions.data?.length">
                                <td colspan="6" class="px-5 py-12 text-center text-amber-100/50">
                                    No competitions found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="competitions.links?.length"
                    class="flex flex-col justify-between gap-4 border-t border-amber-500/20 px-5 py-4 sm:flex-row sm:items-center">
                    <p class="text-xs text-amber-100/50">
                        Showing {{ competitions.from ?? 0 }}–{{ competitions.to ?? 0 }}
                        of {{ competitions.total }} competitions
                    </p>

                    <div class="flex flex-wrap gap-2">
                        <template v-for="(link, index) in competitions.links" :key="index">
                            <button v-if="link.url" type="button" :disabled="link.active" @click="router.get(link.url, {}, {
                                preserveState: true,
                                preserveScroll: true,
                            })" class="rounded-lg border px-3 py-2 text-xs transition" :class="link.active
                                ? 'border-amber-500 bg-amber-500 text-black'
                                : 'border-amber-500/20 text-amber-100 hover:bg-amber-500/10'" v-html="link.label" />
                            <span v-else
                                class="rounded-lg border border-amber-500/10 px-3 py-2 text-xs text-amber-100/30"
                                v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>