<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3'
import MultiSelect from '@/Components/Admin/MultiSelect.vue';
import countries from '../../../helpers/countries';

const props = defineProps({
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
})


const form = useForm({
    title: '',
    category_id: '',
    terms_id: '',
    short_description: '',
    description: '',
    main_image: null,
    video_path: null,
    entry_price: '',
    total_entries: '',
    max_entries_per_customer: '',
    starts_at: '',
    ends_at: '',
    draw_at: '',
    minimum_age: 18,
    is_featured: false,
    qualification_enabled: '',
    instant_win_enabled: '',
    qualification_question_ids: [],
    country_codes: [],
})




const submit = () => {
    form.post(route('admin.competitions.store'), {
        forceFormData: true,
        preserveScroll: true,
    })
}
</script>

<template>

    <Head title="Create competition"></Head>
    <AdminLayout>
        <div class="mx-auto w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">

            <!-- Page Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div
                        class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-amber-500">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                        Competition Management
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                        Create Competition
                    </h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-neutral-400">
                        Configure competition details, entry settings, scheduling, and eligibility.
                    </p>
                </div>

                <div
                    class="flex h-12 w-12 items-center justify-center rounded-2xl border border-amber-500/20 bg-amber-500/10">
                    <svg class="h-6 w-6 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.7">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 4.5h8l1.5 3H20v12H4v-12h2.5L8 4.5Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5V3h6v1.5M4 10h16M9 14h6" />
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
                            class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-400">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m9-9H3" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-white">Competition details</h2>
                            <p class="mt-1 text-sm text-neutral-400">
                                Complete the information below to set up your competition.
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
                                <p class="mt-0.5 text-xs text-neutral-500">Name, category, terms, and descriptions</p>
                            </div>
                        </div>



                        <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 sm:p-6">

                            <!-- TITLE -->
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <!-- Keep your existing input field in this column -->
                                <div class="sm:col-span-2">
                                    <label class="mb-2 block text-sm font-medium text-neutral-200">Competition
                                        Title</label>
                                    <input v-model="form.title" type="text" name="title"
                                        placeholder="e.g. Win a Luxury Car"
                                        class="w-full rounded-xl border border-neutral-700 bg-neutral-950 px-4 py-3 text-sm text-white placeholder-neutral-600 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10" />
                                    <p v-if="form.errors.title" class="mt-2 text-sm text-red-400">{{ form.errors.title
                                    }}</p>
                                </div>
                            </div>

                            <!-- CATEGORY -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-neutral-200">Category</label>
                                <select v-model="form.category_id" name="category_id"
                                    class="w-full rounded-xl border border-neutral-700 bg-neutral-950 px-4 py-3 text-sm text-neutral-200 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10">
                                    <option value="">Select category</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.category_id" class="mt-2 text-sm text-red-400">{{
                                    form.errors.category_id }}</p>
                            </div>

                            <!-- QUESTIONS -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-neutral-200">Qualification
                                    Questions</label>

                                <!-- Reusable MultiSelect Component -->
                                <MultiSelect v-model="form.qualification_question_ids" :options="questions"
                                    label-key="title" sub-label-key="question" value-key="id"
                                    placeholder="Select qualification questions" />

                                <p v-if="form.errors.qualification_question_ids" class="mt-2 text-sm text-red-400">
                                    {{ form.errors.qualification_question_ids }}
                                </p>
                            </div>

                            <!-- TERMS -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-neutral-200">Terms</label>
                                <select v-model="form.terms_id" name="terms"
                                    class="w-full rounded-xl border border-neutral-700 bg-neutral-950 px-4 py-3 text-sm text-neutral-200 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10">
                                    <option value="">Select Terms</option>
                                    <option v-for="term in terms" :key="term.id" :value="term.id">
                                        {{ term.title }}
                                    </option>
                                </select>
                                <p v-if="form.errors.term_id" class="mt-2 text-sm text-red-400">{{ form.errors.term_id
                                    }}</p>
                            </div>

                            <!-- SHORT DESCRIPTION -->
                            <div class="sm:col-span-2">
                                <label class="mb-2 block text-sm font-medium text-neutral-200">Short Description</label>
                                <input v-model="form.short_description" type="text" name="short_description"
                                    placeholder="A short summary of the competition"
                                    class="w-full rounded-xl border border-neutral-700 bg-neutral-950 px-4 py-3 text-sm text-white placeholder-neutral-600 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10" />
                                <p v-if="form.errors.short_description" class="mt-2 text-sm text-red-400">{{
                                    form.errors.short_description }}</p>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="mb-2 block text-sm font-medium text-neutral-200">Full Description</label>
                                <textarea v-model="form.description" name="description" rows="5"
                                    placeholder="Describe the competition..."
                                    class="w-full resize-y rounded-xl border border-neutral-700 bg-neutral-950 px-4 py-3 text-sm leading-6 text-white placeholder-neutral-600 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10"></textarea>
                                <p v-if="form.errors.description" class="mt-2 text-sm text-red-400">{{
                                    form.errors.description }}</p>
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
                                <p class="mt-0.5 text-xs text-neutral-500">Upload the main image and promotional video
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 sm:p-6">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-neutral-200">Main Image</label>
                                <div
                                    class="rounded-xl border border-dashed border-neutral-700 bg-neutral-950 p-4 transition hover:border-amber-500/50">
                                    <input type="file" name="main_image" accept="image/*"
                                        @change="form.main_image = $event.target.files?.[0] ?? null"
                                        class="w-full text-sm text-neutral-400 file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-amber-500 file:px-3 file:py-2 file:text-xs file:font-bold file:text-black hover:file:bg-amber-400" />
                                    <p class="mt-3 text-xs text-neutral-500">Choose an image for your competition.</p>
                                </div>
                                <p v-if="form.errors.main_image" class="mt-2 text-sm text-red-400">{{
                                    form.errors.main_image }}</p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-neutral-200">Promotional Video</label>
                                <div
                                    class="rounded-xl border border-dashed border-neutral-700 bg-neutral-950 p-4 transition hover:border-amber-500/50">
                                    <input type="file" name="video_path" accept="video/*"
                                        @change="form.video_path = $event.target.files?.[0] ?? null"
                                        class="w-full text-sm text-neutral-400 file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-amber-500 file:px-3 file:py-2 file:text-xs file:font-bold file:text-black hover:file:bg-amber-400" />
                                    <p class="mt-3 text-xs text-neutral-500">Choose a video to promote your competition.
                                    </p>
                                </div>
                                <p v-if="form.errors.video_path" class="mt-2 text-sm text-red-400">{{
                                    form.errors.video_path }}</p>
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
                                <p class="mt-0.5 text-xs text-neutral-500">Configure pricing and entry limits</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 lg:grid-cols-3 sm:p-6">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-neutral-200">Entry Price</label>
                                <input v-model="form.entry_price" type="number" name="entry_price" min="0" step="0.01"
                                    placeholder="0.00"
                                    class="w-full rounded-xl border border-neutral-700 bg-neutral-950 px-4 py-3 text-sm text-white placeholder-neutral-600 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10" />
                                <p v-if="form.errors.entry_price" class="mt-2 text-sm text-red-400">{{
                                    form.errors.entry_price }}</p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-neutral-200">Total Entry
                                    Numbers</label>
                                <input v-model="form.total_entries" type="number" name="total_entries" min="1"
                                    placeholder="e.g. 100"
                                    class="w-full rounded-xl border border-neutral-700 bg-neutral-950 px-4 py-3 text-sm text-white placeholder-neutral-600 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10" />
                                <p v-if="form.errors.total_entries" class="mt-2 text-sm text-red-400">{{
                                    form.errors.total_entries }}</p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-neutral-200">Max Entries Per
                                    Customer</label>
                                <input v-model="form.max_entries_per_customer" type="number"
                                    name="max_entries_per_customer" min="1" placeholder="e.g. 10"
                                    class="w-full rounded-xl border border-neutral-700 bg-neutral-950 px-4 py-3 text-sm text-white placeholder-neutral-600 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10" />
                                <p v-if="form.errors.max_entries_per_customer" class="mt-2 text-sm text-red-400">{{
                                    form.errors.max_entries_per_customer }}</p>
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
                                <p class="mt-0.5 text-xs text-neutral-500">Set the start, closing, and draw dates</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 xl:grid-cols-3 sm:p-6">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-neutral-200">Start Date</label>
                                <input v-model="form.starts_at" type="datetime-local" name="starts_at"
                                    class="w-full min-w-0 rounded-xl border border-neutral-700 bg-neutral-950 px-3 py-3 text-sm text-neutral-200 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10" />
                                <p v-if="form.errors.starts_at" class="mt-2 text-sm text-red-400">{{
                                    form.errors.starts_at }}</p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-neutral-200">Closing Date</label>
                                <input v-model="form.ends_at" type="datetime-local" name="ends_at"
                                    class="w-full min-w-0 rounded-xl border border-neutral-700 bg-neutral-950 px-3 py-3 text-sm text-neutral-200 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10" />
                                <p v-if="form.errors.ends_at" class="mt-2 text-sm text-red-400">{{ form.errors.ends_at
                                    }}</p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-neutral-200">Draw Date</label>
                                <input v-model="form.draw_at" type="datetime-local" name="draw_at"
                                    class="w-full min-w-0 rounded-xl border border-neutral-700 bg-neutral-950 px-3 py-3 text-sm text-neutral-200 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10" />
                                <p v-if="form.errors.draw_at" class="mt-2 text-sm text-red-400">{{ form.errors.draw_at
                                    }}</p>
                            </div>
                        </div>
                    </section>

                    <!-- Eligibility -->
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
                                <h3 class="font-semibold text-white">Eligibility</h3>
                                <p class="mt-0.5 text-xs text-neutral-500">Define the minimum age for entry</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 sm:p-6">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-neutral-200">Minimum Age</label>
                                <p class="mb-2 text-xs text-neutral-500">
                                    Select minimum age
                                </p>
                                <input v-model="form.minimum_age" type="number" name="minimum_age" min="18"
                                    class="w-full rounded-xl border border-neutral-700 bg-neutral-950 px-4 py-3 text-sm text-white outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10" />
                                <p v-if="form.errors.minimum_age" class="mt-2 text-sm text-red-400">{{
                                    form.errors.minimum_age }}</p>
                            </div>

                        </div>

                    </section>

                    <!-- ELIGIBLE COUNTRIES -->
                    <div class="bg-amber-500/10 px-8 py-20">
                        <label class="mb-2 block text-sm font-medium text-neutral-200">
                            Eligible Countries
                        </label>

                        <p class="mb-2 text-xs text-neutral-500">
                            Leave empty to allow participants from all countries.
                        </p>

                        <MultiSelect v-model="form.country_codes" :options="countries" label-key="name" value-key="code"
                            placeholder="Select eligible countries" />

                        <p v-if="form.errors.country_codes" class="mt-2 text-sm text-red-400">
                            {{ form.errors.country_codes }}
                        </p>
                    </div>

                    <!-- Competition Options -->
                    <section class="space-y-4">
                        <div>
                            <h3 class="font-semibold text-white">Competition Options</h3>
                            <p class="mt-1 text-sm text-neutral-500">Choose which additional features to enable.</p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                            <label
                                class="group flex cursor-pointer items-start gap-3 rounded-2xl border border-neutral-800 bg-neutral-900/40 p-4 transition hover:border-amber-500/40 hover:bg-amber-500/[0.04]">
                                <input v-model="form.instant_win_enabled" type="checkbox" name="instant_win_enabled"
                                    class="mt-1 h-4 w-4 shrink-0 accent-amber-500" />
                                <div>
                                    <span class="block text-sm font-semibold text-neutral-100">Enable Instant Win</span>
                                    <span class="mt-1 block text-xs leading-5 text-neutral-500">Allow prizes to be won
                                        instantly.</span>
                                </div>
                            </label>

                            <label
                                class="group flex cursor-pointer items-start gap-3 rounded-2xl border border-neutral-800 bg-neutral-900/40 p-4 transition hover:border-amber-500/40 hover:bg-amber-500/[0.04]">
                                <input v-model="form.qualification_enabled" type="checkbox" name="qualification_enabled"
                                    class="mt-1 h-4 w-4 shrink-0 accent-amber-500" />
                                <div>
                                    <span class="block text-sm font-semibold text-neutral-100">Enable
                                        Qualification</span>
                                    <span class="mt-1 block text-xs leading-5 text-neutral-500">Require a qualification
                                        question before entry.</span>
                                </div>
                            </label>

                            <label
                                class="group flex cursor-pointer items-start gap-3 rounded-2xl border border-neutral-800 bg-neutral-900/40 p-4 transition hover:border-amber-500/40 hover:bg-amber-500/[0.04]">
                                <input v-model="form.is_featured" type="checkbox" name="is_featured"
                                    class="mt-1 h-4 w-4 shrink-0 accent-amber-500" />
                                <div>
                                    <span class="block text-sm font-semibold text-neutral-100">Feature this
                                        competition</span>
                                    <span class="mt-1 block text-xs leading-5 text-neutral-500">Highlight this
                                        competition on the platform.</span>
                                </div>
                            </label>
                        </div>
                    </section>
                </div>

                <!-- Form Actions -->
                <div
                    class="flex flex-col-reverse gap-3 border-t border-neutral-800 bg-neutral-900/50 px-5 py-5 sm:flex-row sm:items-center sm:justify-end sm:px-8">
                    <button type="button" @click="form.reset()"
                        class="inline-flex items-center justify-center rounded-xl border border-neutral-700 px-5 py-3 text-sm font-semibold text-neutral-300 transition hover:border-amber-500/40 hover:bg-amber-500/5 hover:text-amber-300">
                        Reset
                    </button>

                    <button type="submit" :disabled="form.processing"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-6 py-3 text-sm font-bold text-black shadow-lg shadow-amber-500/10 transition hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 focus:ring-offset-neutral-950 disabled:cursor-not-allowed disabled:opacity-50">
                        <svg v-if="!form.processing" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" />
                        </svg>
                        <svg v-else class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <circle cx="12" cy="12" r="10" stroke-opacity=".25" />
                            <path stroke-linecap="round" d="M22 12a10 10 0 0 1-10 10" />
                        </svg>
                        {{ form.processing ? 'Creating...' : 'Create Competition' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
