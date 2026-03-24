<template>
    <OlxLayout>
        <div class="max-w-4xl mx-auto px-8 sm:px-4 py-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ unreadCount }} unread {{ unreadCount === 1 ? 'notification' : 'notifications' }}
                    </p>
                </div>
            </div>
            <Link :href="route('orders')" class="block">
            <div class="space-y-1 py-2">
                <div :class="[
                    'rounded-lg shadow-sm border p-2 transition hover:shadow-md bg-brand-teal/10']">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 ">
                            <div class="flex items-center gap-2 mb-1">
                                <p class="text-sm text-gray-600 mt-1">
                                    Manage Your Orders
                                </p>
                            </div>
                        </div>
                        <div class="py-2">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            </Link>

            <!-- Notifications List -->
            <div v-if="notifications.data.length > 0" class="space-y-1">
                <div v-for="notification in notifications.data" :key="notification.id" :class="[
                    'bg-white rounded-lg shadow-sm border p-2 transition hover:shadow-md',
                    notification.read_at ? 'border-gray-100' : 'border-l-4 border-l-brand-teal border-gray-100'
                ]">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <p class="text-sm text-gray-600 mt-1">
                                    {{ getNotificationMessage(notification.data) }}
                                </p>
                            </div>


                            <div class="flex items-center gap-4 mt-2">
                                <span class="text-xs text-gray-400">
                                    {{ formatDate(notification.created_at) }}
                                </span>

                                <button v-if="!notification.read_at" @click="markAsRead(notification.id)"
                                    class="text-xs text-brand-teal hover:text-brand-teal/80">
                                    Mark as read
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12 bg-white rounded-xl shadow-sm border border-gray-100">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-1">No notifications</h3>
                <p class="text-sm text-gray-600">You're all caught up! Check back later for new notifications.</p>
            </div>

            <!-- Pagination -->
            <div v-if="notifications.last_page > 1" class="mt-6">
                <div class="flex justify-center gap-2">
                    <button v-for="page in pages" :key="page" @click="goToPage(page)" :class="[
                        'px-3 py-1 rounded-lg text-sm transition',
                        notifications.current_page === page
                            ? 'bg-brand-teal text-white'
                            : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200'
                    ]">
                        {{ page }}
                    </button>
                </div>
            </div>
        </div>
    </OlxLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import OlxLayout from '@/layouts/OlxLayout.vue'

interface Props {
    notifications: {
        data: any[]
        current_page: number
        last_page: number
        total: number
    }
    unreadCount: number
}

const props = defineProps<Props>()

// Format date
const formatDate = (dateString: string) => {
    const date = new Date(dateString)
    const now = new Date()
    const diffInHours = Math.floor((now.getTime() - date.getTime()) / (1000 * 60 * 60))

    if (diffInHours < 1) {
        const diffInMinutes = Math.floor((now.getTime() - date.getTime()) / (1000 * 60))
        return `${diffInMinutes} minute${diffInMinutes !== 1 ? 's' : ''} ago`
    } else if (diffInHours < 24) {
        return `${diffInHours} hour${diffInHours !== 1 ? 's' : ''} ago`
    } else {
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
    }
}

// Get notification title based on type
const getNotificationTitle = (data: any) => {
    switch (data.type) {
        case 'new_ad':
            return 'New Ad Posted'
        case 'ad_sold':
            return 'Ad Sold'
        case 'message_received':
            return 'New Message'
        case 'favorite_added':
            return 'Added to Favorites'
        default:
            return data.title || 'Notification'
    }
}

// Get notification message
const getNotificationMessage = (data: any) => {
    return data.message || 'You have a new notification'
}

// Get icon based on notification type
const getIconPath = (type: string) => {
    switch (type) {
        case 'new_ad':
            return 'M12 4v16m8-8H4'
        case 'ad_sold':
            return 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
        case 'message_received':
            return 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'
        default:
            return 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'
    }
}

const getIconBgColor = (type: string) => {
    switch (type) {
        case 'new_ad':
            return 'bg-blue-100'
        case 'ad_sold':
            return 'bg-green-100'
        case 'message_received':
            return 'bg-purple-100'
        default:
            return 'bg-gray-100'
    }
}

const getIconColor = (type: string) => {
    switch (type) {
        case 'new_ad':
            return 'text-blue-600'
        case 'ad_sold':
            return 'text-green-600'
        case 'message_received':
            return 'text-purple-600'
        default:
            return 'text-gray-600'
    }
}

// Pagination
const pages = computed(() => {
    const total = props.notifications.last_page
    const current = props.notifications.current_page
    const delta = 2
    const range = []

    for (let i = Math.max(2, current - delta); i <= Math.min(total - 1, current + delta); i++) {
        range.push(i)
    }

    if (current - delta > 2) {
        range.unshift('...')
    }
    if (current + delta < total - 1) {
        range.push('...')
    }

    range.unshift(1)
    if (total !== 1) {
        range.push(total)
    }

    return range
})

const goToPage = (page: number) => {
    if (page === '...') return
    router.get(route('notifications.index'), { page }, { preserveState: true })
}

// Actions
const markAsRead = (id: string) => {
    router.post(route('notifications.markAsRead', id), {}, {
        preserveScroll: true,
        preserveState: true
    })
}

const markAllAsRead = () => {
    if (confirm('Mark all notifications as read?')) {
        router.post(route('notifications.mark-all-read'), {}, {
            preserveScroll: true,
            preserveState: true
        })
    }
}

</script>

<style scoped>
/* No additional styles needed - using Tailwind classes */
</style>