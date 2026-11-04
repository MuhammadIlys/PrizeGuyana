<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({
    competitions: {
        type: Array,
        default: () => [],
    },
})

const form = useForm({
    competition_id: '',
    name: '',
    description: '',
    prize_role: 'main',
    prize_type: 'physical',
    image: null,
    prize_value: '',
    currency: 'USD',
    quantity: 1,
    winning_method: 'draw',
    status: 'active',
    sort_order: 0,
})

const submit = () => {
    form.post(route('admin.competition-prizes.store'), {
        forceFormData: true,
    })
}

const inputClass = (field) => [
    'w-full rounded-xl border bg-[#0d0d0d] px-4 py-3 text-sm text-white outline-none transition placeholder:text-neutral-700 focus:border-amber-500 focus:ring-1 focus:ring-amber-500/20',
    form.errors[field] ? 'border-rose-500/60' : 'border-neutral-800',
]

const selectClass = (field) => [
    'w-full rounded-xl border bg-[#0d0d0d] px-4 py-3 text-sm text-white outline-none transition focus:border-amber-500 focus:ring-1 focus:ring-amber-500/20',
    form.errors[field] ? 'border-rose-500/60' : 'border-neutral-800',
]
</script>

<template>

    <Head title="Create Prize" />

    <AdminLayout>
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">

            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="mb-2 flex items-center gap-2 text-xs text-neutral-500">
                        <Link :href="route('admin.competition-prizes.index')" class="transition hover:text-amber-400">
                            Prizes
                        </Link>

                        <span>/</span>

                        <span class="text-amber-400">Create</span>
                    </div>

                    <h1 class="text-xl font-bold text-white sm:text-2xl">
                        Create Prize
                    </h1>

                    <p class="mt-1 text-sm text-neutral-500">
                        Add a main or small prize to a competition.
                    </p>
                </div>

                <Link :href="route('admin.competition-prizes.index')"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-neutral-800 bg-[#121212] px-4 py-2.5 text-sm font-semibold text-neutral-300 transition hover:border-neutral-700 hover:bg-neutral-800 hover:text-white">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Prizes
                </Link>
            </div>




            <form @submit.prevent="submit" class="space-y-6">

                <!-- Section 1: Competition -->
                <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">
                    <div class="border-b border-neutral-800 px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/10">
                                <svg class="h-5 w-5 text-amber-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M12 8v13m0-13C10.343 8 9 6.657 9 5s1.343-3 3-3 3 1.343 3 3-1.343 3-3 3zm0 0H5.5A2.5 2.5 0 003 10.5v1A2.5 2.5 0 005.5 14H12m0-6h6.5a2.5 2.5 0 012.5 2.5v1a2.5 2.5 0 01-2.5 2.5H12" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-white">Competition</h2>
                                <p class="mt-1 text-xs text-neutral-500">Choose the competition associated with this
                                    prize.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-5">
                        <label for="competition_id" class="mb-2 block text-xs font-semibold text-neutral-300">
                            Competition <span class="text-amber-400">*</span>
                        </label>

                        <select v-model="form.competition_id"
                            class="w-full rounded-lg border border-amber-500 bg-[#0d0d0d] px-4 py-3 text-white">
                            <option value="">Select competition</option>
                            <option v-for="competition in competitions" :key="competition.id" :value="competition.id">
                                {{ competition.title }}
                            </option>
                        </select>
                        <p v-if="form.errors.competition_id" class="mt-1.5 text-xs text-rose-400">{{
                            form.errors.competition_id }}</p>
                        <p v-else class="mt-1.5 text-xs text-neutral-600">Select the competition this prize belongs to.
                        </p>
                    </div>
                </section>

                <!-- Section 2: Basic Information -->
                <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">
                    <div class="border-b border-neutral-800 px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/10">
                                <svg class="h-5 w-5 text-amber-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-white">Basic Information</h2>
                                <p class="mt-1 text-xs text-neutral-500">Define the prize name, type, role, and
                                    description.</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-5 p-5 sm:grid-cols-2">
                        <div>
                            <label for="name" class="mb-2 block text-xs font-semibold text-neutral-300">
                                Prize Name <span class="text-amber-400">*</span>
                            </label>
                            <input id="name" v-model="form.name" type="text" maxlength="255" required
                                placeholder="e.g. Toyota Corolla" :class="inputClass('name')" />
                            <p v-if="form.errors.name" class="mt-1.5 text-xs text-rose-400">{{ form.errors.name }}</p>
                            <p v-else class="mt-1.5 text-xs text-neutral-600">Enter a clear name for the prize.</p>
                        </div>

                        <div>
                            <label for="prize_type" class="mb-2 block text-xs font-semibold text-neutral-300">
                                Prize Type <span class="text-amber-400">*</span>
                            </label>
                            <select id="prize_type" v-model="form.prize_type" required
                                :class="selectClass('prize_type')">
                                <option value="physical">Physical Prize</option>
                                <option value="cash">Cash Prize</option>
                            </select>
                            <p v-if="form.errors.prize_type" class="mt-1.5 text-xs text-rose-400">{{
                                form.errors.prize_type }}</p>
                            <p v-else class="mt-1.5 text-xs text-neutral-600">Choose the type of prize being offered.
                            </p>
                        </div>

                        <div>
                            <label for="prize_role" class="mb-2 block text-xs font-semibold text-neutral-300">
                                Prize Role <span class="text-amber-400">*</span>
                            </label>
                            <select id="prize_role" v-model="form.prize_role" required
                                :class="selectClass('prize_role')">
                                <option value="main">Main Prize</option>
                                <option value="small">Small Prize</option>
                            </select>
                            <p v-if="form.errors.prize_role" class="mt-1.5 text-xs text-rose-400">{{
                                form.errors.prize_role }}</p>
                            <p v-else class="mt-1.5 text-xs text-neutral-600">Identify this as a main or small prize.
                            </p>
                        </div>

                        <div>
                            <label for="winning_method" class="mb-2 block text-xs font-semibold text-neutral-300">
                                Winning Method <span class="text-amber-400">*</span>
                            </label>
                            <select id="winning_method" v-model="form.winning_method" required
                                :class="selectClass('winning_method')">
                                <option value="draw">Scheduled Draw</option>
                                <option value="instant_win">Instant Win</option>
                            </select>
                            <p v-if="form.errors.winning_method" class="mt-1.5 text-xs text-rose-400">{{
                                form.errors.winning_method }}</p>
                            <p v-else class="mt-1.5 text-xs text-neutral-600">Choose how this prize will be awarded.</p>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="description"
                                class="mb-2 block text-xs font-semibold text-neutral-300">Description</label>
                            <textarea id="description" v-model="form.description" rows="4"
                                placeholder="Describe the prize and any relevant details..."
                                :class="[...inputClass('description'), 'resize-y']"></textarea>
                            <p v-if="form.errors.description" class="mt-1.5 text-xs text-rose-400">{{
                                form.errors.description }}</p>
                            <p v-else class="mt-1.5 text-xs text-neutral-600">Provide additional information about the
                                prize.</p>
                        </div>
                    </div>
                </section>

                <!-- Section 3: Prize Image -->
                <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">
                    <div class="border-b border-neutral-800 px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/10">
                                <svg class="h-5 w-5 text-amber-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M3 5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5zm4 12l4-4 3 3 2-2 3 3M8 9h.01" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-white">Prize Image</h2>
                                <p class="mt-1 text-xs text-neutral-500">Upload an image to represent the prize.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-5">
                        <label for="image" class="mb-2 block text-xs font-semibold text-neutral-300">Image</label>
                        <input id="image" type="file" accept="image/*"
                            @input="form.image = $event.target.files?.[0] ?? null"
                            :class="['block w-full rounded-xl border bg-[#0d0d0d] text-sm text-neutral-300 file:mr-4 file:border-0 file:bg-amber-500 file:px-4 file:py-3 file:font-semibold file:text-neutral-950 hover:file:bg-amber-400', form.errors.image ? 'border-rose-500/60' : 'border-neutral-800']" />
                        <p v-if="form.errors.image" class="mt-1.5 text-xs text-rose-400">{{ form.errors.image }}</p>
                        <p v-else class="mt-1.5 text-xs text-neutral-600">Upload an image of the prize. File size and
                            format are validated by the server.</p>
                        <p v-if="form.image" class="mt-2 text-xs text-neutral-400">Selected file: {{ form.image.name }}
                        </p>
                    </div>
                </section>

                <!-- Section 4: Value and Quantity -->
                <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">
                    <div class="border-b border-neutral-800 px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/10">
                                <svg class="h-5 w-5 text-amber-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M12 8c-2.21 0-4 1.12-4 2.5S9.79 13 12 13s4 1.12 4 2.5S14.21 18 12 18m0-10v10m0-15v2m0 13v2M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-white">Value and Quantity</h2>
                                <p class="mt-1 text-xs text-neutral-500">Set the estimated prize value and available
                                    units.</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-5 p-5 sm:grid-cols-2">
                        <div>
                            <label for="prize_value" class="mb-2 block text-xs font-semibold text-neutral-300">Prize
                                Value</label>
                            <div class="flex">
                                <span
                                    class="inline-flex items-center rounded-l-xl border border-r-0 border-neutral-800 bg-[#181818] px-4 text-sm text-neutral-300">$</span>
                                <input id="prize_value" v-model="form.prize_value" type="number" min="0" step="0.01"
                                    placeholder="0.00"
                                    :class="['min-w-0 flex-1 rounded-r-xl border bg-[#0d0d0d] px-4 py-3 text-sm text-white outline-none transition placeholder:text-neutral-700 focus:border-amber-500 focus:ring-1 focus:ring-amber-500/20', form.errors.prize_value ? 'border-rose-500/60' : 'border-neutral-800']" />
                            </div>
                            <p v-if="form.errors.prize_value" class="mt-1.5 text-xs text-rose-400">{{
                                form.errors.prize_value }}</p>
                            <p v-else class="mt-1.5 text-xs text-neutral-600">Estimated value in US dollars (USD).</p>
                        </div>

                        <div>
                            <label for="quantity" class="mb-2 block text-xs font-semibold text-neutral-300">
                                Quantity <span class="text-amber-400">*</span>
                            </label>
                            <input id="quantity" v-model="form.quantity" type="number" min="1" step="1" required
                                :class="inputClass('quantity')" />
                            <p v-if="form.errors.quantity" class="mt-1.5 text-xs text-rose-400">{{ form.errors.quantity
                            }}</p>
                            <p v-else class="mt-1.5 text-xs text-neutral-600">Number of units available for this prize.
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Section 5: Display Settings -->
                <section class="overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212]">
                    <div class="border-b border-neutral-800 px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/10">
                                <svg class="h-5 w-5 text-amber-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M12 3v18m9-9H3m12-6l6 6-6 6" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-white">Display Settings</h2>
                                <p class="mt-1 text-xs text-neutral-500">Control prize status and display order.</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-5 p-5 sm:grid-cols-2">
                        <div>
                            <label for="status" class="mb-2 block text-xs font-semibold text-neutral-300">
                                Status <span class="text-amber-400">*</span>
                            </label>
                            <select id="status" v-model="form.status" required :class="selectClass('status')">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <p v-if="form.errors.status" class="mt-1.5 text-xs text-rose-400">{{ form.errors.status }}
                            </p>
                            <p v-else class="mt-1.5 text-xs text-neutral-600">Choose whether this prize is active.</p>
                        </div>

                        <div>
                            <label for="sort_order" class="mb-2 block text-xs font-semibold text-neutral-300">Display
                                Order</label>
                            <input id="sort_order" v-model="form.sort_order" type="number" min="0" step="1"
                                :class="inputClass('sort_order')" />
                            <p v-if="form.errors.sort_order" class="mt-1.5 text-xs text-rose-400">{{
                                form.errors.sort_order }}</p>
                            <p v-else class="mt-1.5 text-xs text-neutral-600">Lower numbers appear first.</p>
                        </div>
                    </div>
                </section>

                <!-- Form Actions -->
                <div class="flex flex-col-reverse gap-3 border-t border-neutral-800 pt-5 sm:flex-row sm:justify-end">
                    <Link :href="route('admin.competition-prizes.index')"
                        class="inline-flex items-center justify-center rounded-xl border border-neutral-700 px-5 py-3 text-sm font-semibold text-neutral-300 transition hover:bg-neutral-800 hover:text-white">
                        Cancel
                    </Link>

                    <button type="submit" :disabled="form.processing"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-5 py-3 text-sm font-bold text-neutral-950 transition hover:bg-amber-400 disabled:cursor-not-allowed disabled:opacity-60">
                        <svg v-if="form.processing" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                        </svg>
                        {{ form.processing ? 'Saving Prize...' : 'Create Prize' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<style scoped></style>