
<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';

defineOptions({
    layout: AdminLayout,
});

defineProps({
    faqs: {
        type: Array,
        default: () => [],
    },
});

const deleteFaq = (faq) => {
    if (confirm('Are you sure you want to delete this FAQ?')) {
        router.delete(route('admin.faqs.destroy', faq.id));
    }
};
</script>

<template>
    <Head title="Manage FAQs" />

    <div class="space-y-6">

        <!-- Header -->
<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm mb-3">
                <span class="text-neutral-400">FAQs</span>
                <span class="text-neutral-600">/</span>
                <span class="text-amber-400">All FAQs</span>
            </div>

            <h1 class="text-2xl font-bold text-amber-50">
                Manage FAQs
            </h1>

            <p class="text-sm text-neutral-400 mt-1">
                Manage frequently asked questions across your website.
            </p>
        </div>

        <Link
            :href="route('admin.faqs.create')"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-black font-semibold text-sm transition"
        >
            <span class="text-lg leading-none">+</span>
            Add New FAQ
        </Link>
    </div>
</div>

        <!-- FAQ Table -->
        <div class="bg-[#111111] border border-amber-500/20 rounded-xl overflow-hidden">

            <div class="px-5 py-4 border-b border-amber-500/15 flex items-center justify-between">
                <h2 class="text-amber-50 font-semibold">
                    All FAQs
                </h2>

                <span class="text-xs text-neutral-400">
                    {{ faqs.length }} {{ faqs.length === 1 ? 'FAQ' : 'FAQs' }}
                </span>
            </div>

            <div v-if="faqs.length" class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-amber-500/5 text-amber-400/80 uppercase text-xs">
                        <tr>
                            <th class="px-5 py-4">Page</th>
                            <th class="px-5 py-4">Question</th>
                            <th class="px-5 py-4">Answer</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-amber-500/10">
                        <tr
                            v-for="faq in faqs"
                            :key="faq.id"
                            class="hover:bg-amber-500/[0.03] transition"
                        >
                            <td class="px-5 py-4">
                                <span class="inline-flex px-2.5 py-1 rounded-md bg-amber-500/10 text-amber-300 text-xs font-medium capitalize">
                                    {{ faq.page }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-amber-50 font-medium min-w-[220px]">
                                {{ faq.question }}
                            </td>

                            <td class="px-5 py-4 text-neutral-400 max-w-sm">
                                <p class="line-clamp-2">
                                    {{ faq.answer }}
                                </p>
                            </td>

                            <td class="px-5 py-4">
                                <span
                                    :class="faq.is_active
                                        ? 'bg-amber-500/10 text-amber-400'
                                        : 'bg-neutral-500/10 text-neutral-400'"
                                    class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium"
                                >
                                    {{ faq.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-3">
                                    <Link
                                        :href="route('admin.faqs.edit', faq.id)"
                                        class="text-amber-400 hover:text-amber-300 font-medium transition"
                                    >
                                        Edit
                                    </Link>

                                    <button
                                        type="button"
                                        @click="deleteFaq(faq)"
                                        class="text-red-400 hover:text-red-300 font-medium transition"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-else class="py-16 px-5 text-center">
                <div class="text-4xl mb-3 text-amber-500">?</div>

                <h3 class="text-amber-50 font-semibold text-lg">
                    No FAQs found
                </h3>

                <p class="text-neutral-400 text-sm mt-2">
                    Start by adding your first frequently asked question.
                </p>

                <Link
                    :href="route('admin.faqs.create')"
                    class="inline-flex mt-5 px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-400 text-black font-semibold text-sm transition"
                >
                    Add Your First FAQ
                </Link>
            </div>

        </div>
    </div>
</template>
