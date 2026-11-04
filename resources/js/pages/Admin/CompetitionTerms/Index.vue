<script setup>
import { Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { formatDate } from '../../../helpers/date'


const props = defineProps({
    terms: {
        type: Object,
        default: () => ({
            data: [],
            links: [],
            current_page: 1,
            last_page: 1,
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
})

const search = ref(props.filters?.search ?? '')
const status = ref(props.filters?.status ?? '')

let searchTimeout = null

const applyFilters = () => {
    router.get(
        route('admin.competition-terms.index'),
        {
            search: search.value || undefined,
            status: status.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    )
}

watch(search, () => {
    clearTimeout(searchTimeout)

    searchTimeout = setTimeout(() => {
        applyFilters()
    }, 400)
})

watch(status, () => {
    applyFilters()
})

const deleteTerm = (term) => {
    if (!confirm(
        `Are you sure you want to delete "${term.title}"?`
    )) {
        return
    }

    router.delete(
        route('admin.competition-terms.destroy', term.id),
        {
            preserveScroll: true,
        }
    )
}
</script>

<template>
    <AdminLayout>

        <div class="space-y-6">

            <!-- ============================================== -->
            <!-- HEADER -->
            <!-- ============================================== -->

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <h1 class="text-xl font-bold text-white">
                        Terms & Conditions
                    </h1>

                    <p class="mt-1 text-sm text-neutral-500">
                        Manage global Terms & Conditions versions.
                    </p>
                </div>

                <Link
                    :href="route('admin.competition-terms.create')"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-black transition hover:bg-amber-400"
                >
                    <svg
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 4v16m8-8H4"
                        />
                    </svg>

                    Create Terms
                </Link>

            </div>


            <!-- ============================================== -->
            <!-- FILTERS -->
            <!-- ============================================== -->

            <div class="rounded-2xl border border-neutral-800 bg-[#121212] p-4">

                <div class="grid gap-3 sm:grid-cols-[1fr_180px]">

                    <!-- Search -->
                    <div class="relative">

                        <svg
                            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-500"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="m21 21-4.35-4.35m2.35-5.65a8 8 0 11-16 0 8 8 0 0116 0z"
                            />
                        </svg>

                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search by version or title..."
                            class="w-full rounded-xl border border-neutral-800 bg-[#0d0d0d] py-2.5 pl-10 pr-4 text-sm text-white outline-none transition placeholder:text-neutral-600 focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/20"
                        />

                    </div>


                    <!-- Status -->
                    <select
                        v-model="status"
                        class="rounded-xl border border-neutral-800 bg-[#0d0d0d] px-4 py-2.5 text-sm text-white outline-none transition focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/20"
                    >
                        <option value="">
                            All Status
                        </option>

                        <option value="1">
                            Active
                        </option>

                        <option value="0">
                            Inactive
                        </option>
                    </select>

                </div>

            </div>


            <!-- ============================================== -->
            <!-- TABLE -->
            <!-- ============================================== -->

            <div class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">

                <!-- Desktop -->
                <div class="hidden overflow-x-auto md:block">

                    <table class="min-w-full">

                        <thead class="border-b border-neutral-800 bg-[#0d0d0d]">

                            <tr>
                                <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-neutral-500">
                                    Version
                                </th>

                                <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-neutral-500">
                                    Title
                                </th>

                                <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-neutral-500">
                                    Status
                                </th>

                                <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-neutral-500">
                                    Created
                                </th>

                                <th class="px-6 py-4 text-right text-[11px] font-bold uppercase tracking-wider text-neutral-500">
                                    Actions
                                </th>
                            </tr>

                        </thead>


                        <tbody class="divide-y divide-neutral-800/70">

                            <tr
                                v-for="term in terms.data"
                                :key="term.id"
                                class="transition-colors hover:bg-[#181818]"
                            >

                                <!-- Version -->
                                <td class="whitespace-nowrap px-6 py-4">

                                    <span class="inline-flex rounded-lg border border-amber-500/20 bg-amber-500/10 px-2.5 py-1 font-mono text-xs font-semibold text-amber-400">
                                        {{ term.version }}
                                    </span>

                                </td>


                                <!-- Title -->
                                <td class="px-6 py-4">

                                    <div class="max-w-md">

                                        <p class="truncate text-sm font-semibold text-white">
                                            {{ term.title }}
                                        </p>

                                    </div>

                                </td>


                                <!-- Status -->
                                <td class="whitespace-nowrap px-6 py-4">

                                    <span
                                        v-if="term.is_active"
                                        class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-1 text-[11px] font-semibold text-emerald-400"
                                    >
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                        Active
                                    </span>

                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1.5 rounded-full border border-neutral-700 bg-neutral-800/50 px-2.5 py-1 text-[11px] font-semibold text-neutral-500"
                                    >
                                        <span class="h-1.5 w-1.5 rounded-full bg-neutral-500"></span>
                                        Inactive
                                    </span>

                                </td>


                                <!-- Created -->
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-500">
                                    {{ formatDate(term.created_at) }}
                                </td>


                                <!-- Actions -->
                                <td class="whitespace-nowrap px-6 py-4">

                                    <div class="flex items-center justify-end gap-2">

                                        <Link
                                            :href="route('admin.competition-terms.edit', term.id)"
                                            class="rounded-lg border border-neutral-800 px-3 py-1.5 text-xs font-semibold text-neutral-300 transition hover:border-neutral-700 hover:bg-neutral-800 hover:text-white"
                                        >
                                            Edit
                                        </Link>

                                        <button
                                            type="button"
                                            @click="deleteTerm(term)"
                                            class="rounded-lg border border-rose-500/20 px-3 py-1.5 text-xs font-semibold text-rose-400 transition hover:bg-rose-500/10"
                                        >
                                            Delete
                                        </button>

                                    </div>

                                </td>

                            </tr>


                            <!-- Empty -->
                            <tr v-if="!terms.data?.length">

                                <td
                                    colspan="5"
                                    class="px-6 py-16 text-center"
                                >

                                    <div class="flex flex-col items-center">

                                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-neutral-800/70 text-neutral-500">

                                            <svg
                                                class="h-6 w-6"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="1.5"
                                                    d="M9 12h6m-6 4h6M7 3h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"
                                                />
                                            </svg>

                                        </div>

                                        <p class="text-sm font-semibold text-neutral-300">
                                            No Terms & Conditions found
                                        </p>

                                        <p class="mt-1 text-xs text-neutral-600">
                                            Create your first Terms version to get started.
                                        </p>

                                    </div>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>


                <!-- ========================================== -->
                <!-- MOBILE -->
                <!-- ========================================== -->

                <div class="divide-y divide-neutral-800 md:hidden">

                    <div
                        v-for="term in terms.data"
                        :key="term.id"
                        class="p-4"
                    >

                        <div class="flex items-start justify-between gap-4">

                            <div class="min-w-0">

                                <div class="flex items-center gap-2">

                                    <span class="rounded-lg border border-amber-500/20 bg-amber-500/10 px-2 py-1 font-mono text-[11px] font-semibold text-amber-400">
                                        {{ term.version }}
                                    </span>

                                    <span
                                        v-if="term.is_active"
                                        class="text-[10px] font-semibold text-emerald-400"
                                    >
                                        Active
                                    </span>

                                    <span
                                        v-else
                                        class="text-[10px] font-semibold text-neutral-500"
                                    >
                                        Inactive
                                    </span>

                                </div>

                                <h3 class="mt-3 truncate text-sm font-semibold text-white">
                                    {{ term.title }}
                                </h3>

                                <p class="mt-1 text-xs text-neutral-600">
                                    {{ term.created_at }}
                                </p>

                            </div>

                        </div>


                        <div class="mt-4 flex gap-2">

                            <Link
                                :href="route('admin.competition-terms.edit', term.id)"
                                class="flex-1 rounded-lg border border-neutral-800 px-3 py-2 text-center text-xs font-semibold text-neutral-300 transition hover:bg-neutral-800 hover:text-white"
                            >
                                Edit
                            </Link>

                            <button
                                type="button"
                                @click="deleteTerm(term)"
                                class="flex-1 rounded-lg border border-rose-500/20 px-3 py-2 text-xs font-semibold text-rose-400 transition hover:bg-rose-500/10"
                            >
                                Delete
                            </button>

                        </div>

                    </div>


                    <!-- Mobile Empty -->
                    <div
                        v-if="!terms.data?.length"
                        class="px-6 py-16 text-center"
                    >

                        <p class="text-sm font-semibold text-neutral-300">
                            No Terms & Conditions found
                        </p>

                        <p class="mt-1 text-xs text-neutral-600">
                            Create your first Terms version to get started.
                        </p>

                    </div>

                </div>

            </div>


            <!-- ============================================== -->
            <!-- PAGINATION -->
            <!-- ============================================== -->

            <div
                v-if="terms.links?.length && terms.last_page > 1"
                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >

                <p class="text-xs text-neutral-600">
                    Showing
                    <span class="font-medium text-neutral-400">
                        {{ terms.from }}
                    </span>
                    to
                    <span class="font-medium text-neutral-400">
                        {{ terms.to }}
                    </span>
                    of
                    <span class="font-medium text-neutral-400">
                        {{ terms.total }}
                    </span>
                    terms
                </p>


                <div class="flex flex-wrap items-center gap-1">

                    <template
                        v-for="(link, index) in terms.links"
                        :key="index"
                    >

                        <Link
                            v-if="link.url"
                            :href="link.url"
                            preserve-scroll
                            preserve-state
                            class="rounded-lg border px-3 py-1.5 text-xs font-medium transition"
                            :class="[
                                link.active
                                    ? 'border-amber-500/30 bg-amber-500/10 text-amber-400'
                                    : 'border-neutral-800 bg-[#121212] text-neutral-500 hover:bg-neutral-800 hover:text-white'
                            ]"
                        >
                            <span v-html="link.label"></span>
                        </Link>

                        <span
                            v-else
                            class="rounded-lg border border-neutral-900 px-3 py-1.5 text-xs text-neutral-700"
                        >
                            <span v-html="link.label"></span>
                        </span>

                    </template>

                </div>

            </div>

        </div>

    </AdminLayout>
</template>