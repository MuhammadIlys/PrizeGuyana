<script setup>
import { ref, watch } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import CountrySelect from '@/Components/Global/CountrySelect.vue'

const props = defineProps({
    admins: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        default: () => ({ total_admins: 0 }),
    },
    filters: {
        type: Object,
        default: () => ({ search: '' }),
    },
})

const search = ref(props.filters.search || '')
const showModal = ref(false)
const editingAdmin = ref(null)
const showDeleteModal = ref(false)
const deletingAdmin = ref(null)

let searchTimeout = null

watch(search, (value) => {
    clearTimeout(searchTimeout)

    searchTimeout = setTimeout(() => {
        router.get(
            route('admin.admins.index'),
            { search: value },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        )
    }, 400)
})

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    phone_number: '',
    country: '',
    date_of_birth: '',
    password: '',
    password_confirmation: '',
})

const openCreateModal = () => {
    editingAdmin.value = null
    form.reset()
    form.clearErrors()
    showModal.value = true
}

const openEditModal = (admin) => {
    editingAdmin.value = admin

    form.clearErrors()
    form.first_name = admin.first_name || ''
    form.last_name = admin.last_name || ''
    form.email = admin.email || ''
    form.phone_number = admin.phone_number || ''
    form.country = admin.country || ''
    form.date_of_birth = admin.date_of_birth || ''
    form.password = ''
    form.password_confirmation = ''

    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    editingAdmin.value = null
    form.reset()
    form.clearErrors()
}

const submitForm = () => {
    if (editingAdmin.value) {
        form.put(
            route('admin.admins.update', editingAdmin.value.id),
            {
                preserveScroll: true,
                onSuccess: () => closeModal(),
            }
        )
    } else {
        form.post(route('admin.admins.store'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        })
    }
}

const confirmDelete = (admin) => {
    deletingAdmin.value = admin
    showDeleteModal.value = true
}

const deleteAdmin = () => {
    if (!deletingAdmin.value) return

    router.delete(
        route('admin.admins.destroy', deletingAdmin.value.id),
        {
            preserveScroll: true,
            onSuccess: () => {
                showDeleteModal.value = false
                deletingAdmin.value = null
            },
        }
    )
}

const formatDate = (date) => {
    if (!date) return '—'

    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    })
}
</script>

<template>

    <Head title="Admin Management" />

    <AdminLayout>
        <div class="space-y-6">

            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white">
                        Admin Management
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">
                        Manage administrator accounts and access.
                    </p>
                </div>

                <button @click="openCreateModal"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-5 py-3 text-sm font-bold text-slate-950 transition hover:bg-amber-400">
                    <span class="text-lg leading-none">+</span>
                    Add Admin
                </button>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-400">
                            Total Administrators
                        </p>
                        <div class="rounded-xl bg-amber-500/10 p-2 text-amber-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M17 20h5v-2a4 4 0 00-5.5-3.7M17 20H7m10 0v-2c0-.7-.2-1.4-.5-2M7 20H2v-2a4 4 0 015.5-3.7M7 20v-2c0-.7.2-1.4.5-2m0 0a5 5 0 019 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>

                    <p class="mt-4 text-3xl font-bold text-white">
                        {{ stats.total_admins ?? 0 }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">
                        Registered admin accounts
                    </p>
                </div>
            </div>

            <!-- Admin Table -->
            <div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900">

                <div
                    class="flex flex-col gap-4 border-b border-slate-800 p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="font-semibold text-white">
                            Administrators
                        </h2>
                        <p class="mt-1 text-xs text-slate-500">
                            Manage all platform administrator accounts.
                        </p>
                    </div>

                    <div class="relative w-full sm:max-w-xs">
                        <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="7" stroke-width="1.8" />
                            <path stroke-linecap="round" stroke-width="1.8" d="m16 16 4 4" />
                        </svg>

                        <input v-model="search" type="text" placeholder="Search administrators..."
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 py-2.5 pl-10 pr-4 text-sm text-white placeholder-slate-500 outline-none transition focus:border-amber-500" />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[850px] text-left">
                        <thead class="bg-slate-950/70">
                            <tr class="text-xs uppercase tracking-wider text-slate-500">
                                <th class="px-6 py-4 font-semibold">Administrator</th>
                                <th class="px-6 py-4 font-semibold">Contact</th>
                                <th class="px-6 py-4 font-semibold">Country</th>
                                <th class="px-6 py-4 font-semibold">Date Added</th>
                                <th class="px-6 py-4 text-right font-semibold">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-800">
                            <tr v-for="admin in admins.data" :key="admin.id" class="transition hover:bg-slate-800/40">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-sm font-bold text-amber-400">
                                            {{ (admin.name || 'A').charAt(0).toUpperCase() }}
                                        </div>

                                        <div>
                                            <p class="font-semibold text-white">
                                                {{ admin.name }}
                                            </p>
                                            <p class="mt-1 text-xs text-slate-500">
                                                Administrator
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <p class="text-sm text-slate-300">
                                        {{ admin.email }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ admin.phone_number || 'No phone number' }}
                                    </p>
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-300">
                                    {{ admin.country || '—' }}
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-400">
                                    {{ formatDate(admin.created_at) }}
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openEditModal(admin)"
                                            class="rounded-lg border border-slate-700 px-3 py-2 text-xs font-semibold text-slate-300 transition hover:border-amber-500/50 hover:text-amber-400">
                                            Edit
                                        </button>

                                        <button @click="confirmDelete(admin)"
                                            class="rounded-lg border border-red-500/20 px-3 py-2 text-xs font-semibold text-red-400 transition hover:border-red-500/50 hover:bg-red-500/10">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="!admins.data?.length">
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div
                                        class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-800 text-slate-500">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                                d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2m16-10a4 4 0 100-8 4 4 0 000 8zM9 11a4 4 0 100-8 4 4 0 000 8z" />
                                        </svg>
                                    </div>
                                    <p class="mt-3 font-medium text-slate-300">
                                        No administrators found
                                    </p>
                                    <p class="mt-1 text-sm text-slate-500">
                                        Try another search or add a new administrator.
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="admins.links?.length > 3"
                    class="flex flex-col gap-4 border-t border-slate-800 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs text-slate-500">
                        Showing {{ admins.from || 0 }} to {{ admins.to || 0 }}
                        of {{ admins.total || 0 }} administrators
                    </p>

                    <div class="flex flex-wrap items-center gap-1">
                        <template v-for="(link, index) in admins.links" :key="index">
                            <button v-if="link.url" @click="router.visit(link.url, { preserveScroll: true })"
                                v-html="link.label" :class="[
                                    'rounded-lg px-3 py-2 text-xs font-semibold transition',
                                    link.active
                                        ? 'bg-amber-500 text-slate-950'
                                        : 'bg-slate-800 text-slate-400 hover:bg-slate-700 hover:text-white'
                                ]" />
                            <span v-else v-html="link.label" class="rounded-lg px-3 py-2 text-xs text-slate-600" />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add / Edit Modal -->
        <div v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/70 p-4 backdrop-blur-sm"
            @click.self="closeModal">
            <div
                class="my-auto w-full max-w-2xl overflow-hidden rounded-2xl border border-slate-700 bg-slate-900 shadow-2xl">

                <div class="flex items-center justify-between border-b border-slate-800 px-6 py-5">
                    <div>
                        <h2 class="text-lg font-bold text-white">
                            {{ editingAdmin ? 'Edit Administrator' : 'Add Administrator' }}
                        </h2>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ editingAdmin ? 'Update administrator account details.' : 'Create a new platform administrator account.' }}
                        </p>
                    </div>

                    <button @click="closeModal"
                        class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-800 hover:text-white">
                        ✕
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-5 p-6">

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-xs font-semibold text-slate-300">
                                First Name *
                            </label>
                            <input v-model="form.first_name" type="text" required class="form-input"
                                placeholder="First name" />
                            <p v-if="form.errors.first_name" class="mt-1 text-xs text-red-400">
                                {{ form.errors.first_name }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold text-slate-300">
                                Last Name *
                            </label>
                            <input v-model="form.last_name" type="text" required class="form-input"
                                placeholder="Last name" />
                            <p v-if="form.errors.last_name" class="mt-1 text-xs text-red-400">
                                {{ form.errors.last_name }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold text-slate-300">
                                Email Address *
                            </label>
                            <input v-model="form.email" type="email" required class="form-input"
                                placeholder="admin@example.com" />
                            <p v-if="form.errors.email" class="mt-1 text-xs text-red-400">
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold text-slate-300">
                                Phone Number
                            </label>
                            <input v-model="form.phone_number" type="text" class="form-input"
                                placeholder="Phone number" />
                            <p v-if="form.errors.phone_number" class="mt-1 text-xs text-red-400">
                                {{ form.errors.phone_number }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold text-slate-300">
                                Country *
                            </label>

                            <CountrySelect v-model="form.country" :error="form.errors.country" />
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold text-slate-300">
                                Date of Birth *
                            </label>
                            <input v-model="form.date_of_birth" type="date" required
                                :max="new Date().toISOString().split('T')[0]" class="form-input" />
                            <p v-if="form.errors.date_of_birth" class="mt-1 text-xs text-red-400">
                                {{ form.errors.date_of_birth }}
                            </p>
                        </div>
                    </div>

                    <div class="border-t border-slate-800 pt-5">
                        <h3 class="mb-1 text-sm font-semibold text-white">
                            {{ editingAdmin ? 'Change Password' : 'Account Password' }}
                        </h3>
                        <p class="mb-4 text-xs text-slate-500">
                            {{ editingAdmin ? 'Leave blank to keep the current password.' : 'Set a secure password for this administrator.' }}
                        </p>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-xs font-semibold text-slate-300">
                                    Password {{ editingAdmin ? '' : '*' }}
                                </label>
                                <input v-model="form.password" type="password" :required="!editingAdmin"
                                    autocomplete="new-password" class="form-input"
                                    placeholder="Minimum 8 characters" />
                                <p v-if="form.errors.password" class="mt-1 text-xs text-red-400">
                                    {{ form.errors.password }}
                                </p>
                            </div>

                            <div>
                                <label class="mb-2 block text-xs font-semibold text-slate-300">
                                    Confirm Password {{ editingAdmin ? '' : '*' }}
                                </label>
                                <input v-model="form.password_confirmation" type="password"
                                    :required="!editingAdmin && !!form.password" autocomplete="new-password"
                                    class="form-input" placeholder="Confirm password" />
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-800 pt-5 sm:flex-row sm:justify-end">
                        <button type="button" @click="closeModal"
                            class="rounded-xl border border-slate-700 px-5 py-3 text-sm font-semibold text-slate-300 transition hover:bg-slate-800">
                            Cancel
                        </button>

                        <button type="submit" :disabled="form.processing"
                            class="rounded-xl bg-amber-500 px-6 py-3 text-sm font-bold text-slate-950 transition hover:bg-amber-400 disabled:cursor-not-allowed disabled:opacity-50">
                            {{ form.processing ? 'Saving...' : editingAdmin ? 'Save Changes' : 'Create Administrator' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation -->
        <div v-if="showDeleteModal"
            class="fixed inset-0 z-[60] flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm"
            @click.self="showDeleteModal = false">
            <div class="w-full max-w-md rounded-2xl border border-slate-700 bg-slate-900 p-6 shadow-2xl">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-500/10 text-red-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M6 7h12m-10 0V5a1 1 0 011-1h4a1 1 0 011 1v2m2 0v12a1 1 0 01-1 1H9a1 1 0 01-1-1V7m3 4v5m2-5v5" />
                    </svg>
                </div>

                <h2 class="mt-4 text-lg font-bold text-white">
                    Delete Administrator?
                </h2>

                <p class="mt-2 text-sm leading-6 text-slate-400">
                    Are you sure you want to delete
                    <span class="font-semibold text-white">
                        {{ deletingAdmin?.name }}
                    </span>?
                    This action cannot be undone.
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <button @click="showDeleteModal = false"
                        class="rounded-xl border border-slate-700 px-4 py-2.5 text-sm font-semibold text-slate-300 hover:bg-slate-800">
                        Cancel
                    </button>

                    <button @click="deleteAdmin"
                        class="rounded-xl bg-red-500 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-red-400">
                        Delete Admin
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.form-input {
    width: 100%;
    border: 1px solid rgb(51 65 85);
    border-radius: 0.75rem;
    background: rgb(2 6 23);
    padding: 0.7rem 0.85rem;
    font-size: 0.875rem;
    color: white;
    outline: none;
    transition: border-color 0.2s;
}

.form-input:focus {
    border-color: rgb(245 158 11);
}

.form-input::placeholder {
    color: rgb(100 116 139);
}

.form-input:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
