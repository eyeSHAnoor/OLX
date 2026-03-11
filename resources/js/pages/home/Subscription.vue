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
useForceTheme('light');

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
    color: 'bg-teal-600',
    bgColor: 'bg-teal-50',
    borderColor: 'border-teal-200',
    textColor: 'text-teal-700',
    isInstant: true
}

function choosePlan(plan) {
    selectedPlan.value = plan
    form.plan_id = plan.id
    paymentStep.value = 'payment'
    window.scrollTo({ top: 0, behavior: 'smooth' })
}

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
    <div class="min-h-screen bg-gray-50">
        <!-- Success/Error Toast Notification -->
        <transition enter-active-class="transform transition duration-300 ease-out"
            enter-from-class="translate-x-full opacity-0" enter-to-class="translate-x-0 opacity-100"
            leave-active-class="transform transition duration-200 ease-in" leave-from-class="translate-x-0 opacity-100"
            leave-to-class="translate-x-full opacity-0">
            <div v-if="showSuccessMessage"
                class="fixed top-4 right-4 z-50 w-96 bg-white rounded-lg shadow-xl border-l-4 border-teal-500 overflow-hidden">
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-teal-500 rounded-full flex items-center justify-center">
                                <Icon icon="mdi:check" class="text-xl text-white" />
                            </div>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-gray-900">Notification</p>
                            <p class="mt-1 text-sm text-gray-600">{{ successMessage }}</p>
                            <div class="mt-3">
                                <button @click="showSuccessMessage = false"
                                    class="text-sm font-medium text-teal-600 hover:text-teal-500">
                                    Dismiss
                                </button>
                            </div>
                        </div>
                        <button @click="showSuccessMessage = false" class="flex-shrink-0 ml-4">
                            <Icon icon="mdi:close" class="text-gray-400 hover:text-gray-500" />
                        </button>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Header Section -->
        <div class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="text-center">
                    <div class="mb-4">
                        <span
                            class="inline-flex items-center px-3 py-1 text-sm font-medium text-teal-700 bg-teal-50 rounded-full">
                            Welcome back, {{ user?.name }}
                        </span>
                    </div>

                    <h1 class="text-3xl font-bold text-gray-900 sm:text-4xl">
                        {{ hasActiveSubscription ? 'Your Premium Access' : 'Choose Your Plan' }}
                    </h1>

                    <p class="mt-4 text-lg text-gray-600 max-w-3xl mx-auto">
                        {{ hasActiveSubscription ?
                            'Manage your subscription or explore new features' :
                            'Select the perfect plan for your needs' }}
                    </p>

                    <!-- JazzCash Badge -->
                    <div v-if="canSubscribe" class="mt-6">
                        <span
                            class="inline-flex items-center px-4 py-2 bg-teal-50 text-teal-700 rounded-lg text-sm font-medium">
                            <Icon icon="mdi:flash" class="mr-2 text-teal-500" />
                            Instant Activation with JazzCash
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Show blocked message if already subscribed or pending -->
            <div v-if="!canSubscribe && paymentStep === 'select'" class="max-w-lg mx-auto">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center"
                        :class="hasActiveSubscription ? 'bg-teal-100' : 'bg-blue-100'">
                        <Icon :icon="hasActiveSubscription ? 'mdi:check-decagram' : 'mdi:clock-outline'"
                            class="text-3xl" :class="hasActiveSubscription ? 'text-teal-600' : 'text-blue-600'" />
                    </div>

                    <h2 class="text-xl font-semibold text-gray-900 mb-2">
                        {{ hasActiveSubscription ? 'Active Subscription' : 'Subscription Pending' }}
                    </h2>

                    <p class="text-gray-600 mb-6">
                        {{ hasActiveSubscription
                            ? 'You already have an active subscription. Enjoy all the premium features!'
                            : 'Your subscription is being processed.' }}
                    </p>

                    <button @click="goToDashboard"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                        <Icon icon="mdi:view-dashboard" class="mr-2" />
                        Go to Dashboard
                    </button>
                </div>
            </div>

            <!-- Plan Selection Step (only show if can subscribe) -->
            <template v-else-if="canSubscribe">
                <!-- Back button -->
                <transition enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0 -translate-y-4" enter-to-class="opacity-100 translate-y-0">
                    <button v-if="paymentStep !== 'select'" @click="goBack"
                        class="mb-6 inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
                        <Icon icon="mdi:arrow-left" class="mr-2 text-lg" />
                        Back to Plans
                    </button>
                </transition>

                <!-- Plan Selection Step -->
                <transition mode="out-in" enter-active-class="transition duration-500 ease-out"
                    enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition duration-300 ease-in" leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 translate-y-4">
                    <div v-if="paymentStep === 'select'" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="(plan, index) in plans" :key="plan.id"
                            class="relative transform hover:-translate-y-1 transition-all duration-300">

                            <!-- Popular Badge -->
                            <div v-if="index === popularPlanIndex"
                                class="absolute -top-3 left-1/2 transform -translate-x-1/2 z-10">
                                <span class="bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">
                                    <Icon icon="mdi:star" class="inline mr-1 text-yellow-300" />
                                    MOST POPULAR
                                </span>
                            </div>

                            <!-- Plan Card -->
                            <div class="bg-white rounded-xl shadow-sm border hover:shadow-md transition-shadow duration-300 h-full flex flex-col overflow-hidden"
                                :class="index === popularPlanIndex ? 'border-blue-500' : 'border-gray-200'">

                                <!-- Card Header -->
                                <div class="p-6 border-b border-gray-100"
                                    :class="index === popularPlanIndex ? 'bg-blue-50' : 'bg-gray-50'">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ plan.name }}</h3>
                                    <div class="flex items-baseline">
                                        <span class="text-3xl font-bold text-gray-900">{{ formatPrice(plan.price)
                                            }}</span>
                                        <span class="text-sm text-gray-500 ml-2">/{{ plan.duration_days }} days</span>
                                    </div>

                                    <!-- Savings badge for yearly plans -->
                                    <div v-if="plan.duration_days >= 360" class="mt-3">
                                        <span
                                            class="inline-flex items-center px-2 py-1 bg-teal-100 text-teal-700 text-xs font-medium rounded">
                                            Save 20% with annual plan
                                        </span>
                                    </div>
                                </div>

                                <!-- Features List -->
                                <div class="p-6 flex-1">
                                    <p class="text-sm font-medium text-gray-700 mb-4">What's included:</p>
                                    <ul class="space-y-3">
                                        <li v-for="feature in plan.features" :key="feature" class="flex items-start">
                                            <div
                                                class="flex-shrink-0 w-5 h-5 rounded-full bg-teal-100 flex items-center justify-center mt-0.5">
                                                <Icon icon="mdi:check" class="text-teal-600 text-sm" />
                                            </div>
                                            <span class="ml-3 text-sm text-gray-600">{{ feature }}</span>
                                        </li>
                                    </ul>
                                </div>

                                <!-- Plan Footer -->
                                <div class="p-6 pt-0">
                                    <button @click="choosePlan(plan)"
                                        class="w-full py-3 px-4 rounded-lg font-medium text-sm transition-colors duration-200 flex items-center justify-center"
                                        :class="index === popularPlanIndex
                                            ? 'bg-blue-600 text-white hover:bg-blue-700'
                                            : 'bg-gray-900 text-white hover:bg-gray-800'">
                                        <Icon icon="mdi:cash" class="mr-2 text-lg" />
                                        Pay with JazzCash
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
                    <div v-if="paymentStep === 'payment' && selectedPlan" class="max-w-2xl mx-auto">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <!-- Progress Steps -->
                            <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                                <div class="flex items-center justify-between max-w-md mx-auto">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                                            <span class="text-white text-sm font-bold">1</span>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-xs text-gray-500">Step 1</p>
                                            <p class="text-sm font-medium text-gray-900">Choose Plan</p>
                                        </div>
                                    </div>
                                    <div class="w-16 h-px bg-gray-300"></div>
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-teal-600 rounded-full flex items-center justify-center">
                                            <span class="text-white text-sm font-bold">2</span>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-xs text-gray-500">Step 2</p>
                                            <p class="text-sm font-medium text-gray-900">JazzCash Payment</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Form -->
                            <div class="p-4 sm:p-6 md:p-8">
                                <!-- Selected Plan Summary -->
                                <div class="bg-gray-50 rounded-lg p-4 sm:p-6 mb-6 sm:mb-8">
                                    <!-- Stack on mobile, side-by-side on larger screens -->
                                    <div
                                        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 sm:gap-0">
                                        <div>
                                            <p class="text-xs sm:text-sm text-gray-500 mb-1">Selected Plan</p>
                                            <p
                                                class="text-base sm:text-lg md:text-xl font-semibold text-gray-900 break-words">
                                                {{ selectedPlan.name }}
                                            </p>
                                        </div>
                                        <div class="text-left sm:text-right">
                                            <p class="text-xs sm:text-sm text-gray-500 mb-1">Total Amount</p>
                                            <p
                                                class="text-2xl sm:text-3xl md:text-4xl font-bold text-teal-600 break-words">
                                                {{ formatPrice(selectedPlan.price) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- JazzCash Payment Info -->
                                <div class="mb-6 sm:mb-8">
                                    <div class="bg-teal-50 border border-teal-200 rounded-lg p-4 sm:p-6">
                                        <!-- Stack vertically on mobile, side-by-side on tablet/desktop -->
                                        <div class="flex flex-col sm:flex-row sm:items-start gap-4 sm:gap-0">
                                            <!-- Icon - centered on mobile, left-aligned on larger screens -->
                                            <div class="flex justify-center sm:justify-start">
                                                <div
                                                    class="w-12 h-12 sm:w-14 sm:h-14 md:w-16 md:h-16 bg-teal-600 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <Icon icon="mdi:cash" class="text-2xl sm:text-3xl text-white" />
                                                </div>
                                            </div>

                                            <!-- Content - full width on mobile -->
                                            <div class="sm:ml-4 flex-1">
                                                <h4
                                                    class="text-base sm:text-lg md:text-xl font-semibold text-teal-700 mb-2 text-center sm:text-left">
                                                    JazzCash Secure Payment
                                                </h4>
                                                <p
                                                    class="text-sm sm:text-base text-gray-600 mb-3 text-center sm:text-left">
                                                    You'll be redirected to JazzCash's secure payment gateway to
                                                    complete your transaction instantly.
                                                </p>

                                                <!-- Security badges - stack on mobile, row on larger screens -->
                                                <div
                                                    class="flex flex-col sm:flex-row items-center sm:items-start gap-2 sm:gap-4 text-xs sm:text-sm text-teal-600">
                                                    <span class="flex items-center">
                                                        <Icon icon="mdi:check-circle"
                                                            class="mr-1 text-sm sm:text-base" />
                                                        256-bit SSL
                                                    </span>
                                                    <span class="flex items-center">
                                                        <Icon icon="mdi:check-circle"
                                                            class="mr-1 text-sm sm:text-base" />
                                                        PCI Compliant
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Terms Checkbox -->
                                <div class="mb-6 sm:mb-8">
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
                                </div>

                                <!-- Submit Button -->
                                <button @click="submit"
                                    :disabled="form.processing || !form.terms_accepted || isRedirecting"
                                    class="w-full bg-teal-600 text-white py-3 sm:py-4 px-4 sm:px-6 rounded-lg font-medium text-sm sm:text-base md:text-lg hover:bg-teal-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200 flex items-center justify-center">
                                    <span v-if="form.processing || isRedirecting" class="flex items-center">
                                        <Icon icon="mdi:loading"
                                            class="animate-spin mr-2 text-base sm:text-lg md:text-xl" />
                                        <span class="truncate">Redirecting to JazzCash...</span>
                                    </span>
                                    <span v-else class="flex items-center">
                                        <Icon icon="mdi:cash" class="mr-2 text-base sm:text-lg md:text-xl" />
                                        <span class="truncate">Pay with JazzCash</span>
                                    </span>
                                </button>

                                <!-- Security note -->
                                <p
                                    class="mt-4 sm:mt-6 text-xs sm:text-sm text-center text-gray-400 flex items-center justify-center">
                                    <Icon icon="mdi:lock-outline" class="mr-1 sm:mr-2 text-sm sm:text-base" />
                                    Your payment is secure and encrypted
                                </p>

                                <!-- JazzCash Partner Badge -->
                                <div class="mt-4 sm:mt-6 pt-4 sm:pt-6 border-t border-gray-100 text-center">
                                    <p class="text-xs text-gray-400 mb-2">Powered by</p>
                                    <div class="flex items-center justify-center">
                                        <Icon icon="mdi:cash" class="text-teal-600 text-xl sm:text-2xl" />
                                        <span class="ml-2 text-base sm:text-lg md:text-xl font-semibold text-gray-700">
                                            Jazz<span class="text-teal-600">Cash</span>
                                        </span>
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
/* Only keep essential animations */
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

/* Smooth transitions */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .grid {
        gap: 1rem;
    }
}
</style>