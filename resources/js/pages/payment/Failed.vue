<script setup>
import { router } from '@inertiajs/vue3'

defineProps({
    message: String,
    error_code: String,
    error_message: String
})
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
            <p class="text-gray-600 mb-4">{{ message }}</p>

            <div v-if="error_code" class="bg-red-50 rounded-lg p-3 mb-6">
                <p class="text-xs text-red-500 mb-1">Error Code: {{ error_code }}</p>
                <p class="text-sm text-red-600">{{ error_message }}</p>
            </div>

            <div class="space-y-3">
                <button @click="router.visit('/subscriptions')"
                    class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 px-4 rounded-lg font-medium hover:from-blue-700 hover:to-purple-700 transition-all">
                    Try Again
                </button>
                <button @click="router.visit('/contact')"
                    class="w-full bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-medium hover:bg-gray-200 transition-all">
                    Contact Support
                </button>
            </div>
        </div>
    </div>
</template>