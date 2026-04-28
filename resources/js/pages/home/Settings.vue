<template>
    <OlxLayout>

        <Head title="Account Settings" />

        <div class="py-12">
            <div class="max-w-full px-4 md:px-0 md:max-w-8/10 mx-auto space-y-12">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <!-- Tabs Header -->
                    <div class="border-b border-gray-200">
                        <nav class="flex -mb-px">
                            <button @click="activeTab = 'password'" :class="[
                                'px-6 py-4 text-sm font-medium transition-colors duration-200',
                                activeTab === 'password'
                                    ? 'border-b-2 border-brand-teal text-brand-teal'
                                    : 'text-gray-500 hover:text-gray-700 hover:border-gray-300'
                            ]">
                                Change Password
                            </button>
                            <button @click="activeTab = 'notifications'" :class="[
                                'px-6 py-4 text-sm font-medium transition-colors duration-200',
                                activeTab === 'notifications'
                                    ? 'border-b-2 border-brand-teal text-brand-teal'
                                    : 'text-gray-500 hover:text-gray-700 hover:border-gray-300'
                            ]">
                                Notifications
                            </button>
                        </nav>
                    </div>

                    <!-- Tab Panels -->
                    <div class="p-6">
                        <!-- Password Panel -->
                        <div v-if="activeTab === 'password'">
                            <!-- Flash Messages -->
                            <div v-if="$page.props.flash.success"
                                class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                                {{ $page.props.flash.success }}
                            </div>
                            <div v-if="$page.props.flash.error"
                                class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                                {{ $page.props.flash.error }}
                            </div>

                            <form @submit.prevent="submitPassword">
                                <!-- Current Password -->
                                <div class="mb-4">
                                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">
                                        Current Password
                                    </label>
                                    <div class="relative">
                                        <input :type="showCurrentPassword ? 'text' : 'password'" id="current_password"
                                            v-model="passwordForm.current_password"
                                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-brand-teal focus:ring-1 focus:ring-brand-teal"
                                            :class="{ 'border-red-500': passwordForm.errors.current_password }" />
                                        <button type="button" @click="showCurrentPassword = !showCurrentPassword"
                                            class="absolute right-3 top-2.5 text-gray-500 hover:text-brand-teal">
                                            <svg v-if="!showCurrentPassword" xmlns="http://www.w3.org/2000/svg"
                                                class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                            </svg>
                                        </button>
                                    </div>
                                    <p v-if="passwordForm.errors.current_password" class="mt-1 text-sm text-red-500">
                                        {{ passwordForm.errors.current_password }}
                                    </p>
                                </div>

                                <!-- New Password -->
                                <div class="mb-4">
                                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">
                                        New Password
                                    </label>
                                    <div class="relative">
                                        <input :type="showNewPassword ? 'text' : 'password'" id="new_password"
                                            v-model="passwordForm.new_password"
                                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-brand-teal focus:ring-1 focus:ring-brand-teal"
                                            :class="{ 'border-red-500': passwordForm.errors.new_password }" />
                                        <button type="button" @click="showNewPassword = !showNewPassword"
                                            class="absolute right-3 top-2.5 text-gray-500 hover:text-brand-teal">
                                            <svg v-if="!showNewPassword" xmlns="http://www.w3.org/2000/svg"
                                                class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Password Strength Meter -->
                                    <div class="mt-2">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                                <div class="h-full transition-all duration-300" :style="{
                                                    width: (passwordStrength.score * 20) + '%',
                                                    backgroundColor: passwordStrength.color
                                                }">
                                                </div>
                                            </div>
                                            <span class="text-xs" :style="{ color: passwordStrength.color }">
                                                {{ passwordStrength.label }}
                                            </span>
                                        </div>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">Minimum 8 characters</p>
                                    <p v-if="passwordForm.errors.new_password" class="mt-1 text-sm text-red-500">
                                        {{ passwordForm.errors.new_password }}
                                    </p>
                                </div>

                                <!-- Confirm New Password -->
                                <div class="mb-6">
                                    <label for="new_password_confirmation"
                                        class="block text-sm font-medium text-gray-700 mb-1">
                                        Confirm New Password
                                    </label>
                                    <div class="relative">
                                        <input :type="showConfirmPassword ? 'text' : 'password'"
                                            id="new_password_confirmation"
                                            v-model="passwordForm.new_password_confirmation"
                                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-brand-teal focus:ring-1 focus:ring-brand-teal"
                                            :class="{
                                                'border-red-500': passwordForm.errors.new_password_confirmation,
                                                'border-green-500':
                                                    passwordForm.new_password_confirmation && passwordForm.new_password === passwordForm.new_password_confirmation && passwordForm.new_password.length >= 8
                                            }" />
                                        <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                                            class="absolute right-3 top-2.5 text-gray-500 hover:text-brand-teal">
                                            <svg v-if="!showConfirmPassword" xmlns="http://www.w3.org/2000/svg"
                                                class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div v-if="passwordForm.new_password_confirmation"
                                        class="mt-1 flex items-center gap-1">
                                        <svg v-if="passwordForm.new_password === passwordForm.new_password_confirmation && passwordForm.new_password.length >= 8"
                                            xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-brand-teal"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-500"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-xs" :class="{
                                            'text-brand-teal': passwordForm.new_password === passwordForm.new_password_confirmation && passwordForm.new_password.length >= 8,
                                            'text-red-500': passwordForm.new_password !== passwordForm.new_password_confirmation
                                        }">
                                            {{ passwordForm.new_password === passwordForm.new_password_confirmation &&
                                                passwordForm.new_password.length >= 8 ? 'Passwords match' :
                                                'Passwords do not match' }}
                                        </span>
                                    </div>
                                    <p v-if="passwordForm.errors.new_password_confirmation"
                                        class="mt-1 text-sm text-red-500">
                                        {{ passwordForm.errors.new_password_confirmation }}
                                    </p>
                                </div>

                                <button type="submit" :disabled="passwordForm.processing"
                                    class="w-full bg-brand-teal text-white py-2 px-4 rounded-lg hover:bg-brand-teal/80 transition-colors duration-200 disabled:opacity-50">
                                    {{ passwordForm.processing ? 'Changing Password...' : 'Change Password' }}
                                </button>
                            </form>
                        </div>

                        <!-- Notifications Panel -->
                        <div v-if="activeTab === 'notifications'">
                            <div class="space-y-6">
                                <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                                    <div class="flex">
                                        <Icon icon="mdi:bell" class="h-5 w-5 text-blue-400 mr-3" />
                                        <div>
                                            <p class="text-sm text-blue-700">
                                                Enable browser notifications to receive real‑time updates about new
                                                messages, offers, and alerts.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Subscription Status -->
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <h3 class="font-medium">Push Notifications</h3>
                                        <p class="text-sm text-gray-500 mt-1">
                                            {{ notificationStatus }}
                                        </p>
                                    </div>
                                    <div class="flex gap-2">
                                        <!-- Subscribe button (shown when NOT subscribed) -->
                                        <button v-if="!isSubscribed" @click="subscribeToPush"
                                            :disabled="isSubscribing || !isPushSupported"
                                            class="px-4 py-2 rounded-lg text-white transition-colors" :class="{
                                                'bg-brand-teal hover:bg-brand-teal/80': !isSubscribing && isPushSupported,
                                                'bg-gray-400 cursor-not-allowed': !isPushSupported,
                                                'opacity-50': isSubscribing
                                            }">
                                            {{ isSubscribing ? 'Subscribing...' : 'Enable Notifications' }}
                                        </button>

                                        <!-- Unsubscribe button (shown when subscribed) -->
                                        <button v-if="isSubscribed" @click="unsubscribeFromPush"
                                            :disabled="isUnsubscribing"
                                            class="px-4 py-2 rounded-lg bg-red-500 text-white hover:bg-red-600 transition-colors disabled:opacity-50">
                                            {{ isUnsubscribing ? 'Unsubscribing...' : 'Unsubscribe' }}
                                        </button>
                                    </div>
                                </div>

                                <!-- Browser Support Warning (contextual) -->
                                <div v-if="!isPushSupported"
                                    class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <p class="text-sm text-yellow-700">{{ supportReason }}</p>
                                    <p v-if="supportReason.includes('Home Screen')"
                                        class="text-xs text-yellow-600 mt-1">
                                        To enable notifications on iOS: open this page in Safari, tap the Share icon,
                                        then “Add to Home Screen”.
                                    </p>
                                    <p v-if="supportReason.includes('HTTPS')" class="text-xs text-yellow-600 mt-1">
                                        You are currently on HTTP. Switch to the HTTPS version of the site to enable
                                        notifications.
                                    </p>
                                    <p v-else-if="supportReason.includes('does not support')"
                                        class="text-xs text-yellow-600 mt-1">
                                        Please use Chrome, Firefox, or Edge on Android, or add this site to your Home
                                        Screen on iOS 16.4+.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Common Back Link -->
                        <div class="mt-6 text-center pt-4 border-t border-gray-100">
                            <a :href="route('home')"
                                class="text-sm text-brand-blue hover:text-brand-teal transition-colors duration-200">
                                ← Back to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </OlxLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useForm, Head } from '@inertiajs/vue3'
import OlxLayout from '@/layouts/OlxLayout.vue'
import { Icon } from '@iconify/vue'
import axios from 'axios'
import { useForceTheme } from '@/composables/useForceTheme'

useForceTheme('light')

// Active tab state
const activeTab = ref('password')

// ==================== PASSWORD TAB ====================
const passwordForm = useForm({
    current_password: '',
    new_password: '',
    new_password_confirmation: ''
})

const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

const submitPassword = () => {
    passwordForm.post(route('password.change'), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset('current_password', 'new_password', 'new_password_confirmation')
        }
    })
}

const passwordStrength = computed(() => {
    const password = passwordForm.new_password
    if (!password) return { score: 0, label: 'Enter password', color: '#666' }

    let score = 0
    if (password.length >= 8) score++
    if (password.match(/[a-z]/)) score++
    if (password.match(/[A-Z]/)) score++
    if (password.match(/[0-9]/)) score++
    if (password.match(/[^a-zA-Z0-9]/)) score++

    const strengths = ['Weak', 'Fair', 'Good', 'Strong', 'Very Strong']
    const colors = ['#dc3545', '#ffc107', '#4A90E2', '#2C6B5E', '#2C6B5E']

    return {
        score: score,
        label: strengths[score - 1] || 'Weak',
        color: colors[score - 1] || '#dc3545'
    }
})

// ==================== NOTIFICATIONS TAB ====================
const isPushSupported = ref(false)
const isSubscribed = ref(false)
const isSubscribing = ref(false)
const isUnsubscribing = ref(false)
const notificationStatus = ref('Checking browser compatibility...')
const supportReason = ref('') // contextual reason for lack of support

const checkPushSupport = () => {
    // 1. Secure context (HTTPS required)
    if (window.isSecureContext === false) {
        isPushSupported.value = false
        supportReason.value = 'Push notifications require a secure connection (HTTPS). You are viewing this page over HTTP.'
        notificationStatus.value = 'Please access this site via HTTPS.'
        return false
    }

    // 2. Check API availability
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        isPushSupported.value = false
        const isIOS = /iPhone|iPad|iPod/.test(navigator.userAgent)
        if (isIOS) {
            supportReason.value = 'On iOS, push notifications require iOS 16.4 or later, and the website must be added to the Home Screen.'
        } else {
            supportReason.value = 'Your browser does not support the Push API. Please use Chrome, Firefox, or Edge.'
        }
        notificationStatus.value = 'Push notifications not supported.'
        return false
    }

    // 3. All good – API exists and context secure
    isPushSupported.value = true
    supportReason.value = ''
    notificationStatus.value = 'Click the button to enable notifications'
    return true
}

const checkExistingSubscription = async () => {
    if (!isPushSupported.value) return false

    try {
        const registration = await navigator.serviceWorker.getRegistration()
        const subscription = await registration.pushManager.getSubscription()
        if (subscription) {
            isSubscribed.value = true
            notificationStatus.value = 'You are already subscribed to push notifications'
            return true
        }
        isSubscribed.value = false
        notificationStatus.value = 'Not subscribed – click to enable'
        return false
    } catch (error) {
        console.error('Error checking subscription:', error)
        notificationStatus.value = 'Could not check subscription status'
        return false
    }
}

const vapidKey = import.meta.env.VITE_VAPID_PUBLIC_KEY

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4)
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/')
    const rawData = window.atob(base64)
    return new Uint8Array([...rawData].map((c) => c.charCodeAt(0)))
}

const subscribeToPush = async () => {
    if (!isPushSupported.value) {
        alert('Push notifications are not supported in your browser.')
        return
    }

    try {
        isSubscribing.value = true

        const permission = await Notification.requestPermission()
        if (permission !== 'granted') {
            alert('Notification permission denied. You can enable it in your browser settings.')
            notificationStatus.value = 'Permission denied'
            return
        }

        const registration = await navigator.serviceWorker.getRegistration()
        let subscription = await registration.pushManager.getSubscription()

        if (subscription) {
            console.log('Old subscription found → removing...')
            await subscription.unsubscribe()
        }

        subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(vapidKey),
        })

        await axios.post('/push/subscribe', subscription)

        isSubscribed.value = true
        notificationStatus.value = 'Successfully subscribed to push notifications'
        alert('You will now receive notifications!')
    } catch (error) {
        console.error('Push subscription error:', error)
        notificationStatus.value = 'Subscription failed – try again'
        alert('Failed to enable notifications. Please check console for details.')
    } finally {
        isSubscribing.value = false
    }
}

const unsubscribeFromPush = async () => {
    try {
        isUnsubscribing.value = true

        const registration = await navigator.serviceWorker.getRegistration()
        const subscription = await registration.pushManager.getSubscription()

        if (subscription) {
            await axios.post('/push/unsubscribe', {
                endpoint: subscription.endpoint,
            })
            await subscription.unsubscribe()
        }

        isSubscribed.value = false
        notificationStatus.value = 'You have been unsubscribed from push notifications'
    } catch (error) {
        console.error('Unsubscribe error:', error)
        notificationStatus.value = 'Failed to unsubscribe – please try again'
    } finally {
        isUnsubscribing.value = false
    }
}

// Initialize service worker on mount
onMounted(async () => {
    // if ('serviceWorker' in navigator) {
    //     try {
    //         await navigator.serviceWorker.register('/sw.js')
    //         console.log('Service Worker registered')
    //     } catch (err) {
    //         console.error('Service Worker registration failed:', err)
    //     }
    // }

    if (checkPushSupport()) {
        await checkExistingSubscription()
    }
})
</script>

<style scoped>
/* Optional additional styles */
</style>