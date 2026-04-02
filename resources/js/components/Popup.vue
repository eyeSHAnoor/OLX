<script setup>
import axios from 'axios'
import { ref, onMounted, onUnmounted, computed } from 'vue'

const props = defineProps({
    order: {
        type: Object,
        required: true
    },
    type: {
        type: String,
        required: true,       // 'buyer' or 'seller'
        validator: (val) => ['buyer', 'seller'].includes(val)
    }
})

const emit = defineEmits(['close'])

const isLoading = ref(false)
const error = ref(null)
const isMobile = ref(false)

// Determine if we're showing seller actions (Accept/Decline)
const isSeller = computed(() => props.type === 'seller')
// Determine if the order is in a state that requires seller action
const showSellerActions = computed(() => isSeller.value && props.order.status === 'pending')
// For buyer: we may want to show a different action if status is 'requested'
const showBuyerActions = computed(() => props.type === 'buyer' && props.order.status === 'requested')

// ... (rest of the lifecycle and helper functions remain the same)

async function acceptOrder() {
    isLoading.value = true
    error.value = null

    try {
        // Use order.id for seller actions
        await axios.post(`/orders/${props.order.id}/accept`)
        emit('close')
    } catch (err) {
        error.value = 'Failed to accept order. Please try again.'
        console.error('Accept order error:', err)
    } finally {
        isLoading.value = false
    }
}

async function rejectOrder() {
    isLoading.value = true
    error.value = null

    try {
        await axios.post(`/orders/${props.order.id}/reject`)
        emit('close')
    } catch (err) {
        error.value = 'Failed to reject order. Please try again.'
        console.error('Reject order error:', err)
    } finally {
        isLoading.value = false
    }
}

async function createOrder() {
    isLoading.value = true
    error.value = null

    try {

        await axios.post('/order', {
            ad_id: props.order.ad_id,
            qty: props.order.qty ?? 1
        })

        emit('close')

    } catch (err) {

        error.value = err.response?.data?.message || 'Failed to create order'
        console.error('Create order error:', err)

    } finally {
        isLoading.value = false
    }
}
</script>

<template>
    <Transition enter-active-class="transition duration-300 ease-out"
        enter-from-class="transform translate-y-4 opacity-0" enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in" leave-from-class="transform translate-y-0 opacity-100"
        leave-to-class="transform translate-y-4 opacity-0">
        <div v-if="order"
            class="fixed inset-0 z-[60] flex items-end sm:items-start sm:justify-end p-0 sm:p-6 bg-black/20 sm:bg-transparent"
            :class="{ 'bg-black/50': isMobile }">
            <div class="order-notification bg-white rounded-t-2xl sm:rounded-xl shadow-2xl w-full sm:w-80 md:w-96 max-w-full sm:max-w-sm md:max-w-md border-0 sm:border border-gray-100 animate-slide-up sm:animate-none"
                :class="{ 'rounded-b-2xl': isMobile }">
                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        <h3 class="font-semibold text-gray-800 text-sm sm:text-base">
                            {{ isSeller ? 'New Order Request' : 'Order Request Sent' }}
                        </h3>
                    </div>
                    <button @click="emit('close')"
                        class="text-gray-400 hover:text-gray-600 transition-colors p-1 sm:p-0 focus:outline-none focus:ring-2 focus:ring-brand-teal rounded-full"
                        aria-label="Close">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <div class="p-4 sm:p-5">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-3 sm:p-4 mb-4">
                        <p class="text-xs sm:text-sm text-gray-600 mb-1">
                            {{ isSeller ? 'Order Details' : 'Your request was sent' }}
                        </p>
                        <p class="text-base sm:text-lg font-semibold text-gray-800 break-all">
                            Ad #{{ order.ad_id }}
                        </p>
                        <p class="text-xs text-gray-500 mt-2">
                            Received just now
                        </p>
                    </div>

                    <!-- Error Message -->
                    <div v-if="error" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-xs sm:text-sm text-red-600">{{ error }}</p>
                    </div>

                    <!-- Seller Actions (Accept/Decline) -->
                    <div v-if="showSellerActions" class="flex flex-col-reverse sm:flex-row gap-2 sm:gap-3">
                        <button
                            class="w-full sm:flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-3 sm:py-2.5 rounded-lg font-medium transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95 sm:active:scale-100"
                            :disabled="isLoading" @click="rejectOrder">
                            <span class="flex items-center justify-center gap-2 text-sm sm:text-base">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Decline
                            </span>
                        </button>

                        <button
                            class="w-full sm:flex-1 bg-brand-teal hover:bg-brand-teal/90 text-white px-4 py-3 sm:py-2.5 rounded-lg font-medium transition-all duration-200 transform active:scale-95 sm:active:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100 shadow-sm"
                            :disabled="isLoading" @click="acceptOrder">
                            <span v-if="!isLoading" class="flex items-center justify-center gap-2 text-sm sm:text-base">
                                Accept Order
                            </span>
                            <span v-else class="flex items-center justify-center gap-2">
                                <svg class="animate-spin w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                </svg>
                                Processing...
                            </span>
                        </button>
                    </div>

                    <!-- Buyer Actions (Create Order / Close) -->
                    <div v-else-if="showBuyerActions" class="flex flex-col-reverse sm:flex-row gap-2 sm:gap-3">
                        <button
                            class="w-full sm:flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-3 sm:py-2.5 rounded-lg font-medium transition-all duration-200"
                            @click="emit('close')">
                            Close
                        </button>

                        <button
                            class="w-full sm:flex-1 bg-brand-teal hover:bg-brand-teal/90 text-white px-4 py-3 sm:py-2.5 rounded-lg font-medium transition-all duration-200 transform active:scale-95 sm:active:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                            :disabled="isLoading" @click="createOrder">
                            <span v-if="!isLoading" class="flex items-center justify-center gap-2 text-sm sm:text-base">
                                Create Order
                            </span>
                            <span v-else class="flex items-center justify-center gap-2">
                                <svg class="animate-spin w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                </svg>
                                Processing...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>