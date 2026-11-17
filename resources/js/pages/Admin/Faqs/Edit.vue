
<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';

defineOptions({
    layout: AdminLayout,
});

const props = defineProps({
    faq: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    page: props.faq.page,
    question: props.faq.question,
    answer: props.faq.answer,
    is_active: Boolean(props.faq.is_active),
});

const submit = () => {
    form.put(route('admin.faqs.update', props.faq.id));
};
</script>

<template>
    <Head title="Edit FAQ" />

    <div class="space-y-6">

        <!-- Header -->
<div>
    <div class="flex items-center gap-2 text-sm mb-3">
        <Link
            :href="route('admin.faqs.index')"
            class="text-neutral-400 hover:text-amber-400 transition"
        >
            FAQs
        </Link>

        <span class="text-neutral-600">/</span>

        <span class="text-amber-400">Edit</span>
    </div>

    <h1 class="text-2xl font-bold text-amber-50">
        Edit FAQ
    </h1>

    <p class="text-sm text-neutral-400 mt-1">
        Update this frequently asked question.
    </p>
</div>

        <!-- Form -->
        <form
            @submit.prevent="submit"
            class="bg-[#111111] border border-amber-500/20 rounded-xl p-6 space-y-6"
        >

            <!-- Page -->
            <div>
                <label class="block text-sm font-medium text-amber-100/80 mb-2">
                    Display Page <span class="text-red-400">*</span>
                </label>

                <select
                    v-model="form.page"
                    class="w-full rounded-lg bg-[#1A1A1A] border border-amber-500/20 text-amber-50 px-4 py-3 focus:border-amber-500 focus:ring-amber-500"
                >
                    <option value="home">Home</option>
                </select>

                <p v-if="form.errors.page" class="text-red-400 text-xs mt-2">
                    {{ form.errors.page }}
                </p>
            </div>

            <!-- Question -->
            <div>
                <label class="block text-sm font-medium text-amber-100/80 mb-2">
                    Question <span class="text-red-400">*</span>
                </label>

                <input
                    v-model="form.question"
                    type="text"
                    placeholder="Enter FAQ question"
                    class="w-full rounded-lg bg-[#1A1A1A] border border-amber-500/20 text-amber-50 placeholder-neutral-500 px-4 py-3 focus:border-amber-500 focus:ring-amber-500"
                />

                <p v-if="form.errors.question" class="text-red-400 text-xs mt-2">
                    {{ form.errors.question }}
                </p>
            </div>

            <!-- Answer -->
            <div>
                <label class="block text-sm font-medium text-amber-100/80 mb-2">
                    Answer <span class="text-red-400">*</span>
                </label>

                <textarea
                    v-model="form.answer"
                    rows="6"
                    placeholder="Write the answer here..."
                    class="w-full rounded-lg bg-[#1A1A1A] border border-amber-500/20 text-amber-50 placeholder-neutral-500 px-4 py-3 focus:border-amber-500 focus:ring-amber-500 resize-y"
                ></textarea>

                <p v-if="form.errors.answer" class="text-red-400 text-xs mt-2">
                    {{ form.errors.answer }}
                </p>
            </div>

            <!-- Status -->
            <div class="flex items-center justify-between border-t border-amber-500/15 pt-5">
                <div>
                    <p class="text-sm font-medium text-amber-50">
                        FAQ Status
                    </p>
                    <p class="text-xs text-neutral-400 mt-1">
                        Inactive FAQs will not appear on the website.
                    </p>
                </div>

                <label class="relative inline-flex items-center cursor-pointer">
                    <input
                        v-model="form.is_active"
                        type="checkbox"
                        class="sr-only peer"
                    />

                    <div class="w-11 h-6 bg-neutral-700 rounded-full peer peer-checked:bg-amber-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
                </label>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 border-t border-amber-500/15 pt-5">
                <Link
                    :href="route('admin.faqs.index')"
                    class="px-5 py-2.5 rounded-lg border border-amber-500/20 text-neutral-300 hover:bg-amber-500/5 hover:text-amber-300 text-sm font-medium transition"
                >
                    Cancel
                </Link>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-5 py-2.5 rounded-lg bg-amber-500 hover:bg-amber-400 disabled:opacity-50 text-black text-sm font-semibold transition"
                >
                    {{ form.processing ? 'Updating...' : 'Update FAQ' }}
                </button>
            </div>

        </form>
    </div>
</template>
