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

const emit = defineEmits([
    'close',
    'toggle-collapse',
])

const page = usePage()

const dropdowns = ref({
    competitions: false,
    qualificationQuestions: false,
    orders: false,
    customers: false,
    draws: false,
    content: false,
    marketing: false,
    reports: false,
    system: false,
})

const dropdownRoutes = {

    competitions: [
        '/admin/competitions',
        '/admin/competition-categories',
        '/admin/competition-terms',
    ],
    qualificationQuestions: [
        '/admin/qualification-questions',
    ],
    orders: [
        '/admin/orders',
        '/admin/entries',
    ],

    customers: [
        '/admin/customers',
    ],
    draws: [
        '/admin/draws',
        '/admin/winners',
    ],

    content: [
        '/admin/pages',
    ],
    marketing: [
        '/admin/promotions',
    ],

    reports: [
        '/admin/reports',
    ],

    system: [
        '/admin/settings',
        '/admin/audit-logs',
    ],

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
    dropdowns.value[name] = !dropdowns.value[name]
}

watch(
    () => page.url,
    (url) => {
        const activeDropdown = getActiveDropdown(url)

        Object.keys(dropdowns.value).forEach((key) => {
            dropdowns.value[key] = key === activeDropdown
        })
    },
    { immediate: true }
)
const navigate = () => {
    if (window.innerWidth < 1024) {
        emit('close')
    }
}

const isActive = (path) => {
    return page.url.split('?')[0] === path
}

const isSectionActive = (path) => {
    const currentPath = page.url.split('?')[0]
    return currentPath === path || currentPath.startsWith(`${path} /`)
}

</script>

<template>

    <aside id="admin-sidebar" :class="[
        'fixed inset-y-0 left-0 z-50 flex flex-col border-r border-neutral-800/80 bg-[#121212] transition-all duration-200 ease-in-out',
        props.isCollapsed ? 'w-20' : 'w-64',
        props.isOpen
            ? 'translate-x-0'
            : '-translate-x-full lg:translate-x-0'
    ]">

        <div class="flex h-full flex-col">

            <!-- ================================================= -->
            <!-- BRAND -->
            <!-- ================================================= -->

            <div :class="[
                'flex h-16 shrink-0 items-center border-b border-neutral-800/80 transition-all duration-200',
                isCollapsed ? 'justify-center px-3' : 'justify-between px-5'
            ]">

                <Link href="/admin/dashboard" @click="navigate" :title="isCollapsed ? 'Prize Guyana' : undefined"
                    class="flex items-center">
                    <div
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/10 text-sm font-black text-amber-400">
                        PG
                    </div>

                    <span v-if="!isCollapsed"
                        class="ml-2.5 whitespace-nowrap text-base font-black tracking-wider text-white">
                        PRIZE<span class="text-amber-400">GUYANA</span>
                    </span>
                </Link>

                <!-- Mobile Close -->

                <button type="button" @click="navigate" aria-label="Close admin navigation"
                    class="rounded-lg p-1.5 text-neutral-400 transition-colors hover:bg-neutral-800 hover:text-white lg:hidden">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>

                </button>

                <!-- Desktop Collapse -->

                <button type="button" @click="emit('toggle-collapse')"
                    :title="props.isCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                    class="hidden rounded-lg p-1.5 text-neutral-500 transition-colors hover:bg-neutral-800 hover:text-white lg:block">
                    <svg v-if="!props.isCollapsed" class="h-4 w-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>

                    <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            <!-- ================================================= -->
            <!-- NAVIGATION -->
            <!-- ================================================= -->

            <nav class="custom-scrollbar min-h-0 flex-1 overflow-y-auto py-4"
                :class="props.isCollapsed ? 'px-2' : 'px-3'">

                <!-- ================================================= -->
                <!-- MAIN MENU -->
                <!-- ================================================= -->

                <div class="">
                    <p v-if="!isCollapsed"
                        class="mb-2 px-3 text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                        Main Menu
                    </p>

                    <!-- Dashboard -->

                    <Link href="/admin/dashboard" @click="navigate" title="Dashboard" :class="[
                        'mb-1 flex items-center rounded-xl py-2.5 text-xs font-semibold transition-all duration-150',
                        isCollapsed ? 'justify-center px-2' : 'px-3',
                        isActive('/admin/dashboard')
                            ? 'border border-amber-500/20 bg-amber-500/10 text-amber-400 shadow-lg shadow-amber-500/5'
                            : 'text-neutral-400 hover:bg-[#181818] hover:text-white'
                    ]">

                        <svg :class="[
                            'h-4 w-4 shrink-0',
                            !isCollapsed ? 'mr-3' : ''
                        ]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6" />
                        </svg>

                        <span v-if="!isCollapsed">
                            Dashboard
                        </span>
                    </Link>
                </div>

                <!-- ================================================= -->
                <!-- COMPETITIONS -->
                <!-- ================================================= -->

                <div class="">
                    <div>
                        <!-- Competition Parent -->
                        <button type="button" @click="toggleDropdown('competitions')" title="Competitions"
                            :aria-expanded="dropdowns.competitions" :class="[

                                'flex w-full cursor-pointer items-center rounded-xl py-2.5 text-xs font-semibold transition-all duration-200',
                                isSectionActive('/admin/competitions') ||
                                    isActive('/admin/competition-categories') ||
                                    isActive('/admin/competition-terms')
                                    ? 'bg-amber-500/5 text-amber-400'
                                    : 'text-neutral-400 hover:bg-[#181818] hover:text-white',
                                isCollapsed
                                    ? 'justify-center px-2'
                                    : 'justify-between px-3'
                            ]">

                            <div :class="[

                                'flex items-center',

                                isCollapsed ? 'justify-center' : ''

                            ]">

                                <svg :class="[

                                    'h-4 w-4 shrink-0',

                                    !isCollapsed ? 'mr-3' : ''

                                ]" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5a2 2 0 10-2 2h2zm0 13C10.832 19.82 8 17.6 8 12V8a2 2 0 012-2h4a2 2 0 012 2v4c0 5.6-2.832 7.82-4 9z" />

                                </svg>



                                <span v-if="!isCollapsed">

                                    Competitions

                                </span>

                            </div>



                            <svg v-if="!isCollapsed" :class="[

                                'h-3.5 w-3.5 shrink-0 transition-transform duration-200',

                                dropdowns.competitions ? 'rotate-180' : ''

                            ]" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />

                            </svg>

                        </button>



                        <!-- Competition Children -->

                        <div v-if="!isCollapsed" v-show="dropdowns.competitions" class="mt-1 space-y-1 pl-9 pr-1">

                            <!-- All Competitions -->

                            <Link href="/admin/competitions" @click="navigate" :class="[

                                'block rounded-lg px-3 py-2 text-xs font-medium transition-colors',

                                isActive('/admin/competitions')

                                    ? 'bg-amber-500/10 font-semibold text-amber-400'

                                    : 'text-neutral-400 hover:bg-[#181818] hover:text-white'

                            ]">

                                All Competitions

                            </Link>



                            <!-- Create Competition -->

                            <Link href="/admin/competitions/create" @click="navigate" :class="[

                                'block rounded-lg px-3 py-2 text-xs font-medium transition-colors',

                                isActive('/admin/competitions/create')

                                    ? 'bg-amber-500/10 font-semibold text-amber-400'

                                    : 'text-neutral-400 hover:bg-[#181818] hover:text-white'

                            ]">

                                Create Competition

                            </Link>



                            <!-- Categories -->

                            <Link href="/admin/competition-categories" @click="navigate" :class="[

                                'block rounded-lg px-3 py-2 text-xs font-medium transition-colors',

                                isActive('/admin/competition-categories')

                                    ? 'bg-amber-500/10 font-semibold text-amber-400'

                                    : 'text-neutral-400 hover:bg-[#181818] hover:text-white'

                            ]">

                                Categories

                            </Link>



                            <!-- Terms & Conditions -->

                            <Link href="/admin/competition-terms" @click="navigate" :class="[

                                'block rounded-lg px-3 py-2 text-xs font-medium transition-colors',

                                isActive('/admin/competition-terms') ? 'bg-amber-500/10 font-semibold text-amber-400'

                                    : 'text-neutral-400 hover:bg-[#181818] hover:text-white'

                            ]">

                                Terms &amp; Conditions

                            </Link>





                            <!-- Archived Competitions -->

                            <Link href="#" @click.prevent
                                class="block cursor-not-allowed rounded-lg px-3 py-2 text-xs font-medium text-neutral-600">

                                Archived Competitions

                            </Link>

                        </div>

                    </div>

                </div>

                <!-- ================================================= -->
                <!-- Qualification Questions Dropdown -->
                <!-- ================================================= -->

                <div>

                    <!-- Qualification Questions Parent -->

                    <button type="button" @click="toggleDropdown('qualificationQuestions')"
                        title="Qualification Questions" :aria-expanded="dropdowns.qualificationQuestions" :class="[

                            'flex w-full cursor-pointer items-center rounded-xl py-2.5 text-xs font-semibold transition-all duration-200',

                            isSectionActive('/admin/qualification-questions')

                                ? 'bg-amber-500/5 text-amber-400'

                                : 'text-neutral-400 hover:bg-[#181818] hover:text-white',

                            isCollapsed

                                ? 'justify-center px-2'

                                : 'justify-between px-3'

                        ]">

                        <div :class="[

                            'flex items-center',

                            isCollapsed ? 'justify-center' : ''

                        ]">

                            <!-- Question Mark Icon -->

                            <svg :class="[

                                'h-4 w-4 shrink-0',

                                !isCollapsed ? 'mr-3' : ''

                            ]" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8.228 9c.549-1.165 1.806-2 3.272-2 1.933 0 3.5 1.343 3.5 3 0 1.5-1 2.5-2.5 3.25-.75.375-1.25.875-1.25 1.75M11.25 18h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />

                            </svg>



                            <span v-if="!isCollapsed">

                                Qualification Questions

                            </span>

                        </div>



                        <!-- Dropdown Arrow -->

                        <svg v-if="!isCollapsed" :class="[

                            'h-3.5 w-3.5 shrink-0 transition-transform duration-200',

                            dropdowns.qualificationQuestions ? 'rotate-180' : ''

                        ]" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />

                        </svg>

                    </button>



                    <!-- Qualification Questions Children -->

                    <div v-if="!isCollapsed" v-show="dropdowns.qualificationQuestions" class="mt-1 space-y-1 pl-9 pr-1">

                        <!-- All Questions -->

                        <Link :href="route('admin.qualification-questions.index')" @click="navigate" :class="[

                            'block rounded-lg px-3 py-2 text-xs font-medium transition-colors',

                            isSectionActive('/admin/qualification-questions') &&

                                !isActive('/admin/qualification-questions/create')

                                ? 'bg-amber-500/10 font-semibold text-amber-400'

                                : 'text-neutral-400 hover:bg-[#181818] hover:text-white'

                        ]">

                            All Questions

                        </Link>



                        <!-- Create Question -->

                        <Link :href="route('admin.qualification-questions.create')" @click="navigate" :class="[

                            'block rounded-lg px-3 py-2 text-xs font-medium transition-colors',

                            isActive('/admin/qualification-questions/create')

                                ? 'bg-amber-500/10 font-semibold text-amber-400'

                                : 'text-neutral-400 hover:bg-[#181818] hover:text-white'

                        ]">

                            Create Question

                        </Link>

                    </div>

                </div>

                <!-- ================================================= -->
                <!-- Prizes Dropdown -->
                <!-- ================================================= -->
                <div>
                    <button type="button" @click="toggleDropdown('prizes')" title="Prizes"
                        :aria-expanded="dropdowns.prizes" :class="[
                            'flex w-full cursor-pointer items-center rounded-xl py-2.5 text-xs font-semibold transition-all duration-200',
                            isSectionActive('/admin/competition-prizes')
                                ? 'bg-amber-500/5 text-amber-400'
                                : 'text-neutral-400 hover:bg-[#181818] hover:text-white',
                            isCollapsed ? 'justify-center px-2' : 'justify-between px-3'
                        ]">
                        <div :class="['flex items-center', isCollapsed ? 'justify-center' : '']">
                            <svg :class="['h-4 w-4 shrink-0', !isCollapsed ? 'mr-3' : '']" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v13m0-13C10.343 8 9 6.657 9 5s1.343-3 3-3 3 1.343 3 3-1.343 3-3 3zm0 0H5.5A2.5 2.5 0 003 10.5v1A2.5 2.5 0 005.5 14H12m0-6h6.5a2.5 2.5 0 012.5 2.5v1a2.5 2.5 0 01-2.5 2.5H12m-9 0v5A2.5 2.5 0 005.5 21h13a2.5 2.5 0 002.5-2.5v-5" />
                            </svg>
                            <span v-if="!isCollapsed">Prizes</span>
                        </div>
                        <svg v-if="!isCollapsed" :class="[
                            'h-3.5 w-3.5 shrink-0 transition-transform duration-200',
                            dropdowns.prizes ? 'rotate-180' : ''
                        ]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div v-if="!isCollapsed" v-show="dropdowns.prizes" class="mt-1 space-y-1 pl-9 pr-1">
                        <Link :href="route('admin.competition-prizes.index')" @click="navigate" :class="[
                            'block rounded-lg px-3 py-2 text-xs font-medium transition-colors',
                            isSectionActive('/admin/competition-prizes') && !isActive('/admin/competition-prizes/create')
                                ? 'bg-amber-500/10 font-semibold text-amber-400'
                                : 'text-neutral-400 hover:bg-[#181818] hover:text-white'
                        ]">
                            All Prizes
                        </Link>
                        <Link :href="route('admin.competition-prizes.create')" @click="navigate" :class="[
                            'block rounded-lg px-3 py-2 text-xs font-medium transition-colors',
                            isActive('/admin/competition-prizes/create')
                                ? 'bg-amber-500/10 font-semibold text-amber-400'
                                : 'text-neutral-400 hover:bg-[#181818] hover:text-white'
                        ]">
                            Create Prize
                        </Link>
                    </div>
                </div>

                <!-- Ticket Generation Parent -->
                <div>
                    <button type="button" @click="toggleDropdown('ticketGeneration')" title="Ticket Generation"
                        :aria-expanded="dropdowns.ticketGeneration" :class="[
                            'flex w-full cursor-pointer items-center rounded-xl py-2.5 text-xs font-semibold transition-all duration-200',
                            isSectionActive('/admin/competitions/tickets')
                                ? 'bg-amber-500/5 text-amber-400'
                                : 'text-neutral-400 hover:bg-[#181818] hover:text-white',
                            isCollapsed
                                ? 'justify-center px-2'
                                : 'justify-between px-3'
                        ]">
                        <div :class="[
                            'flex items-center',
                            isCollapsed ? 'justify-center' : ''
                        ]">
                            <!-- Ticket Icon -->
                            <svg :class="[
                                'h-4 w-4 shrink-0',
                                !isCollapsed ? 'mr-3' : ''
                            ]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2 9a3 3 0 013-3h14a3 3 0 013 3v1a2 2 0 000 4v1a3 3 0 01-3 3H5a3 3 0 01-3-3v-1a2 2 0 000-4V9z M9 6v12 M15 6v12" />
                            </svg>

                            <span v-if="!isCollapsed">
                                Ticket Generation
                            </span>
                        </div>

                        <!-- Dropdown Arrow -->
                        <svg v-if="!isCollapsed" :class="[
                            'h-3.5 w-3.5 shrink-0 transition-transform duration-200',
                            dropdowns.ticketGeneration ? 'rotate-180' : ''
                        ]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Ticket Generation Children -->
                    <div v-if="!isCollapsed" v-show="dropdowns.ticketGeneration" class="mt-1 space-y-1 pl-9 pr-1">
                        <!-- All Tickets -->
                        <!-- All Tickets -->
                        <Link :href="route('admin.tickets.index')" @click="navigate" :class="[
                            'block rounded-lg px-3 py-2 text-xs font-medium transition-colors',
                            isActive('/admin/competitions/tickets')
                                ? 'bg-amber-500/10 font-semibold text-amber-400'
                                : 'text-neutral-400 hover:bg-[#181818] hover:text-white'
                        ]">
                            All Tickets
                        </Link>
                    </div>
                </div>



                <!-- ================================================= -->
                <!-- ORDERS & ENTRIES -->
                <!-- ================================================= -->

                <div class="mt-5">
                    <p v-if="!isCollapsed"
                        class="mb-2 px-3 text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                        Orders & Entries
                    </p>



                    <!-- Orders -->

                    <Link href="#" @click.prevent title="Orders" :class="[

                        'mb-1 flex cursor-not-allowed items-center rounded-xl py-2.5 text-xs font-semibold text-neutral-600',

                        isCollapsed ? 'justify-center px-2' : 'px-3'

                    ]">

                        <svg :class="[

                            'h-4 w-4 shrink-0',

                            !isCollapsed ? 'mr-3' : ''

                        ]" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 14l6-6m-5.5 1.5h.01M14.5 14.5h.01M7 4h10l3 4v11H4V8l3-4z" />

                        </svg>



                        <span v-if="!isCollapsed">

                            Orders

                        </span>

                    </Link>





                    <!-- Entries & Tickets -->

                    <Link href="#" @click.prevent title="Entries & Tickets" :class="[

                        'mb-1 flex cursor-not-allowed items-center rounded-xl py-2.5 text-xs font-semibold text-neutral-600',

                        isCollapsed ? 'justify-center px-2' : 'px-3'

                    ]">

                        <svg :class="[

                            'h-4 w-4 shrink-0',

                            !isCollapsed ? 'mr-3' : ''

                        ]" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 5h12M6 9h12M6 13h8m-8 4h8" />

                        </svg>



                        <span v-if="!isCollapsed">

                            Entries & Tickets

                        </span>

                    </Link>



                </div>

                <!-- ================================================= -->
                <!-- CUSTOMERS -->
                <!-- ================================================= -->

                <div class="">
                    <p v-if="!isCollapsed"
                        class="mb-2 px-3 text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                        Customers
                    </p>



                    <Link href="#" @click.prevent title="Customers" :class="[

                        'mb-1 flex cursor-not-allowed items-center rounded-xl py-2.5 text-xs font-semibold text-neutral-600',

                        isCollapsed ? 'justify-center px-2' : 'px-3'

                    ]">

                        <svg :class="[

                            'h-4 w-4 shrink-0',

                            !isCollapsed ? 'mr-3' : ''

                        ]" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zm7-3a4 4 0 110-8 4 4 0 000 8z" />

                        </svg>



                        <span v-if="!isCollapsed">

                            Customers

                        </span>

                    </Link>



                </div>

                <!-- ================================================= -->
                <!-- DRAWS & WINNERS -->
                <!-- ================================================= -->

                <div class="">

                    <p v-if="!isCollapsed"
                        class="mb-2 px-3 text-[10px] font-bold uppercase tracking-wider text-neutral-500">

                        Draws & Winners

                    </p>



                    <!-- Draws -->

                    <Link href="#" @click.prevent title="Draws" :class="[

                        'mb-1 flex cursor-not-allowed items-center rounded-xl py-2.5 text-xs font-semibold text-neutral-600',

                        isCollapsed ? 'justify-center px-2' : 'px-3'

                    ]">

                        <svg :class="[

                            'h-4 w-4 shrink-0',

                            !isCollapsed ? 'mr-3' : ''

                        ]" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5h6M9 3h6a2 2 0 012 2v1h1a2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2h1V5a2 2 0 012-2z" />

                        </svg>



                        <span v-if="!isCollapsed">

                            Draws

                        </span>

                    </Link>





                    <!-- Winners -->

                    <Link href="#" @click.prevent title="Winners" :class="[

                        'mb-1 flex cursor-not-allowed items-center rounded-xl py-2.5 text-xs font-semibold text-neutral-600',

                        isCollapsed ? 'justify-center px-2' : 'px-3'

                    ]">

                        <svg :class="[

                            'h-4 w-4 shrink-0',

                            !isCollapsed ? 'mr-3' : ''

                        ]" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 21h8M12 17v4M7 4h10v4a5 5 0 01-10 0V4zM5 4h14M5 8a3 3 0 01-3-3h3m14 3a3 3 0 003-3h-3" />

                        </svg>



                        <span v-if="!isCollapsed">

                            Winners

                        </span>

                    </Link>



                </div>

                <!-- ================================================= -->
                <!-- CONTENT -->
                <!-- ================================================= -->

                <div class="">

                    <p v-if="!isCollapsed"
                        class="mb-2 px-3 text-[10px] font-bold uppercase tracking-wider text-neutral-500">

                        Content

                    </p>



                    <!-- Pages -->

                    <Link href="#" @click.prevent title="Pages" :class="[

                        'mb-1 flex cursor-not-allowed items-center rounded-xl py-2.5 text-xs font-semibold text-neutral-600',

                        isCollapsed ? 'justify-center px-2' : 'px-3'

                    ]">

                        <svg :class="[

                            'h-4 w-4 shrink-0',

                            !isCollapsed ? 'mr-3' : ''

                        ]" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 4h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2zM8 8h8M8 12h8M8 16h5" />

                        </svg>



                        <span v-if="!isCollapsed">

                            Pages

                        </span>

                    </Link>



                </div>

                <!-- ================================================= -->
                <!-- MARKETING -->
                <!-- ================================================= -->


                <div class="">

                    <p v-if="!isCollapsed"
                        class="mb-2 px-3 text-[10px] font-bold uppercase tracking-wider text-neutral-500">

                        Marketing

                    </p>



                    <!-- Promotions -->

                    <Link href="#" @click.prevent title="Promotions" :class="[

                        'mb-1 flex cursor-not-allowed items-center rounded-xl py-2.5 text-xs font-semibold text-neutral-600',

                        isCollapsed ? 'justify-center px-2' : 'px-3'

                    ]">

                        <svg :class="[

                            'h-4 w-4 shrink-0',

                            !isCollapsed ? 'mr-3' : ''

                        ]" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 12v7a2 2 0 01-2 2H6a2 2 0 01-2-2v-7M3 7h18v5H3V7zm9 0v14M8 7a2 2 0 112-2c0 2-2 2-2 2zm8 0a2 2 0 10-2-2c0 2 2 2 2 2z" />

                        </svg>



                        <span v-if="!isCollapsed">

                            Promotions

                        </span>

                    </Link>



                </div>

                <!-- ================================================= -->
                <!-- REPORTS -->
                <!-- ================================================= -->

                <div class="">

                    <p v-if="!isCollapsed"
                        class="mb-2 px-3 text-[10px] font-bold uppercase tracking-wider text-neutral-500">

                        Reports

                    </p>



                    <!-- Reports -->

                    <Link href="#" @click.prevent title="Reports" :class="[

                        'mb-1 flex cursor-not-allowed items-center rounded-xl py-2.5 text-xs font-semibold text-neutral-600',

                        isCollapsed ? 'justify-center px-2' : 'px-3'

                    ]">

                        <svg :class="[

                            'h-4 w-4 shrink-0',

                            !isCollapsed ? 'mr-3' : ''

                        ]" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 19V5m0 14h16M8 16v-5m4 5V7m4 9v-8" />

                        </svg>



                        <span v-if="!isCollapsed">

                            Reports

                        </span>

                    </Link>



                </div>

                <!-- ================================================= -->
                <!-- SYSTEM -->
                <!-- ================================================= -->

                <div class="">

                    <p v-if="!isCollapsed"
                        class="mb-2 px-3 text-[10px] font-bold uppercase tracking-wider text-neutral-500">

                        System

                    </p>



                    <!-- Settings -->

                    <Link href="#" @click.prevent title="Settings" :class="[

                        'mb-1 flex cursor-not-allowed items-center rounded-xl py-2.5 text-xs font-semibold text-neutral-600',

                        isCollapsed ? 'justify-center px-2' : 'px-3'

                    ]">

                        <svg :class="[

                            'h-4 w-4 shrink-0',

                            !isCollapsed ? 'mr-3' : ''

                        ]" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317a1.724 1.724 0 013.35 0 1.724 1.724 0 002.573 1.066 1.724 1.724 0 012.44 2.44 1.724 1.724 0 001.066 2.573 1.724 1.724 0 010 3.35 1.724 1.724 0 00-1.066 2.573 1.724 1.724 0 01-2.44 2.44 1.724 1.724 0 00-2.573 1.066 1.724 1.724 0 01-3.35 0 1.724 1.724 0 00-2.573-1.066 1.724 1.724 0 01-2.44-2.44 1.724 1.724 0 00-1.066-2.573 1.724 1.724 0 010-3.35A1.724 1.724 0 004.317 7.8a1.724 1.724 0 012.44-2.44 1.724 1.724 0 002.573-1.066z" />

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />

                        </svg>



                        <span v-if="!isCollapsed">

                            Settings

                        </span>

                    </Link>





                    <!-- Audit Logs -->

                    <Link href="#" @click.prevent title="Audit Logs" :class="[

                        'mb-1 flex cursor-not-allowed items-center rounded-xl py-2.5 text-xs font-semibold text-neutral-600',

                        isCollapsed ? 'justify-center px-2' : 'px-3'

                    ]">

                        <svg :class="[

                            'h-4 w-4 shrink-0',

                            !isCollapsed ? 'mr-3' : ''

                        ]" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v11a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 006 0M9 12h6M9 16h4" />

                        </svg>



                        <span v-if="!isCollapsed">

                            Audit Logs

                        </span>

                    </Link>



                </div>
            </nav>

            <!-- ================================================= -->
            <!-- FOOTER -->
            <!-- ================================================= -->

            <div class="shrink-0 border-t border-neutral-800/80 p-3">
                <div :class="[
                    'flex items-center rounded-xl border border-neutral-800/80 bg-[#0d0d0d] transition-all duration-200',
                    isCollapsed
                        ? 'justify-center p-2'
                        : 'justify-between p-3'

                ]">

                    <div v-if="!isCollapsed">

                        <p class="mb-0.5 text-xs font-bold text-white">

                            Prize Guyana

                        </p>



                        <p class="font-mono text-[10px] text-neutral-500">

                            Admin v1.0.0

                        </p>

                    </div>



                    <!-- Logout -->

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