<template>
    <!-- Mobile Bottom Navigation - Only visible on mobile -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-50 shadow-lg">
        <div class="flex justify-around items-center ">
            <!-- Home -->
            <Link href="/" class="flex flex-col items-center px-3 py-1">
                <Icon :icon="isActive('home') ? 'mdi:home' : 'mdi:home-outline'" class="text-xl"
                    :class="isActive('home') ? 'text-blue-600' : 'text-gray-600'" />
                <span class="text-xs mt-1"
                    :class="isActive('home') ? 'text-blue-600 font-medium' : 'text-gray-600'">Home</span>
            </Link>

            <!-- Chats -->
            <Link href="/chat" class="flex flex-col items-center px-3 py-1">
                <Icon :icon="isActive('chat') ? 'mdi:chat' : 'mdi:chat-outline'" class="text-xl"
                    :class="isActive('chat') ? 'text-blue-600' : 'text-gray-600'" />
                <span class="text-xs mt-1"
                    :class="isActive('chat') ? 'text-blue-600 font-medium' : 'text-gray-600'">Chats</span>
            </Link>

            <!-- Sell (Centered & Highlighted) -->
            <div class="flex flex-col items-center px-3 py-1 relative -top-3">

                <!-- Gradient Border Circle -->
                <div
                    class="rounded-full p-[4px] bg-gradient-to-r from-brand-orange via-brand-teal to-brand-blue shadow-xl">

                    <!-- Inner White Circle -->
                    <Link :href="route('user.ads.create')"
                        class="flex items-center justify-center w-8 h-8 rounded-full bg-white hover:scale-105 transition-all duration-200">
                        <Icon icon="mdi:plus" class="text-xl text-black" />
                    </Link>

                </div>

                <!-- Text -->
                <span class="text-xs mt-1 text-gray-600 font-medium">Sell</span>
            </div>

            <!-- My Ads -->
            <Link href="user/my/ads" class="flex flex-col items-center px-3 py-1">
                <Icon :icon="isActive('myads') ? 'mdi:bullhorn' : 'mdi:bullhorn-outline'" class="text-xl"
                    :class="isActive('myads') ? 'text-blue-600' : 'text-gray-600'" />
                <span class="text-xs mt-1" :class="isActive('myads') ? 'text-blue-600 font-medium' : 'text-gray-600'">My
                    Ads</span>
            </Link>

            <!-- Account -->
            <!-- Account -->
            <Link :href="accountLink" class="flex flex-col items-center px-3 py-1">

                <!-- Avatar if logged in -->
                <div v-if="user" class="w-6 h-6 rounded-full overflow-hidden border"
                    :class="isActive('account') ? 'border-blue-600' : 'border-gray-300'">

                    <!-- Profile Image -->
                    <img v-if="user?.profile?.profile_image" :src="`/storage/${user.profile.profile_image}`"
                        class="w-full h-full object-cover" />

                    <!-- Initial fallback -->
                    <div v-else
                        class="w-full h-full flex items-center justify-center text-[10px] font-semibold text-white bg-gradient-to-r from-blue-500 to-purple-600">
                        {{ user.name.charAt(0) }}
                    </div>
                </div>

                <!-- Icon if guest -->
                <Icon v-else :icon="isActive('account') ? 'mdi:account' : 'mdi:account-outline'" class="text-xl"
                    :class="isActive('account') ? 'text-blue-600' : 'text-gray-600'" />

                <span class="text-xs mt-1" :class="isActive('account') ? 'text-blue-600 font-medium' : 'text-gray-600'">
                    Account
                </span>

            </Link>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

const page = usePage()
const user = computed(() => page.props.auth?.user)
useForceTheme('light');
// Compute account link based on user authentication
const accountLink = computed(() => {
    return user.value ? route('account') : route('amo.login')
})

// Helper function to determine active route
const isActive = (page) => {
    if (typeof window === 'undefined') return false

    const currentPath = window.location.pathname

    switch (page) {
        case 'home':
            return currentPath === '/'
        case 'chat':
            return currentPath.startsWith('/chat')
        case 'myads':
            return currentPath.startsWith('/my/ads')
        case 'account':
            return user.value ? currentPath.includes('/profile') : currentPath === '/login'
        default:
            return false
    }
}
</script>

<style scoped>
/* Optional: Add animation for bottom nav */
.fixed.bottom-0 {
    transition: transform 0.3s ease;
}

/* Hide bottom nav on scroll down (optional) */
.bottom-nav-hidden {
    transform: translateY(100%);
}
</style>