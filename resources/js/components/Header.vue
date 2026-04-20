<template>
    <nav class="border-b">
        <!-- TOP BAR -->
        <div class="w-full bg-gradient-to-r from-brand-blue/15 via-brand-teal/15 to-brand-orange/15">
            <div class="max-w-full md:max-w-9/11 mx-auto px-4 md:px-3 py-4 flex items-center justify-between">

                <!-- Logo + Hamburger -->
                <div class="flex items-center gap-3 w-full md:w-auto justify-between">
                    <Link :href="route('home')" class="flex items-center">
                        <img src="/images/logo.png" alt="Logo" class="h-6 md:h-7 w-auto" />
                    </Link>

                    <!-- Desktop Top Links -->
                    <div class="hidden md:flex items-center gap-5 text-sm font-medium">
                        <button class="flex items-center gap-1 hover:text-blue-600">
                            <Icon icon="mdi:car-outline" class="text-base" />
                            Motors
                        </button>
                        <button class="flex items-center gap-1 hover:text-blue-600">
                            <Icon icon="mdi:home-outline" class="text-base" />
                            Property
                        </button>
                    </div>
                </div>

                <!-- Right Icons & SELL -->
                <div class="hidden md:flex items-center gap-4">
                    <div v-if="user" class="flex items-center gap-3 relative">
                        <Icon icon="mdi:chat-outline" class="text-base cursor-pointer hover:text-blue-600"
                            @click="router.visit('/chat')" />
                        <div class="relative" v-click-outside="() => showNotifications = false">
                            <Icon icon="mdi:bell-outline" class="text-base cursor-pointer hover:text-blue-600"
                                @click="showNotifications = !showNotifications" />

                            <!-- Badge -->
                            <span v-if="unreadCount > 0"
                                class="absolute -top-2 -right-2 bg-red-500 text-white text-[8px] px-1.5 py-0.5 rounded-full">
                                {{ unreadCount }}
                            </span>

                            <!-- Dropdown -->
                            <div v-if="showNotifications"
                                class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-100 z-50 max-h-96 overflow-y-auto">

                                <!-- Simple Header -->
                                <div class="p-3 border-b">
                                    <span class="text-sm font-semibold">Notifications</span>
                                </div>

                                <!-- Loading indicator while marking as read -->
                                <div v-if="isMarkingRead" class="p-4 text-center">
                                    <Icon icon="mdi:loading" class="text-2xl animate-spin text-blue-600 mx-auto" />
                                    <p class="text-xs text-gray-500 mt-2">Updating...</p>
                                </div>


                                <div v-else-if="notifications.length">
                                    <div v-for="n in notifications" :key="n.id"
                                        class="p-3 text-xs hover:bg-gray-50 border-b cursor-pointer"
                                        @click="openNotification(n)" :class="{ 'bg-blue-50/30': !n.read_at }">
                                        <div>
                                            {{ n.data.message || n.data.body }}
                                            <div class="text-[9px] text-gray-400 mt-0.5">
                                                {{ new Date(n.created_at).toLocaleString() }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- View all link -->
                                    <div class="p-2 text-center border-t">
                                        <Link href="/notifications" class="text-xs text-blue-600 hover:text-blue-800">
                                            View all notifications
                                        </Link>
                                    </div>
                                </div>

                                <div v-else class="p-4 text-xs text-gray-500 text-center">
                                    <Icon icon="mdi:bell-off-outline" class="text-2xl mx-auto mb-2 text-gray-300" />
                                    No notifications
                                </div>
                            </div>
                        </div>
                        <Icon icon="mdi:cart-outline" class="text-base cursor-pointer hover:text-blue-600" />

                        <!-- Avatar Dropdown -->
                        <div class="relative" v-click-outside="() => showDropdown = false">
                            <div @click="showDropdown = !showDropdown"
                                class="w-6 h-6 rounded-full cursor-pointer border border-white shadow-sm hover:shadow transition-shadow overflow-hidden">

                                <!-- Profile Image -->
                                <img v-if="user?.profile?.profile_image" :src="`/storage/${user.profile.profile_image}`"
                                    class="w-full h-full object-cover" />

                                <!-- Fallback Avatar -->
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
                                <div v-if="showDropdown"
                                    class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50">
                                    <!-- User Info Header -->
                                    <div class="px-3 py-2 border-b border-gray-100">
                                        <p class="text-sm font-medium text-gray-900">{{ user.name || 'User Name' }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-0.5 truncate">{{ user.email ||
                                            'user@example.com' }}
                                        </p>
                                    </div>

                                    <!-- Menu Items -->
                                    <div class="py-0.5">
                                        <Link :href="route('user.profile', user.id)"
                                            class="flex items-center gap-2 px-3 py-1.5 text-xs text-gray-700 hover:text-blue-600 transition-colors">
                                            <Icon icon="mdi:account-outline" class="text-sm text-gray-500" />
                                            <span>Public Profile</span>
                                        </Link>
                                        <Link href="/orders" disabled
                                            class="flex items-center gap-2 px-3 py-1.5 text-xs text-gray-700 hover:text-blue-600 transition-colors">
                                            <Icon icon="mdi:package-variant" class="text-sm text-gray-500" />
                                            <span>My Orders</span>
                                        </Link>
                                        <Link href="/favorites"
                                            class="flex items-center gap-2 px-3 py-1.5 text-xs text-gray-700 hover:text-blue-600 transition-colors">
                                            <Icon icon="mdi:heart-outline" class="text-sm text-gray-500" />
                                            <span>My Favourites</span>
                                        </Link>
                                        <Link href="/user/my/ads"
                                            class="flex items-center gap-2 px-3 py-1.5 text-xs text-gray-700 hover:text-blue-600 transition-colors">
                                            <Icon icon="mdi:bullhorn-outline" class="text-sm text-gray-500" />
                                            <span>My Ads</span>
                                        </Link>
                                        <div class="border-t border-gray-100 my-1"></div>
                                        <Link :href="route('subscriptions.index')"
                                            class="flex items-center gap-2 px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-50 transition-colors">
                                            <Icon icon="mdi:crown-outline" class="text-sm text-yellow-500" />
                                            <span>Subscription</span>
                                            <span
                                                class="ml-auto bg-green-100 text-green-600 text-[8px] px-1.5 py-0.5 rounded-full">Premium</span>
                                        </Link>
                                        <Link href="/amo/setting"
                                            class="flex items-center gap-2 px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-50 transition-colors">
                                            <Icon icon="mdi:cog-outline" class="text-sm text-gray-500" />
                                            <span>Settings</span>
                                        </Link>
                                        <div class="border-t border-gray-100 my-1"></div>
                                        <Link href="/logout" method="post" as="button"
                                            class="w-full flex items-center gap-2 px-3 py-1.5 text-xs text-red-600 hover:bg-red-50 transition-colors">
                                            <Icon icon="mdi:logout" class="text-sm" />
                                            <span>Logout</span>
                                        </Link>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>

                    <div v-else class="flex gap-4">
                        <div class="rounded-full p-[3px] bg-gradient-to-l from-yellow-400 via-blue-400 to-orange-400">
                            <Link
                                class="flex items-center gap-0.5 rounded-full px-3 py-1 text-xs font-medium bg-white hover:shadow transition-shadow"
                                :href="route('login')">
                                <Icon icon="mdi:plus" class="text-xs" />
                                Login
                            </Link>
                        </div>
                    </div>
                    <div class="rounded-full p-[3px] bg-gradient-to-r from-brand-orange via-brand-teal to-brand-blue">
                        <Link :href="route('user.ads.create')"
                            class="flex items-center gap-0.5 rounded-full px-3 py-1 text-xs font-medium bg-white hover:shadow transition-shadow">
                            <Icon icon="mdi:plus" class="text-xs" />
                            SELL
                        </Link>
                    </div>


                </div>
                <div class="flex md:hidden items-center gap-4">

                    <Link :href="route('subscriptions.index')"
                        class="flex items-center justify-between  text-gray-700 hover:bg-gray-50 transition-colors">
                        <Icon icon="mdi:crown" class="text-brand-orange text-4xl shrink-0" />

                    </Link>

                </div>
            </div>


        </div>

        <!-- SEARCH ROW - Conditionally hide if hideSearchBar prop is true -->
        <SearchBar v-if="!hideSearchBar" />
    </nav>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed, onUnmounted } from 'vue'
import { router, usePage, Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import SearchBar from './SearchBar.vue'

// Props to control search bar visibility
const props = defineProps({
    hideSearchBar: {
        type: Boolean,
        default: false
    }
})

// Click outside directive
const vClickOutside = {
    mounted: (el: any, binding: any) => {
        el.clickOutsideEvent = (event: Event) => {
            if (!(el === event.target || el.contains(event.target))) {
                binding.value(event)
            }
        }
        document.addEventListener('click', el.clickOutsideEvent)
    },
    unmounted: (el: any) => {
        document.removeEventListener('click', el.clickOutsideEvent)
    }
}

const page = usePage()
const showDropdown = ref(false)
const user = computed(() => page.props.auth?.user)
const mobileMenuOpen = ref(false)
const notifications = computed(() => page.props.notifications || []);
const unreadCount = computed(() => page.props.unreadCount || 0);
const showNotifications = ref(false)
const hasMarkedAsRead = ref(false)
const isMarkingRead = ref(false)
const echoInitialized = ref(false)

// Watch for dropdown opening - auto mark as read
watch(showNotifications, async (newVal) => {
    if (newVal && unreadCount.value > 0 && !hasMarkedAsRead.value && !isMarkingRead.value) {
        await markAllAsRead();
        hasMarkedAsRead.value = true;
    }
});
//console.log(page.props.notifications)

const openNotification = (notification: any) => {
    markAsRead(notification)

    if (notification.data?.url) {

        let url = notification.data.url

        // Fix 127.0.0.1 vs localhost issue
        url = url.replace('http://127.0.0.1:8000', '')

        router.visit(url)
    }
}
// Mark all as read function
const markAllAsRead = () => {
    return new Promise((resolve, reject) => {
        isMarkingRead.value = true;
        router.post(route('notifications.markAllAsRead'), {}, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                isMarkingRead.value = false;
                //console.log('All notifications marked as read');
                resolve(true);
            },
            onError: (errors) => {
                isMarkingRead.value = false;
                console.error('Failed to mark all notifications as read', errors);
                reject(errors);
            }
        });
    });
};

// Individual mark as read
const markAsRead = (notification: any) => {
    if (notification.read_at) return;
    router.post(route('notifications.markAsRead', notification.id), {}, {
        preserveScroll: true,
        preserveState: true,
    });
};

// Refresh notifications from server
const refreshNotifications = () => {
    router.reload({
        only: ['notifications', 'unreadCount'],
        preserveScroll: true,
        preserveState: true
    });
};

// SIMPLIFIED: Setup Echo listeners for ALL real-time notifications
const setupEchoListeners = () => {
    if (!user.value || !window.Echo || echoInitialized.value) return;

    const userId = user.value.id;
    //console.log('Setting up Echo listeners for user:', userId);

    try {
        // Single listener for ALL notification types (chat, rating, etc.)
        window.Echo.private(`App.Models.User.${userId}`)
            .notification((notification: any) => {
                // //console.log('🔔 New notification received:', notification);

                // Optional: Show browser notification (works for any type)
                if (Notification.permission === 'granted') {
                    // Try to get a readable message from the notification
                    const message = notification.message ||
                        notification.body ||
                        'You have a new notification';

                    new Notification('New Notification', {
                        body: message,
                        icon: '/images/logo.png'
                    });
                }

                // ALWAYS refresh notifications - this updates the bell and dropdown
                // for EVERY notification type without needing to check types
                refreshNotifications();
            });

        // Optional: Test connection
        if (window.Echo.connector?.pusher?.connection) {
            window.Echo.connector.pusher.connection.bind('connected', () => {
                //console.log('✅ Connected to Pusher');
                echoInitialized.value = true;
            });

            window.Echo.connector.pusher.connection.bind('error', (error: any) => {
                console.error('❌ Pusher connection error:', error);
            });
        }

    } catch (error) {
        console.error('Error setting up Echo:', error);
    }
};

// Request notification permission
const requestNotificationPermission = () => {
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }
};

// Lifecycle hooks
onMounted(() => {
    hasMarkedAsRead.value = false;
    requestNotificationPermission();

    // Check if Echo is available
    //console.log('Echo available:', !!window.Echo);

    if (user.value) {
        // Small delay to ensure Echo is fully initialized
        setTimeout(() => {
            setupEchoListeners();
        }, 500);
    }
});

// Watch for user changes (login/logout)
watch(user, (newUser, oldUser) => {
    // Leave old channel
    if (oldUser && window.Echo) {
        try {
            window.Echo.leave(`App.Models.User.${oldUser.id}`);
        } catch (error) {
            console.error('Error leaving channel:', error);
        }
        echoInitialized.value = false;
    }

    // Setup new channel
    if (newUser) {
        setTimeout(() => {
            setupEchoListeners();
        }, 500);
    }
});
useForceTheme('light');
// Cleanup on unmount
onUnmounted(() => {
    if (user.value && window.Echo) {
        try {
            window.Echo.leave(`App.Models.User.${user.value.id}`);
        } catch (error) {
            console.error('Error leaving channel:', error);
        }
    }
});

// Debug function to test Echo
const testEcho = () => {
    //console.log('Testing Echo connection...');
    //console.log('Echo object:', window.Echo);
    //console.log('User:', user.value);
    if (user.value && window.Echo) {
        //console.log('Channel:', `App.Models.User.${user.value.id}`);
    }
};

// Expose test function to window for debugging
if (typeof window !== 'undefined') {
    (window as any).testEcho = testEcho;
}

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