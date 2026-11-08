
<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

const isOpen = ref(false)
const loading = ref(false)
const notifications = ref([])
const unreadCount = ref(0)

const dropdownRef = ref(null)

const loadNotifications = async () => {
    loading.value = true

    try {
        const response = await axios.get(route('notifications.index'))

        notifications.value = response.data.notifications?.data ?? []
        unreadCount.value = Number(response.data.unread_count ?? 0)
    } catch (error) {
        console.error('Failed to load admin notifications:', error)
    } finally {
        loading.value = false
    }
}

const toggleDropdown = async () => {
    isOpen.value = !isOpen.value

    if (isOpen.value) {
        await loadNotifications()
    }
}

const markAsRead = async (notification) => {
    try {
        if (!notification.read_at) {
            await axios.patch(route('notifications.read', notification.id))

            notification.read_at = new Date().toISOString()
            unreadCount.value = Math.max(0, unreadCount.value - 1)
        }

        isOpen.value = false

        if (notification.data?.url) {
            router.visit(notification.data.url)
        }
    } catch (error) {
        console.error('Failed to mark notification as read:', error)
    }
}

const markAllAsRead = async () => {
    try {
        await axios.patch(route('notifications.read-all'))

        notifications.value = notifications.value.map(notification => ({
            ...notification,
            read_at: notification.read_at || new Date().toISOString(),
        }))

        unreadCount.value = 0
    } catch (error) {
        console.error('Failed to mark all notifications as read:', error)
    }
}

const formatDate = (date) => {
    if (!date) return ''

    const parsedDate = new Date(date)

    if (Number.isNaN(parsedDate.getTime())) return ''

    return new Intl.DateTimeFormat('en', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(parsedDate)
}

const handleOutsideClick = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isOpen.value = false
    }
}

const handleEscape = (event) => {
    if (event.key === 'Escape') {
        isOpen.value = false
    }
}

onMounted(() => {
    loadNotifications()

    document.addEventListener('click', handleOutsideClick)
    document.addEventListener('keydown', handleEscape)
})

onBeforeUnmount(() => {
    document.removeEventListener('click', handleOutsideClick)
    document.removeEventListener('keydown', handleEscape)
})
</script>

<template>
    <div ref="dropdownRef" class="relative">

        <!-- Notification Bell -->
        <button
            type="button"
            @click.stop="toggleDropdown"
            :aria-expanded="isOpen"
            aria-label="Notifications"
            class="relative flex h-11 w-11 items-center justify-center rounded-xl border border-neutral-800 bg-[#121212] text-neutral-300 transition-all duration-200 hover:border-amber-500/40 hover:bg-neutral-800 hover:text-amber-400"
        >
            <svg
                class="h-5 w-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.8"
                    d="M18 8a6 6 0 00-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"
                />
            </svg>

            <span
                v-if="unreadCount > 0"
                class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full border-2 border-[#0d0d0d] bg-amber-500 px-1 text-[10px] font-bold text-black"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <!-- Notification Dropdown -->
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="translate-y-2 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-2 opacity-0"
        >
            <div
                v-if="isOpen"
                class="absolute right-0 z-[100] mt-3 w-[340px] max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl border border-neutral-800 bg-[#121212] shadow-2xl sm:w-[390px]"
            >

                <!-- Header -->
                <div class="flex items-center justify-between border-b border-neutral-800 px-4 py-4">
                    <div>
                        <h3 class="text-sm font-bold text-white">
                            Notifications
                        </h3>

                        <p class="mt-1 text-xs text-neutral-500">
                            {{ unreadCount }} unread notifications
                        </p>
                    </div>

                    <button
                        v-if="unreadCount > 0"
                        type="button"
                        @click="markAllAsRead"
                        class="text-xs font-semibold text-amber-400 transition hover:text-amber-300"
                    >
                        Mark all read
                    </button>
                </div>

                <!-- Notification List -->
                <div class="max-h-[420px] overflow-y-auto">

                    <!-- Loading -->
                    <div
                        v-if="loading && !notifications.length"
                        class="px-4 py-10 text-center"
                    >
                        <div class="mx-auto h-6 w-6 animate-spin rounded-full border-2 border-neutral-700 border-t-amber-500"></div>

                        <p class="mt-3 text-xs text-neutral-500">
                            Loading notifications...
                        </p>
                    </div>

                    <!-- Empty State -->
                    <div
                        v-else-if="!notifications.length"
                        class="px-4 py-10 text-center"
                    >
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-neutral-800 text-neutral-500">
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M18 8a6 6 0 00-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"
                                />
                            </svg>
                        </div>

                        <p class="mt-3 text-sm font-semibold text-neutral-300">
                            No notifications yet
                        </p>

                        <p class="mt-1 text-xs text-neutral-500">
                            Admin updates will appear here.
                        </p>
                    </div>

                    <!-- Notification Items -->
                    <button
                        v-for="notification in notifications"
                        :key="notification.id"
                        type="button"
                        @click="markAsRead(notification)"
                        class="flex w-full gap-3 border-b border-neutral-800/70 px-4 py-4 text-left transition hover:bg-neutral-800/40"
                        :class="!notification.read_at ? 'bg-amber-500/[0.04]' : ''"
                    >
                        <!-- Read Indicator -->
                        <span
                            class="mt-1.5 h-2 w-2 shrink-0 rounded-full"
                            :class="!notification.read_at ? 'bg-amber-400' : 'bg-neutral-700'"
                        ></span>

                        <!-- Content -->
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-semibold text-white">
                                {{ notification.data?.title || 'Notification' }}
                            </span>

                            <span class="mt-1 block text-xs leading-relaxed text-neutral-400">
                                {{ notification.data?.message || '' }}
                            </span>

                            <span class="mt-2 block text-[10px] text-neutral-600">
                                {{ formatDate(notification.created_at) }}
                            </span>
                        </span>

                        <!-- Navigation Indicator -->
                        <svg
                            v-if="notification.data?.url"
                            class="mt-1 h-4 w-4 shrink-0 text-neutral-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M9 5l7 7-7 7"
                            />
                        </svg>
                    </button>
                </div>

                <!-- Footer -->
                <div class="border-t border-neutral-800 bg-[#0d0d0d] px-4 py-3">
                    <p class="text-center text-[10px] text-neutral-600">
                        PrizeGuyana Admin Notifications
                    </p>
                </div>
            </div>
        </Transition>
    </div>
</template>
