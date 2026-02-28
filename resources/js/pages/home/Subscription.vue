<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { usePage } from '@inertiajs/vue3'
const props = defineProps({
    plans: Array
})

useForceTheme('light');

// Get auth user from page props
const user = computed(() => page.props.auth?.user)
const page = usePage();
console.log('User subscription status:', page.props)
const selectedPlan = ref(null)
const paymentStep = ref('select') // select, payment, confirmation
const showSuccessMessage = ref(false)
const successMessage = ref('')
const uploadedFileName = ref('')

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
        instructions: 'Send payment to 0300-1234567 (JazzCash account)',
        color: 'bg-emerald-500',
        bgColor: 'bg-emerald-50',
        borderColor: 'border-emerald-200',
        textColor: 'text-emerald-700'
    },
    {
        id: 'easypaisa',
        name: 'Easypaisa',
        icon: 'mdi:bank-transfer',
        instructions: 'Send payment to 0300-1234567 (Easypaisa account)',
        color: 'bg-blue-500',
        bgColor: 'bg-blue-50',
        borderColor: 'border-blue-200',
        textColor: 'text-blue-700'
    },
    {
        id: 'bank',
        name: 'Bank Transfer',
        icon: 'mdi:bank',
        instructions: 'Bank: HBL | Account: 1234-5678-9012-3456 | IBAN: PK12HBL1234567890123456',
        color: 'bg-purple-500',
        bgColor: 'bg-purple-50',
        borderColor: 'border-purple-200',
        textColor: 'text-purple-700'
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
    form.receipt = file
    uploadedFileName.value = file ? file.name : ''
}

function submit() {
    form.post('/subscriptions/manual', {
        onSuccess: (response) => {
            showSuccessMessage.value = true
            successMessage.value = response.props?.flash?.message || 'Payment submitted successfully! We\'ll notify you once it\'s verified.'
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
    router.visit('/dashboard')
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
</script>

<template>
    <div class="max-w-8/10 mx-auto  space-y-12 pb-20 pt-10">

        <!-- Success Toast Notification - Compact -->
        <transition enter-active-class="transform transition duration-300 ease-out"
            enter-from-class="translate-x-full opacity-0" enter-to-class="translate-x-0 opacity-100"
            leave-active-class="transform transition duration-200 ease-in" leave-from-class="translate-x-0 opacity-100"
            leave-to-class="translate-x-full opacity-0">

            <div v-if="showSuccessMessage"
                class="fixed top-3 right-3 z-50 w-80 bg-white rounded-lg shadow-xl border-l-4 border-emerald-500 overflow-hidden">
                <div class="p-3">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-emerald-500 rounded-full flex items-center justify-center shadow">
                                <Icon icon="mdi:check" class="text-lg text-white" />
                            </div>
                        </div>
                        <div class="ml-2 flex-1">
                            <p class="text-xs font-medium text-gray-900">
                                Payment Submitted Successfully!
                            </p>
                            <p class="mt-0.5 text-xs text-gray-600 leading-relaxed">
                                {{ successMessage }}
                            </p>
                            <div class="mt-2 flex gap-1.5">
                                <button @click="goHome"
                                    class="px-2.5 py-1 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-xs font-medium rounded hover:from-blue-700 hover:to-blue-800 shadow-sm transition-all">
                                    Dashboard
                                </button>
                                <button @click="showSuccessMessage = false"
                                    class="px-2.5 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded hover:bg-gray-200 transition-colors">
                                    Dismiss
                                </button>
                            </div>
                        </div>
                        <button @click="showSuccessMessage = false" class="flex-shrink-0 ml-1">
                            <Icon icon="mdi:close" class="text-gray-400 hover:text-gray-600 text-sm" />
                        </button>
                    </div>
                </div>
                <!-- Progress bar -->
                <div class="h-0.5 bg-emerald-500 animate-progress"></div>
            </div>
        </transition>

        <!-- Header Section - Compact -->
        <div class="max-w-6xl mx-auto text-center mb-10 relative">
            <div class="absolute inset-0 flex items-center justify-center">
                <div
                    class="w-48 h-48 bg-blue-500 rounded-full mix-blend-multiply filter blur-2xl opacity-10 animate-blob">
                </div>
                <div
                    class="w-48 h-48 bg-emerald-500 rounded-full mix-blend-multiply filter blur-2xl opacity-10 animate-blob animation-delay-2000">
                </div>
                <div
                    class="w-48 h-48 bg-purple-500 rounded-full mix-blend-multiply filter blur-2xl opacity-10 animate-blob animation-delay-4000">
                </div>
            </div>

            <!-- Welcome message with user name -->
            <div class="relative mb-2">
                <span class="text-[10px] font-medium text-blue-600 uppercase tracking-wider">Welcome back, {{ user?.name
                }}!</span>
            </div>

            <h1
                class="relative text-2xl md:text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 via-emerald-500 to-purple-600 sm:text-4xl animate-gradient">
                {{ hasActiveSubscription ? 'Your Premium Access' : 'Choose Your Perfect Plan' }}
            </h1>
            <p class="relative mt-2 text-sm text-gray-600 max-w-3xl mx-auto">
                {{ hasActiveSubscription ?
                    'Manage your subscription or explore new features' : 'Unlock premium features and take your experience'
                    + 'to the next level' }}
            </p>

            <!-- Trust indicators (only show if can subscribe) - Compact -->
            <div v-if="canSubscribe"
                class="relative mt-4 flex flex-wrap items-center justify-center gap-3 text-[10px] text-gray-500">
                <span class="flex items-center gap-1">
                    <Icon icon="mdi:shield-check-outline" class="text-emerald-500 text-sm" />
                    30-day guarantee
                </span>
                <span class="flex items-center gap-1">
                    <Icon icon="mdi:credit-card-check-outline" class="text-blue-500 text-sm" />
                    Secure payments
                </span>
                <span class="flex items-center gap-1">
                    <Icon icon="mdi:headset" class="text-purple-500 text-sm" />
                    24/7 support
                </span>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto relative z-10">
            <!-- Show blocked message if already subscribed or pending - Compact -->
            <div v-if="!canSubscribe && paymentStep === 'select'" class="text-center">
                <div class="bg-white rounded-xl shadow-lg p-8 max-w-lg mx-auto">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center"
                        :class="hasActiveSubscription ? 'bg-emerald-100' : 'bg-yellow-100'">
                        <Icon :icon="hasActiveSubscription ? 'mdi:check-decagram' : 'mdi:clock-outline'"
                            class="text-3xl" :class="hasActiveSubscription ? 'text-emerald-600' : 'text-yellow-600'" />
                    </div>

                    <h2 class="text-lg font-semibold text-gray-900 mb-2">
                        {{ hasActiveSubscription ? 'Active Subscription' : 'Request Pending' }}
                    </h2>

                    <p class="text-xs text-gray-600 mb-4 max-w-md mx-auto">
                        {{ hasActiveSubscription
                            ? 'You already have an active subscription. Enjoy all the premium features!'
                            : 'Your subscription request is being reviewed. This usually takes 24-48 hrs.' }}
                    </p>

                    <div class="space-y-3">
                        <button @click="goToDashboard"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg text-xs font-medium hover:from-blue-700 hover:to-purple-700 transform hover:scale-105 transition-all shadow">
                            <Icon icon="mdi:view-dashboard" class="text-sm" />
                            Go to Dashboard
                        </button>

                        <!-- Show pending details if applicable - Compact -->
                        <div v-if="hasPendingSubscription" class="mt-4 p-3 bg-yellow-50 rounded-lg">
                            <div class="flex items-center gap-1 text-yellow-700 mb-1.5">
                                <Icon icon="mdi:information" class="text-xs" />
                                <span class="text-xs font-medium">What happens next?</span>
                            </div>
                            <ul class="text-[10px] text-yellow-600 space-y-1 text-left">
                                <li class="flex items-start gap-1">
                                    <Icon icon="mdi:check-circle" class="text-[8px] mt-0.5" />
                                    <span>Admin will verify your payment</span>
                                </li>
                                <li class="flex items-start gap-1">
                                    <Icon icon="mdi:check-circle" class="text-[8px] mt-0.5" />
                                    <span>Email confirmation once approved</span>
                                </li>
                                <li class="flex items-start gap-1">
                                    <Icon icon="mdi:check-circle" class="text-[8px] mt-0.5" />
                                    <span>Instant access after approval</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Show active subscription details - Compact -->
                        <div v-if="hasActiveSubscription" class="mt-4 p-3 bg-emerald-50 rounded-lg">
                            <div class="flex items-center gap-1 text-emerald-700 mb-1.5">
                                <Icon icon="mdi:crown" class="text-xs" />
                                <span class="text-xs font-medium">Your Benefits</span>
                            </div>
                            <ul class="text-[10px] text-emerald-600 space-y-1 text-left">
                                <li class="flex items-start gap-1">
                                    <Icon icon="mdi:check-circle" class="text-[8px] mt-0.5" />
                                    <span>All premium features</span>
                                </li>
                                <li class="flex items-start gap-1">
                                    <Icon icon="mdi:check-circle" class="text-[8px] mt-0.5" />
                                    <span>Priority support</span>
                                </li>
                                <li class="flex items-start gap-1">
                                    <Icon icon="mdi:check-circle" class="text-[8px] mt-0.5" />
                                    <span>Early access to new features</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Plan Selection Step (only show if can subscribe) -->
            <template v-else-if="canSubscribe">
                <!-- Back button with animation - Compact -->
                <transition enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0 -translate-y-20" enter-to-class="opacity-100 translate-y-0">
                    <button v-if="paymentStep !== 'select'" @click="goBack"
                        class="mb-4 inline-flex items-center text-gray-600 hover:text-gray-900 group text-xs">
                        <span
                            class="w-6 h-6 rounded-full bg-white shadow-sm group-hover:shadow flex items-center justify-center mr-1.5 transition-all">
                            <Icon icon="mdi:arrow-left"
                                class="text-sm group-hover:-translate-x-0.5 transition-transform" />
                        </span>
                        <span class="font-medium">Back to Plans</span>
                    </button>
                </transition>

                <!-- Plan Selection Step -->
                <transition mode="out-in" enter-active-class="transition duration-500 ease-out"
                    enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition duration-300 ease-in" leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 translate-y-4">

                    <div v-if="paymentStep === 'select'" class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">

                        <div v-for="(plan, index) in plans" :key="plan.id"
                            class="relative group transform hover:-translate-y-1 transition-all duration-300">

                            <!-- Popular Badge - Compact -->
                            <div v-if="index === popularPlanIndex"
                                class="absolute -top-3 left-1/2 transform -translate-x-1/2 z-20">
                                <span
                                    class="bg-gradient-to-r from-blue-600 to-purple-600 text-white text-[8px] font-bold px-2 py-1 rounded-full shadow flex items-center gap-0.5">
                                    <Icon icon="mdi:star" class="text-yellow-300 text-[10px]" />
                                    POPULAR
                                    <Icon icon="mdi:star" class="text-yellow-300 text-[10px]" />
                                </span>
                            </div>

                            <!-- Plan Card - Compact -->
                            <div class="bg-white rounded-xl shadow hover:shadow-lg transition-shadow duration-300 h-full flex flex-col overflow-hidden border"
                                :class="index === popularPlanIndex ? 'border-blue-500' : 'border-transparent hover:border-gray-200'">

                                <!-- Card Header with gradient -->
                                <div class="p-4 pb-3"
                                    :class="index === popularPlanIndex ? 'bg-gradient-to-br from-blue-50 to-purple-50' : 'bg-gray-50'">
                                    <h3 class="text-base font-semibold text-gray-900 mb-1">{{ plan.name }}</h3>
                                    <div class="flex items-baseline">
                                        <span class="text-xl font-bold text-gray-900">{{ formatPrice(plan.price)
                                        }}</span>
                                        <span class="text-[10px] text-gray-500 ml-1">/{{ plan.duration_days }}d</span>
                                    </div>

                                    <!-- Savings badge for yearly plans - Compact -->
                                    <div v-if="plan.duration_days >= 360"
                                        class="mt-1 inline-block px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[8px] font-medium rounded-full">
                                        Save 20%
                                    </div>
                                </div>

                                <!-- Features List - Compact -->
                                <div class="p-4 flex-1">
                                    <p class="text-[10px] font-medium text-gray-700 mb-2">What's included:</p>
                                    <ul class="space-y-2">
                                        <li v-for="feature in plan.features" :key="feature"
                                            class="flex items-start group">
                                            <div
                                                class="flex-shrink-0 w-4 h-4 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-500 flex items-center justify-center mt-0.5 shadow-sm">
                                                <Icon icon="mdi:check" class="text-white text-[8px]" />
                                            </div>
                                            <span
                                                class="ml-2 text-[10px] text-gray-600 group-hover:text-gray-900 transition-colors">{{
                                                    feature }}</span>
                                        </li>
                                    </ul>
                                </div>

                                <!-- Plan Footer - Compact -->
                                <div class="p-4 pt-0">
                                    <button @click="choosePlan(plan)"
                                        class="w-full py-2 px-3 rounded-lg font-medium text-xs transform hover:scale-105 transition-all duration-200 shadow-sm hover:shadow"
                                        :class="index === popularPlanIndex
                                            ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white hover:from-blue-700 hover:to-purple-700'
                                            : 'bg-gradient-to-r from-gray-800 to-gray-900 text-white hover:from-gray-900 hover:to-black'">
                                        <span class="flex items-center justify-center gap-1">
                                            Get Started
                                            <Icon icon="mdi:arrow-right" class="text-sm" />
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </transition>

                <!-- Payment Form Step - Compact -->
                <transition mode="out-in" enter-active-class="transition duration-500 ease-out"
                    enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition duration-300 ease-in" leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 translate-y-4">

                    <div v-if="paymentStep === 'payment' && selectedPlan" class="max-w-2xl mx-auto">
                        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                            <!-- Progress Steps with animation - Compact -->
                            <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-4 py-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center flex-1">
                                        <div class="flex items-center">
                                            <div
                                                class="w-7 h-7 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm">
                                                <span class="text-white text-xs font-bold">1</span>
                                            </div>
                                            <div class="ml-2">
                                                <p class="text-white/60 text-[8px]">Step 1</p>
                                                <p class="text-white text-[10px] font-medium">Choose Plan</p>
                                            </div>
                                        </div>
                                        <div class="flex-1 mx-2 h-0.5 bg-white/20 relative">
                                            <div class="absolute inset-0 bg-white rounded-full" style="width: 100%">
                                            </div>
                                        </div>
                                        <div class="flex items-center">
                                            <div
                                                class="w-7 h-7 bg-white rounded-full flex items-center justify-center shadow">
                                                <span class="text-blue-600 text-xs font-bold">2</span>
                                            </div>
                                            <div class="ml-2">
                                                <p class="text-white/60 text-[8px]">Step 2</p>
                                                <p class="text-white text-[10px] font-medium">Payment</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Form - Compact -->
                            <div class="p-5">
                                <!-- Selected Plan Summary - Compact -->
                                <div
                                    class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg p-3 mb-4 flex items-center justify-between">
                                    <div>
                                        <p class="text-[10px] text-gray-500">Selected Plan</p>
                                        <p class="text-sm font-semibold text-gray-900">{{ selectedPlan.name }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] text-gray-500">Amount</p>
                                        <p class="text-base font-bold text-blue-600">{{ formatPrice(selectedPlan.price)
                                        }}</p>
                                    </div>
                                </div>

                                <!-- Payment Method Selection - Compact -->
                                <div class="mb-4">
                                    <label class="block text-xs font-medium text-gray-700 mb-2">
                                        Choose Payment Method
                                    </label>
                                    <div class="grid grid-cols-3 gap-2">
                                        <button v-for="method in paymentMethods" :key="method.id" type="button"
                                            @click="form.payment_method = method.id"
                                            class="relative p-2 rounded-lg border transition-all duration-200 group"
                                            :class="form.payment_method === method.id
                                                ? method.borderColor + ' ' + method.bgColor + ' shadow'
                                                : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'">
                                            <div class="text-center">
                                                <div class="w-8 h-8 mx-auto mb-1 rounded-full flex items-center justify-center"
                                                    :class="form.payment_method === method.id ? method.color + ' text-white' : 'bg-gray-100 text-gray-500'">
                                                    <Icon :icon="method.icon" class="text-base" />
                                                </div>
                                                <span class="text-[8px] font-medium"
                                                    :class="form.payment_method === method.id ? method.textColor : 'text-gray-600'">
                                                    {{ method.name }}
                                                </span>
                                            </div>

                                            <!-- Selected checkmark - Compact -->
                                            <div v-if="form.payment_method === method.id"
                                                class="absolute -top-1 -right-1 w-4 h-4 bg-green-500 rounded-full flex items-center justify-center border border-white">
                                                <Icon icon="mdi:check" class="text-white text-[8px]" />
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                <!-- Payment Instructions - Compact -->
                                <transition enter-active-class="transition duration-300"
                                    enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100">
                                    <div v-if="form.payment_method" class="mb-4 p-3 rounded-lg"
                                        :class="currentPaymentMethod.bgColor + ' ' + currentPaymentMethod.borderColor"
                                        :style="{ borderWidth: '1px' }">

                                        <div class="flex items-start gap-2">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0"
                                                :class="currentPaymentMethod.color + ' text-white'">
                                                <Icon icon="mdi:information" class="text-base" />
                                            </div>
                                            <div class="flex-1">
                                                <h4 class="text-xs font-medium mb-1"
                                                    :class="currentPaymentMethod.textColor">
                                                    Payment Instructions
                                                </h4>
                                                <p class="text-[10px] text-gray-600 mb-2">
                                                    {{ currentPaymentMethod.instructions }}
                                                </p>
                                                <div class="flex items-center justify-between p-2 bg-white/50 rounded">
                                                    <span class="text-[8px] font-medium text-gray-700">Amount:</span>
                                                    <span class="text-xs font-bold"
                                                        :class="currentPaymentMethod.textColor">
                                                        {{ formatPrice(selectedPlan.price) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </transition>

                                <!-- File Upload - Compact -->
                                <div class="mb-4">
                                    <label class="block text-xs font-medium text-gray-700 mb-2">
                                        Upload Payment Receipt
                                    </label>
                                    <div class="relative">
                                        <input type="file" @change="handleFileUpload" accept="image/*,.pdf"
                                            class="hidden" id="receipt-upload" />
                                        <label for="receipt-upload"
                                            class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-colors cursor-pointer group">
                                            <Icon icon="mdi:cloud-upload"
                                                class="text-2xl text-gray-400 group-hover:text-blue-500 mb-1 transition-colors" />
                                            <span
                                                class="text-[10px] text-gray-500 group-hover:text-blue-600 font-medium">
                                                Click to upload
                                            </span>
                                            <span class="text-[8px] text-gray-400 mt-0.5">
                                                PNG, JPG, PDF (Max 5MB)
                                            </span>
                                        </label>
                                    </div>

                                    <!-- File preview - Compact -->
                                    <transition enter-active-class="transition duration-200"
                                        enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100">
                                        <div v-if="uploadedFileName"
                                            class="mt-2 flex items-center justify-between p-2 bg-green-50 border border-green-200 rounded">
                                            <div class="flex items-center gap-2">
                                                <div
                                                    class="w-6 h-6 bg-green-100 rounded flex items-center justify-center">
                                                    <Icon icon="mdi:file-document" class="text-green-600 text-xs" />
                                                </div>
                                                <div>
                                                    <p class="text-[10px] font-medium text-gray-700">{{ uploadedFileName
                                                        }}
                                                    </p>
                                                    <p class="text-[8px] text-gray-500">Ready to upload</p>
                                                </div>
                                            </div>
                                            <button @click="form.receipt = null; uploadedFileName = ''"
                                                class="text-gray-400 hover:text-red-500 transition-colors">
                                                <Icon icon="mdi:close" class="text-sm" />
                                            </button>
                                        </div>
                                    </transition>

                                    <div v-if="form.errors.receipt"
                                        class="mt-1 text-[10px] text-red-600 flex items-center gap-1">
                                        <Icon icon="mdi:alert-circle" class="text-xs" />
                                        {{ form.errors.receipt }}
                                    </div>
                                </div>

                                <!-- Terms Checkbox - Compact -->
                                <div class="mb-4">
                                    <label class="flex items-center gap-1.5 cursor-pointer group">
                                        <div class="relative">
                                            <input type="checkbox" v-model="form.terms_accepted"
                                                class="w-3.5 h-3.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer" />
                                        </div>
                                        <span class="text-[10px] text-gray-600 group-hover:text-gray-900">
                                            I confirm payment and agree to
                                            <a href="#"
                                                class="text-blue-600 hover:text-blue-700 font-medium hover:underline">Terms</a>
                                        </span>
                                    </label>
                                </div>

                                <!-- Submit Button - Compact -->
                                <button @click="submit"
                                    :disabled="form.processing || !form.receipt || !form.terms_accepted"
                                    class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-2.5 px-4 rounded-lg font-medium text-xs hover:from-blue-700 hover:to-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-105 transition-all duration-200 shadow hover:shadow flex items-center justify-center gap-1">
                                    <span v-if="form.processing" class="flex items-center gap-1">
                                        <Icon icon="mdi:loading" class="animate-spin text-sm" />
                                        Processing...
                                    </span>
                                    <span v-else class="flex items-center gap-1">
                                        Submit Payment
                                        <Icon icon="mdi:arrow-right" class="text-sm" />
                                    </span>
                                </button>

                                <!-- Security note - Compact -->
                                <p
                                    class="mt-2 text-[8px] text-center text-gray-400 flex items-center justify-center gap-0.5">
                                    <Icon icon="mdi:lock-outline" class="text-[8px]" />
                                    Your payment information is secure
                                </p>
                            </div>
                        </div>
                    </div>
                </transition>

                <!-- Confirmation Step - Compact -->
                <transition mode="out-in" enter-active-class="transition duration-500 ease-out"
                    enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100"
                    leave-active-class="transition duration-300 ease-in" leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95">

                    <div v-if="paymentStep === 'confirmation'" class="max-w-md mx-auto">
                        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                            <!-- Success animation header - Compact -->
                            <div class="bg-gradient-to-r from-emerald-500 to-green-500 p-6 text-center">
                                <div
                                    class="w-16 h-16 mx-auto bg-white rounded-full flex items-center justify-center shadow-lg animate-bounce">
                                    <Icon icon="mdi:check" class="text-3xl text-emerald-500" />
                                </div>
                            </div>

                            <div class="p-5 text-center">
                                <h2 class="text-lg font-semibold text-gray-900 mb-1">
                                    Payment Submitted! 🎉
                                </h2>
                                <p class="text-xs text-gray-600 mb-4">
                                    {{ successMessage }}
                                </p>

                                <!-- Timeline - Compact -->
                                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-1">
                                            <div
                                                class="w-6 h-6 bg-yellow-100 rounded-full flex items-center justify-center">
                                                <Icon icon="mdi:clock-outline" class="text-yellow-600 text-xs" />
                                            </div>
                                            <span class="text-[10px] font-medium text-gray-700">Pending
                                                Verification</span>
                                        </div>
                                        <span class="text-[8px] text-gray-500">~ 24 hrs</span>
                                    </div>
                                    <div class="relative">
                                        <div class="absolute inset-0 flex items-center">
                                            <div class="w-full h-0.5 bg-gray-200"></div>
                                        </div>
                                        <div class="relative flex justify-between">
                                            <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                                            <div class="w-2 h-2 bg-gray-300 rounded-full"></div>
                                            <div class="w-2 h-2 bg-gray-300 rounded-full"></div>
                                        </div>
                                    </div>
                                    <div class="flex justify-between mt-1 text-[8px] text-gray-500">
                                        <span>Submitted</span>
                                        <span>Verifying</span>
                                        <span>Approved</span>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <button @click="goHome"
                                        class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-2 px-3 rounded-lg text-xs font-medium hover:from-blue-700 hover:to-purple-700 transform hover:scale-105 transition-all shadow hover:shadow flex items-center justify-center gap-1">
                                        <Icon icon="mdi:home" class="text-sm" />
                                        Go to Dashboard
                                    </button>
                                    <button @click="goBack"
                                        class="w-full bg-gradient-to-r from-gray-100 to-gray-200 text-gray-700 py-2 px-3 rounded-lg text-xs font-medium hover:from-gray-200 hover:to-gray-300 transform hover:scale-105 transition-all flex items-center justify-center gap-1">
                                        <Icon icon="mdi:refresh" class="text-sm" />
                                        Choose Another Plan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </transition>
            </template>
        </div>
    </div>
</template>

<style scoped>
@keyframes blob {
    0% {
        transform: translate(0px, 0px) scale(1);
    }

    33% {
        transform: translate(30px, -50px) scale(1.1);
    }

    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }

    100% {
        transform: translate(0px, 0px) scale(1);
    }
}

@keyframes gradient {
    0% {
        background-position: 0% 50%;
    }

    50% {
        background-position: 100% 50%;
    }

    100% {
        background-position: 0% 50%;
    }
}

@keyframes progress {
    0% {
        width: 0%;
    }

    100% {
        width: 100%;
    }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animate-gradient {
    background-size: 200% 200%;
    animation: gradient 8s ease infinite;
}

.animate-progress {
    animation: progress 5s linear forwards;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}

/* Smooth transitions */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
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
</style>