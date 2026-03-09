<script setup>
import { router } from '@inertiajs/vue3'

const props = defineProps({
    message: { type: String, required: true },
    error_code: { type: String, default: '' },
    error_message: { type: String, default: '' }
})

useForceTheme('light')

// Router actions
const goToSubscriptions = () => router.visit('/subscriptions')
const contactSupport = () => router.visit('/contact')
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-red-50 to-orange-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-8 text-center">
            <div class="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <div class="w-20 h-20 bg-red-500 rounded-full flex items-center justify-center">
                    <Icon icon="mdi:close" class="text-4xl text-white" />
                </div>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment Failed</h1>
            <p class="text-gray-600 mb-4">{{ props.message }}</p>

            <div v-if="props.error_code" class="bg-red-50 rounded-lg p-3 mb-6">
                <p class="text-xs text-red-500 mb-1">Error Code: {{ props.error_code }}</p>
                <p class="text-sm text-red-600">{{ props.error_message }}</p>
            </div>

            <div class="space-y-3">
                <button @click="goToSubscriptions"
                    class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 px-4 rounded-lg font-medium hover:from-blue-700 hover:to-purple-700 transition-all">
                    Try Again
                </button>
                <button @click="contactSupport"
                    class="w-full bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-medium hover:bg-gray-200 transition-all">
                    Contact Support
                </button>
            </div>
        </div>
    </div>
</template>