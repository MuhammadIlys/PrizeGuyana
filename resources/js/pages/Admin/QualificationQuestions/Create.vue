<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { generateSlug } from '../../../helpers/slug'


const form = useForm({
    title: '',
    slug: '',
    question: '',
    option_a: '',
    option_b: '',
    option_c: '',
    option_d: '',
    correct_answer: 'a',
    is_active: true,
})

watch(
    () => form.title,
    (title) => {
        form.slug = generateSlug(title)
    }
)

const submit = () => {
    form.post(route('admin.qualification-questions.store'), {
        preserveScroll: true,
    })
}

const options = [
    { key: 'a', label: 'Option A', field: 'option_a' },
    { key: 'b', label: 'Option B', field: 'option_b' },
    { key: 'c', label: 'Option C', field: 'option_c' },
    { key: 'd', label: 'Option D', field: 'option_d' },
]
</script>

<template>

    <Head title="Create Qualification Question" />

    <AdminLayout>
        <div class="min-h-screen space-y-6 bg-[#0d0d0d] p-4 text-white sm:p-6 lg:p-8">

            <!-- Page Header -->
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <div class="mb-2 flex items-center gap-2 text-xs text-neutral-500">
                        <Link :href="route('admin.qualification-questions.index')"
                            class="transition-colors hover:text-amber-400">
                            Qualification Questions
                        </Link>

                        <span>/</span>
                        <span class="text-amber-400">Create</span>
                    </div>

                    <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">
                        Create Qualification Question
                    </h1>

                    <p class="mt-2 text-sm text-neutral-400">
                        Create a question with four answer options and define the correct answer.
                    </p>
                </div>

                <Link :href="route('admin.qualification-questions.index')"
                    class="inline-flex items-center justify-center gap-2 self-start rounded-xl border border-neutral-700 px-4 py-2.5 text-sm font-medium text-neutral-300 transition hover:border-neutral-500 hover:bg-neutral-800 hover:text-white sm:self-auto">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7 7-7M3 12h18" />
                    </svg>
                    Back to Questions
                </Link>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">

                <!-- Section 1: Basic Information -->
                <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">
                    <div class="border-b border-neutral-800 px-5 py-4 sm:px-6">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-400">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M12 20h9M16.5 3.5a2.12 2.12 0 013 3L8 18l-4 1 1-4 11.5-11.5z" />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-sm font-semibold text-white">Basic Information</h2>
                                <p class="mt-1 text-xs text-neutral-500">
                                    Set the question title and its unique URL identifier.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-5 p-5 sm:grid-cols-2 sm:p-6">

                        <!-- Title -->
                        <div class="space-y-2">
                            <label for="title" class="block text-sm font-medium text-neutral-200">
                                Title <span class="text-amber-400">*</span>
                            </label>

                            <input id="title" v-model="form.title" type="text" required maxlength="255"
                                placeholder="e.g. General Knowledge Question"
                                class="w-full rounded-xl border border-neutral-700 bg-[#0d0d0d] px-4 py-3 text-sm text-white outline-none transition placeholder:text-neutral-600 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10"
                                :class="{ 'border-rose-500': form.errors.title }" />

                            <p v-if="form.errors.title" class="text-xs text-rose-400">
                                {{ form.errors.title }}
                            </p>
                        </div>

                        <!-- Slug -->
                        <div class="space-y-2">
                            <label for="slug" class="block text-sm font-medium text-neutral-200">
                                Slug <span class="text-amber-400">*</span>
                            </label>

                            <input id="slug" v-model="form.slug" type="text" required maxlength="255"
                                placeholder="general-knowledge-question"
                                class="w-full rounded-xl border border-neutral-700 bg-[#0d0d0d] px-4 py-3 text-sm text-white outline-none transition placeholder:text-neutral-600 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10"
                                :class="{ 'border-rose-500': form.errors.slug }" />

                            <p class="text-xs text-neutral-500">
                                Generated from the title. You can edit it manually.
                            </p>

                            <p v-if="form.errors.slug" class="text-xs text-rose-400">
                                {{ form.errors.slug }}
                            </p>
                        </div>

                    </div>
                </section>

                <!-- Section 2: Question Content -->
                <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">
                    <div class="border-b border-neutral-800 px-5 py-4 sm:px-6">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-400">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M9 9h.01M15 9h.01M8 14s1.5 2 4 2 4-2 4-2M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-sm font-semibold text-white">Question Content</h2>
                                <p class="mt-1 text-xs text-neutral-500">
                                    Write the question customers will answer.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="p-5 sm:p-6">
                        <label for="question" class="mb-2 block text-sm font-medium text-neutral-200">
                            Question <span class="text-amber-400">*</span>
                        </label>

                        <textarea id="question" v-model="form.question" required rows="4"
                            placeholder="Enter the full question here..."
                            class="w-full resize-y rounded-xl border border-neutral-700 bg-[#0d0d0d] px-4 py-3 text-sm leading-6 text-white outline-none transition placeholder:text-neutral-600 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10"
                            :class="{ 'border-rose-500': form.errors.question }"></textarea>

                        <p v-if="form.errors.question" class="mt-2 text-xs text-rose-400">
                            {{ form.errors.question }}
                        </p>
                    </div>
                </section>

                <!-- Section 3: Answer Options -->
                <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">
                    <div class="border-b border-neutral-800 px-5 py-4 sm:px-6">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-400">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 006 0M9 12h6M9 16h6" />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-sm font-semibold text-white">Answer Options</h2>
                                <p class="mt-1 text-xs text-neutral-500">
                                    Provide four possible answers for this question.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-5 p-5 sm:grid-cols-2 sm:p-6">
                        <div v-for="option in options" :key="option.key" class="rounded-xl border p-4 transition-colors"
                            :class="form.correct_answer === option.key
                                ? 'border-amber-500/50 bg-amber-500/[0.04]'
                                : 'border-neutral-800 bg-[#0d0d0d]'">
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <label :for="option.field"
                                    class="flex items-center gap-2 text-sm font-semibold text-neutral-200">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg text-xs font-bold"
                                        :class="form.correct_answer === option.key
                                            ? 'bg-amber-500 text-black'
                                            : 'bg-neutral-800 text-neutral-300'">
                                        {{ option.key.toUpperCase() }}
                                    </span>

                                    {{ option.label }}
                                </label>

                                <span v-if="form.correct_answer === option.key"
                                    class="rounded-full bg-amber-500/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-amber-400">
                                    Correct Answer
                                </span>
                            </div>

                            <input :id="option.field" v-model="form[option.field]" type="text" required
                                :placeholder="`Enter answer option ${option.key.toUpperCase()}...`"
                                class="w-full rounded-lg border border-neutral-700 bg-[#121212] px-3 py-3 text-sm text-white outline-none transition placeholder:text-neutral-600 focus:border-amber-500"
                                :class="{ 'border-rose-500': form.errors[option.field] }" />

                            <p v-if="form.errors[option.field]" class="mt-2 text-xs text-rose-400">
                                {{ form.errors[option.field] }}
                            </p>

                            <label class="mt-3 flex cursor-pointer items-center gap-2.5">
                                <input v-model="form.correct_answer" type="radio" :value="option.key"
                                    class="h-4 w-4 accent-amber-500" />

                                <span class="text-xs text-neutral-400">
                                    Mark as correct answer
                                </span>
                            </label>
                        </div>

                        <p v-if="form.errors.correct_answer" class="text-xs text-rose-400 sm:col-span-2">
                            {{ form.errors.correct_answer }}
                        </p>
                    </div>
                </section>

                <!-- Section 4: Status -->
                <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">
                    <div class="border-b border-neutral-800 px-5 py-4 sm:px-6">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-400">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-sm font-semibold text-white">Publication Status</h2>
                                <p class="mt-1 text-xs text-neutral-500">
                                    Control whether this question is available for use.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-4 p-5 sm:p-6">
                        <div>
                            <p class="text-sm font-medium text-white">
                                Active Question
                            </p>
                            <p class="mt-1 text-xs leading-5 text-neutral-500">
                                Inactive questions should not be offered to customers.
                            </p>
                        </div>

                        <button type="button" role="switch" :aria-checked="form.is_active"
                            @click="form.is_active = !form.is_active" :class="[
                                'relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-amber-500/40 focus:ring-offset-2 focus:ring-offset-[#121212]',
                                form.is_active ? 'bg-amber-500' : 'bg-neutral-700'
                            ]">
                            <span :class="[
                                'inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform',
                                form.is_active ? 'translate-x-6' : 'translate-x-1'
                            ]"></span>
                        </button>
                    </div>
                </section>

                <!-- Form Actions -->
                <div class="flex flex-col-reverse justify-end gap-3 border-t border-neutral-800 pt-5 sm:flex-row">
                    <Link :href="route('admin.qualification-questions.index')"
                        class="inline-flex items-center justify-center rounded-xl border border-neutral-700 px-5 py-3 text-sm font-semibold text-neutral-300 transition hover:bg-neutral-800 hover:text-white">
                        Cancel
                    </Link>

                    <button type="submit" :disabled="form.processing"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-6 py-3 text-sm font-bold text-black shadow-lg shadow-amber-500/10 transition hover:bg-amber-400 disabled:cursor-not-allowed disabled:opacity-50">
                        <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>

                        <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>

                        {{ form.processing ? 'Saving Question...' : 'Save Question' }}
                    </button>
                </div>

            </form>
        </div>
    </AdminLayout>
</template>
