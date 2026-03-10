<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50">
        <div class="max-w-md w-full p-6 bg-white rounded-lg shadow-lg">
            <div class="text-center">
                <svg class="mx-auto h-16 w-16 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
                <h2 class="mt-4 text-2xl font-bold text-gray-900">Payment Failed</h2>
                <p class="mt-2 text-gray-600">{{ message }}</p>
                <p v-if="error_code" class="mt-1 text-sm text-red-500">Error Code: {{ error_code }}</p>
                <p v-if="error_message" class="mt-1 text-sm text-gray-500">{{ error_message }}</p>
                <button @click="tryAgain" class="mt-6 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Try Again
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'

defineProps({
    message: String,
    error_code: String,
    error_message: String
})

const tryAgain = () => {
    router.get('/subscriptions') // Adjust this to your subscription page
}
</script>