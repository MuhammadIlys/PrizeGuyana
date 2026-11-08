<script setup>
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

const isOpen = ref(false)
const loading = ref(false)
const notifications = ref([])
const unreadCount = ref(0)

const loadNotifications = async () => {
    loading.value = true

    try {
        const response = await axios.get(route('notifications.index'))

        notifications.value = response.data.notifications.data
        unreadCount.value = response.data.unread_count
    } catch (error) {
        console.error('Failed to load notifications:', error)
    } finally {
        loading.value = false
    }
}

const toggleDropdown = () => {
    isOpen.value = !isOpen.value

    if (isOpen.value) {
        loadNotifications()
    }
}

const markAsRead = async (notification) => {
    try {
        if (!notification.read_at) {
            await axios.patch(
                route('notifications.read', notification.id)
            )

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

        notifications.value.forEach(notification => {
            notification.read_at = notification.read_at || new Date().toISOString()
        })

        unreadCount.value = 0
    } catch (error) {
        console.error('Failed to mark all notifications as read:', error)
    }
}

onMounted(loadNotifications)
</script>

<template>
    <div class="relative flex justify-end">
        <!-- Notification Bell -->
        <button @click="toggleDropdown"
            class="relative flex h-11 w-11 items-center justify-center rounded-xl border border-neutral-800 bg-[#151515] text-neutral-300 transition hover:border-amber-500/50 hover:text-amber-400"
            aria-label="Notifications">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="1.7">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 00-5-5.9V4a1 1 0 00-2 0v1.1A6 6 0 006 11v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 01-6 0" />
            </svg>

            <span v-if="unreadCount > 0"
                class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-amber-500 px-1 text-[10px] font-bold text-black">
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <!-- Dropdown -->
        <div v-if="isOpen"
            class="absolute right-0 top-14 z-50 w-[340px] max-w-[calc(100vw-2rem)] overflow-hidden rounded-xl border border-neutral-800 bg-[#151515] shadow-2xl">
            <div class="flex items-center justify-between border-b border-neutral-800 px-4 py-3">
                <div>
                    <h3 class="text-sm font-semibold text-white">
                        Notifications
                    </h3>
                    <p class="mt-0.5 text-xs text-neutral-500">
                        {{ unreadCount }} unread
                    </p>
                </div>

                <button v-if="unreadCount > 0" @click="markAllAsRead"
                    class="text-xs font-medium text-amber-400 hover:text-amber-300">
                    Mark all as read
                </button>
            </div>

            <div class="max-h-[380px] overflow-y-auto">
                <div v-if="loading" class="px-4 py-8 text-center text-sm text-neutral-500">
                    Loading notifications...
                </div>

                <div v-else-if="notifications.length === 0" class="px-4 py-10 text-center">
                    <p class="text-sm text-neutral-400">
                        No notifications yet
                    </p>
                    <p class="mt-1 text-xs text-neutral-600">
                        We'll notify you when something happens.
                    </p>
                </div>

                <button v-for="notification in notifications" :key="notification.id" @click="markAsRead(notification)"
                    class="flex w-full gap-3 border-b border-neutral-800/70 px-4 py-4 text-left transition hover:bg-neutral-800/50"
                    :class="!notification.read_at ? 'bg-amber-500/[0.04]' : ''">
                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full"
                        :class="!notification.read_at ? 'bg-amber-400' : 'bg-transparent'"></span>

                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-neutral-100">
                            {{ notification.data.title }}
                        </p>

                        <p class="mt-1 text-xs leading-relaxed text-neutral-400">
                            {{ notification.data.message }}
                        </p>

                        <p class="mt-2 text-[10px] text-neutral-600">
                            {{ new Date(notification.created_at).toLocaleString() }}
                        </p>
                    </div>
                </button>
            </div>
        </div>
    </div>
</template>
