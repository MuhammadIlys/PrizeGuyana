<script setup>
import { computed, ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { formatDate } from '../../../helpers/date'


const props = defineProps({
    questions: {
        type: Object,
        required: true,
    },
})

const search = ref(
    new URLSearchParams(
        typeof window !== 'undefined' ? window.location.search : ''
    ).get('search') ?? ''
)

const status = ref(
    new URLSearchParams(
        typeof window !== 'undefined' ? window.location.search : ''
    ).get('status') ?? 'all'
)

const filteredQuestions = computed(() => {
    let items = props.questions.data ?? []

    if (search.value.trim()) {
        const term = search.value.toLowerCase().trim()

        items = items.filter((question) =>
            [
                question.title,
                question.slug,
                question.question,
            ].some((value) => String(value ?? '').toLowerCase().includes(term))
        )
    }

    if (status.value === 'active') {
        items = items.filter((question) => Boolean(question.is_active))
    } else if (status.value === 'inactive') {
        items = items.filter((question) => !Boolean(question.is_active))
    }

    return items
})

const activeCount = computed(() =>
    (props.questions.data ?? []).filter((question) => Boolean(question.is_active)).length
)

const inactiveCount = computed(() =>
    (props.questions.data ?? []).filter((question) => !Boolean(question.is_active)).length
)

const deleteQuestion = (question) => {
    if (!confirm(`Delete "${question.title}"? This action cannot be undone.`)) {
        return
    }

    router.delete(
        route('admin.qualification-questions.destroy', question.encrypted_id),
        { preserveScroll: true }
    )
}

const clearFilters = () => {
    search.value = ''
    status.value = 'all'
}
</script>

<template>

    <Head title="Qualification Questions" />

    <AdminLayout>
        <div class="min-h-screen space-y-6 bg-[#0d0d0d] p-4 text-white sm:p-6 lg:p-8">

            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-amber-400">
                        Competition Management
                    </p>

                    <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">
                        Qualification Questions
                    </h1>

                    <p class="mt-2 max-w-2xl text-sm leading-6 text-neutral-400">
                        Create and manage the questions customers must answer to qualify
                        for your competitions.
                    </p>
                </div>

                <Link :href="route('admin.qualification-questions.create')"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-5 py-3 text-sm font-bold text-black transition hover:bg-amber-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14" />
                    </svg>
                    Create Question
                </Link>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-neutral-800 bg-[#121212] p-5">
                    <p class="text-sm text-neutral-400">Questions on this page</p>
                    <div class="mt-3 flex items-end justify-between">
                        <p class="text-3xl font-bold tabular-nums">
                            {{ questions.data?.length ?? 0 }}
                        </p>
                        <span class="rounded-lg bg-neutral-800 p-2 text-neutral-300">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M8.228 9c.549-1.165 1.806-2 3.272-2 1.933 0 3.5 1.343 3.5 3 0 1.5-1 2.5-2.5 3.25-.75.375-1.25.875-1.25 1.75M11.25 18h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                    </div>
                </div>

                <div class="rounded-2xl border border-neutral-800 bg-[#121212] p-5">
                    <p class="text-sm text-neutral-400">Active on this page</p>
                    <div class="mt-3 flex items-end justify-between">
                        <p class="text-3xl font-bold tabular-nums">{{ activeCount }}</p>
                        <span class="rounded-lg bg-emerald-500/10 p-2 text-emerald-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                    </div>
                </div>

                <div class="rounded-2xl border border-neutral-800 bg-[#121212] p-5">
                    <p class="text-sm text-neutral-400">Inactive on this page</p>
                    <div class="mt-3 flex items-end justify-between">
                        <p class="text-3xl font-bold tabular-nums">{{ inactiveCount }}</p>
                        <span class="rounded-lg bg-neutral-700/50 p-2 text-neutral-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </span>
                    </div>
                </div>
            </div>


            <!-- Questions Table -->
            <section class="overflow-hidden rounded-xl border border-neutral-800 bg-[#121212]">

                <!-- Table Header -->
                <div
                    class="flex flex-col gap-4 border-b border-neutral-800 px-5 py-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-white">
                            All Questions
                        </h2>
                        <p class="mt-1 text-xs text-neutral-500">
                            Manage your qualification question library.
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-500"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="11" cy="11" r="7" stroke-width="1.7" />
                                <path stroke-linecap="round" stroke-width="1.7" d="m16 16 4 4" />
                            </svg>

                            <input v-model="search" type="search" placeholder="Search questions..."
                                class="w-full rounded-lg border border-neutral-800 bg-[#0d0d0d] py-2.5 pl-9 pr-3 text-sm text-neutral-200 outline-none placeholder:text-neutral-600 transition focus:border-amber-500 sm:w-64" />
                        </div>

                        <select v-model="status"
                            class="rounded-lg border border-neutral-800 bg-[#0d0d0d] px-3 py-2.5 text-sm text-neutral-300 outline-none focus:border-amber-500">
                            <option value="all">All statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] table-fixed text-left">

                        <colgroup>
                            <col class="w-[34%]" />
                            <col class="w-[27%]" />
                            <col class="w-[13%]" />
                            <col class="w-[14%]" />
                            <col class="w-[12%]" />
                        </colgroup>

                        <thead>
                            <tr class="border-b border-neutral-800 bg-[#0e0e0e]">
                                <th
                                    class="px-5 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-neutral-500">
                                    Question Details
                                </th>
                                <th
                                    class="px-5 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-neutral-500">
                                    Correct Answer
                                </th>
                                <th
                                    class="px-5 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-neutral-500">
                                    Status
                                </th>
                                <th
                                    class="px-5 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-neutral-500">
                                    Date Created
                                </th>
                                <th
                                    class="px-5 py-3.5 text-right text-[11px] font-semibold uppercase tracking-wider text-neutral-500">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-neutral-800/80">

                            <tr v-for="question in filteredQuestions" :key="question.id"
                                class="transition-colors hover:bg-white/[0.025]">
                                <!-- Question Details -->
                                <td class="px-5 py-4">
                                    <div class="flex min-w-0 items-start gap-3">
                                        <div
                                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-neutral-800 bg-[#191919] text-xs font-semibold text-amber-400">
                                            Q{{ question.id }}
                                        </div>

                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-medium text-neutral-100">
                                                {{ question.title }}
                                            </p>

                                            <p class="mt-1 truncate text-xs text-neutral-500">
                                                {{ question.slug }}
                                            </p>

                                            <p class="mt-1.5 line-clamp-2 text-xs leading-5 text-neutral-400">
                                                {{ question.question }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Correct Answer -->
                                <td class="px-5 py-4">
                                    <div class="flex min-w-0 items-start gap-2.5">
                                        <span
                                            class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md border border-emerald-500/20 bg-emerald-500/10 text-[11px] font-semibold uppercase text-emerald-400">
                                            {{ question.correct_answer }}
                                        </span>

                                        <span class="line-clamp-2 pt-0.5 text-sm leading-5 text-neutral-300">
                                            {{ question[`option_${question.correct_answer}`] ?? '—' }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-2 text-xs font-medium"
                                        :class="question.is_active ? 'text-emerald-400' : 'text-neutral-500'">
                                        <span class="h-1.5 w-1.5 rounded-full"
                                            :class="question.is_active ? 'bg-emerald-400' : 'bg-neutral-600'"></span>

                                        {{ question.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>

                                <!-- Date -->
                                <td class="whitespace-nowrap px-5 py-4 text-xs text-neutral-400">
                                    {{ formatDate(question.created_at) }}
                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link :href="route('admin.qualification-questions.edit', question.encrypted_id)"
                                            class="rounded-md bg-amber-500 px-3 py-1.5 text-sm font-medium text-black transition hover:bg-amber-400">
                                            Edit
                                        </Link>

                                        <button type="button" @click="deleteQuestion(question)"
                                            class="rounded-md bg-rose-500/10 px-3 py-1.5 text-sm font-medium text-rose-400 transition hover:bg-rose-500/20">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Empty State -->
                            <tr v-if="filteredQuestions.length === 0">
                                <td colspan="5" class="px-5 py-16 text-center">
                                    <div
                                        class="mx-auto flex h-11 w-11 items-center justify-center rounded-xl border border-neutral-800 bg-[#0d0d0d] text-neutral-500">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <circle cx="11" cy="11" r="7" stroke-width="1.7" />
                                            <path stroke-linecap="round" stroke-width="1.7" d="m16 16 4 4" />
                                        </svg>
                                    </div>

                                    <p class="mt-4 text-sm font-medium text-neutral-200">
                                        No questions found
                                    </p>

                                    <p class="mt-1 text-xs text-neutral-500">
                                        Try another search term or change the status filter.
                                    </p>

                                    <button v-if="search || status !== 'all'" type="button" @click="clearFilters"
                                        class="mt-3 text-xs font-semibold text-amber-400 hover:text-amber-300">
                                        Clear filters
                                    </button>

                                    <Link v-else :href="route('admin.qualification-questions.create')"
                                        class="mt-3 inline-block text-xs font-semibold text-amber-400 hover:text-amber-300">
                                        Create your first question
                                    </Link>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="questions.links?.length > 3"
                    class="flex flex-col gap-4 border-t border-neutral-800 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs text-neutral-500">
                        Showing
                        <span class="font-medium text-neutral-300">{{ questions.from ?? 0 }}–{{ questions.to ?? 0
                            }}</span>
                        of
                        <span class="font-medium text-neutral-300">{{ questions.total ?? 0 }}</span>
                        questions
                    </p>

                    <div class="flex flex-wrap items-center gap-1">
                        <template v-for="(link, index) in questions.links" :key="index">
                            <span v-if="!link.url" class="rounded-md px-3 py-2 text-xs text-neutral-700"
                                v-html="link.label"></span>

                            <Link v-else :href="link.url" preserve-scroll
                                class="rounded-md px-3 py-2 text-xs transition-colors" :class="link.active
                                    ? 'bg-amber-500 font-semibold text-black'
                                    : 'text-neutral-400 hover:bg-neutral-800 hover:text-white'" v-html="link.label" />
                        </template>
                    </div>
                </div>

            </section>

        </div>
    </AdminLayout>

</template>
