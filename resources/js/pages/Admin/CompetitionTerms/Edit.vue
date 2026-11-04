```vue
<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    term: {
        type: Object,
        required: true,
    },
})

const form = useForm({
    version: props.term.version ?? '',
    title: props.term.title ?? '',
    content: props.term.content ?? '',
    is_active: Boolean(props.term.is_active),
})

const submit = () => {
    form.put(
        route('admin.competition-terms.update', props.term.id),
        {
            preserveScroll: true,
        }
    )
}
</script>

<template>
    <Head title="Edit Terms & Conditions" />

    <AdminLayout>
        <div class="mx-auto max-w-5xl space-y-6">

            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="mb-2 flex items-center gap-2 text-xs text-neutral-500">
                        <Link
                            :href="route('admin.competition-terms.index')"
                            class="transition hover:text-amber-400"
                        >
                            Terms & Conditions
                        </Link>

                        <span>/</span>

                        <span class="text-amber-400">Edit</span>
                    </div>

                    <h1 class="text-xl font-bold text-white sm:text-2xl">
                        Edit Terms & Conditions
                    </h1>

                    <p class="mt-1 text-sm text-neutral-500">
                        Update the version, content, and availability of your terms.
                    </p>
                </div>

                <Link
                    :href="route('admin.competition-terms.index')"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-neutral-800 bg-[#121212] px-4 py-2.5 text-sm font-semibold text-neutral-300 transition hover:border-neutral-700 hover:bg-neutral-800 hover:text-white"
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
                            d="M15 19l-7-7 7-7"
                        />
                    </svg>

                    Back to Terms
                </Link>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">

                <!-- Basic Information -->
                <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">
                    <div class="border-b border-neutral-800 px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/10">
                                <svg
                                    class="h-5 w-5 text-amber-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"
                                    />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-sm font-bold text-white">
                                    Basic Information
                                </h2>
                                <p class="mt-1 text-xs text-neutral-500">
                                    Update the version and title of these terms.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-5 p-5 sm:grid-cols-2">

                        <!-- Version -->
                        <div>
                            <label
                                for="version"
                                class="mb-2 block text-xs font-semibold text-neutral-300"
                            >
                                Version <span class="text-amber-400">*</span>
                            </label>

                            <input
                                id="version"
                                v-model="form.version"
                                type="text"
                                maxlength="50"
                                required
                                placeholder="e.g. v1.0"
                                :class="[
                                    'w-full rounded-xl border bg-[#0d0d0d] px-4 py-3 text-sm text-white outline-none transition placeholder:text-neutral-700 focus:border-amber-500 focus:ring-1 focus:ring-amber-500/20',
                                    form.errors.version
                                        ? 'border-rose-500/60'
                                        : 'border-neutral-800',
                                ]"
                            />

                            <p
                                v-if="form.errors.version"
                                class="mt-1.5 text-xs text-rose-400"
                            >
                                {{ form.errors.version }}
                            </p>

                            <p
                                v-else
                                class="mt-1.5 text-xs text-neutral-600"
                            >
                                Use a version such as v1.0 or v1.1.
                            </p>
                        </div>

                        <!-- Title -->
                        <div>
                            <label
                                for="title"
                                class="mb-2 block text-xs font-semibold text-neutral-300"
                            >
                                Title <span class="text-amber-400">*</span>
                            </label>

                            <input
                                id="title"
                                v-model="form.title"
                                type="text"
                                maxlength="255"
                                required
                                placeholder="e.g. Prize Guyana Terms & Conditions"
                                :class="[
                                    'w-full rounded-xl border bg-[#0d0d0d] px-4 py-3 text-sm text-white outline-none transition placeholder:text-neutral-700 focus:border-amber-500 focus:ring-1 focus:ring-amber-500/20',
                                    form.errors.title
                                        ? 'border-rose-500/60'
                                        : 'border-neutral-800',
                                ]"
                            />

                            <p
                                v-if="form.errors.title"
                                class="mt-1.5 text-xs text-rose-400"
                            >
                                {{ form.errors.title }}
                            </p>

                            <p
                                v-else
                                class="mt-1.5 text-xs text-neutral-600"
                            >
                                Give this version a clear, recognizable title.
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Terms Content -->
                <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">
                    <div class="border-b border-neutral-800 px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/10">
                                <svg
                                    class="h-5 w-5 text-amber-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M8 6h12M8 12h12M8 18h8M4 5h.01M4 11h.01M4 17h.01"
                                    />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-sm font-bold text-white">
                                    Terms & Conditions Content
                                </h2>
                                <p class="mt-1 text-xs text-neutral-500">
                                    Edit the complete terms for this version.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="p-5">
                        <label
                            for="content"
                            class="mb-2 block text-xs font-semibold text-neutral-300"
                        >
                            Full Content <span class="text-amber-400">*</span>
                        </label>

                        <textarea
                            id="content"
                            v-model="form.content"
                            rows="16"
                            required
                            placeholder="Enter the complete Terms & Conditions..."
                            :class="[
                                'w-full resize-y rounded-xl border bg-[#0d0d0d] px-4 py-3 text-sm leading-7 text-neutral-200 outline-none transition placeholder:text-neutral-700 focus:border-amber-500 focus:ring-1 focus:ring-amber-500/20',
                                form.errors.content
                                    ? 'border-rose-500/60'
                                    : 'border-neutral-800',
                            ]"
                        />

                        <div class="mt-2 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                            <p
                                v-if="form.errors.content"
                                class="text-xs text-rose-400"
                            >
                                {{ form.errors.content }}
                            </p>

                            <p
                                v-else
                                class="text-xs text-neutral-600"
                            >
                                Review your changes before saving.
                            </p>

                            <span class="text-xs text-neutral-600">
                                {{ form.content.length }} characters
                            </span>
                        </div>
                    </div>
                </section>

                <!-- Publication Status -->
                <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">
                    <div class="border-b border-neutral-800 px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/10">
                                <svg
                                    class="h-5 w-5 text-amber-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M9 12l2 2 4-4m6 0a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-sm font-bold text-white">
                                    Publication Status
                                </h2>
                                <p class="mt-1 text-xs text-neutral-500">
                                    Manage whether this version can be selected for new competitions.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="p-5">
                        <label class="flex cursor-pointer items-center justify-between gap-4 rounded-xl border border-neutral-800 bg-[#0d0d0d] p-4 transition hover:border-neutral-700">
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-500/10">
                                    <svg
                                        class="h-5 w-5 text-amber-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.8"
                                            d="M5 13l4 4L19 7"
                                        />
                                    </svg>
                                </div>

                                <div>
                                    <span class="block text-sm font-semibold text-neutral-200">
                                        Active Terms
                                    </span>
                                    <span class="mt-1 block text-xs leading-5 text-neutral-500">
                                        When enabled, this version is available for selection when creating competitions.
                                    </span>
                                </div>
                            </div>

                            <input
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-5 w-5 shrink-0 rounded border-neutral-700 bg-[#121212] text-amber-500 focus:ring-amber-500/30 focus:ring-offset-0"
                            />
                        </label>

                        <p
                            v-if="form.errors.is_active"
                            class="mt-2 text-xs text-rose-400"
                        >
                            {{ form.errors.is_active }}
                        </p>
                    </div>
                </section>

                <!-- Actions -->
                <div class="flex flex-col-reverse gap-3 border-t border-neutral-800 pt-5 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs text-neutral-600">
                        <span class="text-amber-400">*</span>
                        Required fields
                    </p>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row">
                        <Link
                            :href="route('admin.competition-terms.index')"
                            class="inline-flex items-center justify-center rounded-xl border border-neutral-800 bg-[#121212] px-5 py-2.5 text-sm font-semibold text-neutral-300 transition hover:bg-neutral-800 hover:text-white"
                        >
                            Cancel
                        </Link>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-bold text-black transition hover:bg-amber-400 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <svg
                                v-if="form.processing"
                                class="h-4 w-4 animate-spin"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                />
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                                />
                            </svg>

                            <svg
                                v-else
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>

                            {{ form.processing ? 'Updating...' : 'Save Changes' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
```