<template>
    <Teleport to="body">
        <transition enter-active-class="transition ease-out duration-300"
            enter-from-class="transform translate-x-full opacity-0" enter-to-class="transform translate-x-0 opacity-100"
            leave-active-class="transition ease-in duration-200" leave-from-class="transform translate-x-0 opacity-100"
            leave-to-class="transform translate-x-full opacity-0">
            <div v-if="notificationStore.showOrderPopup" class="fixed bottom-4 right-4 z-[9999] w-80 sm:w-96">
                <div class="bg-white rounded-lg shadow-xl border-l-4 border-green-500 overflow-hidden">
                    <div class="p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <h3 class="font-semibold text-gray-900">Order Accepted!</h3>
                                </div>
                                <p class="text-sm text-gray-600 mb-3">
                                    {{ notificationStore.pendingOrder?.message }}
                                </p>
                                <div class="bg-gray-50 rounded p-2 mb-3">
                                    <p class="text-xs text-gray-500">Order #{{ notificationStore.pendingOrder?.id }}</p>
                                    <p class="text-sm font-medium text-gray-900">{{
                                        notificationStore.pendingOrder?.ad_title }}</p>
                                    <p class="text-xs text-gray-600">Seller: {{
                                        notificationStore.pendingOrder?.seller_name }}</p>
                                    <p class="text-sm font-bold text-green-600 mt-1">Rs. {{
                                        notificationStore.pendingOrder?.total }}</p>
                                </div>
                                <div class="flex gap-2">
                                    <button @click="handleReviewOrder"
                                        class="flex-1 px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                        Review Order
                                    </button>
                                    <button @click="handleCancelOrder"
                                        class="flex-1 px-3 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                            <button @click="notificationStore.closeNotification"
                                class="text-gray-400 hover:text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </Teleport>
</template>

<script setup>
import { useNotificationStore } from '@/stores/notificationStore'
import { router } from '@inertiajs/vue3'

const notificationStore = useNotificationStore()

const handleReviewOrder = () => {
    if (notificationStore.pendingOrder.value) {
        const orderId = notificationStore.pendingOrder.value.id
        notificationStore.closeNotification()
        router.get(`/orders/${orderId}/review`)
    }
}

const handleCancelOrder = () => {
    if (notificationStore.pendingOrder.value && confirm('Are you sure you want to cancel this order?')) {
        const orderId = notificationStore.pendingOrder.value.id
        router.post(`/orders/${orderId}/cancel`, {}, {
            onSuccess: () => {
                notificationStore.closeNotification()
            }
        })
    }
}
</script>