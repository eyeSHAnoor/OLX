<script setup>
import { onMounted } from 'vue'

const props = defineProps({
    paymentData: Object,
    endpoint: String
})

onMounted(() => {
    // Auto-submit the form
    document.getElementById('jazzcash-form').submit()
})
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full text-center">
            <div class="mb-6">
                <div class="w-20 h-20 mx-auto mb-4 relative">
                    <div class="absolute inset-0 bg-blue-500 rounded-full opacity-20 animate-ping"></div>
                    <div class="relative w-20 h-20 bg-blue-500 rounded-full flex items-center justify-center">
                        <Icon icon="mdi:cash" class="text-3xl text-white" />
                    </div>
                </div>
                <h2 class="text-xl font-semibold text-gray-900 mb-2">Redirecting to JazzCash</h2>
                <p class="text-sm text-gray-600">Please wait while we redirect you to secure payment page...</p>
            </div>

            <div class="mb-4">
                <div class="animate-spin w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full mx-auto">
                </div>
            </div>

            <div class="text-xs text-gray-500">
                <p>You will be redirected to JazzCash payment gateway</p>
                <p class="mt-1">Do not close this window</p>
            </div>

            <!-- Hidden form that auto-submits -->
            <form id="jazzcash-form" :action="endpoint" method="POST">
                <input v-for="(value, key) in paymentData" :key="key" type="hidden" :name="key" :value="value" />
            </form>
        </div>
    </div>
</template>