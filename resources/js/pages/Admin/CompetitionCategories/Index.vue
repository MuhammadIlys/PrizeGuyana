<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    categories: {
        type: Object,
        required: true,
    },

    filters: {
        type: Object,
        default: () => ({
            search: '',
            status: '',
        }),
    },
});

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');

const applyFilters = () => {
    router.get(
        route('admin.competition-categories.index'),
        {
            search: search.value || undefined,
            status: status.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

const clearFilters = () => {
    search.value = '';
    status.value = '';

    applyFilters();
};

const deleteCategory = (category) => {
    if (!confirm(`Are you sure you want to delete "${category.name}"?`)) {
        return;
    }

    router.delete(
        route(
            'admin.competition-categories.destroy',
            category.id
        ),
        {
            preserveScroll: true,
        }
    );
};
</script>

<template>
    <Head title="Competition Categories" />

    <AdminLayout>
        <div class="mx-auto max-w-7xl space-y-6">

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-white">
                        Competition Categories
                    </h1>

                    <p class="mt-1 text-sm text-neutral-400">
                        Organize competitions into customer-facing categories.
                    </p>
                </div>

                <Link
                    :href="route('admin.competition-categories.create')"
                    class="inline-flex items-center justify-center rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-500"
                >
                    + Create Category
                </Link>
            </div>

            <section class="rounded-2xl border border-neutral-800 bg-[#121212] p-5">
                <div class="flex flex-col gap-4 md:flex-row md:items-end">

                    <div class="flex-1">
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-neutral-500">
                            Search
                        </label>

                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search categories..."
                            @keyup.enter="applyFilters"
                            class="w-full rounded-xl border border-neutral-800 bg-neutral-950 px-3.5 py-2.5 text-sm text-white outline-none placeholder:text-neutral-600 focus:border-red-600 focus:ring-1 focus:ring-red-600"
                        />
                    </div>

                    <div class="w-full md:w-48">
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-neutral-500">
                            Status
                        </label>

                        <select
                            v-model="status"
                            class="w-full rounded-xl border border-neutral-800 bg-neutral-950 px-3.5 py-2.5 text-sm text-white outline-none focus:border-red-600 focus:ring-1 focus:ring-red-600"
                        >
                            <option value="">All</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button
                            type="button"
                            @click="applyFilters"
                            class="rounded-xl bg-neutral-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-neutral-700"
                        >
                            Filter
                        </button>

                        <button
                            type="button"
                            @click="clearFilters"
                            class="rounded-xl border border-neutral-800 px-4 py-2.5 text-sm font-semibold text-neutral-400 hover:bg-neutral-800 hover:text-white"
                        >
                            Clear
                        </button>
                    </div>
                </div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">

                <div
                    v-if="categories.data.length === 0"
                    class="px-6 py-20 text-center"
                >
                    <h3 class="text-sm font-semibold text-white">
                        No categories found
                    </h3>

                    <p class="mt-1 text-sm text-neutral-500">
                        Create your first competition category.
                    </p>
                </div>

                <div
                    v-else
                    class="overflow-x-auto"
                >
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-neutral-800 bg-neutral-950/50">
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                    Category
                                </th>

                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                    Slug
                                </th>

                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                    Competitions
                                </th>

                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                    Status
                                </th>

                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-neutral-800">
                            <tr
                                v-for="category in categories.data"
                                :key="category.id"
                                class="hover:bg-neutral-900/50"
                            >
                                <td class="px-5 py-4">
                                    <p class="text-sm font-semibold text-white">
                                        {{ category.name }}
                                    </p>

                                    <p
                                        v-if="category.description"
                                        class="mt-1 max-w-md truncate text-xs text-neutral-500"
                                    >
                                        {{ category.description }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-sm text-neutral-400">
                                    {{ category.slug }}
                                </td>

                                <td class="px-5 py-4 text-sm text-neutral-300">
                                    {{ category.competitions_count }}
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        class="rounded-full border px-2.5 py-1 text-xs font-semibold"
                                        :class="
                                            category.is_active
                                                ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400'
                                                : 'border-neutral-700 bg-neutral-800 text-neutral-400'
                                        "
                                    >
                                        {{ category.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <Link
                                            :href="route('admin.competition-categories.edit', category.id)"
                                            class="rounded-lg border border-neutral-800 px-3 py-2 text-xs font-semibold text-neutral-300 hover:bg-neutral-800 hover:text-white"
                                        >
                                            Edit
                                        </Link>

                                        <button
                                            type="button"
                                            @click="deleteCategory(category)"
                                            class="rounded-lg border border-red-500/20 px-3 py-2 text-xs font-semibold text-red-400 hover:bg-red-500/10"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="categories.links?.length > 3"
                    class="flex flex-wrap justify-end gap-1 border-t border-neutral-800 p-4"
                >
                    <Link
                        v-for="(link, index) in categories.links"
                        :key="index"
                        :href="link.url || '#'"
                        preserve-scroll
                        class="rounded-lg px-3 py-2 text-xs"
                        :class="
                            link.active
                                ? 'bg-red-600 text-white'
                                : link.url
                                    ? 'text-neutral-400 hover:bg-neutral-800 hover:text-white'
                                    : 'text-neutral-700'
                        "
                        v-html="link.label"
                    />
                </div>
            </section>
        </div>
    </AdminLayout>
</template>