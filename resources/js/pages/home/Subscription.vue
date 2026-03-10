<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
    plans: Array
})

// Get auth user from page props
const user = computed(() => page.props.auth?.user)
const page = usePage()
const selectedPlan = ref(null)
const paymentStep = ref('select') // select, payment, confirmation
const showSuccessMessage = ref(false)
const successMessage = ref('')
const isRedirecting = ref(false)

const form = useForm({
    plan_id: null,
    payment_method: 'jazzcash', // Fixed to jazzcash only
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

// JazzCash is the only payment method
const currentPaymentMethod = {
    id: 'jazzcash',
    name: 'JazzCash',
    icon: 'mdi:cash',
    description: 'Pay instantly with JazzCash',
    instructions: 'You will be redirected to JazzCash secure payment gateway',
    color: 'bg-emerald-500',
    bgColor: 'bg-emerald-50',
    borderColor: 'border-emerald-200',
    textColor: 'text-emerald-700',
    isInstant: true
}

function choosePlan(plan) {
    selectedPlan.value = plan
    form.plan_id = plan.id
    paymentStep.value = 'payment'
    window.scrollTo({ top: 0, behavior: 'smooth' })
}
useForceTheme('light');
function submit() {
    if (!form.terms_accepted) {
        return
    }

    isRedirecting.value = true

    // Only JazzCash is available
    form.post('/subscriptions/jazzcash/initiate', {
        onSuccess: () => {
            // The Inertia response will handle the redirect
            // This will show the redirect page
        },
        onError: (errors) => {
            console.error('JazzCash initiation failed', errors)
            isRedirecting.value = false
            showSuccessMessage.value = true
            successMessage.value = 'Failed to initiate payment. Please try again.'

            setTimeout(() => {
                showSuccessMessage.value = false
            }, 5000)
        }
    })
}

function goBack() {
    paymentStep.value = 'select'
    selectedPlan.value = null
    form.reset()
    showSuccessMessage.value = false
    isRedirecting.value = false
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

// Check if user can subscribe
const canSubscribe = computed(() => {
    return hasNoSubscription.value || hasExpiredSubscription.value
})
</script>

<template>
    <div class="max-w-8/10 mx-auto space-y-12 pb-20 pt-10">

        <!-- Success/Error Toast Notification -->
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
                            <p class="text-xs font-medium text-gray-900">Notice</p>
                            <p class="mt-0.5 text-xs text-gray-600 leading-relaxed">{{ successMessage }}</p>
                            <div class="mt-2 flex gap-1.5">
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
                <div class="h-0.5 bg-emerald-500 animate-progress"></div>
            </div>
        </transition>

        <!-- Header Section -->
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
                    'Manage your subscription or explore new features' :
                    'Unlock premium features with instant JazzCash payment' }}
            </p>

            <!-- JazzCash Badge -->
            <div v-if="canSubscribe" class="relative mt-4 flex flex-wrap items-center justify-center gap-3">
                <span
                    class="flex items-center gap-1 px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-[10px] font-medium">
                    <Icon icon="mdi:flash" class="text-sm" />
                    Instant Activation with JazzCash
                </span>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto relative z-10">
            <!-- Show blocked message if already subscribed or pending -->
            <div v-if="!canSubscribe && paymentStep === 'select'" class="text-center">
                <div class="bg-white rounded-xl shadow-lg p-8 max-w-lg mx-auto">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center"
                        :class="hasActiveSubscription ? 'bg-emerald-100' : 'bg-yellow-100'">
                        <Icon :icon="hasActiveSubscription ? 'mdi:check-decagram' : 'mdi:clock-outline'"
                            class="text-3xl" :class="hasActiveSubscription ? 'text-emerald-600' : 'text-yellow-600'" />
                    </div>

                    <h2 class="text-lg font-semibold text-gray-900 mb-2">
                        {{ hasActiveSubscription ? 'Active Subscription' : 'Subscription Pending' }}
                    </h2>

                    <p class="text-xs text-gray-600 mb-4 max-w-md mx-auto">
                        {{ hasActiveSubscription
                            ? 'You already have an active subscription. Enjoy all the premium features!'
                            : 'Your subscription is being processed.' }}
                    </p>

                    <button @click="goToDashboard"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg text-xs font-medium hover:from-blue-700 hover:to-purple-700 transform hover:scale-105 transition-all shadow">
                        <Icon icon="mdi:view-dashboard" class="text-sm" />
                        Go to Home
                    </button>
                </div>
            </div>

            <!-- Plan Selection Step (only show if can subscribe) -->
            <template v-else-if="canSubscribe">
                <!-- Back button -->
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

                            <!-- Popular Badge -->
                            <div v-if="index === popularPlanIndex"
                                class="absolute -top-3 left-1/2 transform -translate-x-1/2 z-20">
                                <span
                                    class="bg-gradient-to-r from-blue-600 to-purple-600 text-white text-[8px] font-bold px-2 py-1 rounded-full shadow flex items-center gap-0.5">
                                    <Icon icon="mdi:star" class="text-yellow-300 text-[10px]" />
                                    POPULAR
                                    <Icon icon="mdi:star" class="text-yellow-300 text-[10px]" />
                                </span>
                            </div>

                            <!-- Plan Card -->
                            <div class="bg-white rounded-xl shadow hover:shadow-lg transition-shadow duration-300 h-full flex flex-col overflow-hidden border"
                                :class="index === popularPlanIndex ? 'border-blue-500' : 'border-transparent hover:border-gray-200'">

                                <!-- Card Header -->
                                <div class="p-4 pb-3"
                                    :class="index === popularPlanIndex ? 'bg-gradient-to-br from-blue-50 to-purple-50' : 'bg-gray-50'">
                                    <h3 class="text-base font-semibold text-gray-900 mb-1">{{ plan.name }}</h3>
                                    <div class="flex items-baseline">
                                        <span class="text-xl font-bold text-gray-900">{{ formatPrice(plan.price)
                                            }}</span>
                                        <span class="text-[10px] text-gray-500 ml-1">/{{ plan.duration_days }}d</span>
                                    </div>

                                    <!-- Savings badge for yearly plans -->
                                    <div v-if="plan.duration_days >= 360"
                                        class="mt-1 inline-block px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[8px] font-medium rounded-full">
                                        Save 20%
                                    </div>
                                </div>

                                <!-- Features List -->
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

                                <!-- Plan Footer -->
                                <div class="p-4 pt-0">
                                    <button @click="choosePlan(plan)"
                                        class="w-full py-2 px-3 rounded-lg font-medium text-xs transform hover:scale-105 transition-all duration-200 shadow-sm hover:shadow flex items-center justify-center gap-1"
                                        :class="index === popularPlanIndex
                                            ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white hover:from-blue-700 hover:to-purple-700'
                                            : 'bg-gradient-to-r from-gray-800 to-gray-900 text-white hover:from-gray-900 hover:to-black'">
                                        <Icon icon="mdi:cash" class="text-sm" />
                                        Pay with JazzCash
                                        <Icon icon="mdi:arrow-right" class="text-sm" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </transition>

                <!-- Payment Form Step - JazzCash Only -->
                <transition mode="out-in" enter-active-class="transition duration-500 ease-out"
                    enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition duration-300 ease-in" leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 translate-y-4">
                    <div v-if="paymentStep === 'payment' && selectedPlan" class="max-w-md mx-auto">
                        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                            <!-- Progress Steps -->
                            <div class="bg-gradient-to-r from-emerald-600 to-emerald-500 px-4 py-3">
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
                                                <span class="text-emerald-600 text-xs font-bold">2</span>
                                            </div>
                                            <div class="ml-2">
                                                <p class="text-white/60 text-[8px]">Step 2</p>
                                                <p class="text-white text-[10px] font-medium">JazzCash</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Form -->
                            <div class="p-6">
                                <!-- Selected Plan Summary -->
                                <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg p-4 mb-6">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-xs text-gray-500">Selected Plan</p>
                                            <p class="text-lg font-semibold text-gray-900">{{ selectedPlan.name }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs text-gray-500">Amount</p>
                                            <p class="text-2xl font-bold text-emerald-600">{{
                                                formatPrice(selectedPlan.price) }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- JazzCash Payment Info -->
                                <div class="mb-6">
                                    <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4">
                                        <div class="flex items-start gap-3">
                                            <div
                                                class="w-12 h-12 bg-emerald-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                <Icon icon="mdi:cash" class="text-2xl text-white" />
                                            </div>
                                            <div class="flex-1">
                                                <h4 class="text-sm font-semibold text-emerald-700 mb-1">
                                                    JazzCash Secure Payment
                                                </h4>
                                                <p class="text-xs text-gray-600 mb-2">
                                                    You'll be redirected to JazzCash's secure payment gateway to
                                                    complete your transaction instantly.
                                                </p>
                                                <div class="flex items-center gap-2 text-[10px] text-emerald-600">
                                                    <span class="flex items-center gap-1">
                                                        <Icon icon="mdi:check-circle" class="text-xs" />
                                                        256-bit SSL
                                                    </span>
                                                    <span class="flex items-center gap-1">
                                                        <Icon icon="mdi:check-circle" class="text-xs" />
                                                        PCI Compliant
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Terms Checkbox -->
                                <div class="mb-6">
                                    <label class="flex items-center gap-2 cursor-pointer group">
                                        <div class="relative">
                                            <input type="checkbox" v-model="form.terms_accepted"
                                                class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer" />
                                        </div>
                                        <span class="text-xs text-gray-600 group-hover:text-gray-900">
                                            I agree to the
                                            <a href="#"
                                                class="text-emerald-600 hover:text-emerald-700 font-medium hover:underline">Terms
                                                of Service</a>
                                            and
                                            <a href="#"
                                                class="text-emerald-600 hover:text-emerald-700 font-medium hover:underline">Privacy
                                                Policy</a>
                                        </span>
                                    </label>
                                </div>

                                <!-- Submit Button -->
                                <button @click="submit"
                                    :disabled="form.processing || !form.terms_accepted || isRedirecting"
                                    class="w-full bg-gradient-to-r from-emerald-600 to-emerald-500 text-white py-3 px-4 rounded-lg font-medium text-sm hover:from-emerald-700 hover:to-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                                    <span v-if="form.processing || isRedirecting" class="flex items-center gap-2">
                                        <Icon icon="mdi:loading" class="animate-spin text-lg" />
                                        Redirecting to JazzCash...
                                    </span>
                                    <span v-else class="flex items-center gap-2">
                                        <Icon icon="mdi:cash" class="text-lg" />
                                        Pay with JazzCash
                                        <Icon icon="mdi:arrow-right" class="text-lg" />
                                    </span>
                                </button>

                                <!-- Security note -->
                                <p
                                    class="mt-4 text-[10px] text-center text-gray-400 flex items-center justify-center gap-1">
                                    <Icon icon="mdi:lock-outline" class="text-xs" />
                                    Your payment is secure and encrypted
                                </p>

                                <!-- JazzCash Partner Badge -->
                                <div class="mt-4 pt-4 border-t border-gray-100 text-center">
                                    <p class="text-[8px] text-gray-400 mb-1">Powered by</p>
                                    <div class="flex items-center justify-center gap-1">
                                        <Icon icon="mdi:cash" class="text-emerald-500 text-base" />
                                        <span class="text-xs font-semibold text-gray-700">Jazz<span
                                                class="text-emerald-500">Cash</span></span>
                                    </div>
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