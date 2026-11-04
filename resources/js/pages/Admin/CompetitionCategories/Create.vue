<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { watch } from 'vue'
import { generateSlug } from '../../../helpers/slug'

const form = useForm({
    name: '',
    slug: '',
    description: '',
    is_active: true,
    sort_order: 0,
})

watch(
    () => form.name,
    (name) => {
        form.slug = generateSlug(name)
    }
)


const submit = () => {
    form.post(route('admin.competition-categories.store'), {
        preserveScroll: true,
    })
}
</script>

<template>

    <Head title="Create Competition Category" />

    <AdminLayout>
        <div class="mx-auto max-w-5xl space-y-6 text-white">

            <!-- Page Header -->
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <div class="mb-2 flex items-center gap-2 text-xs text-neutral-500">
                        <Link :href="route('admin.competition-categories.index')"
                            class="transition-colors hover:text-amber-400">
                            Categories
                        </Link>

                        <span>/</span>
                        <span class="text-amber-400">Create</span>
                    </div>

                    <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">
                        Create Category
                    </h1>

                    <p class="mt-2 text-sm text-neutral-400">
                        Create and configure a category for organizing competitions.
                    </p>
                </div>

                <Link :href="route('admin.competition-categories.index')"
                    class="inline-flex items-center justify-center gap-2 self-start rounded-xl border border-neutral-700 px-4 py-2.5 text-sm font-medium text-neutral-300 transition hover:border-neutral-500 hover:bg-neutral-800 hover:text-white sm:self-auto">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7 7-7M3 12h18" />
                    </svg>
                    Back to Categories
                </Link>
            </div>

            <!-- Category Form -->
            <form @submit.prevent="submit" class="space-y-6">

                <!-- Section 1: Basic Information -->
                <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">
                    <div class="border-b border-neutral-800 px-5 py-4 sm:px-6">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-400">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M4 5a2 2 0 012-2h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5zM8 7h8M8 11h8M8 15h5" />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-sm font-semibold">Basic Information</h2>
                                <p class="mt-1 text-xs text-neutral-500">
                                    Define the category name, URL slug, and description.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-5 p-5 sm:grid-cols-2 sm:p-6">

                        <!-- Category Name -->
                        <div class="space-y-2">
                            <label for="name" class="block text-sm font-medium text-neutral-200">
                                Category Name <span class="text-amber-400">*</span>
                            </label>

                            <input id="name" v-model="form.name" type="text" required maxlength="255"
                                placeholder="e.g. Cars & Vehicles"
                                class="w-full rounded-xl border border-neutral-700 bg-[#0d0d0d] px-4 py-3 text-sm text-white outline-none transition placeholder:text-neutral-600 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10"
                                :class="{ 'border-rose-500': form.errors.name }" />

                            <p v-if="form.errors.name" class="text-xs text-rose-400">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Slug -->
                        <div class="space-y-2">
                            <label for="slug" class="block text-sm font-medium text-neutral-200">
                                Slug <span class="text-amber-400">*</span>
                            </label>

                            <input id="slug" v-model="form.slug" type="text" required maxlength="255"
                                placeholder="cars-vehicles"
                                class="w-full rounded-xl border border-neutral-700 bg-[#0d0d0d] px-4 py-3 text-sm text-white outline-none transition placeholder:text-neutral-600 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10"
                                :class="{ 'border-rose-500': form.errors.slug }" />

                            <p class="text-xs text-neutral-500">
                                Generated from the name. You can edit it manually.
                            </p>

                            <p v-if="form.errors.slug" class="text-xs text-rose-400">
                                {{ form.errors.slug }}
                            </p>
                        </div>

                        <!-- Description -->
                        <div class="space-y-2 sm:col-span-2">
                            <label for="description" class="block text-sm font-medium text-neutral-200">
                                Description
                            </label>

                            <textarea id="description" v-model="form.description" rows="4"
                                placeholder="Describe what types of competitions belong in this category..."
                                class="w-full resize-y rounded-xl border border-neutral-700 bg-[#0d0d0d] px-4 py-3 text-sm leading-6 text-white outline-none transition placeholder:text-neutral-600 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10"
                                :class="{ 'border-rose-500': form.errors.description }"></textarea>

                            <p v-if="form.errors.description" class="text-xs text-rose-400">
                                {{ form.errors.description }}
                            </p>
                        </div>

                    </div>
                </section>

                <!-- Section 2: Display Settings -->
                <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">
                    <div class="border-b border-neutral-800 px-5 py-4 sm:px-6">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-400">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M4 6h16M4 12h16M4 18h16M8 4v4M16 10v4M10 16v4" />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-sm font-semibold">Display Settings</h2>
                                <p class="mt-1 text-xs text-neutral-500">
                                    Configure the category's display order and availability.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-5 p-5 sm:p-6">

                        <!-- Sort Order -->
                        <div class="max-w-md space-y-2">
                            <label for="sort_order" class="block text-sm font-medium text-neutral-200">
                                Sort Order
                            </label>

                            <input id="sort_order" v-model.number="form.sort_order" type="number" min="0" required
                                class="w-full rounded-xl border border-neutral-700 bg-[#0d0d0d] px-4 py-3 text-sm text-white outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10"
                                :class="{ 'border-rose-500': form.errors.sort_order }" />

                            <p class="text-xs text-neutral-500">
                                Lower numbers appear first when categories are sorted by this field.
                            </p>

                            <p v-if="form.errors.sort_order" class="text-xs text-rose-400">
                                {{ form.errors.sort_order }}
                            </p>
                        </div>

                        <!-- Active Status -->
                        <div
                            class="flex items-center justify-between gap-4 rounded-xl border border-neutral-800 bg-[#0d0d0d] p-4">
                            <div>
                                <p class="text-sm font-medium text-white">
                                    Active Category
                                </p>
                                <p class="mt-1 text-xs leading-5 text-neutral-500">
                                    Enable this category for use in the application.
                                </p>
                            </div>

                            <button type="button" role="switch" :aria-checked="form.is_active"
                                @click="form.is_active = !form.is_active" :class="[
                                    'relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-amber-500/40 focus:ring-offset-2 focus:ring-offset-[#0d0d0d]',
                                    form.is_active ? 'bg-amber-500' : 'bg-neutral-700'
                                ]">
                                <span :class="[
                                    'inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform',
                                    form.is_active ? 'translate-x-6' : 'translate-x-1'
                                ]"></span>
                            </button>
                        </div>

                        <p v-if="form.errors.is_active" class="text-xs text-rose-400">
                            {{ form.errors.is_active }}
                        </p>

                    </div>
                </section>

                <!-- Form Actions -->
                <div class="flex flex-col-reverse justify-end gap-3 border-t border-neutral-800 pt-5 sm:flex-row">
                    <Link :href="route('admin.competition-categories.index')"
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

                        {{ form.processing ? 'Creating Category...' : 'Create Category' }}
                    </button>
                </div>

            </form>
        </div>
    </AdminLayout>
</template>
