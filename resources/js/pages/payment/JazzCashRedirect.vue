<template>
    <div class="min-h-screen flex items-center justify-center">
        <div class="text-center">
            <h2 class="text-xl mb-4">Redirecting to JazzCash...</h2>
            <p class="text-gray-600">Please wait while we redirect you to the payment gateway.</p>

            <!-- Hidden form that will auto-submit -->
            <form ref="jazzcashForm" method="POST" :action="endpoint" class="hidden">
                <input v-for="(value, key) in paymentData" type="hidden" :name="key" :value="value" :key="key">
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const props = defineProps({
    paymentData: Object,
    endpoint: String
})

const jazzcashForm = ref(null)

onMounted(() => {
    // Auto-submit the form when component mounts
    if (jazzcashForm.value) {
        jazzcashForm.value.submit()
    }
})
</script>