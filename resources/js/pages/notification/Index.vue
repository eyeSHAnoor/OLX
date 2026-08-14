<template>
    <OlxLayout :hide-search-bar="true">
        <div class="min-h-screen" :class="theme.bg">
            <div class="max-w-full mx-auto px-4 py-6 md:py-8">
                <!-- Header -->
                <div class="mb-4 overflow-hidden">
                    <div class="md:px-5 px-2 py-4 border-b" :class="theme.border">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-2xl font-bold" :class="theme.text">Notifications</h1>
                                <p class="text-sm mt-1 flex items-center gap-2" :class="theme.textMuted">
                                    <span class="inline-block size-2 bg-blue-500 rounded-full"></span>
                                    {{ unreadCount }} unread {{ unreadCount === 1 ? 'notification' : 'notifications' }}
                                </p>
                            </div>

                            <button v-if="unreadCount > 0" @click="markAllAsRead"
                                class="px-4 py-2 text-sm font-medium rounded-xl transition-colors"
                                :class="[theme.textAccent, theme.hover]">
                                Mark all as read
                            </button>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <div class="flex border-b" :class="[theme.border, theme.card]">
                        <button @click="activeTab = 'all'" :class="[
                            'flex-1 px-4 py-3 text-sm font-medium transition-all relative',
                            activeTab === 'all'
                                ? theme.textAccent
                                : `${theme.textMuted} hover:${theme.text}`
                        ]">
                            All
                            <div v-if="activeTab === 'all'"
                                class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-600 rounded-full"></div>
                        </button>
                        <button @click="activeTab = 'unread'" :class="[
                            'flex-1 px-4 py-3 text-sm font-medium transition-all relative',
                            activeTab === 'unread'
                                ? theme.textAccent
                                : `${theme.textMuted} hover:${theme.text}`
                        ]">
                            Unread
                            <span v-if="unreadCount > 0"
                                class="ml-2 px-2 py-0.5 text-xs bg-red-500 text-white rounded-full">
                                {{ unreadCount }}
                            </span>
                            <div v-if="activeTab === 'unread'"
                                class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-600 rounded-full"></div>
                        </button>
                    </div>
                </div>

                <!-- Orders Card -->
                <Link :href="route('orders')" class="block mb-4">
                    <div class="rounded-2xl shadow-sm border p-4 transition-all hover:shadow-md hover:scale-[1.01]"
                        :class="[theme.card, theme.border]">
                        <div class="flex items-center gap-4">
                            <div class="size-12 rounded-full flex items-center justify-center flex-shrink-0"
                                :class="theme.bgLight">
                                <svg class="w-6 h-6" :class="theme.icon" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold" :class="theme.text">Manage Your Orders</h3>
                                <p class="text-sm" :class="theme.textMuted">View and track all your orders in one place
                                </p>
                            </div>
                            <svg class="w-5 h-5" :class="theme.textMuted" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </Link>

                <!-- Notifications List -->
                <div v-if="filteredNotifications.length > 0" class="space-y-2">
                    <!-- Date Groups -->
                    <template v-for="(group, dateKey) in groupedNotifications" :key="dateKey">
                        <div class="text-xs font-semibold px-4 py-2 rounded-lg mt-4 first:mt-0"
                            :class="[theme.textMuted, theme.bgLight]">
                            {{ dateKey }}
                        </div>

                        <div v-for="notification in group" :key="notification.id"
                            @click="openNotification(notification)"
                            class="group rounded-xl shadow-sm border transition-all hover:shadow-md cursor-pointer"
                            :class="[
                                notification.read_at ? theme.border : 'border-l-4 border-l-blue-500 ' + theme.border,
                                theme.card,
                                theme.hover
                            ]">
                            <div class="md:p-4 py-4 px-2">
                                <div class="flex gap-3">
                                    <!-- Icon -->
                                    <div :class="[
                                        'size-12 rounded-full flex-shrink-0 flex items-center justify-center',
                                        getIconBgColor(notification.data.type)
                                    ]">
                                        <Icon :icon="getIconName(notification.data.type)"
                                            :class="['size-6', getIconColor(notification.data.type)]" />
                                    </div>

                                    <!-- Content -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="flex-1">
                                                <p class="text-sm font-medium" :class="theme.text">
                                                    {{ getNotificationTitle(notification.data) }}
                                                </p>
                                                <p class="text-sm mt-1 leading-relaxed" :class="theme.textMuted">
                                                    {{ getNotificationMessage(notification.data) }}
                                                </p>

                                                <!-- Metadata -->
                                                <div class="flex items-center gap-3 mt-2">
                                                    <span class="text-xs flex items-center gap-1"
                                                        :class="theme.textMuted">
                                                        <Icon icon="lucide:clock" class="size-3" />
                                                        {{ formatDate(notification.created_at) }}
                                                    </span>

                                                    <span v-if="notification.data.metadata?.type"
                                                        class="text-xs px-2 py-0.5 rounded-full"
                                                        :class="[theme.bgLight, theme.textMuted]">
                                                        {{ notification.data.metadata.type }}
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Unread dot & actions -->
                                            <div class="flex flex-col items-end gap-2">
                                                <div v-if="!notification.read_at"
                                                    class="size-2 bg-blue-500 rounded-full animate-pulse"></div>

                                                <button v-if="!notification.read_at"
                                                    @click.stop="markAsRead(notification.id)"
                                                    class="opacity-0 group-hover:opacity-100 text-xs transition-all"
                                                    :class="[theme.textMuted, `hover:${theme.textAccent}`]">
                                                    Mark as read
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Empty State -->
                <div v-else class="rounded-2xl shadow-sm border py-12 px-4 text-center"
                    :class="[theme.card, theme.border]">
                    <div class="size-20 rounded-full flex items-center justify-center mx-auto mb-4"
                        :class="theme.bgLight">
                        <Icon icon="lucide:bell-off" class="size-10" :class="theme.textMuted" />
                    </div>
                    <h3 class="text-lg font-semibold mb-1" :class="theme.text">
                        {{ activeTab === 'unread' ? 'No unread notifications' : 'All caught up!' }}
                    </h3>
                    <p class="text-sm max-w-sm mx-auto" :class="theme.textMuted">
                        {{ activeTab === 'unread'
                            ? 'You have no unread notifications. Check back later for updates.'
                            : 'You have no notifications at the moment. We\'ll notify you when something happens.'
                        }}
                    </p>
                </div>

                <!-- Pagination -->
                <div v-if="notifications.last_page > 1" class="mt-6">
                    <div class="flex justify-center gap-2">
                        <button v-for="page in pages" :key="page" @click="goToPage(page)" :disabled="page === '...'"
                            :class="[
                                'min-w-[36px] h-9 rounded-lg text-sm font-medium transition-all',
                                notifications.current_page === page
                                    ? 'bg-blue-600 text-white shadow-sm'
                                    : page === '...'
                                        ? 'bg-transparent cursor-default ' + theme.textMuted
                                        : `${theme.card} ${theme.text} ${theme.hover} ${theme.border}`
                            ]">
                            {{ page }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </OlxLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { router, Link, usePage } from '@inertiajs/vue3'
import OlxLayout from '@/layouts/OlxLayout.vue'
import { Icon } from '@iconify/vue'
import { useTheme } from '@/composables/useTheme'

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
const { theme } = useTheme()
const activeTab = ref<'all' | 'unread'>('all')

// Filter notifications based on active tab
const filteredNotifications = computed(() => {
    if (activeTab.value === 'all') {
        return props.notifications.data
    }
    return props.notifications.data.filter(n => !n.read_at)
})

// Group notifications by date
const groupedNotifications = computed(() => {
    const groups: Record<string, any[]> = {}

    filteredNotifications.value.forEach(notification => {
        const dateKey = getDateGroup(notification.created_at)
        if (!groups[dateKey]) {
            groups[dateKey] = []
        }
        groups[dateKey].push(notification)
    })

    return groups
})

// Get date group label
const getDateGroup = (dateString: string) => {
    const date = new Date(dateString)
    const now = new Date()
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate())
    const yesterday = new Date(today)
    yesterday.setDate(yesterday.getDate() - 1)
    const thisWeek = new Date(today)
    thisWeek.setDate(thisWeek.getDate() - 7)

    if (date >= today) {
        return 'Today'
    } else if (date >= yesterday) {
        return 'Yesterday'
    } else if (date >= thisWeek) {
        return 'This Week'
    } else {
        return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
    }
}

// Format date
const formatDate = (dateString: string) => {
    const date = new Date(dateString)
    const now = new Date()
    const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000)
    const diffInMinutes = Math.floor(diffInSeconds / 60)
    const diffInHours = Math.floor(diffInMinutes / 60)
    const diffInDays = Math.floor(diffInHours / 24)

    if (diffInSeconds < 60) {
        return 'Just now'
    } else if (diffInMinutes < 60) {
        return `${diffInMinutes}m ago`
    } else if (diffInHours < 24) {
        return `${diffInHours}h ago`
    } else if (diffInDays === 1) {
        return 'Yesterday'
    } else if (diffInDays < 7) {
        return `${diffInDays}d ago`
    } else {
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
    }
}

// Get notification title based on type
const getNotificationTitle = (data: any) => {
    const titles: Record<string, string> = {
        'new_ad': 'New Ad Posted',
        'ad_sold': 'Ad Sold',
        'message_received': 'New Message',
        'favorite_added': 'Added to Favorites',
        'order_placed': 'New Order',
        'order_shipped': 'Order Shipped',
        'order_delivered': 'Order Delivered',
        'price_drop': 'Price Drop Alert',
        'review_received': 'New Review'
    }
    return titles[data.type] || data.title || 'Notification'
}

// Get notification message
const getNotificationMessage = (data: any) => {
    if (data.message) return data.message

    const messages: Record<string, string> = {
        'new_ad': `A new ad "${data.ad_title || 'item'}" has been posted in your area.`,
        'ad_sold': `Your ad "${data.ad_title || 'item'}" has been sold!`,
        'message_received': `You have a new message from ${data.sender_name || 'someone'}.`,
        'favorite_added': `${data.user_name || 'Someone'} added your ad to favorites.`,
        'order_placed': `Order #${data.order_id} has been placed successfully.`,
        'order_shipped': `Your order #${data.order_id} has been shipped.`,
        'order_delivered': `Your order #${data.order_id} has been delivered.`,
        'price_drop': `Price dropped on "${data.ad_title || 'item'}" by ${data.price_drop || ''}`,
        'review_received': `${data.reviewer_name || 'Someone'} left a review on your ad.`
    }
    return messages[data.type] || 'You have a new notification'
}

// Get icon name
const getIconName = (type: string) => {
    const icons: Record<string, string> = {
        'new_ad': 'lucide:megaphone',
        'ad_sold': 'lucide:shopping-bag',
        'message_received': 'lucide:message-circle',
        'favorite_added': 'lucide:heart',
        'order_placed': 'lucide:shopping-cart',
        'order_shipped': 'lucide:truck',
        'order_delivered': 'lucide:check-circle',
        'price_drop': 'lucide:trending-down',
        'review_received': 'lucide:star'
    }
    return icons[type] || 'lucide:bell'
}

// Get icon background color
const getIconBgColor = (type: string) => {
    const colors: Record<string, string> = {
        'new_ad': 'bg-blue-100',
        'ad_sold': 'bg-green-100',
        'message_received': 'bg-purple-100',
        'favorite_added': 'bg-pink-100',
        'order_placed': 'bg-orange-100',
        'order_shipped': 'bg-cyan-100',
        'order_delivered': 'bg-emerald-100',
        'price_drop': 'bg-red-100',
        'review_received': 'bg-yellow-100'
    }
    return colors[type] || 'bg-gray-100'
}

// Get icon color
const getIconColor = (type: string) => {
    const colors: Record<string, string> = {
        'new_ad': 'text-blue-600',
        'ad_sold': 'text-green-600',
        'message_received': 'text-purple-600',
        'favorite_added': 'text-pink-600',
        'order_placed': 'text-orange-600',
        'order_shipped': 'text-cyan-600',
        'order_delivered': 'text-emerald-600',
        'price_drop': 'text-red-600',
        'review_received': 'text-yellow-600'
    }
    return colors[type] || 'text-gray-600'
}

// Pagination
const pages = computed(() => {
    const total = props.notifications.last_page
    const current = props.notifications.current_page
    const delta = 2
    const range: (number | string)[] = []

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

const goToPage = (page: number | string) => {
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
    router.post(route('notifications.markAllAsRead'), {}, {
        preserveScroll: true,
        preserveState: true
    })
}

onMounted(() => {
    if (props.unreadCount > 0) {
        markAllAsRead()
    }
})

const openNotification = (notification: any) => {
    // Mark as read if not already
    if (!notification.read_at) {
        markAsRead(notification.id)
    }

    if (notification.data?.url) {
        let url = notification.data.url
        url = url.replace('http://127.0.0.1:8000', '')
        url = url.replace('http://localhost:8000', '')
        router.visit(url)
    }
}
</script>

<style scoped>
/* Smooth transitions */
* {
    transition: all 0.2s ease;
}

/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
    width: 5px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Animation for new notifications */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.rounded-xl {
    animation: slideIn 0.3s ease-out;
}

/* Pulse animation for unread dot */
@keyframes pulse {

    0%,
    100% {
        opacity: 1;
    }

    50% {
        opacity: 0.5;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>