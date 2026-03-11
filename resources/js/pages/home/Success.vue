<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50">
        <div class="max-w-md w-full p-6 bg-white rounded-lg shadow-lg">
            <div class="text-center">
                <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <h2 class="mt-4 text-2xl font-bold text-gray-900">Payment Successful!</h2>
                <p class="mt-2 text-gray-600">{{ message }}</p>
                <p class="mt-1 text-sm text-gray-500">Transaction ID: {{ transaction_id }}</p>
                <button @click="goHome" class="mt-6 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Go to Dashboard
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'

defineProps({
    message: String,
    transaction_id: String
})

const goHome = () => {
    router.get('/home')
}
useForceTheme('light');
</script>