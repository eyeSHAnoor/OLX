<template>
    <OlxLayout :hide-search-bar="true">
        <div
            class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 flex flex-col items-center justify-center p-3 sm:p-4">
            <!-- Logo & heading -->
            <div class="mb-6 md:mb-8">
                <img src="/images/logo.png" alt="AMO Mercatus" class="h-10 md:h-14 w-auto mx-auto" />
                <h1 class="mt-2 text-xl md:text-2xl font-semibold text-center text-gray-800">
                    Choose Your Plan
                </h1>
                <p class="text-gray-600 text-center mt-1 text-xs md:text-sm">
                    {{
                        hasActiveSubscription
                            ? "Manage your subscription"
                            : "Get access to premium features"
                    }}
                </p>
            </div>

            <div class="w-full max-w-5xl">
                <!-- ========== STATUS MESSAGES (unchanged) ========== -->
                <div v-if="!canSubscribe && paymentStep === 'select'" class="max-w-md mx-auto">
                    <!-- Header -->
                    <div class="rounded-t-3xl p-6 text-white text-center" :class="hasActiveSubscription
                            ? 'bg-gradient-to-r from-brand-teal/70 to-brand-teal/90'
                            : hasPendingSubscription
                                ? 'bg-gradient-to-r from-brand-blue/70 to-brand-blue/90'
                                : 'bg-gradient-to-r from-gray-500 to-gray-600'
                        ">
                        <div class="w-14 h-14 mx-auto mb-3 rounded-full bg-white/20 flex items-center justify-center">
                            <Icon v-if="hasActiveSubscription" icon="mdi:check-circle" class="text-2xl" />
                            <Icon v-else-if="hasPendingSubscription" icon="mdi:clock-outline" class="text-2xl" />
                            <Icon v-else icon="mdi:information" class="text-2xl" />
                        </div>

                        <h2 class="text-lg font-semibold">
                            <span v-if="hasActiveSubscription">You're All Set 🎉</span>
                            <span v-else-if="hasPendingSubscription">Processing Payment ⏳</span>
                            <span v-else>Subscription Status</span>
                        </h2>

                        <p class="text-xs opacity-80 mt-1">
                            <span v-if="hasActiveSubscription"> Your subscription is active </span>
                            <span v-else-if="hasPendingSubscription">
                                We're verifying your payment
                            </span>
                            <span v-else>
                                {{ user?.subscription_status || "inactive" }}
                            </span>
                        </p>
                    </div>

                    <!-- Body -->
                    <div class="bg-white rounded-b-3xl px-5 py-6 shadow-md -mt-4 text-center space-y-4">
                        <!-- ACTIVE -->
                        <template v-if="hasActiveSubscription">
                            <p class="text-sm text-gray-600">
                                You can now post ads and reach more buyers.
                            </p>

                            <Link :href="route('user.ads.create')"
                                class="block w-full py-3 rounded-full text-white font-semibold bg-gradient-to-r from-brand-teal/70 to-brand-teal/100 hover:opacity-90 transition">
                                Post Your Ad
                            </Link>
                        </template>

                        <!-- PENDING -->
                        <template v-else-if="hasPendingSubscription">
                            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-left">
                                <p class="text-xs font-semibold text-blue-700 mb-2">What happens next?</p>

                                <ul class="space-y-1 text-xs text-blue-600">
                                    <li class="flex items-start gap-1">
                                        <Icon icon="mdi:check" class="mt-0.5" />
                                        We review your receipt
                                    </li>
                                    <li class="flex items-start gap-1">
                                        <Icon icon="mdi:check" class="mt-0.5" />
                                        Payment gets approved
                                    </li>
                                    <li class="flex items-start gap-1">
                                        <Icon icon="mdi:check" class="mt-0.5" />
                                        You can start posting ads
                                    </li>
                                </ul>
                            </div>

                            <button @click="goToDashboard"
                                class="w-full py-3 rounded-full border border-blue-500 text-blue-600 font-semibold hover:bg-blue-50 transition">
                                Go to Dashboard
                            </button>
                        </template>

                        <!-- INACTIVE -->
                        <template v-else>
                            <p class="text-sm text-gray-600">You don’t have an active subscription.</p>

                            <button @click="goToDashboard"
                                class="w-full py-3 rounded-full text-white font-semibold bg-gradient-to-r from-gray-500 to-gray-600">
                                Go to Dashboard
                            </button>
                        </template>

                        <!-- Footer -->
                        <p class="text-[10px] text-gray-400">
                            Need help?
                            <a href="/page/contact" class="text-indigo-600">Contact support</a>
                        </p>
                    </div>
                </div>

                <!-- ========== PLAN SELECTION & PAYMENT FLOW ========== -->
                <template v-else-if="canSubscribe">
                    <!-- Back button -->
                    <button v-if="paymentStep === 'select'" @click="Back"
                        class="inline-flex items-center gap-1 px-3 py-2 mb-2 rounded-md border border-gray-200 bg-white text-sm text-gray-700 hover:bg-gray-50 transition">
                        <Icon icon="mdi:arrow-left" class="text-base" /> Back
                    </button>
                    <div v-if="paymentStep !== 'select'" class="mb-4 max-w-xl mx-auto">
                        <button @click="goBack"
                            class="flex items-center text-xs text-gray-500 hover:text-gray-700 transition-colors">
                            <Icon icon="mdi:arrow-left" class="text-sm" /> Back to plans
                        </button>
                    </div>

                    <!-- ========== STEP 1: PLAN SELECTION (Redesigned Cards) ========== -->
                    <div v-if="paymentStep === 'select'" class="max-w-md md:hidden mx-auto">
                        <!-- Header -->
                        <div
                            class="rounded-t-3xl bg-gradient-to-r from-brand-blue/70 to-brand-blue/90 h-36 flex items-center justify-center text-white">
                            <span class="text-lg font-semibold">Choose Your Plan</span>
                        </div>

                        <!-- Body -->
                        <div class="bg-white rounded-b-3xl px-5 py-6 shadow-md -mt-6">
                            <!-- Plans -->
                            <div class="space-y-4">
                                <div v-for="plan in plans" :key="plan.id" @click="choosePlan(plan)"
                                    class="rounded-2xl p-4 transition-all cursor-pointer border" :class="plan.is_popular
                                            ? 'border-brand-orange bg-orange-50 shadow-md'
                                            : 'border-gray-200 bg-gray-50 hover:bg-gray-100'
                                        ">
                                    <!-- Top Row -->
                                    <div class="flex justify-between items-center mb-2">
                                        <div>
                                            <p class="text-sm font-bold text-gray-800">
                                                {{ plan.name }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{ plan.duration_days }} days access
                                            </p>
                                        </div>

                                        <div class="text-right flex flex-col items-end space-y-1">
                                            <!-- Discount badge -->
                                            <div v-if="plan.discount > 0"
                                                class="inline-flex items-center text-[10px] bg-red-500 text-white px-2 py-0.5 rounded font-semibold">
                                                -{{ getDiscountPercentage(plan) }}%
                                            </div>

                                            <!-- Old price -->
                                            <p v-if="plan.discount > 0" class="text-xs text-gray-400 line-through">
                                                Rs {{ plan.price }}
                                            </p>

                                            <!-- Final price -->
                                            <p class="text-sm font-bold text-gray-900">
                                                Rs {{ plan.discount }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Popular Badge -->
                                    <div v-if="plan.is_popular"
                                        class="inline-block text-[10px] bg-brand-orange text-white px-2 py-0.5 rounded mb-2 font-semibold">
                                        MOST POPULAR
                                    </div>

                                    <!-- Features -->
                                    <ul class="mt-2 space-y-1">
                                        <li v-for="feature in plan.features" :key="feature"
                                            class="flex items-start text-xs text-gray-600">
                                            <span class="text-green-500 mr-1">✔</span>
                                            <span>{{ feature }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Continue Button -->
                            <button v-if="plans.length" @click="choosePlan(selectedPlan || plans[0])"
                                class="mt-6 w-full bg-gradient-to-r from-brand-blue/80 to-brand-blue/100 text-white py-3 rounded-full font-semibold shadow-md hover:opacity-90 transition">
                                Continue
                            </button>
                        </div>
                    </div>

                    <div v-if="paymentStep === 'select'" class="hidden md:flex md:flex-col">
                        <!-- <div>{{ plan }}</div> -->
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
                                        <h3 class="text-base font-medium text-gray-900 mb-1">
                                            {{ plan.name }}
                                        </h3>
                                        <div class="flex items-baseline gap-2 mb-3">
                                            <span class="text-xl font-light line-through text-gray-900">{{
                                                formatPrice(plan.price)
                                                }}</span>
                                            <span class="text-xl font-bold text-gray-900">{{
                                                formatPrice(plan.discount)
                                                }}</span>
                                            <span class="text-xs text-gray-500 ml-1">/{{ plan.duration_days }}
                                                days</span>
                                        </div>

                                        <div class="border-t border-gray-100 pt-3 mt-2">
                                            <p
                                                class="text-[10px] font-medium text-gray-500 uppercase tracking-wider mb-2">
                                                Features
                                            </p>
                                            <ul class="space-y-1.5">
                                                <li v-for="feature in plan.features" :key="feature"
                                                    class="flex items-start text-xs">
                                                    <Icon icon="mdi:check"
                                                        class="text-teal-500 text-xs mt-0.5 mr-1.5" />
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
                                                    : 'border border-gray-300 text-gray-700 hover:border-teal-500 hover:text-teal-600'
                                                ">
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

                    <!-- ========== STEP 2: PAYMENT FORM (unchanged) ========== -->
                    <div v-if="paymentStep === 'payment' && selectedPlan" class="md:hidden max-w-md mx-auto">
                        <!-- Header -->
                        <div class="rounded-t-3xl bg-gradient-to-r from-brand-blue/90 to-brand-blue/70 p-5 text-white">
                            <p class="text-sm opacity-80">You're subscribing to</p>
                            <div class="flex justify-between items-center mt-1">
                                <div>
                                    <h2 class="text-lg font-semibold">{{ selectedPlan.name }}</h2>
                                    <p class="text-xs opacity-80">{{ selectedPlan.duration_days }} days</p>
                                </div>
                                <p class="text-lg font-bold">Rs {{ selectedPlan.price }}</p>
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="bg-white rounded-b-3xl px-5 py-6 shadow-md -mt-4 space-y-5">
                            <!-- Payment Methods -->
                            <div>
                                <p class="text-xs font-semibold text-gray-500 mb-2">
                                    Select Payment Method
                                </p>

                                <div class="grid grid-cols-3 gap-2">
                                    <button v-for="method in paymentMethods" :key="method.id"
                                        @click="method.enabled ? (form.payment_method = method.id) : null"
                                        :disabled="!method.enabled"
                                        class="rounded-xl p-3 border text-center transition-all" :class="{
                                            'border-brand-blue bg-brand-blue/10':
                                                form.payment_method === method.id && method.enabled,
                                            'border-gray-200 bg-gray-50':
                                                method.enabled && form.payment_method !== method.id,
                                            'opacity-40 cursor-not-allowed': !method.enabled,
                                        }">
                                        <Icon :icon="method.icon" class="text-lg mb-1" :class="form.payment_method === method.id
                                                ? 'text-indigo-600'
                                                : 'text-gray-500'
                                            " />

                                        <p class="text-[10px]" :class="form.payment_method === method.id
                                                ? 'text-indigo-600 font-medium'
                                                : 'text-gray-500'
                                            ">
                                            {{ method.name }}
                                        </p>
                                    </button>
                                </div>
                            </div>

                            <!-- Instructions -->
                            <div v-if="form.payment_method"
                                class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 text-center">
                                <p class="text-xs font-semibold text-indigo-700 mb-2">
                                    Payment Instructions
                                </p>

                                <!-- JazzCash -->
                                <div v-if="form.payment_method === 'jazzcash'" class="space-y-3">
                                    <img src="/images/jazzcashQR.jpeg"
                                        class="w-32 h-32 mx-auto rounded-lg border p-1 bg-white" />

                                    <div>
                                        <p class="text-xs text-gray-500">TILL ID</p>
                                        <p class="font-mono text-sm font-semibold text-gray-800">982295010</p>

                                        <button @click="copyTillId" class="text-[10px] text-indigo-600 mt-1">
                                            Copy ID
                                        </button>
                                    </div>

                                    <p class="text-[11px] text-gray-600">
                                        Send exact amount via JazzCash and upload receipt below.
                                    </p>
                                </div>

                                <!-- Other Methods -->
                                <p v-else class="text-xs text-gray-600">
                                    {{ currentPaymentMethod.instructions }}
                                </p>
                            </div>

                            <!-- Upload -->
                            <div>
                                <p class="text-xs font-semibold text-gray-500 mb-2">Upload Receipt</p>

                                <!-- Empty -->
                                <label v-if="!uploadedFileName" for="receipt-upload"
                                    class="flex flex-col items-center justify-center h-28 border-2 border-dashed rounded-xl cursor-pointer text-center hover:bg-gray-50 transition">
                                    <Icon icon="mdi:cloud-upload" class="text-2xl text-gray-400 mb-1" />

                                    <p class="text-xs text-gray-600">Click to upload or drag file</p>

                                    <p class="text-[10px] text-gray-400">PNG, JPG, PDF (max 5MB)</p>

                                    <input id="receipt-upload" type="file" @change="handleFileUpload"
                                        accept="image/*,.pdf" class="hidden" />
                                </label>

                                <!-- Selected -->
                                <div v-else class="flex items-center justify-between border rounded-xl p-3">
                                    <div class="flex items-center gap-2">
                                        <Icon icon="mdi:file-document" class="text-lg text-indigo-500" />
                                        <div>
                                            <p class="text-xs font-medium text-gray-800">
                                                {{ uploadedFileName }}
                                            </p>
                                            <p class="text-[10px] text-gray-400">
                                                {{ formatFileSize(form.receipt?.size) }}
                                            </p>
                                        </div>
                                    </div>

                                    <button @click="removeFile">
                                        <Icon icon="mdi:close" class="text-gray-400" />
                                    </button>
                                </div>

                                <p v-if="form.errors.receipt" class="text-[10px] text-red-500 mt-1">
                                    {{ form.errors.receipt }}
                                </p>
                            </div>

                            <!-- Terms -->
                            <label class="flex items-start gap-2 text-xs text-gray-600">
                                <input type="checkbox" v-model="form.terms_accepted"
                                    class="mt-1 w-4 h-4 text-indigo-600 rounded" />

                                <span>
                                    I agree to the
                                    <Link :href="route('policy.show', 'terms')" class="text-indigo-600">Terms</Link>
                                    &
                                    <Link :href="route('policy.show', 'privacy')" class="text-indigo-600">Privacy</Link>
                                </span>
                            </label>

                            <!-- Submit -->
                            <button @click="submit" :disabled="form.processing || !form.receipt || !form.terms_accepted"
                                class="w-full py-3 rounded-full text-white font-semibold transition-all" :class="form.processing
                                        ? 'bg-gray-400'
                                        : 'bg-gradient-to-r from-brand-blue/80 to-brand-blue/100 hover:opacity-90'
                                    ">
                                <span v-if="form.processing" class="flex items-center justify-center gap-2">
                                    <Icon icon="mdi:loading" class="animate-spin" />
                                    Processing...
                                </span>

                                <span v-else> Confirm Payment </span>
                            </button>
                        </div>
                    </div>
                    <div v-if="paymentStep === 'payment' && selectedPlan"
                        class="hidden md:flex md:flex-col bg-white rounded-xl shadow-md overflow-hidden max-w-xl mx-auto py-4">
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
                                    <p class="text-base font-medium text-gray-900">
                                        {{ selectedPlan.name }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ selectedPlan.duration_days }} days access
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500 mb-0.5">Total</p>
                                    <p class="text-xl font-light text-blue-700">
                                        {{ formatPrice(selectedPlan.price) }}
                                    </p>
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
                                        @click="method.enabled ? (form.payment_method = method.id) : null"
                                        :disabled="!method.enabled"
                                        class="flex flex-col items-center p-2.5 border rounded-lg transition-all duration-200"
                                        :class="{
                                            'border-blue-500 bg-blue-50/50 shadow-sm':
                                                form.payment_method === method.id && method.enabled,
                                            'border-gray-200 hover:border-blue-300 hover:bg-gray-50':
                                                method.enabled && form.payment_method !== method.id,
                                            'opacity-50 cursor-not-allowed bg-gray-100 border-gray-200': !method.enabled,
                                        }">
                                        <Icon :icon="method.icon" class="text-base mb-1" :class="form.payment_method === method.id && method.enabled
                                                ? 'text-blue-600'
                                                : 'text-gray-500'
                                            " />
                                        <span class="text-[10px]" :class="form.payment_method === method.id && method.enabled
                                                ? 'text-blue-700 font-medium'
                                                : 'text-gray-500'
                                            ">
                                            {{ method.name }}
                                        </span>
                                    </button>
                                </div>
                            </div>

                            <!-- Instructions (with QR and TILL ID for JazzCash) -->
                            <div v-if="form.payment_method" class="p-3 rounded-lg border border-blue-100">
                                <h4 class="text-xs font-medium text-brand-blue mb-2">
                                    Payment Instructions
                                </h4>

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
                                        Send the exact amount to the TILL ID above using JazzCash app, then
                                        upload the receipt.
                                    </p>
                                </div>

                                <!-- Default instructions for other methods -->
                                <p v-else class="text-xs text-brand-blue/90">
                                    {{ currentPaymentMethod.instructions }}
                                </p>
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
                                                : 'border-gray-300 hover:border-blue-400 hover:bg-blue-50'
                                            ">
                                        <Icon icon="mdi:cloud-upload" class="text-2xl mb-1"
                                            :class="isDragging ? 'text-blue-600' : 'text-gray-400'" />
                                        <span class="text-xs font-medium text-gray-700">
                                            {{ isDragging ? "Drop your file here" : "Click or drag to upload" }}
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
                                                <p class="text-xs font-medium text-gray-900">
                                                    {{ uploadedFileName }}
                                                </p>
                                                <p class="text-[10px] text-gray-400">
                                                    {{ formatFileSize(form.receipt?.size) }}
                                                </p>
                                            </div>
                                        </div>
                                        <button @click="removeFile"
                                            class="p-1 hover:bg-gray-100 rounded transition-colors">
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

                                <button @click="submit"
                                    :disabled="form.processing || !form.receipt || !form.terms_accepted"
                                    class="w-full bg-brand-blue text-white py-2.5 px-4 rounded-lg text-xs font-medium hover:bg-brand-blue/80 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 active:scale-[0.98]">
                                    <span v-if="form.processing" class="flex items-center justify-center gap-2">
                                        <Icon icon="mdi:loading" class="animate-spin" />
                                        Processing...
                                    </span>
                                    <span v-else> Complete Payment </span>
                                </button>

                                <p class="text-[10px] text-center text-gray-400">
                                    By completing this payment, you agree to our terms of service
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- ========== STEP 3: CONFIRMATION (unchanged) ========== -->
                    <div v-if="paymentStep === 'confirmation'" class="bg-white rounded-xl shadow-md p-8">
                        <div class="text-center max-w-sm mx-auto">
                            <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center">
                                <Icon icon="mdi:check" class="text-2xl text-teal-600" />
                            </div>
                            <h2 class="text-lg font-medium text-gray-900 mb-2">Payment submitted</h2>
                            <p class="text-xs text-gray-500 mb-5">{{ successMessage }}</p>
                            <div class="rounded-lg p-4 mb-5">
                                <div class="flex items-center justify-between text-xs mb-3">
                                    <span class="text-gray-600">Status</span><span
                                        class="font-medium text-blue-600">Pending verification</span>
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
                                    Go to dashboard</button><button @click="goBack"
                                    class="w-full border border-gray-300 text-gray-700 py-2.5 px-4 rounded-lg text-xs font-medium hover:border-teal-500 hover:text-teal-600 transition-colors">
                                    Choose another plan
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Footer -->
            <div class="mt-6 md:mt-8 text-center">
                <div class="flex flex-wrap justify-center gap-3 text-[10px] text-gray-500">
                    <a href="/page/contact" class="hover:text-gray-700 transition-colors">Help</a>
                    <a :href="route('policy.show', 'privacy')" class="hover:text-gray-700 transition-colors">Privacy</a>
                    <a :href="route('policy.show', 'terms')" class="hover:text-gray-700 transition-colors">Terms</a>
                    <a href="/page/about" class="hover:text-gray-700 transition-colors">About</a>
                </div>
                <p class="mt-2 text-[10px] text-gray-500">
                    © {{ new Date().getFullYear() }} AMO Mercatus. All rights reserved.
                </p>
            </div>
        </div>
    </OlxLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { useForm, router, Link } from "@inertiajs/vue3";
import { Icon } from "@iconify/vue";
import { usePage } from "@inertiajs/vue3";
import OlxLayout from "@/layouts/OlxLayout.vue";

const props = defineProps({
    plans: Array,
});

// console.log("Available subscription plans:", props.plans);
const getDiscountPercentage = (plan) => {
    if (!plan.price || !plan.discount) return 0;

    const discount = ((plan.price - plan.discount) / plan.price) * 100;
    return Math.round(discount);
};

// Generic premium features (from the first screenshot)
const genericFeatures = [
    "No Ads",
    "Faster Connection",
    "Worldwide Location",
    "Link up to 10 Devices",
];

// Auth user
const page = usePage();
const user = computed(() => page.props.auth?.user);

// Subscription status checks
const hasPendingSubscription = computed(
    () => user.value?.subscription_status === "pending"
);
const hasActiveSubscription = computed(
    () => user.value?.subscription_status === "active"
);
const hasExpiredSubscription = computed(
    () => user.value?.subscription_status === "expired"
);
const hasNoSubscription = computed(
    () =>
        !user.value?.subscription_status ||
        user.value?.subscription_status === "none" ||
        user.value?.subscription_status === null
);
const canSubscribe = computed(
    () => hasNoSubscription.value || hasExpiredSubscription.value
);

// Component state
const selectedPlan = ref(null);
const paymentStep = ref("select");
const showSuccessMessage = ref(false);
const successMessage = ref("");
const uploadedFileName = ref("");
const isDragging = ref(false);

const form = useForm({
    plan_id: null,
    payment_method: "jazzcash",
    receipt: null,
    terms_accepted: false,
});

const Back = () => window.history.back();

// Payment methods (only JazzCash enabled)
const paymentMethods = [
    {
        id: "jazzcash",
        name: "JazzCash",
        icon: "mdi:cash",
        instructions: "Send payment to the TILL ID above using JazzCash app.",
        enabled: true,
    },
    {
        id: "easypaisa",
        name: "Easypaisa",
        icon: "mdi:bank-transfer",
        instructions: "Send payment to 0300-1234567 (Easypaisa account)",
        enabled: false,
    },
    {
        id: "bank",
        name: "Bank Transfer",
        icon: "mdi:bank",
        instructions:
            "Bank: HBL | Account: 1234-5678-9012-3456 | IBAN: PK12HBL1234567890123456",
        enabled: false,
    },
];

const currentPaymentMethod = computed(
    () => paymentMethods.find((m) => m.id === form.payment_method) || paymentMethods[0]
);

function choosePlan(plan) {
    selectedPlan.value = plan;
    form.plan_id = plan.id;
    paymentStep.value = "payment";
    window.scrollTo({ top: 0, behavior: "smooth" });
}

// File upload handlers
function handleFileUpload(event) {
    const file = event.target.files[0];
    if (file) {
        form.receipt = file;
        uploadedFileName.value = file.name;
    }
}

function handleDrop(event) {
    event.preventDefault();
    isDragging.value = false;
    const file = event.dataTransfer.files[0];
    if (file && (file.type.startsWith("image/") || file.type === "application/pdf")) {
        form.receipt = file;
        uploadedFileName.value = file.name;
    }
}

function handleDragOver(event) {
    event.preventDefault();
    isDragging.value = true;
}
function handleDragLeave() {
    isDragging.value = false;
}
function removeFile() {
    form.receipt = null;
    uploadedFileName.value = "";
}

function copyTillId() {
    navigator.clipboard.writeText("982295010");
    alert("TILL ID copied!");
}

function submit() {
    form.post("/subscriptions/manual", {
        onSuccess: (response) => {
            router.visit("/");
            showSuccessMessage.value = true;
            successMessage.value =
                response.props?.flash?.message ||
                "Payment submitted successfully. We'll notify you once it's verified.";
            paymentStep.value = "confirmation";
            form.reset();
            uploadedFileName.value = "";
            setTimeout(() => {
                showSuccessMessage.value = false;
            }, 5000);
        },
        onError: (errors) => console.error("Payment submission failed", errors),
    });
}

function goBack() {
    paymentStep.value = "select";
    selectedPlan.value = null;
    form.reset();
    uploadedFileName.value = "";
    showSuccessMessage.value = false;
}

function goHome() {
    router.visit("/");
}
function goToDashboard() {
    router.visit("/");
}

const formatPrice = (price) => {
    return new Intl.NumberFormat("en-PK", {
        style: "currency",
        currency: "PKR",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    })
        .format(price)
        .replace("PKR", "₨");
};

const formatFileSize = (bytes) => {
    if (!bytes) return "0 Bytes";
    const k = 1024;
    const sizes = ["Bytes", "KB", "MB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + " " + sizes[i];
};
</script>

<style scoped>
/* Keep only essential animations – all visual styles come from Tailwind */
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

/* Custom scrollbar for the carousel (optional) */
.overflow-x-auto::-webkit-scrollbar {
    height: 4px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #00a6a0;
    border-radius: 2px;
}
</style>
