<script setup>
import { ref, computed, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const props = defineProps({
    isCollapsed: {
        type: Boolean,
        default: false,
    },
});
const emit = defineEmits(['toggle-collapse']);

const isMobileSidebarOpen = ref(false);

// Track open state for dropdown menus
const openMenus = ref({
    orders: false,
    account: false,
    support: false,
});

// Toggle dropdown
const toggleMenu = (key) => {
    openMenus.value[key] = !openMenus.value[key];
};

// Close mobile sidebar
const closeMobileSidebar = () => {
    isMobileSidebarOpen.value = false;
};

// User
const pageProps = page.props;

const user = computed(() => pageProps.auth?.user || {
    name: 'John Doe',
    email: 'user@example.com'
});

// Navigation
const navigation = [
    {
        name: 'Dashboard',
        href: '/user/dashboard',
        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'
    },

    {
        key: 'orders',
        name: 'Orders & Activity',
        icon: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
        children: [
            {
                name: 'All Orders',
                href: '/user/orders'
            },
            {
                name: 'Order History',
                href: '/user/orders/history'
            },
            {
                name: 'Transactions & Invoices',
                href: '/user/transactions'
            },
        ]
    },

    {
        name: 'My Entries',
        href: '/user/entries',
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h6l5 5v11a2 2 0 01-2 2z'
    },

    {
        name: 'My Tickets',
        href: '/user/tickets',
        icon: 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 012-2h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5z'
    },

    // {
    //     name: 'Saved & Favorites',
    //     href: '/user/favorites',
    //     icon: 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'
    // },

    // {
    //     name: 'Notifications',
    //     href: '/user/notifications',
    //     icon: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'
    // },

    {
        key: 'account',
        name: 'Profile',
        icon: 'M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm0-6a2 2 0 0 1 2 2v.35a7.98 7.98 0 0 1 2.1.87l.25-.25a2 2 0 1 1 2.83 2.83l-.25.25c.39.65.69 1.36.87 2.1H20a2 2 0 1 1 0 4h-.35a7.98 7.98 0 0 1-.87 2.1l.25.25a2 2 0 1 1-2.83 2.83l-.25-.25a7.98 7.98 0 0 1-2.1.87V20a2 2 0 1 1-4 0v-.35a7.98 7.98 0 0 1-2.1-.87l-.25.25a2 2 0 1 1-2.83-2.83l.25-.25a7.98 7.98 0 0 1-.87-2.1H3a2 2 0 1 1 0-4h.35a7.98 7.98 0 0 1 .87-2.1l-.25-.25A2 2 0 1 1 6.8 4.67l.25.25a7.98 7.98 0 0 1 2.1-.87V3a2 2 0 0 1 2-1z',
        href: '/user/profile',
    },
    {
        key: 'security',
        name: 'Change Password',
        href: '/user/password',
        icon: 'M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm0-6a2 2 0 0 1 2 2v.35a7.98 7.98 0 0 1 2.1.87l.25-.25a2 2 0 1 1 2.83 2.83l-.25.25c.39.65.69 1.36.87 2.1H20a2 2 0 1 1 0 4h-.35a7.98 7.98 0 0 1-.87 2.1l.25.25a2 2 0 1 1-2.83 2.83l-.25-.25a7.98 7.98 0 0 1-2.1.87V20a2 2 0 1 1-4 0v-.35a7.98 7.98 0 0 1-2.1-.87l-.25.25a2 2 0 1 1-2.83-2.83l.25-.25a7.98 7.98 0 0 1-.87-2.1H3a2 2 0 1 1 0-4h.35a7.98 7.98 0 0 1 .87-2.1l-.25-.25A2 2 0 1 1 6.8 4.67l.25.25a7.98 7.98 0 0 1 2.1-.87V3a2 2 0 0 1 2-2z',
    },

    // {
    //     key: 'support',
    //     name: 'Help & Support',
    //     icon: 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z',
    //     children: [
    //         {
    //             name: 'Support Tickets',
    //             href: '/user/support/tickets'
    //         },
    //         {
    //             name: 'FAQs & Guides',
    //             href: '/user/support/faq'
    //         },
    //         {
    //             name: 'Contact Us',
    //             href: '/user/support/contact'
    //         }
    //     ]
    // }
];

// Exact URL active check
const isUrlActive = (url) => {
    return page.url === url;
};

// Check if child is active
const isChildActive = (children) => {
    return children?.some(child => isUrlActive(child.href));
};

watch(() => page.url, () => {
    navigation.forEach((item) => {
        if (item.children && isChildActive(item.children)) {
            openMenus.value[item.key] = true;
        }
    });
}, { immediate: true });
</script>


<template>

    <!-- Mobile Floating Sidebar Button -->
    <button @click="isMobileSidebarOpen = true"
        class="lg:hidden fixed top-4 left-4 z-40 p-2.5 rounded-xl bg-[#121212] border border-neutral-800 text-neutral-300 shadow-xl hover:text-white hover:border-neutral-700 transition-all"
        aria-label="Open Navigation Sidebar" :aria-expanded="isMobileSidebarOpen" aria-controls="user-sidebar">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>


    <!-- Mobile Backdrop -->
    <div v-if="isMobileSidebarOpen" @click="closeMobileSidebar"
        class="fixed inset-0 z-40 bg-black/80 backdrop-blur-sm lg:hidden"></div>


    <!-- Sidebar -->
    <aside id="user-sidebar"
        class="fixed top-0 bottom-0 left-0 z-50 w-64 bg-[#121212] border-r border-neutral-800/80 flex flex-col transition-all duration-300 lg:translate-x-0"
        :class="[
            isMobileSidebarOpen ? 'translate-x-0' : '-translate-x-full',
            isCollapsed ? 'lg:w-20' : 'lg:w-64'
        ]">

        <!-- Top Accent -->
        <div class="h-1 bg-gradient-to-r from-transparent via-amber-500 to-transparent opacity-60 shrink-0"></div>


        <!-- Brand -->
        <div class="p-6 border-b border-neutral-800/80 flex items-center justify-between shrink-0"
            :class="isCollapsed ? 'lg:px-3' : ''">

            <Link href="/user/dashboard" @click="closeMobileSidebar" :title="isCollapsed ? 'User Portal' : undefined"
                class="flex items-center space-x-3">

                <div
                    class="w-9 h-9 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center font-black text-amber-400 text-lg">
                    U
                </div>

                <div :class="isCollapsed ? 'lg:hidden' : ''">
                    <span class="text-sm font-extrabold tracking-tight text-white block">
                        User Portal
                    </span>

                    <span class="text-[10px] text-neutral-500 uppercase tracking-wider font-semibold">
                        User Dashboard
                    </span>
                </div>

            </Link>


            <!-- Mobile Close -->
            <button @click="closeMobileSidebar"
                class="lg:hidden p-1.5 rounded-lg text-neutral-400 hover:text-white hover:bg-neutral-800 transition-colors"
                aria-label="Close Navigation Sidebar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <button type="button" @click="emit('toggle-collapse')"
                :title="isCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                :aria-label="isCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                class="hidden lg:block rounded-lg p-1.5 text-neutral-500 transition-colors hover:bg-neutral-800 hover:text-white">
                <svg v-if="!isCollapsed" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>

        </div>


        <!-- Scrollable Navigation -->
        <nav class="flex-1 min-h-0 px-4 py-6 overflow-y-auto overscroll-contain sidebar-scroll"
            :class="isCollapsed ? 'lg:px-2' : ''">

            <div
                :class="['text-[10px] font-bold text-neutral-500 uppercase tracking-wider px-3 mb-2', isCollapsed ? 'lg:hidden' : '']">
                Main Menu
            </div>


            <div class="space-y-1.5">

                <template v-for="item in navigation" :key="item.name">

                    <!-- Normal Link -->
                    <Link v-if="!item.children" :href="item.href" @click="closeMobileSidebar"
                        :title="isCollapsed ? item.name : undefined"
                        class="flex items-center space-x-3 px-3.5 py-3 rounded-xl text-xs font-semibold transition-all duration-200"
                        :class="[
                            isCollapsed ? 'lg:justify-center lg:px-2' : '',
                            isUrlActive(item.href)
                                ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20 shadow-lg shadow-amber-500/5'
                                : 'text-neutral-400 hover:text-white hover:bg-[#181818]'
                        ]">

                        <svg class="w-4 h-4 shrink-0 m-0 lg:me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                        </svg>

                        <span :class="isCollapsed ? 'lg:hidden' : ''">{{ item.name }}</span>

                    </Link>


                    <!-- Dropdown -->
                    <div v-else class="space-y-1">

                        <!-- Dropdown Header -->
                        <button type="button" @click="toggleMenu(item.key)" :title="isCollapsed ? item.name : undefined"
                            class="w-full flex items-center justify-between px-3.5 py-3 rounded-xl text-xs font-semibold transition-all duration-200"
                            :class="[
                                isCollapsed ? 'lg:justify-center lg:px-2' : '',
                                isChildActive(item.children)
                                    ? 'text-amber-400 bg-amber-500/5'
                                    : 'text-neutral-400 hover:text-white hover:bg-[#181818]'
                            ]">

                            <div class="flex items-center space-x-3" :class="isCollapsed ? 'lg:justify-center' : ''">

                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        :d="item.icon" />
                                </svg>

                                <span :class="isCollapsed ? 'lg:hidden' : ''">{{ item.name }}</span>

                            </div>


                            <!-- Chevron -->
                            <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="[
                                isCollapsed ? 'lg:hidden' : '',
                                openMenus[item.key]
                                    ? 'rotate-180 text-amber-400'
                                    : 'text-neutral-500'
                            ]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>

                        </button>


                        <!-- Dropdown Children -->
                        <div :class="isCollapsed ? 'lg:hidden!' : ''" v-show="openMenus[item.key]"
                            class="ml-5 pl-4 pr-1 py-1 space-y-1 border-l border-neutral-800">

                            <Link v-for="child in item.children" :key="child.name" :href="child.href"
                                @click="closeMobileSidebar"
                                class="relative block px-3 py-2.5 rounded-lg text-xs font-medium transition-all duration-200"
                                :class="[
                                    isUrlActive(child.href)
                                        ? 'text-amber-400 bg-amber-500/10 font-semibold'
                                        : 'text-neutral-500 hover:text-white hover:bg-[#181818]'
                                ]">

                                <span v-if="isUrlActive(child.href)"
                                    class="absolute left-0 top-1/2 -translate-x-[18px] -translate-y-1/2 w-1.5 h-1.5 rounded-full bg-amber-400"></span>

                                {{ child.name }}

                            </Link>

                        </div>

                    </div>

                </template>

            </div>

        </nav>


        <!-- User Footer -->
        <div class="p-4 border-t border-neutral-800/80 bg-[#0d0d0d] shrink-0" :class="isCollapsed ? 'lg:p-2' : ''">

            <div class="flex items-center justify-between" :class="isCollapsed ? 'lg:flex-col lg:gap-2' : ''">

                <div class="flex items-center space-x-3 overflow-hidden"
                    :class="isCollapsed ? 'lg:justify-center' : ''">

                    <!-- Avatar -->
                    <div
                        class="w-8 h-8 rounded-full bg-neutral-800 border border-neutral-700 flex items-center justify-center font-bold text-xs text-amber-400 shrink-0">
                        {{ user.name ? user.name.charAt(0).toUpperCase() : 'U' }}
                    </div>


                    <!-- User Information -->
                    <div class="truncate" :class="isCollapsed ? 'lg:hidden' : ''">

                        <p class="text-xs font-bold text-white truncate">
                            {{ user.name }}
                        </p>

                        <p class="text-[10px] text-neutral-500 truncate">
                            {{ user.email }}
                        </p>

                    </div>

                </div>


                <!-- Logout -->
                <Link href="/logout" method="post" as="button"
                    class="cursor-pointer p-2 rounded-lg text-neutral-400 hover:text-rose-400 hover:bg-neutral-800 transition-colors shrink-0"
                    title="Sign Out" aria-label="Sign out">

                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>

                </Link>

            </div>

        </div>

    </aside>

</template>


<style scoped>
/* Professional sidebar scrollbar */
.sidebar-scroll {
    scrollbar-width: thin;
    scrollbar-color: #2a2a2a transparent;
}

.sidebar-scroll::-webkit-scrollbar {
    width: 5px;
}

.sidebar-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar-scroll::-webkit-scrollbar-thumb {
    background: #2a2a2a;
    border-radius: 999px;
}

.sidebar-scroll::-webkit-scrollbar-thumb:hover {
    background: #3a3a3a;
}
</style>