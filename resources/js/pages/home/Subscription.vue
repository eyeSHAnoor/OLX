<template>
    <div
        class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 flex flex-col items-center justify-center p-3 sm:p-4">

        <!-- Logo -->
        <div class="mb-6 md:mb-8">
            <img src="/images/logo.png" alt="OLX Clone Logo" class="h-10 md:h-14 w-auto mx-auto" />
            <h1 class="mt-2 text-xl md:text-2xl font-semibold text-center text-gray-800">
                Choose Your Plan
            </h1>
            <p class="text-gray-600 text-center mt-1 text-xs md:text-sm">
                {{ hasActiveSubscription ? 'Manage your subscription' : 'Get access to premium features' }}
            </p>
        </div>

        <!-- Main Card -->
        <div class="w-full max-w-3xl">
            <!-- Blocked Message Card -->
            <div v-if="!canSubscribe && paymentStep === 'select'" class="bg-white rounded-xl shadow-md p-8">
                <div class="text-center max-w-md mx-auto">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center">
                        <Icon :icon="hasActiveSubscription ? 'mdi:check' : 'mdi:clock-outline'" class="text-2xl"
                            :class="hasActiveSubscription ? 'text-teal-600' : 'text-blue-600'" />
                    </div>
                    <h2 class="text-lg font-medium text-gray-900 mb-2">
                        {{ hasActiveSubscription ? 'Active Subscription' : 'Request Pending' }}
                    </h2>
                    <p class="text-sm text-gray-500 mb-6">
                        {{ hasActiveSubscription
                            ? 'You have an active subscription. Head to your dashboard to manage it.'
                            : 'Your subscription request is being reviewed by our team.'
                        }}
                    </p>
                    <button @click="goToDashboard"
                        class="w-full sm:w-auto px-6 py-2.5 bg-brand-blue text-white text-sm font-medium rounded-lg hover:bg-brand-blue/80 transition-all duration-200 active:scale-[0.98]">
                        Go to Dashboard
                    </button>
                </div>
            </div>

            <!-- Plan Selection -->
            <template v-else-if="canSubscribe">


                <!-- Plan Grid -->
                <div v-if="paymentStep === 'select'">
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="(plan, index) in plans" :key="plan.id" class="relative">
                            <!-- Popular badge -->
                            <div v-if="index === popularPlanIndex" class="absolute -top-2 left-4 z-10">
                                <span class="bg-brand-teal text-white text-[10px] px-2 py-0.5 rounded-full">
                                    Most popular
                                </span>
                            </div>

                            <!-- Plan Card -->
                            <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-200 hover:-translate-y-1"
                                :class="{ 'border-2 border-teal-500': index === popularPlanIndex }">
                                <div class="p-5">
                                    <h3 class="text-base font-medium text-gray-900 mb-1">{{ plan.name }}</h3>
                                    <div class="flex items-baseline mb-3">
                                        <span class="text-2xl font-light text-gray-900">{{ formatPrice(plan.price)
                                        }}</span>
                                        <span class="text-xs text-gray-500 ml-1">/{{ plan.duration_days }} days</span>
                                    </div>

                                    <div class="border-t border-gray-100 pt-3 mt-2">
                                        <p class="text-[10px] font-medium text-gray-500 uppercase tracking-wider mb-2">
                                            Features
                                        </p>
                                        <ul class="space-y-1.5">
                                            <li v-for="feature in plan.features" :key="feature"
                                                class="flex items-start text-xs">
                                                <Icon icon="mdi:check" class="text-teal-500 text-xs mt-0.5 mr-1.5" />
                                                <span class="text-gray-600">{{ feature }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="px-5 pb-5 pt-0">
                                    <button @click="choosePlan(plan)"
                                        class="w-full py-2 px-3 rounded-lg text-xs font-medium transition-all duration-200 active:scale-[0.98]"
                                        :class="index === popularPlanIndex
                                            ? 'bg-brand-blue text-white hover:bg-brand-blue/80'
                                            : 'border border-gray-300 text-gray-700 hover:border-teal-500 hover:text-teal-600'">
                                        Select {{ plan.name }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Trust badges -->
                    <div class="mt-6 flex items-center justify-center gap-6 text-xs text-gray-400">
                        <span class="flex items-center gap-1">
                            <Icon icon="mdi:shield-outline" class="text-sm text-teal-500" />
                            Secure payment
                        </span>
                        <span class="flex items-center gap-1">
                            <Icon icon="mdi:lock-outline" class="text-sm text-blue-500" />
                            Encrypted data
                        </span>
                        <span class="flex items-center gap-1">
                            <Icon icon="mdi:headset" class="text-sm text-teal-500" />
                            24/7 support
                        </span>
                    </div>
                </div>

                <!-- Payment Form -->
                <!-- Breadcrumb -->
                <div v-if="paymentStep !== 'select'" class="mb-4 max-w-xl mx-auto">
                    <button @click="goBack"
                        class="flex items-center text-xs text-gray-500 hover:text-gray-700 transition-colors">
                        <Icon icon="mdi:arrow-left" class="text-sm " />
                        Back to plans
                    </button>
                </div>
                <div v-if="paymentStep === 'payment' && selectedPlan"
                    class="bg-white rounded-xl shadow-md overflow-hidden max-w-xl mx-auto py-4">
                    <!-- Progress Steps -->
                    <div class="border-b border-gray-100 bg-gray-50/50 px-5 py-3">
                        <div class="flex items-center justify-between max-w-sm mx-auto">
                            <div class="flex items-center">
                                <div class="w-5 h-5 rounded-full bg-teal-600 flex items-center justify-center">
                                    <Icon icon="mdi:check" class="text-xs text-white" />
                                </div>
                                <span class="ml-1.5 text-xs text-gray-600">Plan</span>
                            </div>
                            <div class="w-12 h-px bg-gray-300"></div>
                            <div class="flex items-center">
                                <div class="w-5 h-5 rounded-full bg-brand-teal flex items-center justify-center">
                                    <span class="text-xs text-white">2</span>
                                </div>
                                <span class="ml-1.5 text-xs font-medium text-gray-900">Payment</span>
                            </div>
                            <div class="w-12 h-px bg-gray-300"></div>
                            <div class="flex items-center">
                                <div class="w-5 h-5 rounded-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-xs text-gray-400">3</span>
                                </div>
                                <span class="ml-1.5 text-xs text-gray-400">Done</span>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary Card -->
                    <div class="p-5 bg-brand-blue/10 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-gray-500 mb-0.5">Selected Plan</p>
                                <p class="text-base font-medium text-gray-900">{{ selectedPlan.name }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ selectedPlan.duration_days }} days access</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500 mb-0.5">Total</p>
                                <p class="text-xl font-light text-blue-700">{{ formatPrice(selectedPlan.price) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Form -->
                    <div class="p-5 space-y-5">
                        <!-- Payment Method -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-2">
                                Payment method
                            </label>
                            <div class="grid grid-cols-3 gap-2">
                                <button v-for="method in paymentMethods" :key="method.id" type="button"
                                    @click="form.payment_method = method.id"
                                    class="flex flex-col items-center p-2.5 border rounded-lg transition-all duration-200"
                                    :class="form.payment_method === method.id
                                        ? 'border-blue-500 bg-blue-50/50 shadow-sm'
                                        : 'border-gray-200 hover:border-blue-300 hover:bg-gray-50'">
                                    <Icon :icon="method.icon" class="text-base mb-1"
                                        :class="form.payment_method === method.id ? 'text-blue-600' : 'text-gray-500'" />
                                    <span class="text-[10px]"
                                        :class="form.payment_method === method.id ? 'text-blue-700 font-medium' : 'text-gray-500'">
                                        {{ method.name }}
                                    </span>
                                </button>
                            </div>
                        </div>

                        <!-- Instructions (with QR and TILL ID for JazzCash) -->
                        <div v-if="form.payment_method" class="p-3 rounded-lg border border-blue-100">
                            <h4 class="text-xs font-medium text-brand-blue mb-2">Payment Instructions</h4>

                            <!-- JazzCash specific: QR Code + TILL ID -->
                            <div v-if="form.payment_method === 'jazzcash'">
                                <div class="flex flex-col items-center gap-2 mb-3">
                                    <!-- QR Code Image (replace with your actual QR code URL) -->
                                    <img src="/images/jazzcashQR.jpeg" alt="JazzCash QR Code"
                                        class="w-32 h-32 sm:w-40 sm:h-40 object-contain border rounded-lg p-1 bg-white shadow-sm" />
                                    <!-- TILL ID -->
                                    <div class="text-center">
                                        <p class="text-xs text-gray-500">TILL ID / Merchant ID</p>
                                        <p class="text-sm font-mono font-medium text-gray-800">982295010</p>
                                        <button @click="copyTillId"
                                            class="mt-1 text-[10px] text-blue-600 hover:underline flex items-center gap-1 mx-auto">
                                            <Icon icon="mdi:content-copy" class="text-xs" />
                                            Copy TILL ID
                                        </button>
                                    </div>
                                </div>
                                <p class="text-xs text-brand-blue/90">
                                    Send the exact amount to the TILL ID above using JazzCash app, then upload the
                                    receipt.
                                </p>
                            </div>

                            <!-- Default instructions for other methods -->
                            <p v-else class="text-xs text-brand-blue/90">{{ currentPaymentMethod.instructions }}</p>
                        </div>

                        <!-- File Upload -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-2">
                                Upload payment receipt
                            </label>

                            <!-- Dropzone -->
                            <div v-if="!uploadedFileName" @drop="handleDrop" @dragover="handleDragOver"
                                @dragleave="handleDragLeave" class="relative">
                                <input type="file" @change="handleFileUpload" accept="image/*,.pdf" class="hidden"
                                    id="receipt-upload" />
                                <label for="receipt-upload"
                                    class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed rounded-lg cursor-pointer transition-all duration-200"
                                    :class="isDragging
                                        ? 'border-blue-500 bg-blue-50/50'
                                        : 'border-gray-300 hover:border-blue-400 hover:bg-blue-50'">
                                    <Icon icon="mdi:cloud-upload" class="text-2xl mb-1"
                                        :class="isDragging ? 'text-blue-600' : 'text-gray-400'" />
                                    <span class="text-xs font-medium text-gray-700">
                                        {{ isDragging ? 'Drop your file here' : 'Click or drag to upload' }}
                                    </span>
                                    <span class="text-[10px] text-gray-400 mt-0.5">
                                        PNG, JPG, PDF (max 5MB)
                                    </span>
                                </label>
                            </div>

                            <!-- File Preview -->
                            <div v-else class="border border-gray-200 rounded-lg p-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 bg-teal-50 rounded flex items-center justify-center">
                                            <Icon icon="mdi:file-document" class="text-base text-teal-600" />
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-900">{{ uploadedFileName }}</p>
                                            <p class="text-[10px] text-gray-400">
                                                {{ formatFileSize(form.receipt?.size) }}
                                            </p>
                                        </div>
                                    </div>
                                    <button @click="removeFile" class="p-1 hover:bg-gray-100 rounded transition-colors">
                                        <Icon icon="mdi:close" class="text-sm text-gray-400" />
                                    </button>
                                </div>
                            </div>

                            <p v-if="form.errors.receipt" class="mt-1 text-[10px] text-red-600">
                                {{ form.errors.receipt }}
                            </p>
                        </div>

                        <!-- Terms & Submit -->
                        <div class="space-y-3 pt-2">
                            <label class="flex items-start sm:items-center">
                                <input type="checkbox" v-model="form.terms_accepted"
                                    class="mt-1 sm:mt-0 w-4 h-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500 flex-shrink-0" />
                                <span class="ml-2 sm:ml-3 text-xs sm:text-sm text-gray-600 leading-relaxed">
                                    I agree to the
                                    <Link :href="route('policy.show', 'terms')"
                                        class="text-blue-600 hover:text-blue-700 font-medium whitespace-nowrap">
                                    Terms of Service</Link>
                                    and
                                    <Link :href="route('policy.show', 'privacy')"
                                        class="text-blue-600 hover:text-blue-700 font-medium whitespace-nowrap">
                                    Privacy Policy</Link>
                                </span>
                            </label>

                            <button @click="submit" :disabled="form.processing || !form.receipt || !form.terms_accepted"
                                class="w-full bg-brand-blue text-white py-2.5 px-4 rounded-lg text-xs font-medium hover:bg-brand-blue/80 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 active:scale-[0.98]">
                                <span v-if="form.processing" class="flex items-center justify-center gap-2">
                                    <Icon icon="mdi:loading" class="animate-spin" />
                                    Processing...
                                </span>
                                <span v-else>
                                    Complete Payment
                                </span>
                            </button>

                            <p class="text-[10px] text-center text-gray-400">
                                By completing this payment, you agree to our terms of service
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Confirmation -->
                <div v-if="paymentStep === 'confirmation'" class="bg-white rounded-xl shadow-md p-8">
                    <div class="text-center max-w-sm mx-auto">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center">
                            <Icon icon="mdi:check" class="text-2xl text-teal-600" />
                        </div>

                        <h2 class="text-lg font-medium text-gray-900 mb-2">Payment submitted</h2>
                        <p class="text-xs text-gray-500 mb-5">{{ successMessage }}</p>

                        <!-- Status Timeline -->
                        <div class="rounded-lg p-4 mb-5">
                            <div class="flex items-center justify-between text-xs mb-3">
                                <span class="text-gray-600">Status</span>
                                <span class="font-medium text-blue-600">Pending verification</span>
                            </div>
                            <div class="relative">
                                <div class="absolute top-1.5 left-0 w-full h-0.5 bg-gray-200"></div>
                                <div class="relative flex justify-between">
                                    <div class="flex flex-col items-center">
                                        <div class="w-3 h-3 bg-teal-600 rounded-full mb-1"></div>
                                        <span class="text-[10px] text-gray-500">Submitted</span>
                                    </div>
                                    <div class="flex flex-col items-center">
                                        <div class="w-3 h-3 bg-blue-400 rounded-full mb-1"></div>
                                        <span class="text-[10px] text-gray-500">Verifying</span>
                                    </div>
                                    <div class="flex flex-col items-center">
                                        <div class="w-3 h-3 bg-gray-300 rounded-full mb-1"></div>
                                        <span class="text-[10px] text-gray-400">Approved</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <button @click="goHome"
                                class="w-full bg-brand-blue text-white py-2.5 px-4 rounded-lg text-xs font-medium hover:bg-brand-blue/70 transition-all duration-200 active:scale-[0.98]">
                                Go to dashboard
                            </button>
                            <button @click="goBack"
                                class="w-full border border-gray-300 text-gray-700 py-2.5 px-4 rounded-lg text-xs font-medium hover:border-teal-500 hover:text-teal-600 transition-colors">
                                Choose another plan
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <!-- App Download Links -->
            <div v-if="canSubscribe && paymentStep === 'select'" class="mt-6 text-center">
                <p class="text-xs text-gray-600 mb-2">Get the app</p>
                <div class="flex justify-center space-x-2">
                    <a href="#" class="inline-block">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg"
                            alt="Google Play" class="h-8">
                    </a>
                    <a href="#" class="inline-block">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/3/3c/Download_on_the_App_Store_Badge.svg"
                            alt="App Store" class="h-8">
                    </a>
                </div>
            </div>
        </div>

        <!-- Footer Links -->
        <div class="mt-6 md:mt-8 text-center">
            <div class="flex flex-wrap justify-center gap-3 text-[10px] text-gray-500">
                <a href="#" class="hover:text-gray-700 transition-colors">Help</a>
                <a href="#" class="hover:text-gray-700 transition-colors">Privacy</a>
                <a href="#" class="hover:text-gray-700 transition-colors">Terms</a>
                <a href="#" class="hover:text-gray-700 transition-colors">Blog</a>
                <a href="#" class="hover:text-gray-700 transition-colors">Careers</a>
                <a href="#" class="hover:text-gray-700 transition-colors">About</a>
            </div>
            <p class="mt-2 text-[10px] text-gray-500">
                © {{ new Date().getFullYear() }} AMO Mercatus. All rights reserved.
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
    plans: Array
})

// Get auth user from page props
const page = usePage()
const user = computed(() => page.props.auth?.user)

console.log(page.props)

const selectedPlan = ref(null)
const paymentStep = ref('select')
const showSuccessMessage = ref(false)
const successMessage = ref('')
const uploadedFileName = ref('')
const isDragging = ref(false)

const form = useForm({
    plan_id: null,
    payment_method: 'jazzcash',
    receipt: null,
    terms_accepted: false
})

// Check subscription status
const hasPendingSubscription = computed(() => {
    return user.value?.subscription_status === 'pending'
})

const hasActiveSubscription = computed(() => {
    return user.value?.subscription_status === 'active'
})

const hasExpiredSubscription = computed(() => {
    return user.value?.subscription_status === 'expired'
})

const hasNoSubscription = computed(() => {
    return !user.value?.subscription_status || user.value?.subscription_status === 'none' || user.value?.subscription_status === null
})

// Popular plan detection
const popularPlanIndex = computed(() => {
    return props.plans?.length === 3 ? 1 : 0
})

const paymentMethods = [
    {
        id: 'jazzcash',
        name: 'JazzCash',
        icon: 'mdi:cash',
        instructions: 'Send payment to the TILL ID above using JazzCash app.',
    },
    {
        id: 'easypaisa',
        name: 'Easypaisa',
        icon: 'mdi:bank-transfer',
        instructions: 'Send payment to 0300-1234567 (Easypaisa account)',
    },
    {
        id: 'bank',
        name: 'Bank Transfer',
        icon: 'mdi:bank',
        instructions: 'Bank: HBL | Account: 1234-5678-9012-3456 | IBAN: PK12HBL1234567890123456',
    }
]

function choosePlan(plan) {
    selectedPlan.value = plan
    form.plan_id = plan.id
    paymentStep.value = 'payment'
    window.scrollTo({ top: 0, behavior: 'smooth' })
}

function handleFileUpload(event) {
    const file = event.target.files[0]
    if (file) {
        form.receipt = file
        uploadedFileName.value = file.name
    }
}

function handleDrop(event) {
    event.preventDefault()
    isDragging.value = false

    const file = event.dataTransfer.files[0]
    if (file && (file.type.startsWith('image/') || file.type === 'application/pdf')) {
        form.receipt = file
        uploadedFileName.value = file.name
    }
}

function handleDragOver(event) {
    event.preventDefault()
    isDragging.value = true
}

function handleDragLeave() {
    isDragging.value = false
}

function removeFile() {
    form.receipt = null
    uploadedFileName.value = ''
}

// Copy TILL ID to clipboard
function copyTillId() {
    navigator.clipboard.writeText('1234567890')
    // Optional: show a small toast message
    alert('TILL ID copied!')
}

function submit() {
    form.post('/subscriptions/manual', {
        onSuccess: (response) => {
            router.visit('/')
            showSuccessMessage.value = true
            successMessage.value = response.props?.flash?.message || 'Payment submitted successfully. We\'ll notify you once it\'s verified.'
            paymentStep.value = 'confirmation'
            form.reset()
            uploadedFileName.value = ''

            setTimeout(() => {
                showSuccessMessage.value = false
            }, 5000)
        },
        onError: (errors) => {
            console.error('Payment submission failed', errors)
        }
    })
}

function goBack() {
    paymentStep.value = 'select'
    selectedPlan.value = null
    form.reset()
    uploadedFileName.value = ''
    showSuccessMessage.value = false
}

function goHome() {
    router.visit('/')
}

function goToDashboard() {
    router.visit('/')
}

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-PK', {
        style: 'currency',
        currency: 'PKR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(price).replace('PKR', '₨')
}

// Get current payment method details
const currentPaymentMethod = computed(() => {
    return paymentMethods.find(m => m.id === form.payment_method) || paymentMethods[0]
})

// Check if user can subscribe
const canSubscribe = computed(() => {
    return hasNoSubscription.value || hasExpiredSubscription.value
})

// Format file size
const formatFileSize = (bytes) => {
    if (!bytes) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i]
}
</script>

<style scoped>
/* Smooth transitions */
* {
    transition: all 0.2s ease-in-out;
}

/* Custom focus styles */
input:focus {
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

@keyframes spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* Custom scrollbar for form */
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
    background: #2563eb;
}
</style>