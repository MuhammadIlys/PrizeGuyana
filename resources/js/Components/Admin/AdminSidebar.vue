<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false,
    },
    isCollapsed: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['close', 'toggle-collapse'])

const page = usePage()

// Only sections with actual child navigation use dropdowns.
const dropdowns = ref({
    competitions: false,
    questions: false,
    prizes: false,
})

const dropdownRoutes = {
    competitions: [
        '/admin/competitions',
        '/admin/competition-categories',
        '/admin/competition-terms',
    ],
    questions: [
        '/admin/qualification-questions',
    ],
    prizes: [
        '/admin/competition-prizes',
    ],
}

const currentPath = () => page.url.split('?')[0]

const isActive = (path) => currentPath() === path

const isSectionActive = (path) => {
    const current = currentPath()
    return current === path || current.startsWith(`${path}/`)
}

const getActiveDropdown = (url) => {
    const path = url.split('?')[0]

    return Object.keys(dropdownRoutes).find((key) =>
        dropdownRoutes[key].some((route) =>
            path === route || path.startsWith(`${route}/`)
        )
    )
}

const toggleDropdown = (name) => {
    if (props.isCollapsed) {
        emit('toggle-collapse')
        return
    }

    dropdowns.value[name] = !dropdowns.value[name]
}

watch(
    () => page.url,
    (url) => {
        const active = getActiveDropdown(url)

        Object.keys(dropdowns.value).forEach((key) => {
            dropdowns.value[key] = key === active
        })
    },
    { immediate: true }
)

const navigate = () => {
    if (window.innerWidth < 1024) {
        emit('close')
    }
}

const navClass = (path) => [
    'mb-1 flex items-center rounded-xl py-2.5 text-xs font-semibold transition-all duration-200',
    props.isCollapsed ? 'justify-center px-2' : 'px-3',
    isSectionActive(path)
        ? 'border border-amber-500/20 bg-amber-500/10 text-amber-400'
        : 'text-neutral-400 hover:bg-[#181818] hover:text-white',
]

const childClass = (path, exclude = []) => [
    'block rounded-lg px-3 py-2 text-xs font-medium transition-colors',
    isSectionActive(path) && !exclude.some((item) => isActive(item))
        ? 'bg-amber-500/10 font-semibold text-amber-400'
        : 'text-neutral-400 hover:bg-[#181818] hover:text-white',
]
</script>

<template>
    <aside id="admin-sidebar" :class="[
        'fixed inset-y-0 left-0 z-50 flex flex-col border-r border-neutral-800/80 bg-[#121212] transition-all duration-200 ease-in-out',
        isCollapsed ? 'w-20' : 'w-64',
        isOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
    ]">
        <div class="flex h-full flex-col">

            <!-- BRAND -->
            <div :class="[
                'flex h-16 shrink-0 items-center border-b border-neutral-800/80',
                isCollapsed ? 'justify-center px-3' : 'justify-between px-5',
            ]">
                <Link href="/admin/dashboard" @click="navigate" title="Prize Guyana" class="flex items-center">
                    <div
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/10 text-sm font-black text-amber-400">
                        PG
                    </div>

                    <span v-if="!isCollapsed"
                        class="ml-2.5 whitespace-nowrap text-base font-black tracking-wider text-white">
                        PRIZE<span class="text-amber-400">GUYANA</span>
                    </span>
                </Link>

                <button type="button" @click="emit('close')" aria-label="Close admin navigation"
                    class="rounded-lg p-1.5 text-neutral-400 transition-colors hover:bg-neutral-800 hover:text-white lg:hidden">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <button type="button" @click="emit('toggle-collapse')"
                    :title="isCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                    class="hidden rounded-lg p-1.5 text-neutral-500 transition-colors hover:bg-neutral-800 hover:text-white lg:block">
                    <svg v-if="!isCollapsed" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>

                    <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            <!-- NAVIGATION -->
            <nav class="custom-scrollbar min-h-0 flex-1 overflow-y-auto py-4" :class="isCollapsed ? 'px-2' : 'px-3'">

                <!-- MAIN -->
                <div class="mb-5">
                    <p v-if="!isCollapsed"
                        class="mb-2 px-3 text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                        Main Menu
                    </p>

                    <Link href="/admin/dashboard" @click="navigate" title="Dashboard"
                        :class="navClass('/admin/dashboard')">
                        <svg :class="['h-4 w-4 shrink-0', !isCollapsed ? 'mr-3' : '']" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6" />
                        </svg>
                        <span v-if="!isCollapsed">Dashboard</span>
                    </Link>
                </div>

                <!-- COMPETITION MANAGEMENT -->
                <div class="mb-5">
                    <p v-if="!isCollapsed"
                        class="mb-2 px-3 text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                        Competition Management
                    </p>

                    <!-- Competitions -->
                    <div>
                        <button type="button" @click="toggleDropdown('competitions')" title="Competitions"
                            :aria-expanded="dropdowns.competitions" :class="[
                                'flex w-full items-center rounded-xl py-2.5 text-xs font-semibold transition-all duration-200',
                                isSectionActive('/admin/competitions') ||
                                    isActive('/admin/competition-categories') ||
                                    isActive('/admin/competition-terms')
                                    ? 'bg-amber-500/5 text-amber-400'
                                    : 'text-neutral-400 hover:bg-[#181818] hover:text-white',
                                isCollapsed ? 'justify-center px-2' : 'justify-between px-3',
                            ]">
                            <span class="flex items-center">
                                <svg :class="['h-4 w-4 shrink-0', !isCollapsed ? 'mr-3' : '']" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5a2 2 0 10-2 2h2zm0 13C10.832 19.82 8 17.6 8 12V8a2 2 0 012-2h4a2 2 0 012 2v4c0 5.6-2.832 7.82-4 9z" />
                                </svg>

                                <span v-if="!isCollapsed">Competitions</span>
                            </span>

                            <svg v-if="!isCollapsed" :class="[
                                'h-3.5 w-3.5 transition-transform duration-200',
                                dropdowns.competitions ? 'rotate-180' : '',
                            ]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div v-if="!isCollapsed" v-show="dropdowns.competitions" class="mt-1 space-y-1 pl-9 pr-1">
                            <Link href="/admin/competitions" @click="navigate"
                                :class="childClass('/admin/competitions', ['/admin/competitions/create'])">
                                All Competitions
                            </Link>

                            <Link href="/admin/competitions/create" @click="navigate"
                                :class="childClass('/admin/competitions/create')">
                                Create Competition
                            </Link>

                            <Link href="/admin/competition-categories" @click="navigate"
                                :class="childClass('/admin/competition-categories')">
                                Categories
                            </Link>

                            <Link href="/admin/competition-terms" @click="navigate"
                                :class="childClass('/admin/competition-terms')">
                                Terms &amp; Conditions
                            </Link>
                        </div>
                    </div>

                    <!-- Qualification Questions -->
                    <div>
                        <button type="button" @click="toggleDropdown('questions')" title="Qualification Questions"
                            :aria-expanded="dropdowns.questions" :class="[
                                'flex w-full items-center rounded-xl py-2.5 text-xs font-semibold transition-all duration-200',
                                isSectionActive('/admin/qualification-questions')
                                    ? 'bg-amber-500/5 text-amber-400'
                                    : 'text-neutral-400 hover:bg-[#181818] hover:text-white',
                                isCollapsed ? 'justify-center px-2' : 'justify-between px-3',
                            ]">
                            <span class="flex items-center">
                                <svg :class="['h-4 w-4 shrink-0', !isCollapsed ? 'mr-3' : '']" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8.228 9c.549-1.165 1.806-2 3.272-2 1.933 0 3.5 1.343 3.5 3 0 1.5-1 2.5-2.5 3.25-.75.375-1.25.875-1.25 1.75M11.25 18h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>

                                <span v-if="!isCollapsed">Qualification Questions</span>
                            </span>

                            <svg v-if="!isCollapsed" :class="[
                                'h-3.5 w-3.5 transition-transform duration-200',
                                dropdowns.questions ? 'rotate-180' : '',
                            ]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div v-if="!isCollapsed" v-show="dropdowns.questions" class="mt-1 space-y-1 pl-9 pr-1">
                            <Link :href="route('admin.qualification-questions.index')" @click="navigate"
                                :class="childClass('/admin/qualification-questions', ['/admin/qualification-questions/create'])">
                                All Questions
                            </Link>

                            <Link :href="route('admin.qualification-questions.create')" @click="navigate"
                                :class="childClass('/admin/qualification-questions/create')">
                                Create Question
                            </Link>
                        </div>
                    </div>

                    <!-- Prizes -->
                    <div>
                        <button type="button" @click="toggleDropdown('prizes')" title="Prizes"
                            :aria-expanded="dropdowns.prizes" :class="[
                                'flex w-full items-center rounded-xl py-2.5 text-xs font-semibold transition-all duration-200',
                                isSectionActive('/admin/competition-prizes')
                                    ? 'bg-amber-500/5 text-amber-400'
                                    : 'text-neutral-400 hover:bg-[#181818] hover:text-white',
                                isCollapsed ? 'justify-center px-2' : 'justify-between px-3',
                            ]">
                            <span class="flex items-center">
                                <svg :class="['h-4 w-4 shrink-0', !isCollapsed ? 'mr-3' : '']" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v13m0-13C10.343 8 9 6.657 9 5s1.343-3 3-3 3 1.343 3 3-1.343 3-3 3zm0 0H5.5A2.5 2.5 0 003 10.5v1A2.5 2.5 0 005.5 14H12m0-6h6.5a2.5 2.5 0 012.5 2.5v1a2.5 2.5 0 01-2.5 2.5H12m-9 0v5A2.5 2.5 0 005.5 21h13a2.5 2.5 0 002.5-2.5v-5" />
                                </svg>

                                <span v-if="!isCollapsed">Prizes</span>
                            </span>

                            <svg v-if="!isCollapsed" :class="[
                                'h-3.5 w-3.5 transition-transform duration-200',
                                dropdowns.prizes ? 'rotate-180' : '',
                            ]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div v-if="!isCollapsed" v-show="dropdowns.prizes" class="mt-1 space-y-1 pl-9 pr-1">
                            <Link :href="route('admin.competition-prizes.index')" @click="navigate"
                                :class="childClass('/admin/competition-prizes', ['/admin/competition-prizes/create'])">
                                All Prizes
                            </Link>

                            <Link :href="route('admin.competition-prizes.create')" @click="navigate"
                                :class="childClass('/admin/competition-prizes/create')">
                                Create Prize
                            </Link>
                        </div>
                    </div>

                     <Link :href="route('admin.tickets.index')" @click="navigate" title="Tickets"
                        :class="navClass('/admin/tickets')">
                        <svg :class="['h-4 w-4 shrink-0', !isCollapsed ? 'mr-3' : '']" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2 9a3 3 0 013-3h14a3 3 0 013 3v1a2 2 0 000 4v1a3 3 0 01-3 3H5a3 3 0 01-3-3v-1a2 2 0 000-4V9z" />
                        </svg>
                        <span v-if="!isCollapsed">Tickets</span>
                    </Link>
                    
                </div>

                <!-- SALES & TRANSACTIONS -->
                <div class="mb-5">
                    <p v-if="!isCollapsed"
                        class="mb-2 px-3 text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                        Sales &amp; Transactions
                    </p>

                    <Link :href="route('admin.orders.index')" @click="navigate" title="Orders"
                        :class="navClass('/admin/orders')">
                        <svg :class="['h-4 w-4 shrink-0', !isCollapsed ? 'mr-3' : '']" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 14l6-6m-5.5 1.5h.01M14.5 14.5h.01M7 4h10l3 4v11H4V8l3-4z" />
                        </svg>
                        <span v-if="!isCollapsed">Orders</span>
                    </Link>

                    <Link :href="route('admin.entries.index')" @click="navigate" title="Entries"
                        :class="navClass('/admin/entries')">
                        <svg :class="['h-4 w-4 shrink-0', !isCollapsed ? 'mr-3' : '']" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 5h12M6 9h12M6 13h8m-8 4h8" />
                        </svg>
                        <span v-if="!isCollapsed">Entries</span>
                    </Link>

                   
                </div>

                <!-- CUSTOMERS -->
                <div class="mb-5">
                    <p v-if="!isCollapsed"
                        class="mb-2 px-3 text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                        Customers
                    </p>

                    <Link href="/admin/customers" @click="navigate" title="Customers"
                        :class="navClass('/admin/customers')">
                        <svg :class="['h-4 w-4 shrink-0', !isCollapsed ? 'mr-3' : '']" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857m0 0a5.002 5.002 0 00-9.288 0M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span v-if="!isCollapsed">Customers</span>
                    </Link>
                </div>

                <!-- V1 MODULES AWAITING ROUTE CONFIRMATION -->
                <div class="mb-5">
                    <p v-if="!isCollapsed"
                        class="mb-2 px-3 text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                        Draw Management
                    </p>

                    <div v-for="item in [
                        { label: 'Draws', icon: 'draws' },
                        { label: 'Winners', icon: 'winners' },
                    ]" :key="item.label" :title="item.label + ' — route not connected yet'"
                        class="mb-1 flex cursor-not-allowed items-center rounded-xl px-3 py-2.5 text-xs font-semibold text-neutral-600"
                        :class="isCollapsed ? 'justify-center' : ''">
                        <span v-if="!isCollapsed">{{ item.label }}</span>
                        <span v-else class="text-[10px]">{{ item.label[0] }}</span>
                    </div>
                </div>

                <!-- REPORTS -->
                <div class="mb-5">
                    <p v-if="!isCollapsed"
                        class="mb-2 px-3 text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                        Analytics
                    </p>

                    <div :title="'Reports — route not connected yet'"
                        class="mb-1 flex cursor-not-allowed items-center rounded-xl px-3 py-2.5 text-xs font-semibold text-neutral-600"
                        :class="isCollapsed ? 'justify-center' : ''">
                        <span v-if="!isCollapsed">Reports</span>
                        <span v-else class="text-[10px]">R</span>
                    </div>
                </div>

                <!-- SYSTEM -->
                <div class="mb-5">
                    <p v-if="!isCollapsed"
                        class="mb-2 px-3 text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                        System
                    </p>

                    <div v-for="item in ['Settings', 'Audit Logs']" :key="item"
                        :title="item + ' — route not connected yet'"
                        class="mb-1 flex cursor-not-allowed items-center rounded-xl px-3 py-2.5 text-xs font-semibold text-neutral-600"
                        :class="isCollapsed ? 'justify-center' : ''">
                        <span v-if="!isCollapsed">{{ item }}</span>
                        <span v-else class="text-[10px]">{{ item[0] }}</span>
                    </div>
                </div>

            </nav>

            <!-- FOOTER -->
            <div class="shrink-0 border-t border-neutral-800/80 p-3">
                <div :class="[
                    'flex items-center rounded-xl border border-neutral-800/80 bg-[#0d0d0d]',
                    isCollapsed ? 'justify-center p-2' : 'justify-between p-3',
                ]">
                    <div v-if="!isCollapsed">
                        <p class="mb-0.5 text-xs font-bold text-white">
                            Prize Guyana
                        </p>
                        <p class="font-mono text-[10px] text-neutral-500">
                            Admin v1.0.0
                        </p>
                    </div>

                    <Link href="/logout" method="post" as="button"
                        class="shrink-0 cursor-pointer rounded-lg p-2 text-neutral-400 transition-colors hover:bg-neutral-800 hover:text-rose-400"
                        title="Sign Out" aria-label="Sign out">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 013-3V7a3 3 0 013 3v1" />
                        </svg>
                    </Link>
                </div>
            </div>
        </div>
    </aside>
</template>
