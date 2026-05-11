<template>
    <div
        class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 flex flex-col items-center justify-center p-3 sm:p-4">
        <!-- Logo -->
        <div class="mb-6 md:mb-8">
            <img src="/images/logo.png" alt="AMO Mercatus" class="h-10 md:h-14 w-auto mx-auto" />
            <h1 class="mt-2 text-xl md:text-2xl font-semibold text-center text-gray-800">
                Order Management
            </h1>
            <p class="text-gray-600 text-center mt-1 text-xs md:text-sm">
                Review and update your order status
            </p>
        </div>

        <!-- Order Card -->
        <div class="w-full max-w-md">
            <div class="bg-white rounded-xl shadow-md p-5 md:p-6">
                <!-- Order Status Badge -->
                <div class="mb-5 flex justify-center">
                    <span :class="{
                        'bg-yellow-100 text-yellow-800': order.status === 'pending',
                        'bg-green-100 text-green-800': order.status === 'completed',
                        'bg-red-100 text-red-800': order.status === 'cancelled',
                    }" class="inline-flex px-4 py-2 rounded-full text-sm font-semibold">
                        {{ order.status.toUpperCase() }}
                    </span>
                </div>

                <!-- Order Details -->
                <div class="space-y-4 mb-6">
                    <div class="border-b border-gray-100 pb-3">
                        <label class="block text-xs font-medium text-gray-500 mb-1">
                            Order Number
                        </label>
                        <p class="text-lg font-bold text-gray-900">#{{ order.id }}</p>
                    </div>

                    <div class="border-b border-gray-100 pb-3">
                        <label class="block text-xs font-medium text-gray-500 mb-1"> Product </label>
                        <p class="text-gray-800 font-medium">{{ order.ad?.ad_title || "N/A" }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 border-b border-gray-100 pb-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">
                                Quantity
                            </label>
                            <p class="text-gray-800">{{ order.qty }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1"> Price </label>
                            <p class="text-gray-800">Rs. {{ formatPrice(order.price) }}</p>
                        </div>
                    </div>

                    <div class="pt-2">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-semibold text-gray-700">Total Amount</span>
                            <span class="text-xl font-bold text-brand-blue">
                                Rs. {{ formatPrice(order.price * order.qty) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div v-if="order.status === 'accepted'" class="space-y-3">
                    <button @click="completeOrder" :disabled="processing"
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2.5 px-3 rounded-lg focus:outline-none focus:ring-1 focus:ring-green-600 focus:ring-offset-1 transition-all duration-200 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed text-sm">
                        <div class="flex items-center justify-center gap-2">
                            <svg v-if="processing && actionType === 'complete'" class="animate-spin h-4 w-4 text-white"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span>{{
                                processing && actionType === "complete"
                                    ? "Processing..."
                                : "Complete Order"
                                }}</span>
                        </div>
                    </button>

                    <button @click="cancelOrder" :disabled="processing"
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-2.5 px-3 rounded-lg focus:outline-none focus:ring-1 focus:ring-red-600 focus:ring-offset-1 transition-all duration-200 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed text-sm">
                        <div class="flex items-center justify-center gap-2">
                            <svg v-if="processing && actionType === 'cancel'" class="animate-spin h-4 w-4 text-white"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span>{{
                                processing && actionType === "cancel" ? "Processing..." : "Cancel Order"
                                }}</span>
                        </div>
                    </button>
                </div>

                <!-- Already Processed Message -->
                <div v-else class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-gray-600 text-sm">
                        This order has been
                        <span :class="order.status === 'completed' ? 'text-green-600' : 'text-red-600'">
                            {{ order.status }}
                        </span>
                    </p>
                    <p class="text-xs text-gray-500 mt-1">No further actions available</p>
                </div>

                <!-- Flash Messages -->
                <div v-if="$page.props.flash?.success" class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-green-700 text-xs text-center">
                        {{ $page.props.flash.success }}
                    </p>
                </div>

                <div v-if="$page.props.flash?.error" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-red-700 text-xs text-center">
                        {{ $page.props.flash.error }}
                    </p>
                </div>
            </div>

            <!-- Help Text -->
            <div class="mt-6 text-center">
                <p class="text-xs text-gray-500">
                    Need help?
                    <a href="/page/contact" class="text-brand-teal hover:text-brand-teal/80">Contact support</a>
                </p>
            </div>
        </div>

        <!-- Footer Links -->
        <div class="mt-6 md:mt-8 text-center">
            <div class="flex flex-wrap justify-center gap-3 text-[10px] text-gray-500">
                <a href="/page/help" class="hover:text-gray-700 transition-colors">Help</a>
                <a href="/page/privacy" class="hover:text-gray-700 transition-colors">Privacy</a>
                <a href="/page/terms" class="hover:text-gray-700 transition-colors">Terms</a>
                <a href="/page/about" class="hover:text-gray-700 transition-colors">About</a>
            </div>
            <p class="mt-2 text-[10px] text-gray-500">
                © {{ new Date().getFullYear() }} Amo Mercatus. All rights reserved.
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    order: Object,
});

const processing = ref(false);
const actionType = ref("");

const formatPrice = (price) => {
    return Number(price).toLocaleString("en-PK");
};

const completeOrder = () => {
    if (confirm("Are you sure you want to complete this order?")) {
        processing.value = true;
        actionType.value = "complete";

        router.post(
            `/orders/${props.order.id}/complete`,
            {},
            {
                onFinish: () => {
                    processing.value = false;
                    actionType.value = "";
                },
            }
        );
    }
};

const cancelOrder = () => {
    if (confirm("Are you sure you want to cancel this order?")) {
        processing.value = true;
        actionType.value = "cancel";

        router.post(
            `/orders/${props.order.id}/cancel`,
            {},
            {
                onFinish: () => {
                    processing.value = false;
                    actionType.value = "";
                },
            }
        );
    }
};
</script>

<style scoped>
/* Smooth transitions */
* {
    transition: all 0.2s ease-in-out;
}

/* Custom focus styles */
button:focus {
    box-shadow: 0 0 0 2px rgba(var(--brand-teal-rgb), 0.1);
}

/* Button hover effects */
button:not(:disabled):hover {
    transform: translateY(-1px);
}

/* Smooth scale animation */
@keyframes gentleScale {
    0% {
        transform: scale(0.95);
        opacity: 0;
    }

    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.bg-white {
    animation: gentleScale 0.3s ease-out;
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 4px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}

::-webkit-scrollbar-thumb {
    background: #14b8a6;
    border-radius: 2px;
}

::-webkit-scrollbar-thumb:hover {
    background: #0d9488;
}
</style>
