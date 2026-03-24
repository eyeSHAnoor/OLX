<template>
    <OlxLayout>
        <AppContainer class="max-w-full mx-auto px-0 sm:px-4">

            <Head title="My Account" />

            <!-- Account Sections -->
            <div class="p-4 space-y-6">
                <!-- Main Navigation Cards -->
                <div class="grid grid-cols-2 gap-3">
                    <Link href="/motors"
                        class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col items-center gap-2 hover:border-brand-teal/30 transition-all">
                        <div class="w-12 h-12 rounded-full bg-brand-teal/10 flex items-center justify-center">
                            <Icon icon="mdi:car" class="text-2xl text-brand-teal" />
                        </div>
                        <span class="text-sm font-medium">Motors</span>
                    </Link>

                    <Link href="/property"
                        class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col items-center gap-2 hover:border-brand-blue/30 transition-all">
                        <div class="w-12 h-12 rounded-full bg-brand-blue/10 flex items-center justify-center">
                            <Icon icon="mdi:home" class="text-2xl text-brand-blue" />
                        </div>
                        <span class="text-sm font-medium">Property</span>
                    </Link>
                </div>

                <!-- Account Settings Section -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-4 border-b border-gray-100">
                        <h2 class="font-semibold text-gray-800">Account Settings</h2>
                    </div>

                    <div class="divide-y divide-gray-100">
                        <!-- Profile -->
                        <Link :href="route('user.profile', user?.id)"
                            class="flex items-center gap-3 p-4 hover:bg-gray-50/80 active:bg-gray-100 transition-colors">
                            <div class="w-10 h-10 rounded-full bg-brand-teal/10 flex items-center justify-center">
                                <Icon icon="mdi:account-outline" class="text-xl text-brand-teal" />
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-800">Profile</p>
                                <p class="text-xs text-gray-500">View and edit your profile</p>
                            </div>
                            <Icon icon="mdi:chevron-right" class="text-gray-400 text-lg" />
                        </Link>

                        <!-- My Ads -->
                        <Link :href="route('user.ads')"
                            class="flex items-center gap-3 p-4 hover:bg-gray-50/80 active:bg-gray-100 transition-colors">
                            <div class="w-10 h-10 rounded-full bg-brand-blue/10 flex items-center justify-center">
                                <Icon icon="mdi:bullhorn-outline" class="text-xl text-brand-blue" />
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-800">My Ads</p>
                                <p class="text-xs text-gray-500">Manage your listings</p>
                            </div>
                            <Icon icon="mdi:chevron-right" class="text-gray-400 text-lg" />
                        </Link>

                        <!-- Orders -->
                        <Link href="/orders"
                            class="flex items-center gap-3 p-4 hover:bg-gray-50/80 active:bg-gray-100 transition-colors">
                            <div class="w-10 h-10 rounded-full bg-brand-teal/10 flex items-center justify-center">
                                <Icon icon="mdi:package-variant" class="text-xl text-brand-teal" />
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-800">Orders</p>
                                <p class="text-xs text-gray-500">Track your purchases</p>
                            </div>
                            <Icon icon="mdi:chevron-right" class="text-gray-400 text-lg" />
                        </Link>

                        <!-- Favourites -->
                        <Link href="/favorites"
                            class="flex items-center gap-3 p-4 hover:bg-gray-50/80 active:bg-gray-100 transition-colors">
                            <div class="w-10 h-10 rounded-full bg-brand-blue/10 flex items-center justify-center">
                                <Icon icon="mdi:heart-outline" class="text-xl text-brand-blue" />
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-800">Favourites</p>
                                <p class="text-xs text-gray-500">Saved items</p>
                            </div>
                            <Icon icon="mdi:chevron-right" class="text-gray-400 text-lg" />
                        </Link>

                        <!-- Chats -->
                        <Link href="/chat"
                            class="flex items-center gap-3 p-4 hover:bg-gray-50/80 active:bg-gray-100 transition-colors">
                            <div class="w-10 h-10 rounded-full bg-brand-teal/10 flex items-center justify-center">
                                <Icon icon="mdi:chat-outline" class="text-xl text-brand-teal" />
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-800">Chats</p>
                                <p class="text-xs text-gray-500">Your conversations</p>
                            </div>
                            <Icon icon="mdi:chevron-right" class="text-gray-400 text-lg" />
                        </Link>
                    </div>
                </div>

                <!-- Subscription & Settings Section -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-4 border-b border-gray-100">
                        <h2 class="font-semibold text-gray-800">More</h2>
                    </div>

                    <div class="divide-y divide-gray-100">
                        <!-- Subscription with Premium Badge -->
                        <Link :href="route('subscriptions.index')"
                            class="flex items-center gap-3 p-4 hover:bg-gray-50/80 active:bg-gray-100 transition-colors">
                            <div
                                class="w-10 h-10 rounded-full bg-gradient-to-r from-brand-teal to-brand-blue flex items-center justify-center">
                                <Icon icon="mdi:crown-outline" class="text-xl text-white" />
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="font-medium text-gray-800">Subscription</p>
                                    <span
                                        class="px-2 py-0.5 bg-gradient-to-r from-brand-teal to-brand-blue text-white text-[10px] rounded-full">Premium</span>
                                </div>
                                <p class="text-xs text-gray-500">Manage your plan</p>
                            </div>
                            <Icon icon="mdi:chevron-right" class="text-gray-400 text-lg" />
                        </Link>

                        <!-- Settings -->
                        <Link href="/amo/setting"
                            class="flex items-center gap-3 p-4 hover:bg-gray-50/80 active:bg-gray-100 transition-colors">
                            <div class="w-10 h-10 rounded-full bg-brand-teal/10 flex items-center justify-center">
                                <Icon icon="mdi:cog-outline" class="text-xl text-brand-teal" />
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-800">Settings</p>
                                <p class="text-xs text-gray-500">App preferences</p>
                            </div>
                            <Icon icon="mdi:chevron-right" class="text-gray-400 text-lg" />
                        </Link>
                    </div>
                </div>

                <!-- Logout Button -->
                <Link href="/logout" method="post" as="button"
                    class="w-full flex items-center justify-center gap-2 p-4 bg-red-50 text-red-600 rounded-xl font-medium hover:bg-red-100 active:bg-red-200 transition-colors">
                    <Icon icon="mdi:logout" class="text-lg" />
                    Logout
                </Link>

                <!-- Login Section for Guests -->
                <div v-if="!user" class="space-y-3">
                    <Link :href="route('login')"
                        class="w-full flex items-center justify-center gap-2 p-4 bg-gradient-to-r from-brand-teal to-brand-blue text-white rounded-xl font-medium hover:opacity-90 transition-opacity">
                        <Icon icon="mdi:login" class="text-lg" />
                        Login
                    </Link>

                    <Link :href="route('user.ads.create')"
                        class="w-full flex items-center justify-center gap-2 p-4 bg-white border-2 border-brand-teal text-brand-teal rounded-xl font-medium hover:bg-brand-teal/5 transition-colors">
                        <Icon icon="mdi:plus" class="text-lg" />
                        SELL
                    </Link>
                </div>
            </div>
        </AppContainer>
    </OlxLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import OlxLayout from '@/layouts/OlxLayout.vue'

const page = usePage()
const user = computed(() => page.props.auth?.user)

// Sample stats - replace with actual data from your backend
const stats = computed(() => ({
    ads: user.value ? 12 : 0,
    favourites: user.value ? 24 : 0,
    orders: user.value ? 6 : 0
}))

useForceTheme('light')
</script>

<style scoped>
/* Smooth page transitions */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Touch-friendly tap targets */
button,
a {
    min-height: 44px;
    -webkit-tap-highlight-color: transparent;
}

/* Active state for touch devices */
@media (hover: none) {
    .active\:bg-gray-100:active {
        background-color: #f3f4f6;
    }

    .active\:bg-red-200:active {
        background-color: #fecaca;
    }
}

/* Pull to refresh hint (optional) */
.pull-to-refresh {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 60px;
    background: linear-gradient(to bottom, #f3f4f6, transparent);
    transform: translateY(-100%);
    transition: transform 0.3s ease;
    pointer-events: none;
    z-index: 100;
}

.pull-to-refresh.show {
    transform: translateY(0);
}

/* Bottom sheet animation for modals (if needed) */
.bottom-sheet-enter-active,
.bottom-sheet-leave-active {
    transition: transform 0.3s ease;
}

.bottom-sheet-enter-from,
.bottom-sheet-leave-to {
    transform: translateY(100%);
}
</style>