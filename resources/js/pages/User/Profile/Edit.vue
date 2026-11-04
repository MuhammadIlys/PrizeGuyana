
<script setup>
import { computed, ref, watch } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import UserLayout from '@/Layouts/UserLayout.vue'
import CountrySelect from '@/Components/Global/CountrySelect.vue'

const props = defineProps({
    profile: {
        type: Object,
        required: true,
    },
})

const form = useForm({
    first_name: props.profile.first_name ?? '',
    last_name: props.profile.last_name ?? '',
    country: props.profile.country ?? '',
    date_of_birth: props.profile.date_of_birth ?? '',
    phone_number: props.profile.phone_number ?? '',
})

watch(
    () => props.profile,
    (profile) => {
        form.first_name = profile.first_name ?? ''
        form.last_name = profile.last_name ?? ''
        form.country = profile.country ?? ''
        form.date_of_birth = profile.date_of_birth ?? ''
        form.phone_number = profile.phone_number ?? ''
    },
    { deep: true }
)

const initials = computed(() => (
    form.first_name.trim().charAt(0) +
    form.last_name.trim().charAt(0)
).toUpperCase() || 'U')

function submit() {
    form.put(route('user.profile.update'), {
        preserveScroll: true,
    })
}
</script>

<template>
    <Head title="My Profile" />

    <UserLayout>
        <div class="mx-auto min-w-0 max-w-5xl space-y-6 text-amber-50">
            <header>
                <p class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-amber-400/70">
                    Account settings
                </p>

                <h1 class="text-2xl font-bold tracking-tight text-amber-400 sm:text-3xl">
                    My Profile
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-amber-100/50">
                    Manage your personal information and keep your account details up to date.
                </p>
            </header>

            <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
                <aside class="h-fit overflow-hidden rounded-2xl border border-amber-500/15 bg-[#0b0b0b]">
                    <div class="h-20 bg-gradient-to-r from-amber-500/15 via-amber-500/5 to-transparent"></div>

                    <div class="-mt-10 px-5 pb-5">
                        <div
                            class="flex h-20 w-20 items-center justify-center rounded-2xl border border-amber-500/30 bg-[#17120a] text-2xl font-bold tracking-wide text-amber-400 shadow-lg shadow-black/30"
                        >
                            {{ initials }}
                        </div>

                        <h2 class="mt-4 break-words text-lg font-semibold text-amber-100">
                            {{ [profile.first_name, profile.last_name].filter(Boolean).join(' ') || profile.name || 'Your profile' }}
                        </h2>

                        <p class="mt-1 break-all text-sm text-amber-100/45">
                            {{ profile.email }}
                        </p>
                    </div>
                </aside>

                <section class="overflow-hidden rounded-2xl border border-amber-500/15 bg-[#0b0b0b]">
                    <div class="border-b border-amber-500/15 px-5 py-4 sm:px-6">
                        <h2 class="text-base font-semibold text-amber-100">
                            Personal information
                        </h2>

                        <p class="mt-1 text-sm text-amber-100/45">
                            Update the details associated with your account.
                        </p>
                    </div>

                    <form class="space-y-6 p-5 sm:p-6" @submit.prevent="submit">
                        <div class="grid gap-5 sm:grid-cols-2">
                            <label class="block">
                                <span class="mb-2 block text-sm font-medium text-amber-100/75">
                                    First name <span class="text-amber-400">*</span>
                                </span>

                                <input
                                    v-model="form.first_name"
                                    type="text"
                                    name="first_name"
                                    autocomplete="given-name"
                                    required
                                    maxlength="100"
                                    placeholder="Enter first name"
                                    :class="[
                                        'h-11 w-full rounded-lg border bg-black px-3.5 text-sm text-amber-50 outline-none transition placeholder:text-amber-100/25 focus:ring-2 focus:ring-amber-500/10',
                                        form.errors.first_name
                                            ? 'border-red-500/60 focus:border-red-500'
                                            : 'border-amber-500/15 hover:border-amber-500/30 focus:border-amber-500/70',
                                    ]"
                                />

                                <p v-if="form.errors.first_name" class="mt-1.5 text-xs text-red-400">
                                    {{ form.errors.first_name }}
                                </p>
                            </label>

                            <label class="block">
                                <span class="mb-2 block text-sm font-medium text-amber-100/75">
                                    Last name <span class="text-amber-400">*</span>
                                </span>

                                <input
                                    v-model="form.last_name"
                                    type="text"
                                    name="last_name"
                                    autocomplete="family-name"
                                    required
                                    maxlength="100"
                                    placeholder="Enter last name"
                                    :class="[
                                        'h-11 w-full rounded-lg border bg-black px-3.5 text-sm text-amber-50 outline-none transition placeholder:text-amber-100/25 focus:ring-2 focus:ring-amber-500/10',
                                        form.errors.last_name
                                            ? 'border-red-500/60 focus:border-red-500'
                                            : 'border-amber-500/15 hover:border-amber-500/30 focus:border-amber-500/70',
                                    ]"
                                />

                                <p v-if="form.errors.last_name" class="mt-1.5 text-xs text-red-400">
                                    {{ form.errors.last_name }}
                                </p>
                            </label>

                            <label class="block sm:col-span-2">
                                <span class="mb-2 block text-sm font-medium text-amber-100/75">
                                    Email address
                                </span>

                                <div class="relative">
                                    <input
                                        :value="profile.email"
                                        type="email"
                                        name="email"
                                        autocomplete="email"
                                        readonly
                                        aria-describedby="email-help"
                                        class="h-11 w-full cursor-not-allowed rounded-lg border border-amber-500/10 bg-white/[0.025] px-3.5 pr-11 text-sm text-amber-100/45 outline-none"
                                    />

                                    <svg
                                        class="pointer-events-none absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-amber-100/30"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                    >
                                        <rect x="5" y="10" width="14" height="11" rx="2" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10V7a4 4 0 0 1 8 0v3" />
                                    </svg>
                                </div>
                            </label>

                            <label class="block">
                                <span class="mb-2 block text-sm font-medium text-amber-100/75">
                                    Phone number
                                </span>

                                <input
                                    v-model="form.phone_number"
                                    type="tel"
                                    name="phone_number"
                                    autocomplete="tel"
                                    maxlength="30"
                                    placeholder="Enter phone number"
                                    :class="[
                                        'h-11 w-full rounded-lg border bg-black px-3.5 text-sm text-amber-50 outline-none transition placeholder:text-amber-100/25 focus:ring-2 focus:ring-amber-500/10',
                                        form.errors.phone_number
                                            ? 'border-red-500/60 focus:border-red-500'
                                            : 'border-amber-500/15 hover:border-amber-500/30 focus:border-amber-500/70',
                                    ]"
                                />

                                <p v-if="form.errors.phone_number" class="mt-1.5 text-xs text-red-400">
                                    {{ form.errors.phone_number }}
                                </p>
                            </label>

                            <div>
                                <label
                                    for="country"
                                    class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-neutral-300"
                                >
                                    Country of Residence
                                </label>

                                <CountrySelect
                                    v-model="form.country"
                                    :error="form.errors.country"
                                />

                                <p v-if="form.errors.country" class="mt-1 text-xs font-medium text-red-500">
                                    {{ form.errors.country }}
                                </p>
                            </div>

                            <label class="block sm:col-span-2">
                                <span class="mb-2 block text-sm font-medium text-amber-100/75">
                                    Date of birth
                                </span>

                                <input
                                    v-model="form.date_of_birth"
                                    type="date"
                                    name="date_of_birth"
                                    autocomplete="bday"
                                    :max="new Date().toISOString().slice(0, 10)"
                                    :class="[
                                        'h-11 w-full rounded-lg border bg-black px-3.5 text-sm text-amber-50 outline-none transition focus:ring-2 focus:ring-amber-500/10 sm:max-w-sm',
                                        form.errors.date_of_birth
                                            ? 'border-red-500/60 focus:border-red-500'
                                            : 'border-amber-500/15 hover:border-amber-500/30 focus:border-amber-500/70',
                                    ]"
                                />

                                <p v-if="form.errors.date_of_birth" class="mt-1.5 text-xs text-red-400">
                                    {{ form.errors.date_of_birth }}
                                </p>
                            </label>
                        </div>

                        <div class="flex flex-col-reverse gap-3 border-t border-amber-500/10 pt-5 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-xs text-amber-100/35">
                                Fields marked <span class="text-amber-400">*</span> are required.
                            </p>

                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-amber-500 px-5 text-sm font-semibold text-black transition hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-500/40 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <svg
                                    v-if="!form.processing"
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                                </svg>

                                <svg
                                    v-else
                                    class="h-4 w-4 animate-spin"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                >
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4z" />
                                </svg>

                                {{ form.processing ? 'Saving changes...' : 'Save changes' }}
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </UserLayout>
</template>
