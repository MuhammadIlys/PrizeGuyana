<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head } from '@inertiajs/vue3'
import { useForm } from '@inertiajs/vue3'
import { formatDate } from '../../../helpers/date'
import countries from '../../../helpers/countries'
import MultiSelect from '@/Components/Admin/MultiSelect.vue';


const props = defineProps({
    competition: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        default: () => [],
    },
    terms: {
        type: Array,
        default: () => [],
    },
    questions: {
        type: Array,
        default: () => [],
    },
    countries: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    title: props.competition.title ?? '',
    category_id: props.competition.category_id ?? '',
    terms_id: props.competition.terms_id ?? '',
    short_description: props.competition.short_description ?? '',
    description: props.competition.description ?? '',
    main_image: null,
    video: null,
    entry_price: props.competition.entry_price ?? '',
    total_entries: props.competition.total_entries ?? '',
    max_entries_per_customer: props.competition.max_entries_per_customer ?? '',
    starts_at: props.competition.starts_at_local ?? '', ends_at: props.competition.ends_at?.slice(0, 16) ?? '',
    draw_at: props.competition.draw_at_local ?? '', minimum_age: props.competition.minimum_age ?? 18,
    is_featured: Boolean(props.competition.is_featured),
    qualification_enabled: Boolean(props.competition.qualification_enabled),
    instant_win_enabled: Boolean(props.competition.instant_win_enabled),
    status: props.competition.status ?? 'draft',
    qualification_question_ids: props.competition.qualification_question_ids ?? [],
    country_codes: props.competition.country_codes ?? [],
})

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post(route('admin.competitions.update', props.competition.encrypted_id), {
        forceFormData: true,
        preserveScroll: true,
    })
}
</script>

<template>

    <Head title="Edit Competition"></Head>
    <AdminLayout>
        <div class="mx-auto w-full  space-y-6 px-4 py-6 text-neutral-100 sm:px-6 lg:px-8">

            <!-- Page Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div
                        class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-amber-500">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                        Competition Management
                    </div>

                    <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                        Edit Competition
                    </h1>

                    <p class="mt-2 text-sm leading-6 text-neutral-400">
                        Update competition details, media, entry settings, and scheduling.
                    </p>
                </div>

                <div
                    class="flex h-12 w-12 items-center justify-center rounded-2xl border border-amber-500/20 bg-amber-500/10">
                    <svg class="h-6 w-6 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.7">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 4.5h8l1.5 3H20v12H4v-12h2.5L8 4.5Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 14h6M12 11v6" />
                    </svg>
                </div>
            </div>

            <form @submit.prevent="submit"
                class="overflow-hidden rounded-2xl border border-neutral-800 bg-neutral-950 shadow-2xl shadow-black/20">
                <!-- Form Introduction -->
                <div
                    class="border-b border-neutral-800 bg-gradient-to-r from-amber-500/[0.08] via-transparent to-transparent px-5 py-5 sm:px-8">
                    <div class="flex items-start gap-3">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-400">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14 6 4 4" />
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-base font-semibold text-white">Competition Details</h2>
                            <p class="mt-1 text-sm text-neutral-400">
                                Make changes to the existing competition information below.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-6 p-5 sm:p-8">

                    <!-- Basic Information -->
                    <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-neutral-900/40">
                        <div class="flex items-center gap-3 border-b border-neutral-800 px-5 py-4 sm:px-6">
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/10 text-amber-400">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.7">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-white">Basic Information</h3>
                                <p class="mt-0.5 text-xs text-neutral-500">Competition name, category, terms, and
                                    description</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 sm:p-6">


                            <div class="">
                                <label class="mb-2 block text-sm font-medium text-neutral-200">
                                    Competition Title
                                </label>
                                <input v-model="form.title" type="text"
                                    class="w-full rounded-xl border border-neutral-700 bg-neutral-950 px-4 py-3 text-sm text-white outline-none transition placeholder:text-neutral-600 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10" />
                                <p v-if="form.errors.title" class="mt-2 text-sm text-red-400">
                                    {{ form.errors.title }}
                                </p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-neutral-200">
                                    Category
                                </label>
                                <select v-model="form.category_id"
                                    class="w-full rounded-xl border border-neutral-700 bg-neutral-950 px-4 py-3 text-sm text-neutral-200 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10">
                                    <option value="">Select category</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.category_id" class="mt-2 text-sm text-red-400">
                                    {{ form.errors.category_id }}
                                </p>
                            </div>

                            <!-- Qualification Questions MultiSelect Field -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-neutral-200">
                                    Qualification Questions
                                </label>

                                <MultiSelect v-model="form.qualification_question_ids" :options="questions"
                                    label-key="title" sub-label-key="question" value-key="id"
                                    placeholder="Select qualification questions" />

                                <p class="mt-1.5 text-xs text-neutral-500">
                                    {{ form.qualification_question_ids.length }} question(s) selected.
                                </p>

                                <p v-if="form.errors.qualification_question_ids" class="mt-2 text-sm text-red-400">
                                    {{ form.errors.qualification_question_ids }}
                                </p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-neutral-200">
                                    Terms
                                </label>
                                <select v-model="form.terms_id"
                                    class="w-full rounded-xl border border-neutral-700 bg-neutral-950 px-4 py-3 text-sm text-neutral-200 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10">
                                    <option value="">Select Terms</option>
                                    <option v-for="term in terms" :key="term.id" :value="term.id">
                                        {{ term.title }}
                                    </option>
                                </select>
                                <p v-if="form.errors.terms_id" class="mt-2 text-sm text-red-400">
                                    {{ form.errors.terms_id }}
                                </p>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="mb-2 block text-sm font-medium text-neutral-200">
                                    Short Description
                                </label>
                                <input v-model="form.short_description" type="text"
                                    class="w-full rounded-xl border border-neutral-700 bg-neutral-950 px-4 py-3 text-sm text-white outline-none transition placeholder:text-neutral-600 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10" />
                                <p v-if="form.errors.short_description" class="mt-2 text-sm text-red-400">
                                    {{ form.errors.short_description }}
                                </p>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="mb-2 block text-sm font-medium text-neutral-200">
                                    Full Description
                                </label>
                                <textarea v-model="form.description" rows="5"
                                    class="w-full resize-y rounded-xl border border-neutral-700 bg-neutral-950 px-4 py-3 text-sm leading-6 text-white outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10"></textarea>
                                <p v-if="form.errors.description" class="mt-2 text-sm text-red-400">
                                    {{ form.errors.description }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Media -->
                    <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-neutral-900/40">
                        <div class="flex items-center gap-3 border-b border-neutral-800 px-5 py-4 sm:px-6">
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/10 text-amber-400">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.7">
                                    <rect x="3" y="3" width="18" height="18" rx="3" />
                                    <circle cx="8.5" cy="8.5" r="1.5" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 15-5-5L5 21" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-white">Competition Media</h3>
                                <p class="mt-0.5 text-xs text-neutral-500">Manage the current image and promotional
                                    video</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 p-5 sm:grid-cols-2 sm:p-6">
                            <div class="min-w-0">
                                <label class="mb-3 block text-sm font-medium text-neutral-200">
                                    Main Image
                                </label>

                                <div class="rounded-xl border border-neutral-800 bg-neutral-950 p-3">
                                    <img v-if="competition.main_image" :src="`/storage/${competition.main_image}`"
                                        alt="Current competition image"
                                        class="mb-4 h-48 w-full rounded-lg border border-neutral-800 object-cover" />

                                    <div v-else
                                        class="mb-4 flex h-40 items-center justify-center rounded-lg border border-dashed border-neutral-800 bg-neutral-900/50">
                                        <div class="text-center">
                                            <svg class="mx-auto h-8 w-8 text-neutral-600" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="1.5">
                                                <rect x="3" y="3" width="18" height="18" rx="3" />
                                                <circle cx="8.5" cy="8.5" r="1.5" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m21 15-5-5L5 21" />
                                            </svg>
                                            <p class="mt-2 text-xs text-neutral-500">No image available</p>
                                        </div>
                                    </div>

                                    <input type="file" accept="image/*"
                                        @change="form.main_image = $event.target.files?.[0] ?? null"
                                        class="w-full text-sm text-neutral-400 file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-amber-500 file:px-3 file:py-2 file:text-xs file:font-bold file:text-black hover:file:bg-amber-400" />

                                    <p class="mt-3 text-xs leading-5 text-neutral-500">
                                        Leave empty to keep the current image.
                                    </p>
                                </div>

                                <p v-if="form.errors.main_image" class="mt-2 text-sm text-red-400">
                                    {{ form.errors.main_image }}
                                </p>
                            </div>

                            <div class="min-w-0">
                                <label class="mb-3 block text-sm font-medium text-neutral-200">
                                    Promotional Video
                                </label>

                                <div class="rounded-xl border border-neutral-800 bg-neutral-950 p-3">
                                    <video v-if="competition.video_path" :src="`/storage/${competition.video_path}`"
                                        controls
                                        class="mb-4 h-48 w-full rounded-lg border border-neutral-800 bg-black object-contain"></video>

                                    <div v-else
                                        class="mb-4 flex h-40 items-center justify-center rounded-lg border border-dashed border-neutral-800 bg-neutral-900/50">
                                        <div class="text-center">
                                            <svg class="mx-auto h-8 w-8 text-neutral-600" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="1.5">
                                                <rect x="3" y="5" width="18" height="14" rx="2" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m10 9 5 3-5 3V9Z" />
                                            </svg>
                                            <p class="mt-2 text-xs text-neutral-500">No video available</p>
                                        </div>
                                    </div>

                                    <input type="file" accept="video/*"
                                        @change="form.video = $event.target.files?.[0] ?? null"
                                        class="w-full text-sm text-neutral-400 file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-amber-500 file:px-3 file:py-2 file:text-xs file:font-bold file:text-black hover:file:bg-amber-400" />

                                    <p class="mt-3 text-xs leading-5 text-neutral-500">
                                        Leave empty to keep the current video.
                                    </p>
                                </div>

                                <p v-if="form.errors.video" class="mt-2 text-sm text-red-400">
                                    {{ form.errors.video }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Entry Settings -->
                    <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-neutral-900/40">
                        <div class="flex items-center gap-3 border-b border-neutral-800 px-5 py-4 sm:px-6">
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/10 text-amber-400">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.7">
                                    <circle cx="12" cy="12" r="9" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 8.5c-.7-.7-1.7-1-3-1-1.7 0-3 .8-3 2s1.3 2 3 2 3 .8 3 2-1.3 2-3 2c-1.3 0-2.3-.3-3-1M12 5.5v13" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-white">Entry Settings</h3>
                                <p class="mt-0.5 text-xs text-neutral-500">Pricing and entry limits</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 lg:grid-cols-3 sm:p-6">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-neutral-200">
                                    Entry Price
                                </label>
                                <input v-model="form.entry_price" type="number" min="0" step="0.01"
                                    class="w-full rounded-xl border border-neutral-700 bg-neutral-950 px-4 py-3 text-sm text-white outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10" />
                                <p v-if="form.errors.entry_price" class="mt-2 text-sm text-red-400">
                                    {{ form.errors.entry_price }}
                                </p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-neutral-200">
                                    Total Entry Numbers
                                </label>
                                <input v-model="form.total_entries" type="number" min="1"
                                    class="w-full rounded-xl border border-neutral-700 bg-neutral-950 px-4 py-3 text-sm text-white outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10" />
                                <p v-if="form.errors.total_entries" class="mt-2 text-sm text-red-400">
                                    {{ form.errors.total_entries }}
                                </p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-neutral-200">
                                    Max Entries Per Customer
                                </label>
                                <input v-model="form.max_entries_per_customer" type="number" min="1"
                                    class="w-full rounded-xl border border-neutral-700 bg-neutral-950 px-4 py-3 text-sm text-white outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10" />
                                <p v-if="form.errors.max_entries_per_customer" class="mt-2 text-sm text-red-400">
                                    {{ form.errors.max_entries_per_customer }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Schedule -->
                    <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-neutral-900/40">
                        <div class="flex items-center gap-3 border-b border-neutral-800 px-5 py-4 sm:px-6">
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/10 text-amber-400">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.7">
                                    <rect x="3" y="5" width="18" height="16" rx="2" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 3v4M8 3v4M3 10h18" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-white">Competition Schedule</h3>
                                <p class="mt-0.5 text-xs text-neutral-500">Manage the start, closing, and draw dates</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 xl:grid-cols-3 sm:p-6">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-neutral-200">
                                    Start Date
                                </label>
                                <input v-model="form.starts_at" type="datetime-local"
                                    class="w-full min-w-0 rounded-xl border border-neutral-700 bg-neutral-950 px-3 py-3 text-sm text-neutral-200 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10" />
                                <p v-if="form.errors.starts_at" class="mt-2 text-sm text-red-400">
                                    {{ form.errors.starts_at }}
                                </p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-neutral-200">
                                    Closing Date
                                </label>
                                <input v-model="form.ends_at" type="datetime-local"
                                    class="w-full min-w-0 rounded-xl border border-neutral-700 bg-neutral-950 px-3 py-3 text-sm text-neutral-200 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10" />
                                <p v-if="form.errors.ends_at" class="mt-2 text-sm text-red-400">
                                    {{ form.errors.ends_at }}
                                </p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-neutral-200">
                                    Draw Date
                                </label>
                                <input v-model="form.draw_at" type="datetime-local"
                                    class="w-full min-w-0 rounded-xl border border-neutral-700 bg-neutral-950 px-3 py-3 text-sm text-neutral-200 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10" />
                                <p v-if="form.errors.draw_at" class="mt-2 text-sm text-red-400">
                                    {{ form.errors.draw_at }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Eligibility and Status -->
                    <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-neutral-900/40">
                        <div class="flex items-center gap-3 border-b border-neutral-800 px-5 py-4 sm:px-6">
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/10 text-amber-400">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.7">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-white">Eligibility & Status</h3>
                                <p class="mt-0.5 text-xs text-neutral-500">Set minimum age and competition status</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 sm:p-6">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-neutral-200">
                                    Minimum Age
                                </label>
                                <input v-model="form.minimum_age" type="number" min="18"
                                    class="w-full rounded-xl border border-neutral-700 bg-neutral-950 px-4 py-3 text-sm text-white outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10" />
                                <p v-if="form.errors.minimum_age" class="mt-2 text-sm text-red-400">
                                    {{ form.errors.minimum_age }}
                                </p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-neutral-200">
                                    Competition Status
                                </label>
                                <select v-model="form.status"
                                    class="w-full rounded-xl border border-neutral-700 bg-neutral-950 px-4 py-3 text-sm text-neutral-200 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <!-- <option value="active">Active</option> -->
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                                <p v-if="form.errors.status" class="mt-2 text-sm text-red-400">
                                    {{ form.errors.status }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <MultiSelect v-model="form.country_codes" :options="countries" label-key="name" value-key="code"
                        placeholder="Select eligible countries" />

                    <!-- Competition Options -->
                    <section class="space-y-4">
                        <div>
                            <h3 class="font-semibold text-white">Competition Options</h3>
                            <p class="mt-1 text-sm text-neutral-500">
                                Enable or disable additional competition features.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                            <label
                                class="flex cursor-pointer items-start gap-3 rounded-2xl border border-neutral-800 bg-neutral-900/40 p-4 transition hover:border-amber-500/40 hover:bg-amber-500/[0.04]">
                                <input v-model="form.is_featured" type="checkbox"
                                    class="mt-1 h-4 w-4 shrink-0 accent-amber-500" />
                                <div>
                                    <span class="block text-sm font-semibold text-neutral-100">
                                        Feature this competition
                                    </span>
                                    <span class="mt-1 block text-xs leading-5 text-neutral-500">
                                        Highlight this competition on the platform.
                                    </span>
                                </div>
                            </label>

                            <label
                                class="flex cursor-pointer items-start gap-3 rounded-2xl border border-neutral-800 bg-neutral-900/40 p-4 transition hover:border-amber-500/40 hover:bg-amber-500/[0.04]">
                                <input v-model="form.qualification_enabled" type="checkbox"
                                    class="mt-1 h-4 w-4 shrink-0 accent-amber-500" />
                                <div>
                                    <span class="block text-sm font-semibold text-neutral-100">
                                        Enable Qualification
                                    </span>
                                    <span class="mt-1 block text-xs leading-5 text-neutral-500">
                                        Require a qualification question before entry.
                                    </span>
                                </div>
                            </label>

                            <label
                                class="flex cursor-pointer items-start gap-3 rounded-2xl border border-neutral-800 bg-neutral-900/40 p-4 transition hover:border-amber-500/40 hover:bg-amber-500/[0.04]">
                                <input v-model="form.instant_win_enabled" type="checkbox"
                                    class="mt-1 h-4 w-4 shrink-0 accent-amber-500" />
                                <div>
                                    <span class="block text-sm font-semibold text-neutral-100">
                                        Enable Instant Win
                                    </span>
                                    <span class="mt-1 block text-xs leading-5 text-neutral-500">
                                        Allow prizes to be won instantly.
                                    </span>
                                </div>
                            </label>
                        </div>
                    </section>
                </div>

                <!-- Form Actions -->
                <div
                    class="flex flex-col-reverse gap-3 border-t border-neutral-800 bg-neutral-900/50 px-5 py-5 sm:flex-row sm:items-center sm:justify-end sm:px-8">
                    <a :href="route('admin.competitions.index')"
                        class="inline-flex items-center justify-center rounded-xl border border-neutral-700 px-5 py-3 text-sm font-semibold text-neutral-300 transition hover:border-amber-500/40 hover:bg-amber-500/5 hover:text-amber-300">
                        Cancel
                    </a>

                    <button type="submit" :disabled="form.processing"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-6 py-3 text-sm font-bold text-black shadow-lg shadow-amber-500/10 transition hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 focus:ring-offset-neutral-950 disabled:cursor-not-allowed disabled:opacity-50">
                        <svg v-if="!form.processing" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7 7 7-7 7" />
                        </svg>

                        <svg v-else class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <circle cx="12" cy="12" r="10" stroke-opacity=".25" />
                            <path stroke-linecap="round" d="M22 12a10 10 0 0 1-10 10" />
                        </svg>

                        {{ form.processing ? 'Saving...' : 'Update Competition' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
