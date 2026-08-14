<template>
    <!-- Mobile Bottom Navigation - Only visible on mobile -->
    <div :class="['md:hidden fixed bottom-0 left-0 right-0 border-t z-50 shadow-lg',
        theme.bg, theme.border]">
        <div class="flex justify-around items-center px-auto max-w-md mx-auto pl-2">
            <div class="flex justify-around items-center w-2/5">
                <!-- Home -->
                <Link href="/" class="flex flex-col items-center">
                    <img src="/images/logo.png" class="w-8 h-6 object-contain" />
                    <span :class="['text-xs', theme.textMuted]">Mercatus</span>
                </Link>

                <!-- Chats -->
                <Link href="/chat" class="flex flex-col items-center px-3 py-1">
                    <Icon :icon="isActive('chat') ? 'mdi:chat' : 'mdi:chat-outline'" class="text-xl"
                        :class="isActive('chat') ? theme.textAccent : theme.textMuted" />
                    <span class="text-xs mt-1"
                        :class="isActive('chat') ? [theme.textAccent, 'font-medium'] : theme.textMuted">
                        Chats
                    </span>
                </Link>
            </div>

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

            <div class="flex justify-around items-center gap-0 w-2/5">
                <!-- Notifications -->
                <div class="relative">
                    <Link href="/notifications" class="flex flex-col items-center">
                        <Icon :icon="isActive('notifications') ? 'mdi:bell' : 'mdi:bell-outline'" class="text-xl"
                            :class="isActive('notifications') ? theme.textAccent : theme.textMuted" />
                        <span class="text-[11px] mt-1"
                            :class="isActive('notifications') ? [theme.textAccent, 'font-medium'] : theme.textMuted">
                            Notifications
                        </span>
                    </Link>
                    <!-- Badge -->
                    <span v-if="unreadCount > 0"
                        class="absolute -top-1 right-1 bg-red-500 text-white text-[8px] px-1.5 py-0.5 rounded-full">
                        {{ unreadCount }}
                    </span>
                </div>

                <!-- Account -->
                <Link :href="accountLink" class="flex flex-col items-center px-3 py-1">
                    <!-- Avatar if logged in -->
                    <div v-if="user" class="w-6 h-6 rounded-full overflow-hidden border"
                        :class="[isActive('account') ? theme.border : 'border-gray-300']">
                        <img v-if="user?.profile_image" :src="`/storage/${user.profile_image}`"
                            class="w-full h-full object-cover" />
                        <div v-else
                            class="w-full h-full flex items-center justify-center text-[10px] font-semibold text-white bg-gradient-to-r from-blue-500 to-purple-600">
                            {{ user.name.charAt(0) }}
                        </div>
                    </div>
                    <!-- Icon if guest -->
                    <Icon v-else :icon="isActive('account') ? 'mdi:account' : 'mdi:account-outline'" class="text-xl"
                        :class="isActive('account') ? theme.textAccent : theme.textMuted" />
                    <span class="text-xs mt-1"
                        :class="isActive('account') ? [theme.textAccent, 'font-medium'] : theme.textMuted">
                        Account
                    </span>
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { Icon } from "@iconify/vue";
import { useTheme } from "@/composables/useTheme";
const { theme } = useTheme();

const page = usePage();
const user = computed(() => page.props.auth?.user);
const notifications = computed(() => page.props.notifications || []);
const unreadCount = computed(() => page.props.unreadCount || 0);
useForceTheme("light");
// Compute account link based on user authentication
const accountLink = computed(() => {
    return user.value ? route("account") : route("login");
});

// Helper function to determine active route
const isActive = (page) => {
    if (typeof window === "undefined") return false;

    const currentPath = window.location.pathname;

    switch (page) {
        case "home":
            return currentPath === "/";
        case "chat":
            return currentPath.startsWith("/chat");
        case "myads":
            return currentPath.startsWith("/my/ads");
        case "account":
            return user.value ? currentPath.includes("/profile") : currentPath === "/login";
        default:
            return false;
    }
};
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
