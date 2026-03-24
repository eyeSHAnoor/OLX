<!-- resources/js/Pages/Orders/Index.vue -->
<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import OlxLayout from '@/layouts/OlxLayout.vue'

const props = defineProps({
    orders: Object,
    currentStatus: String,
    currentView: String,
    buyingStats: Object,
    sellingStats: Object
})

const expandedOrder = ref(null)
const activeTab = ref(props.currentView || 'buying')

// Separate orders into buying and selling
const buyingOrders = computed(() => {
    if (!props.orders?.data) return []
    return props.orders.data.filter(order => order.role === 'buyer')
})

const sellingOrders = computed(() => {
    if (!props.orders?.data) return []
    return props.orders.data.filter(order => order.role === 'seller')
})

const currentOrders = computed(() => {
    return activeTab.value === 'buying' ? buyingOrders.value : sellingOrders.value
})

const statuses = ['pending', 'accepted', 'rejected', 'completed', 'cancelled']

const statusColors = {
    pending: 'bg-yellow-100 text-yellow-800 border-yellow-200',
    accepted: 'bg-green-100 text-green-800 border-green-200',
    rejected: 'bg-red-100 text-red-800 border-red-200',
    completed: 'bg-blue-100 text-blue-800 border-blue-200',
    cancelled: 'bg-gray-100 text-gray-800 border-gray-200'
}

// Get status count for current view
const getStatusCount = (status) => {
    if (activeTab.value === 'buying') {
        return props.buyingStats?.[status] || 0
    } else {
        return props.sellingStats?.[status] || 0
    }
}

const changeStatus = (status) => {
    router.get('/orders', {
        status: status,
        view: activeTab.value
    }, { preserveState: true })
}

// Function to switch between buying and selling views
const switchView = (view) => {
    activeTab.value = view
    router.get('/orders', {
        status: props.currentStatus,
        view: view
    }, { preserveState: true })
}

const updateOrder = (orderId, action) => {
    const routeMap = {
        accepted: `/orders/${orderId}/accept`,
        rejected: `/orders/${orderId}/reject`,
    }

    router.post(routeMap[action], {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Reload the page with current filters after action
            router.get('/orders', {
                status: props.currentStatus,
                view: activeTab.value
            }, { preserveState: true })
        }
    })
}

// Function to open review page for order completion/cancellation
const openReviewPage = (orderId) => {
    router.get(`/orders/${orderId}/review`, {}, {
        preserveScroll: true
    })
}

const toggleExpand = (orderId) => {
    expandedOrder.value = expandedOrder.value === orderId ? null : orderId
}

const getPrimaryImage = (ad) => {
    if (!ad?.images || ad.images.length === 0) return null
    const primary = ad.images.find(img => img.is_primary === 1)
    return primary || ad.images[0]
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

// Helper to check if user can accept/reject (only for selling orders)
const canAcceptReject = (order) => {
    return activeTab.value === 'selling' && order.status === 'pending'
}

// Helper to check if buyer can complete/cancel (only for buying orders with accepted status)
const canCompleteCancel = (order) => {
    return activeTab.value === 'buying' && order.status === 'accepted'
}

// Helper to get the other party name
const getOtherParty = (order) => {
    if (activeTab.value === 'buying') {
        return order.seller?.name || 'Seller'
    } else {
        return order.buyer?.name || 'Buyer'
    }
}

// Helper to get role badge color
const getRoleBadgeClass = (order) => {
    return order.role === 'buyer'
        ? 'bg-blue-100 text-blue-800'
        : 'bg-green-100 text-green-800'
}

// Get empty state message
const getEmptyMessage = () => {
    if (activeTab.value === 'buying') {
        return `No ${props.currentStatus} orders as a buyer`
    } else {
        return `No ${props.currentStatus} orders as a seller`
    }
}
</script>

<template>
    <OlxLayout>
        <div class="max-w-9/11 mx-auto py-6 sm:py-8 px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6 sm:mb-8">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">My Orders</h1>
                <p class="mt-1 text-xs sm:text-sm text-gray-500">Track what you're buying and selling</p>
            </div>

            <!-- View Toggle (Buying vs Selling) with permanent totals -->
            <div class="mb-6 border-b border-gray-200">
                <div class="flex space-x-8">
                    <button @click="switchView('buying')"
                        class="py-3 px-1 font-medium text-sm focus:outline-none relative"
                        :class="activeTab === 'buying' ? 'text-blue-600' : 'text-gray-500 hover:text-gray-700'">
                        <div class="flex flex-col items-start">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span>Buying</span>
                                <span class="text-xs mt-1 font-normal">
                                    ({{ buyingStats?.total || 0 }})
                                </span>
                            </div>
                        </div>
                        <span class="absolute bottom-0 left-0 w-full h-0.5 transition-transform duration-200"
                            :class="activeTab === 'buying' ? 'bg-blue-600 scale-100' : 'scale-0'"></span>
                    </button>

                    <button @click="switchView('selling')"
                        class="py-3 px-1 font-medium text-sm focus:outline-none relative"
                        :class="activeTab === 'selling' ? 'text-blue-600' : 'text-gray-500 hover:text-gray-700'">
                        <div class="flex flex-col items-start">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <span>Selling</span>
                                <span class="text-xs mt-1 font-normal">
                                    ({{ sellingStats?.total || 0 }})
                                </span>
                            </div>
                        </div>
                        <span class="absolute bottom-0 left-0 w-full h-0.5 transition-transform duration-200"
                            :class="activeTab === 'selling' ? 'bg-blue-600 scale-100' : 'scale-0'"></span>
                    </button>
                </div>
            </div>

            <!-- Status Tabs with counts -->
            <div class="border-b border-gray-200 overflow-x-auto hide-scrollbar">
                <nav class="flex space-x-4 sm:space-x-8 min-w-max sm:min-w-0 px-1" aria-label="Tabs">
                    <button v-for="status in statuses" :key="status" @click="changeStatus(status)"
                        class="group relative py-3 sm:py-4 px-2 sm:px-1 font-medium text-xs sm:text-sm capitalize focus:outline-none whitespace-nowrap"
                        :class="[
                            currentStatus === status
                                ? 'text-blue-600'
                                : 'text-gray-500 hover:text-gray-700'
                        ]">
                        <div class="flex gap-1 items-center">
                            <span>{{ status }}</span>
                            <span class="text-xs mt-0.5 font-normal opacity-75">
                                ({{ getStatusCount(status) }})
                            </span>
                        </div>
                        <span class="absolute bottom-0 left-0 w-full h-0.5 transform transition-transform duration-200"
                            :class="currentStatus === status ? 'bg-blue-600 scale-100' : 'scale-0 group-hover:scale-100 bg-gray-300'">
                        </span>
                    </button>
                </nav>
            </div>

            <!-- Orders List -->
            <div class="mt-6 sm:mt-8 space-y-3 sm:space-y-4">
                <div v-if="currentOrders.length === 0"
                    class="text-center py-12 sm:py-16 bg-white rounded-xl sm:rounded-2xl border-2 border-dashed">
                    <div class="text-gray-400 mb-3">
                        <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                    </div>
                    <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-1">{{ getEmptyMessage() }}</h3>
                    <p class="text-xs sm:text-sm text-gray-500">Orders will appear here when you {{ activeTab ===
                        'buying' ? 'make a purchase' : 'receive orders' }}</p>
                </div>

                <transition-group name="list" tag="div" class="space-y-3 sm:space-y-4">
                    <div v-for="order in currentOrders" :key="order.id"
                        class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-all duration-200">

                        <!-- Compact View - Always Visible -->
                        <div class="p-3 sm:p-4 flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 cursor-pointer"
                            @click="toggleExpand(order.id)">

                            <!-- Top row on mobile: Image and basic info -->
                            <div class="flex items-center gap-3 w-full sm:w-auto">
                                <!-- Role Badge (Mobile) -->
                                <div class="sm:hidden">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full capitalize"
                                        :class="getRoleBadgeClass(order)">
                                        {{ order.role === 'buyer' ? 'Buying' : 'Selling' }}
                                    </span>
                                </div>

                                <!-- Ad Image -->
                                <div class="relative w-16 h-16 sm:w-20 sm:h-20 flex-shrink-0">
                                    <div
                                        class="absolute inset-0 bg-gradient-to-br from-blue-400 to-purple-500 rounded-xl opacity-10">
                                    </div>
                                    <div class="relative w-full h-full bg-gray-100 rounded-xl overflow-hidden">
                                        <img v-if="getPrimaryImage(order.ad)"
                                            :src="`/storage/${getPrimaryImage(order.ad).path}`"
                                            :alt="order.ad?.ad_title" class="w-full h-full object-cover">
                                        <div v-else class="w-full h-full flex items-center justify-center">
                                            <svg class="w-6 h-6 sm:w-8 sm:h-8 text-gray-300" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- Mobile: Title and Order ID -->
                                <div class="flex-1 min-w-0 sm:hidden">
                                    <h3 class="font-semibold text-gray-900 truncate">{{ order.ad?.ad_title }}</h3>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-xs text-gray-400">#{{ order.id }}</span>
                                        <span class="text-xs font-medium text-gray-900">Rs. {{ order.price * order.qty
                                        }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Desktop: Basic Info (hidden on mobile) -->
                            <div class="hidden sm:block flex-1 min-w-0">
                                <!-- Role Badge Desktop -->
                                <div class="mb-2">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full capitalize"
                                        :class="getRoleBadgeClass(order)">
                                        {{ order.role === 'buyer' ? 'Buying' : 'Selling' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 mb-1.5">
                                    <h3 class="font-semibold text-gray-900 truncate">{{ order.ad?.ad_title }}</h3>
                                    <span class="text-xs text-gray-400">#{{ order.id }}</span>
                                </div>

                                <div class="flex flex-wrap items-center gap-3 text-xs sm:text-sm">
                                    <!-- Counter Party -->
                                    <div class="flex items-center gap-1.5 text-gray-600">
                                        <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span class="truncate max-w-[80px] sm:max-w-[120px]">{{ getOtherParty(order)
                                        }}</span>
                                    </div>

                                    <!-- Quantity -->
                                    <div class="flex items-center gap-1.5 text-gray-600">
                                        <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                                        </svg>
                                        <span>Qty: {{ order.qty }}</span>
                                    </div>

                                    <!-- Delivery -->
                                    <div class="flex items-center gap-1.5"
                                        :class="order.delivery_option === 'pickup' ? 'text-purple-600' : 'text-blue-600'">
                                        <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span class="capitalize">{{ order.delivery_option }}</span>
                                    </div>

                                    <!-- Location -->
                                    <div class="flex items-center gap-1.5 text-gray-600">
                                        <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span class="truncate max-w-[80px] sm:max-w-[100px]">{{ order.ad?.city }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Price & Actions - Stack on mobile, row on desktop -->
                            <div
                                class="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4 w-full sm:w-auto mt-2 sm:mt-0">
                                <!-- Mobile: Basic info (hidden on desktop) -->
                                <div class="sm:hidden w-full">
                                    <div class="flex flex-wrap items-center gap-2 text-xs text-gray-600">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            {{ getOtherParty(order) }}
                                        </span>
                                        <span>•</span>
                                        <span>Qty: {{ order.qty }}</span>
                                        <span>•</span>
                                        <span class="capitalize">{{ order.delivery_option }}</span>
                                    </div>
                                </div>

                                <!-- Price and Action Buttons -->
                                <div class="flex items-center justify-between sm:justify-end w-full sm:w-auto gap-3">
                                    <!-- Price on mobile -->
                                    <div class="sm:hidden">
                                        <div class="text-xs text-gray-400">Total</div>
                                        <div class="text-base font-bold text-gray-900">Rs. {{ order.price * order.qty }}
                                        </div>
                                    </div>

                                    <!-- Desktop Price -->
                                    <div class="hidden sm:block text-right">
                                        <div class="text-lg font-bold text-gray-900">Rs. {{ order.price * order.qty }}
                                        </div>
                                        <div class="text-xs text-gray-400">Total</div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex items-center gap-2" @click.stop>
                                        <!-- Seller Actions: Accept/Reject -->
                                        <div v-if="canAcceptReject(order)" class="flex gap-2">
                                            <button @click="updateOrder(order.id, 'accepted')"
                                                class="px-3 sm:px-4 py-1.5 sm:py-2 bg-green-600 text-white text-xs sm:text-sm font-medium rounded-lg hover:bg-green-700 transform hover:scale-105 transition-all duration-200 shadow-md hover:shadow-lg whitespace-nowrap">
                                                Accept
                                            </button>
                                            <button @click="updateOrder(order.id, 'rejected')"
                                                class="px-3 sm:px-4 py-1.5 sm:py-2 bg-red-600 text-white text-xs sm:text-sm font-medium rounded-lg hover:bg-red-700 transform hover:scale-105 transition-all duration-200 shadow-md hover:shadow-lg whitespace-nowrap">
                                                Reject
                                            </button>
                                        </div>

                                        <!-- Buyer Actions: Complete/Cancel (opens review page) -->
                                        <div v-else-if="canCompleteCancel(order)" class="flex gap-2">
                                            <button @click="openReviewPage(order.id)"
                                                class="px-2 sm:px-3 py-1 sm:py-1.5 bg-brand-blue text-white text-xs sm:text-sm font-medium rounded-lg hover:bg-blue-700 transform hover:scale-105 transition-all duration-200 shadow-md hover:shadow-lg whitespace-nowrap">
                                                Review Order
                                            </button>
                                        </div>

                                        <!-- Status Badge for other statuses -->
                                        <span v-else
                                            class="px-2 sm:px-3 py-1 sm:py-1.5 text-xs sm:text-sm font-medium rounded-lg capitalize whitespace-nowrap"
                                            :class="statusColors[order.status]">
                                            {{ order.status }}
                                        </span>
                                    </div>

                                    <!-- Expand Icon -->
                                    <button class="p-1.5 sm:p-2 hover:bg-gray-50 rounded-lg transition-colors">
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 transition-transform duration-300 ease-in-out"
                                            :class="{ 'rotate-180': expandedOrder === order.id }" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Expanded Details -->
                        <transition enter-active-class="transition-all duration-300 ease-out"
                            enter-from-class="opacity-0 max-h-0" enter-to-class="opacity-100 max-h-[2000px]"
                            leave-active-class="transition-all duration-200 ease-in"
                            leave-from-class="opacity-100 max-h-[2000px]" leave-to-class="opacity-0 max-h-0">

                            <div v-show="expandedOrder === order.id"
                                class="border-t border-gray-100 bg-gradient-to-br from-gray-50 to-white">
                                <div class="p-4 sm:p-6">
                                    <!-- Header with role -->
                                    <div class="flex items-center justify-between mb-4 sm:mb-6">
                                        <div class="flex items-center gap-2">
                                            <div class="w-1 h-5 sm:h-6 bg-blue-500 rounded-full"></div>
                                            <h3
                                                class="text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">
                                                Order Details
                                            </h3>
                                        </div>
                                        <div class="flex gap-2">
                                            <!-- Review button in expanded view for buyers -->
                                            <button v-if="canCompleteCancel(order)" @click="openReviewPage(order.id)"
                                                class="px-3 py-1.5 text-xs font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-all duration-200">
                                                Review Order
                                            </button>
                                            <span class="px-3 py-1 text-xs font-medium rounded-full capitalize"
                                                :class="getRoleBadgeClass(order)">
                                                {{ order.role === 'buyer' ? 'As a Buyer' : 'As a Seller' }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Responsive grid: 1 column on mobile, 2 on desktop -->
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 lg:gap-8">
                                        <!-- Left Column -->
                                        <div class="space-y-4 sm:space-y-6">
                                            <!-- Counter Party Card -->
                                            <div
                                                class="bg-white rounded-lg sm:rounded-xl p-4 sm:p-5 shadow-sm border border-gray-100">
                                                <h4
                                                    class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 sm:mb-4 flex items-center gap-2">
                                                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                    </svg>
                                                    {{ activeTab === 'buying' ? 'Seller' : 'Buyer' }} Information
                                                </h4>
                                                <div class="space-y-2 sm:space-y-3">
                                                    <div
                                                        class="flex flex-col sm:flex-row sm:items-center justify-between py-2 border-b border-gray-50">
                                                        <span class="text-xs sm:text-sm text-gray-500">Name</span>
                                                        <span
                                                            class="text-xs sm:text-sm font-medium text-gray-900 break-words">{{
                                                                activeTab === 'buying' ? order.seller?.name :
                                                                    order.buyer?.name }}</span>
                                                    </div>
                                                    <div
                                                        class="flex flex-col sm:flex-row sm:items-center justify-between py-2 border-b border-gray-50">
                                                        <span class="text-xs sm:text-sm text-gray-500">Contact</span>
                                                        <span class="text-xs sm:text-sm font-medium text-gray-900">{{
                                                            order.contact_number }}</span>
                                                    </div>
                                                    <div
                                                        class="flex flex-col sm:flex-row sm:items-center justify-between py-2">
                                                        <span class="text-xs sm:text-sm text-gray-500">Email</span>
                                                        <span
                                                            class="text-xs sm:text-sm font-medium text-gray-900 break-words">{{
                                                                activeTab === 'buying' ? order.seller?.email :
                                                                    order.buyer?.email || 'N/A' }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Delivery Card -->
                                            <div
                                                class="bg-white rounded-lg sm:rounded-xl p-4 sm:p-5 shadow-sm border border-gray-100">
                                                <h4
                                                    class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 sm:mb-4 flex items-center gap-2">
                                                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Delivery Details
                                                </h4>
                                                <div class="space-y-2 sm:space-y-3">
                                                    <div
                                                        class="flex flex-col sm:flex-row sm:items-center justify-between py-2 border-b border-gray-50">
                                                        <span class="text-xs sm:text-sm text-gray-500">Method</span>
                                                        <span
                                                            class="text-xs sm:text-sm font-medium capitalize px-2 sm:px-3 py-1 rounded-full w-fit sm:w-auto"
                                                            :class="order.delivery_option === 'pickup' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'">
                                                            {{ order.delivery_option }}
                                                        </span>
                                                    </div>
                                                    <div v-if="order.delivery_address" class="py-2">
                                                        <span
                                                            class="text-xs sm:text-sm text-gray-500 block mb-1">Address</span>
                                                        <p
                                                            class="text-xs sm:text-sm text-gray-900 bg-gray-50 p-2 sm:p-3 rounded-lg break-words">
                                                            {{ order.delivery_address }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Right Column -->
                                        <div class="space-y-4 sm:space-y-6">
                                            <!-- Order Card -->
                                            <div
                                                class="bg-white rounded-lg sm:rounded-xl p-4 sm:p-5 shadow-sm border border-gray-100">
                                                <h4
                                                    class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 sm:mb-4 flex items-center gap-2">
                                                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M16 11V7a4 4 0 00-8 0v4M5 11h14v7a2 2 0 01-2 2H7a2 2 0 01-2-2v-7z" />
                                                    </svg>
                                                    Order Summary
                                                </h4>
                                                <div class="space-y-2 sm:space-y-3">
                                                    <div
                                                        class="flex flex-col sm:flex-row sm:items-center justify-between py-2 border-b border-gray-50">
                                                        <span class="text-xs sm:text-sm text-gray-500">Order ID</span>
                                                        <span class="text-xs sm:text-sm font-medium text-gray-900">#{{
                                                            order.id }}</span>
                                                    </div>
                                                    <div
                                                        class="flex flex-col sm:flex-row sm:items-center justify-between py-2 border-b border-gray-50">
                                                        <span class="text-xs sm:text-sm text-gray-500">Quantity</span>
                                                        <span class="text-xs sm:text-sm font-medium text-gray-900">{{
                                                            order.qty }}</span>
                                                    </div>
                                                    <div
                                                        class="flex flex-col sm:flex-row sm:items-center justify-between py-2 border-b border-gray-50">
                                                        <span class="text-xs sm:text-sm text-gray-500">Price per
                                                            item</span>
                                                        <span class="text-xs sm:text-sm font-medium text-gray-900">Rs.
                                                            {{ order.price }}</span>
                                                    </div>
                                                    <div
                                                        class="flex flex-col sm:flex-row sm:items-center justify-between py-2 border-b border-gray-50">
                                                        <span class="text-xs sm:text-sm text-gray-500">Ordered on</span>
                                                        <span class="text-xs sm:text-sm font-medium text-gray-900">{{
                                                            formatDate(order.created_at) }}</span>
                                                    </div>
                                                    <div
                                                        class="flex flex-col sm:flex-row sm:items-center justify-between py-3 sm:py-4 bg-blue-50 -m-4 sm:-m-5 mt-2 p-4 sm:p-5 rounded-b-lg sm:rounded-b-xl">
                                                        <span
                                                            class="text-xs sm:text-sm font-semibold text-gray-700">Total
                                                            Amount</span>
                                                        <span class="text-base sm:text-lg font-bold text-blue-600">Rs.
                                                            {{ order.price * order.qty }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Notes Card -->
                                            <div v-if="order.notes"
                                                class="bg-white rounded-lg sm:rounded-xl p-4 sm:p-5 shadow-sm border border-gray-100">
                                                <h4
                                                    class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 sm:mb-4 flex items-center gap-2">
                                                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    Additional Notes
                                                </h4>
                                                <p
                                                    class="text-xs sm:text-sm text-gray-600 bg-gray-50 p-3 sm:p-4 rounded-lg italic break-words">
                                                    {{ order.notes }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Ad Details Section -->
                                    <div class="mt-4 sm:mt-6 pt-4 sm:pt-6 border-t border-gray-200">
                                        <div class="flex items-center gap-2 mb-3 sm:mb-4">
                                            <div class="w-1 h-4 sm:h-5 bg-purple-500 rounded-full"></div>
                                            <h4
                                                class="text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">
                                                Advertisement Details
                                            </h4>
                                        </div>

                                        <div
                                            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 lg:gap-6">
                                            <div class="bg-white rounded-lg p-3 sm:p-4 border border-gray-100">
                                                <span class="text-xs text-gray-400 block mb-1">Category</span>
                                                <span
                                                    class="text-xs sm:text-sm font-medium text-gray-900 break-words">{{
                                                        order.ad?.category_id }}</span>
                                            </div>
                                            <div class="bg-white rounded-lg p-3 sm:p-4 border border-gray-100">
                                                <span class="text-xs text-gray-400 block mb-1">Location</span>
                                                <span
                                                    class="text-xs sm:text-sm font-medium text-gray-900 break-words">{{
                                                        order.ad?.city }}, {{ order.ad?.location }}</span>
                                            </div>
                                            <div
                                                class="bg-white rounded-lg p-3 sm:p-4 border border-gray-100 sm:col-span-2 lg:col-span-1">
                                                <span class="text-xs text-gray-400 block mb-1">Seller</span>
                                                <span
                                                    class="text-xs sm:text-sm font-medium text-gray-900 break-words">{{
                                                        order.ad?.seller_name }}</span>
                                            </div>
                                        </div>

                                        <div class="mt-3 sm:mt-4 bg-white rounded-lg p-3 sm:p-4 border border-gray-100">
                                            <span class="text-xs text-gray-400 block mb-2">Description</span>
                                            <p class="text-xs sm:text-sm text-gray-600 leading-relaxed break-words">{{
                                                order.ad?.description }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </transition>
                    </div>
                </transition-group>
            </div>

            <!-- Pagination -->
            <div v-if="orders.last_page > 1" class="mt-6 sm:mt-8 flex justify-center overflow-x-auto hide-scrollbar">
                <div
                    class="flex gap-1 sm:gap-2 bg-white p-1 rounded-lg shadow-sm border border-gray-100 min-w-max sm:min-w-0">
                    <button v-for="link in orders.links" :key="link.label"
                        @click="link.url && router.get(link.url + `&view=${activeTab.value}&status=${currentStatus}`)"
                        v-html="link.label" :disabled="!link.url" :class="[
                            'px-2 sm:px-4 py-1.5 sm:py-2 rounded-md text-xs sm:text-sm font-medium transition-all duration-200',
                            link.active
                                ? 'bg-blue-600 text-white shadow-md'
                                : 'text-gray-600 hover:bg-gray-50',
                            !link.url && 'opacity-50 cursor-not-allowed'
                        ]">
                    </button>
                </div>
            </div>
        </div>
    </OlxLayout>
</template>

<style scoped>
/* Hide scrollbar for Chrome, Safari and Opera */
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}

/* Hide scrollbar for IE, Edge and Firefox */
.hide-scrollbar {
    -ms-overflow-style: none;
    /* IE and Edge */
    scrollbar-width: none;
    /* Firefox */
}

.list-enter-active,
.list-leave-active {
    transition: all 0.3s ease;
}

.list-enter-from {
    opacity: 0;
    transform: translateY(20px);
}

.list-leave-to {
    opacity: 0;
    transform: translateY(-20px);
}

.list-move {
    transition: transform 0.3s ease;
}

/* Custom scrollbar for expanded content */
.overflow-y-auto::-webkit-scrollbar {
    width: 4px;
    height: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Responsive text adjustments */
@media (max-width: 640px) {
    .text-2xl {
        font-size: 1.5rem;
    }

    .text-lg {
        font-size: 1rem;
    }
}
</style>