<template>
    <div class="max-w-md mx-auto bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-emerald-600 to-emerald-500 px-6 py-4">
            <h3 class="text-white text-lg font-semibold">Complete Your Payment</h3>
            <p class="text-emerald-100 text-sm">Enter your payment details to subscribe</p>
        </div>

        <!-- Payment Form -->
        <form @submit.prevent="submitPayment" class="p-6 space-y-4">
            <!-- Selected Plan Summary -->
            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500">Plan: {{ selectedPlan.name }}</p>
                        <p class="text-xs text-gray-400">{{ selectedPlan.duration_days }} days access</p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-emerald-600">{{ formatPrice(selectedPlan.price) }}</p>
                    </div>
                </div>
            </div>

            <!-- Payment Method Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                <div class="space-y-2">
                    <!-- JazzCash Mobile Account -->
                    <label class="flex items-center p-3 border rounded-lg cursor-pointer"
                        :class="form.payment_method === 'jazzcash_mobile' ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200'">
                        <input type="radio" v-model="form.payment_method" value="jazzcash_mobile"
                            class="h-4 w-4 text-emerald-600">
                        <span class="ml-3">
                            <span class="block text-sm font-medium text-gray-700">JazzCash Mobile Account</span>
                            <span class="block text-xs text-gray-500">Pay using your mobile number & MPIN</span>
                        </span>
                    </label>

                    <!-- Credit/Debit Card -->
                    <label class="flex items-center p-3 border rounded-lg cursor-pointer"
                        :class="form.payment_method === 'card' ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200'">
                        <input type="radio" v-model="form.payment_method" value="card" class="h-4 w-4 text-emerald-600">
                        <span class="ml-3">
                            <span class="block text-sm font-medium text-gray-700">Credit/Debit Card</span>
                            <span class="block text-xs text-gray-500">Pay via Visa / Mastercard</span>
                        </span>
                    </label>
                </div>
            </div>

            <!-- Mobile Number Field (for JazzCash Mobile) -->
            <div v-if="form.payment_method === 'jazzcash_mobile'">
                <label class="block text-sm font-medium text-gray-700 mb-1">Mobile Number</label>
                <input type="tel" v-model="form.mobile_number" placeholder="03XXXXXXXXX"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                    required>
                <p class="text-xs text-gray-500 mt-1">Enter your JazzCash registered mobile number</p>
            </div>

            <!-- MPIN Field (for JazzCash Mobile) -->
            <div v-if="form.payment_method === 'jazzcash_mobile'">
                <label class="block text-sm font-medium text-gray-700 mb-1">MPIN</label>
                <input type="password" v-model="form.mpin" placeholder="Enter your MPIN"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                    required>
            </div>

            <!-- Card Fields (for Card Payment) -->
            <template v-if="form.payment_method === 'card'">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Card Number</label>
                    <input type="text" v-model="form.card_number" placeholder="XXXX XXXX XXXX XXXX" maxlength="19"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                        required>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                        <input type="text" v-model="form.expiry" placeholder="MM/YY" maxlength="5"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CVV</label>
                        <input type="password" v-model="form.cvv" placeholder="123" maxlength="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cardholder Name</label>
                    <input type="text" v-model="form.cardholder_name" placeholder="As on card"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                        required>
                </div>
            </template>

            <!-- CNIC (Optional but recommended) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">CNIC (Optional)</label>
                <input type="text" v-model="form.cnic" placeholder="XXXXX-XXXXXXX-X"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <!-- Terms -->
            <div class="flex items-center">
                <input type="checkbox" v-model="form.terms_accepted"
                    class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded" required>
                <label class="ml-2 block text-sm text-gray-700">
                    I agree to the Terms of Service and Privacy Policy
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" :disabled="form.processing || !form.terms_accepted"
                class="w-full bg-gradient-to-r from-emerald-600 to-emerald-500 text-white py-3 px-4 rounded-lg font-medium hover:from-emerald-700 hover:to-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                <span v-if="form.processing" class="flex items-center justify-center">
                    <Icon icon="mdi:loading" class="animate-spin mr-2" />
                    Processing Payment...
                </span>
                <span v-else>Pay {{ formatPrice(selectedPlan.price) }}</span>
            </button>

            <!-- Error Message -->
            <div v-if="errorMessage" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-600">{{ errorMessage }}</p>
            </div>

            <!-- Success Message -->
            <div v-if="successMessage" class="p-3 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-sm text-green-600">{{ successMessage }}</p>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

const props = defineProps({
    selectedPlan: Object,
    subscription: Object // Pass the pending subscription
})

const emit = defineEmits(['payment-complete', 'payment-failed'])

const form = useForm({
    payment_method: 'jazzcash_mobile',
    mobile_number: '',
    mpin: '',
    card_number: '',
    expiry: '',
    cvv: '',
    cardholder_name: '',
    cnic: '',
    terms_accepted: false,
    subscription_id: props.subscription?.id,
    plan_id: props.selectedPlan.id,
    amount: props.selectedPlan.price
})

const errorMessage = ref('')
const successMessage = ref('')

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-PK', {
        style: 'currency',
        currency: 'PKR',
        minimumFractionDigits: 0
    }).format(price).replace('PKR', '₨')
}

const submitPayment = () => {
    errorMessage.value = ''
    successMessage.value = ''

    // Validate amount (prevent tampering)
    if (form.amount !== props.selectedPlan.price) {
        errorMessage.value = 'Invalid payment amount'
        return
    }

    form.processing = true

    // Send payment details to your backend
    form.post('/jazzcash/process-payment', {
        preserveScroll: true,
        onSuccess: (page) => {
            form.processing = false
            successMessage.value = 'Payment successful! Redirecting...'

            // Emit success event
            emit('payment-complete', page.props)

            // Redirect to success page or show success
            setTimeout(() => {
                router.visit('/payment/success', {
                    data: { subscription_id: props.subscription.id }
                })
            }, 2000)
        },
        onError: (errors) => {
            form.processing = false
            errorMessage.value = errors.message || 'Payment failed. Please try again.'
            emit('payment-failed', errors)
        }
    })
}
</script>