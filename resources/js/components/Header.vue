<template>
    <nav :class="['border-b', theme.border]">
        <!-- TOP BAR -->
        <div :class="['w-full', theme.bgNav]">
            <div class="max-w-full md:max-w-9/11 mx-auto px-4 md:px-3 py-4 flex items-center justify-between">

                <!-- Left: Logo -->
                <div class="flex items-center gap-3 w-full md:w-auto justify-between">
                    <Link :href="route('home')" class="flex items-center">
                        <img src="/images/logo.png" alt="Logo" class="h-6 md:h-7 w-auto" />
                    </Link>

                    <!-- Desktop Category Links -->
                    <div class="hidden md:flex items-center gap-5 text-sm font-medium" :class="theme.text">
                        <button class="flex items-center gap-1" :class="theme.hover">
                            <Icon icon="mdi:car-outline" class="text-base" /> Motors
                        </button>
                        <button class="flex items-center gap-1" :class="theme.hover">
                            <Icon icon="mdi:home-outline" class="text-base" /> Property
                        </button>
                    </div>
                </div>

                <!-- Right: Icons & Actions -->
                <div class="hidden md:flex items-center gap-4">
                    <template v-if="user">
                        <div class="flex items-center gap-3">
                            <!-- Chat -->
                            <Icon icon="mdi:chat-outline" :class="['text-base cursor-pointer', theme.text, theme.hover]"
                                @click="router.visit('/chat')" />

                            <!-- Notifications -->
                            <div class="relative" v-click-outside="() => (showNotifications = false)">
                                <Icon icon="mdi:bell-outline"
                                    :class="['text-base cursor-pointer', theme.text, theme.hover]"
                                    @click="showNotifications = !showNotifications" />

                                <span v-if="unreadCount > 0"
                                    class="absolute -top-2 -right-2 bg-red-500 text-white text-[8px] px-1.5 py-0.5 rounded-full">
                                    {{ unreadCount }}
                                </span>

                                <!-- Notifications Dropdown -->
                                <div v-if="showNotifications" :class="['absolute right-0 mt-2 w-80 rounded-lg shadow-lg border z-50 max-h-96 overflow-y-auto',
                                    theme.card, theme.cardBorder, theme.shadow]">
                                    <div class="p-3 border-b" :class="[theme.border, theme.text]">
                                        <span class="text-sm font-semibold">Notifications</span>
                                    </div>

                                    <div v-if="isMarkingRead" class="p-4 text-center">
                                        <Icon icon="mdi:loading" class="text-2xl animate-spin text-blue-600 mx-auto" />
                                        <p class="text-xs mt-2" :class="theme.textMuted">Updating...</p>
                                    </div>

                                    <div v-else-if="notifications.length">
                                        <div v-for="n in notifications" :key="n.id" :class="['p-3 text-xs border-b cursor-pointer', theme.hover, theme.border,
                                            { 'bg-blue-50/30': !n.read_at }]" @click="openNotification(n)">
                                            <div :class="theme.text">
                                                {{ n.data.message || n.data.body }}
                                                <div class="text-[9px] mt-0.5" :class="theme.textMuted">
                                                    {{ new Date(n.created_at).toLocaleString() }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="p-2 text-center border-t" :class="theme.border">
                                            <Link href="/notifications"
                                                :class="['text-xs', theme.textAccent, theme.hover]">
                                                View all notifications
                                            </Link>
                                        </div>
                                    </div>

                                    <div v-else class="p-4 text-xs text-center" :class="theme.textMuted">
                                        <Icon icon="mdi:bell-off-outline" class="text-2xl mx-auto mb-2 text-gray-300" />
                                        No notifications
                                    </div>
                                </div>
                            </div>

                            <!-- Cart -->
                            <Icon icon="mdi:cart-outline"
                                :class="['text-base cursor-pointer', theme.text, theme.hover]" />

                            <!-- Avatar Dropdown -->
                            <div class="relative" v-click-outside="() => (showDropdown = false)">
                                <div @click="showDropdown = !showDropdown" :class="['w-6 h-6 rounded-full cursor-pointer border-2 shadow-sm hover:shadow transition-shadow overflow-hidden',
                                    theme.border, theme.shadow]">
                                    <img v-if="user?.profile_image" :src="`/storage/${user.profile_image}`"
                                        class="w-full h-full object-cover" />
                                    <div v-else
                                        class="w-full h-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white text-[10px] font-semibold">
                                        {{ user?.name?.charAt(0) }}
                                    </div>
                                </div>

                                <transition enter-active-class="transition duration-200 ease-out"
                                    enter-from-class="transform scale-95 opacity-0"
                                    enter-to-class="transform scale-100 opacity-100"
                                    leave-active-class="transition duration-150 ease-in"
                                    leave-from-class="transform scale-100 opacity-100"
                                    leave-to-class="transform scale-95 opacity-0">

                                    <div v-if="showDropdown" :class="['absolute right-0 mt-2 w-56 rounded-lg shadow-lg border py-1 z-50',
                                        theme.card, theme.cardBorder, theme.shadow]">

                                        <!-- User Info -->
                                        <div class="px-3 py-2 border-b" :class="[theme.border]">
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <p class="text-sm font-medium" :class="theme.text">{{ user.name }}
                                                    </p>
                                                    <p class="text-xs mt-0.5 truncate" :class="theme.textMuted">{{
                                                        user.email }}</p>
                                                </div>
                                                <!-- Plan Badge -->
                                                <span v-if="activePlan !== 'free'"
                                                    :class="['px-2 py-0.5 text-xs font-bold rounded-full', theme.badge]">
                                                    {{ activePlan.charAt(0).toUpperCase() + activePlan.slice(1) }}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Menu Items -->
                                        <div class="py-0.5">
                                            <Link v-for="item in menuItems" :key="item.label" :href="item.route"
                                                :method="item.method" :as="item.as" :class="['flex items-center gap-2 px-3 py-1.5 text-xs transition-colors',
                                                    theme.text, theme.hover]">
                                                <Icon :icon="item.icon" :class="['text-sm', theme.textMuted]" />
                                                <span>{{ item.label }}</span>
                                                <span v-if="item.badge"
                                                    class="ml-auto text-[8px] px-1.5 py-0.5 rounded-full"
                                                    :class="theme.badge">{{ item.badge }}</span>
                                            </Link>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                        </div>
                    </template>

                    <!-- Guest Actions -->
                    <template v-else>
                        <div class="rounded-full p-[3px]" :class="['bg-gradient-to-r', theme.gradient]">
                            <Link
                                :class="['flex items-center gap-0.5 rounded-full px-3 py-1 text-xs font-medium bg-white hover:shadow transition-shadow']"
                                :href="route('login')">
                                <Icon icon="mdi:login" class="text-xs" /> Login
                            </Link>
                        </div>
                    </template>

                    <!-- SELL Button -->
                    <div class="rounded-full p-[3px] bg-gradient-to-r from-brand-orange via-brand-teal to-brand-blue">
                        <Link :href="route('user.ads.create')"
                            class="flex items-center gap-0.5 rounded-full px-3 py-1 text-xs font-medium bg-white hover:shadow transition-shadow">
                            <Icon icon="mdi:plus" class="text-xs" />
                            SELL
                        </Link>
                    </div>
                </div>

                <!-- Mobile Crown -->
                <div class="flex md:hidden items-center gap-4">
                    <Link :href="route('subscriptions.index')"
                        :class="['flex items-center justify-between', theme.text, theme.hover]">
                        <Icon icon="mdi:crown" class="text-3xl shrink-0" :class="theme.icon" />
                    </Link>
                </div>
            </div>
        </div>

        <!-- Search Bar -->
        <SearchBar v-if="!hideSearchBar" />
    </nav>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, onUnmounted } from "vue"
import { router, usePage, Link } from "@inertiajs/vue3"
import { useTheme } from "@/composables/useTheme"
import { Icon } from "@iconify/vue"
import SearchBar from "./SearchBar.vue"

const props = defineProps({
    hideSearchBar: { type: Boolean, default: false }
})

const { theme, activePlan } = useTheme()
const page = usePage()
const user = computed(() => page.props.auth?.user)

// Menu items configuration
const menuItems = computed(() => [
    { label: 'Public Profile', route: route('user.profile', user.value?.id), icon: 'mdi:account-outline' },
    { label: 'My Orders', route: '/orders', icon: 'mdi:package-variant' },
    { label: 'My Favourites', route: '/favorites', icon: 'mdi:heart-outline' },
    { label: 'My Ads', route: '/user/my/ads', icon: 'mdi:bullhorn-outline' },
    ...(user.value?.referral_code ? [{
        label: 'My Referrals',
        route: '/downline-referrals',
        icon: 'mdi:account-multiple-outline',
        badge: 'Prominent'
    }] : []),
    { label: 'Subscription', route: route('subscriptions.index'), icon: 'mdi:crown-outline', badge: 'Premium' },
    { label: 'Settings', route: '/amo/setting', icon: 'mdi:cog-outline' },
    { label: 'Logout', route: '/logout', icon: 'mdi:logout', method: 'post', as: 'button' }
])

// State
const showDropdown = ref(false)
const showNotifications = ref(false)
const isMarkingRead = ref(false)
const echoInitialized = ref(false)

const notifications = computed(() => page.props.notifications || [])
const unreadCount = computed(() => page.props.unreadCount || 0)

// Click outside directive
const vClickOutside = {
    mounted(el: any, binding: any) {
        el.clickOutsideEvent = (event: Event) => {
            if (!(el === event.target || el.contains(event.target))) {
                binding.value(event)
            }
        }
        document.addEventListener("click", el.clickOutsideEvent)
    },
    unmounted(el: any) {
        document.removeEventListener("click", el.clickOutsideEvent)
    }
}

// Notification functions
const markAllAsRead = () => {
    return new Promise((resolve, reject) => {
        isMarkingRead.value = true
        router.post(route("notifications.markAllAsRead"), {}, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                isMarkingRead.value = false
                resolve(true)
            },
            onError: (errors) => {
                isMarkingRead.value = false
                reject(errors)
            }
        })
    })
}

const markAsRead = (notification: any) => {
    if (notification.read_at) return
    router.post(route("notifications.markAsRead", notification.id), {}, {
        preserveScroll: true,
        preserveState: true
    })
}

const openNotification = (notification: any) => {
    markAsRead(notification)
    if (notification.data?.url) {
        const url = notification.data.url.replace("http://127.0.0.1:8000", "")
        router.visit(url)
    }
}

const refreshNotifications = () => {
    router.reload({
        only: ["notifications", "unreadCount"],
        preserveScroll: true,
        preserveState: true
    })
}

// Auto-mark notifications as read when dropdown opens
watch(showNotifications, async (newVal) => {
    if (newVal && unreadCount.value > 0 && !isMarkingRead.value) {
        await markAllAsRead()
    }
})

// Echo setup
const setupEchoListeners = () => {
    if (!user.value || !window.Echo || echoInitialized.value) return

    const userId = user.value.id
    try {
        window.Echo.private(`App.Models.User.${userId}`).notification((notification: any) => {
            if (Notification.permission === "granted") {
                new Notification("New Notification", {
                    body: notification.message || notification.body || "You have a new notification",
                    icon: "/images/logo.png"
                })
            }
            refreshNotifications()
        })

        if (window.Echo.connector?.pusher?.connection) {
            window.Echo.connector.pusher.connection.bind("connected", () => {
                echoInitialized.value = true
            })
        }
    } catch (error) {
        console.error("Error setting up Echo:", error)
    }
}

// Lifecycle
onMounted(() => {
    if (user.value) {
        setTimeout(setupEchoListeners, 500)
    }
})

watch(user, (newUser, oldUser) => {
    if (oldUser && window.Echo) {
        try {
            window.Echo.leave(`App.Models.User.${oldUser.id}`)
        } catch (error) {
            console.error("Error leaving channel:", error)
        }
        echoInitialized.value = false
    }
    if (newUser) {
        setTimeout(setupEchoListeners, 500)
    }
})

onUnmounted(() => {
    if (user.value && window.Echo) {
        try {
            window.Echo.leave(`App.Models.User.${user.value.id}`)
        } catch (error) {
            console.error("Error leaving channel:", error)
        }
    }
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.scale-enter-active,
.scale-leave-active {
    transition: all 0.2s ease;
}

.scale-enter-from,
.scale-leave-to {
    opacity: 0;
    transform: scale(0.95);
}
</style>