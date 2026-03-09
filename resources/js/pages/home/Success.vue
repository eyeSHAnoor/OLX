<script setup>
import { router } from '@inertiajs/vue3'

const props = defineProps({
    message: { type: String, required: true },
    transaction_id: { type: String, default: '' }
})

useForceTheme('light')

// Router actions
const goHome = () => router.visit('/')
const viewDashboard = () => router.visit('/dashboard')
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-emerald-50 to-blue-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-8 text-center">
            <div class="w-24 h-24 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <div class="w-20 h-20 bg-emerald-500 rounded-full flex items-center justify-center animate-bounce">
                    <Icon icon="mdi:check" class="text-4xl text-white" />
                </div>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment Successful!</h1>
            <p class="text-gray-600 mb-4">{{ props.message }}</p>

            <div v-if="props.transaction_id" class="bg-gray-50 rounded-lg p-3 mb-6">
                <p class="text-xs text-gray-500">Transaction ID</p>
                <p class="text-sm font-mono font-medium">{{ props.transaction_id }}</p>
            </div>

            <div class="space-y-3">
                <button @click="goHome"
                    class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 px-4 rounded-lg font-medium hover:from-blue-700 hover:to-purple-700 transition-all">
                    Go to Home
                </button>
                <button @click="viewDashboard"
                    class="w-full bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-medium hover:bg-gray-200 transition-all">
                    View Dashboard
                </button>
            </div>
        </div>
    </div>
</template>