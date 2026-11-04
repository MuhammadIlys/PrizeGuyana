
<script setup>
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import UserLayout from '@/Layouts/UserLayout.vue'

const visibility = ref({
    current_password: true,
    password: true,
    password_confirmation: true,
})

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
})

const fields = [
    {
        key: 'current_password',
        label: 'Current password',
        placeholder: 'Enter your current password',
        autocomplete: 'current-password',
    },
    {
        key: 'password',
        label: 'New password',
        placeholder: 'Enter your new password',
        autocomplete: 'new-password',
    },
    {
        key: 'password_confirmation',
        label: 'Confirm new password',
        placeholder: 'Re-enter your new password',
        autocomplete: 'new-password',
    },
]

function toggleVisibility(key) {
    visibility.value[key] = !visibility.value[key]
}

function submit() {
    form.put(route('user.password.update'), {
        preserveScroll: true,

        onSuccess: () => {
            form.reset()

            visibility.value = {
                current_password: true,
                password: true,
                password_confirmation: true,
            }
        },
    })
}
</script>

<template>
    <Head title="Account Security" />

    <UserLayout>
        <div class="mx-auto max-w-4xl space-y-6 text-amber-50">
            <header>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-400/70">
                    Account settings
                </p>

                <h1 class="mt-2 text-2xl font-bold tracking-tight text-amber-400 sm:text-3xl">
                    Account Security
                </h1>

                <p class="mt-2 text-sm leading-6 text-amber-100/50">
                    Keep your PrizeGuyana account secure by updating your password.
                </p>
            </header>

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_260px]">
                <section class="overflow-hidden rounded-2xl border border-amber-500/15 bg-[#0b0b0b]">
                    <div class="border-b border-amber-500/15 px-5 py-4 sm:px-6">
                        <h2 class="font-semibold text-amber-100">
                            Change password
                        </h2>

                        <p class="mt-1 text-sm text-amber-100/45">
                            Enter your current password to authorize this change.
                        </p>
                    </div>

                    <form class="space-y-5 p-5 sm:p-6" @submit.prevent="submit">
                        <div v-for="field in fields" :key="field.key">
                            <label
                                :for="field.key"
                                class="mb-2 block text-sm font-medium text-amber-100/75"
                            >
                                {{ field.label }}
                                <span class="text-amber-400">*</span>
                            </label>

                            <div class="relative">
                                <input
                                    :id="field.key"
                                    v-model="form[field.key]"
                                    :type="visibility[field.key] ? 'text' : 'password'"
                                    :name="field.key"
                                    :autocomplete="field.autocomplete"
                                    :placeholder="field.placeholder"
                                    required
                                    :minlength="field.key !== 'current_password' ? 8 : undefined"
                                    class="h-11 w-full rounded-lg border border-amber-500/15 bg-black px-3.5 pr-12 text-sm text-amber-50 outline-none transition placeholder:text-amber-100/25 hover:border-amber-500/30 focus:border-amber-500/70 focus:ring-2 focus:ring-amber-500/10"
                                    :aria-invalid="!!form.errors[field.key]"
                                />

                                <button
                                    type="button"
                                    class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-amber-100/50 transition hover:text-amber-300"
                                    :aria-label="visibility[field.key] ? 'Hide password' : 'Show password'"
                                    :title="visibility[field.key] ? 'Hide password' : 'Show password'"
                                    @click="toggleVisibility(field.key)"
                                >
                                    <!-- Visible password: eye icon -->
                                    <svg
                                        v-if="visibility[field.key]"
                                        class="h-5 w-5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>

                                    <!-- Hidden password: slashed eye icon -->
                                    <svg
                                        v-else
                                        class="h-5 w-5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path d="M3 3l18 18" />
                                        <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8" />
                                        <path d="M9.9 5.2A10.8 10.8 0 0 1 12 5c6.5 0 10 7 10 7a16 16 0 0 1-3.2 4.1" />
                                        <path d="M6.2 6.2C3.5 8 2 12 2 12s3.5 7 10 7c1.4 0 2.7-.3 3.8-.8" />
                                    </svg>
                                </button>
                            </div>

                            <p
                                v-if="form.errors[field.key]"
                                class="mt-1.5 text-xs text-red-400"
                            >
                                {{ form.errors[field.key] }}
                            </p>
                        </div>

                        <p class="text-xs leading-5 text-amber-100/40">
                            Your new password must contain at least 8 characters,
                            include uppercase and lowercase letters, a number and a
                            special character, and differ from your current password.
                        </p>

                        <div class="flex justify-end border-t border-amber-500/10 pt-5">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex h-11 items-center justify-center rounded-lg bg-amber-500 px-5 text-sm font-semibold text-black transition hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-500/40 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                {{ form.processing ? 'Updating password...' : 'Update password' }}
                            </button>
                        </div>
                    </form>
                </section>

                <aside class="h-fit rounded-2xl border border-amber-500/15 bg-[#0b0b0b] p-5">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/5 text-amber-400"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M12 3 4.5 6v5.5c0 4.4 3.2 7.6 7.5 9.5 4.3-1.9 7.5-5.1 7.5-9.5V6L12 3Z" />
                            <path d="m9 12 2 2 4-4" />
                        </svg>
                    </div>

                    <h3 class="mt-4 text-sm font-semibold text-amber-100">
                        Password security tips
                    </h3>

                    <ul class="mt-3 space-y-3 text-xs leading-5 text-amber-100/50">
                        <li>• Use at least 8 characters.</li>
                        <li>• Include at least one uppercase letter (A–Z).</li>
                        <li>• Include at least one lowercase letter (a–z).</li>
                        <li>• Include at least one number (0–9).</li>
                        <li>• Include at least one special character (@, #, !, etc.).</li>
                        <li>• Choose a password different from your current password.</li>
                        <li>• Avoid reusing passwords from other accounts.</li>
                    </ul>
                </aside>
            </div>
        </div>
    </UserLayout>
</template>
