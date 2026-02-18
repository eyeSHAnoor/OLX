<template>
    <nav class="border-b">

        <!-- TOP BAR -->
        <div class="w-full bg-slate-100">
            <div class="max-w-9/11 mx-auto px-4 py-4 flex items-center justify-between">

                <!-- Logo + Hamburger -->
                <div class="flex items-center gap-4 w-full md:w-auto justify-between">
                    <Link :href="route('home')" class="flex items-center">
                        <img src="/images/logo.png" alt="Logo" class="h-10 w-auto" />
                    </Link>

                    <!-- Mobile Hamburger -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-gray-700">
                        <Icon :icon="mobileMenuOpen ? 'mdi:close' : 'mdi:menu'" class="text-3xl" />
                    </button>

                    <!-- Desktop Top Links -->
                    <div class="hidden md:flex items-center gap-8 text-xl font-bold">
                        <button class="flex items-center gap-1 hover:text-blue-600">
                            <Icon icon="mdi:car-outline" class="text-3xl" />
                            Motors
                        </button>

                        <button class="flex items-center gap-1 hover:text-blue-600">
                            <Icon icon="mdi:home-outline" class="text-3xl" />
                            Property
                        </button>
                    </div>
                </div>

                <!-- Right Icons & SELL -->
                <div class="hidden md:flex items-center gap-8">
                    <div class="flex items-center gap-5 relative" v-if="user">
                        <Icon icon="mdi:chat-outline" class="text-3xl cursor-pointer hover:text-blue-600" />
                        <Icon icon="mdi:bell-outline" class="text-3xl cursor-pointer hover:text-blue-600" />
                        <Icon icon="mdi:cart-outline" class="text-3xl cursor-pointer hover:text-blue-600" />

                        <!-- Avatar with Dropdown -->
                        <div class="relative" v-click-outside="() => showDropdown = false">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 cursor-pointer border-2 border-white shadow-md hover:shadow-lg transition-shadow"
                                @click="showDropdown = !showDropdown">
                                <!-- If you have user avatar image -->
                                <!-- <img v-if="user.avatar" :src="user.avatar" class="w-full h-full rounded-full object-cover" /> -->
                            </div>

                            <!-- Professional Dropdown Menu -->
                            <transition enter-active-class="transition duration-200 ease-out"
                                enter-from-class="transform scale-95 opacity-0"
                                enter-to-class="transform scale-100 opacity-100"
                                leave-active-class="transition duration-150 ease-in"
                                leave-from-class="transform scale-100 opacity-100"
                                leave-to-class="transform scale-95 opacity-0">

                                <div v-if="showDropdown"
                                    class="absolute right-0 mt-3 w-64 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50">

                                    <!-- User Info Header -->
                                    <div class="px-4 py-3 border-b border-gray-100">
                                        <p class="text-sm font-semibold text-gray-900">{{ user.name || 'User Name' }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ user.email || 'user@example.com' }}
                                        </p>
                                    </div>

                                    <!-- Menu Items -->
                                    <div class="py-1">
                                        <!-- Public Profile -->
                                        <Link href="/profile"
                                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                            <Icon icon="mdi:account-outline" class="text-xl text-gray-500" />
                                            <span>Public Profile</span>
                                        </Link>

                                        <!-- My Orders (shown twice as requested) -->
                                        <Link href="/orders"
                                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                            <Icon icon="mdi:package-variant" class="text-xl text-gray-500" />
                                            <span>My Orders</span>
                                        </Link>

                                        <!-- My Favourites -->
                                        <Link href="/favourites"
                                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                            <Icon icon="mdi:heart-outline" class="text-xl text-gray-500" />
                                            <span>My Favourites</span>
                                        </Link>

                                        <!-- My Orders (second instance) -->
                                        <Link href="/orders"
                                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                            <Icon icon="mdi:package-variant-closed" class="text-xl text-gray-500" />
                                            <span>My Orders</span>
                                            <span
                                                class="ml-auto bg-blue-100 text-blue-600 text-xs px-2 py-0.5 rounded-full">2</span>
                                        </Link>

                                        <!-- My Ads -->
                                        <Link href="/ads"
                                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                            <Icon icon="mdi:bullhorn-outline" class="text-xl text-gray-500" />
                                            <span>My Ads</span>
                                        </Link>

                                        <!-- Divider -->
                                        <div class="border-t border-gray-100 my-1"></div>

                                        <!-- Subscription -->
                                        <Link :href="route('subscriptions.index')"
                                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                            <Icon icon="mdi:crown-outline" class="text-xl text-yellow-500" />
                                            <span>Subscription</span>
                                            <span
                                                class="ml-auto bg-green-100 text-green-600 text-xs px-2 py-0.5 rounded-full">Premium</span>
                                        </Link>

                                        <!-- Settings -->
                                        <Link href="/settings"
                                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                            <Icon icon="mdi:cog-outline" class="text-xl text-gray-500" />
                                            <span>Settings</span>
                                        </Link>

                                        <!-- Divider -->
                                        <div class="border-t border-gray-100 my-1"></div>

                                        <!-- Logout -->
                                        <Link href="/logout" method="post" as="button"
                                            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                            <Icon icon="mdi:logout" class="text-xl" />
                                            <span>Logout</span>
                                        </Link>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>

                    <div v-else
                        class="rounded-full p-[5px] bg-gradient-to-l from-yellow-400 via-blue-400 to-orange-400">
                        <Link
                            class="flex items-center gap-1 rounded-full px-6 py-2 text-xl font-bold bg-white hover:shadow-md transition-shadow"
                            :href="route('amo.login')">
                            <Icon icon="mdi:plus" />
                            Login
                        </Link>
                    </div>

                    <div class="rounded-full p-[5px] bg-gradient-to-r from-yellow-400 via-blue-400 to-orange-400">
                        <button
                            class="flex items-center gap-1 rounded-full px-6 py-2 text-xl font-bold bg-white hover:shadow-md transition-shadow">
                            <Icon icon="mdi:plus" />
                            SELL
                        </button>
                    </div>
                </div>

            </div>

            <!-- Mobile Menu -->
            <div v-if="mobileMenuOpen" class="md:hidden px-4 pb-6 space-y-4 bg-gray-50 rounded-b-xl shadow-inner">

                <!-- USER HEADER with Mobile Dropdown -->
                <div v-if="user" class="relative" v-click-outside="() => showDropdown = false">
                    <div class="flex items-center gap-3 bg-white rounded-xl p-4 shadow-sm cursor-pointer"
                        @click="showDropdown = !showDropdown">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-600" />

                        <div class="flex-1">
                            <p class="text-sm font-semibold">{{ user.name || 'User Name' }}</p>
                            <p class="text-xs text-gray-500">{{ user.email || 'user@example.com' }}</p>
                        </div>

                        <Icon :icon="showDropdown ? 'mdi:chevron-up' : 'mdi:chevron-down'"
                            class="text-xl text-gray-500" />
                    </div>

                    <!-- Mobile Dropdown -->
                    <transition enter-active-class="transition duration-200 ease-out"
                        enter-from-class="transform scale-95 opacity-0"
                        enter-to-class="transform scale-100 opacity-100">

                        <div v-if="showDropdown"
                            class="absolute left-0 right-0 mt-2 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50">
                            <!-- Same dropdown items as desktop but full width -->
                            <div class="py-1">
                                <Link href="/profile"
                                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
                                    <Icon icon="mdi:account-outline" class="text-xl" />
                                    <span>Public Profile</span>
                                </Link>
                                <Link href="/orders"
                                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
                                    <Icon icon="mdi:package-variant" class="text-xl" />
                                    <span>My Orders</span>
                                </Link>
                                <Link href="/favourites"
                                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
                                    <Icon icon="mdi:heart-outline" class="text-xl" />
                                    <span>My Favourites</span>
                                </Link>
                                <Link href="/orders"
                                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
                                    <Icon icon="mdi:package-variant-closed" class="text-xl" />
                                    <span>My Orders</span>
                                </Link>
                                <Link href="/ads"
                                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
                                    <Icon icon="mdi:bullhorn-outline" class="text-xl" />
                                    <span>My Ads</span>
                                </Link>
                                <div class="border-t border-gray-100 my-1"></div>
                                <Link :href="route('subscriptions.index')"
                                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
                                    <Icon icon="mdi:crown-outline" class="text-xl text-yellow-500" />
                                    <span>Subscription</span>
                                </Link>
                                <Link href="/settings"
                                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
                                    <Icon icon="mdi:cog-outline" class="text-xl" />
                                    <span>Settings</span>
                                </Link>
                                <div class="border-t border-gray-100 my-1"></div>
                                <Link href="/logout" method="post" as="button"
                                    class="w-full flex items-center gap-3 px-4 py-3 text-sm text-red-600 hover:bg-red-50">
                                    <Icon icon="mdi:logout" class="text-xl" />
                                    <span>Logout</span>
                                </Link>
                            </div>
                        </div>
                    </transition>
                </div>

                <!-- LOGIN -->
                <div v-else class="bg-white rounded-xl p-4 shadow-sm">
                    <Link :href="route('amo.login')"
                        class="flex items-center justify-center gap-2 w-full rounded-full py-2 text-lg font-semibold hover:bg-gray-50">
                        <Icon icon="mdi:login" />
                        Login
                    </Link>
                </div>

                <!-- QUICK ACTIONS -->
                <div class="grid grid-cols-3 gap-3">
                    <button
                        class="flex flex-col items-center justify-center bg-white rounded-xl py-3 shadow-sm hover:shadow-md transition-shadow">
                        <Icon icon="mdi:chat-outline" class="text-2xl" />
                        <span class="text-xs mt-1">Chat</span>
                    </button>

                    <button
                        class="flex flex-col items-center justify-center bg-white rounded-xl py-3 shadow-sm hover:shadow-md transition-shadow">
                        <Icon icon="mdi:bell-outline" class="text-2xl" />
                        <span class="text-xs mt-1">Alerts</span>
                    </button>

                    <button
                        class="flex flex-col items-center justify-center bg-white rounded-xl py-3 shadow-sm hover:shadow-md transition-shadow">
                        <Icon icon="mdi:cart-outline" class="text-2xl" />
                        <span class="text-xs mt-1">Orders</span>
                    </button>
                </div>

                <!-- CATEGORIES -->
                <div class="bg-white rounded-xl shadow-sm divide-y">
                    <button class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 w-full text-left">
                        <Icon icon="mdi:car-outline" class="text-xl" />
                        Motors
                    </button>

                    <button class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 w-full text-left">
                        <Icon icon="mdi:home-outline" class="text-xl" />
                        Property
                    </button>
                </div>

                <!-- SELL BUTTON -->
                <button
                    class="w-full flex items-center justify-center gap-2 rounded-full py-3 text-lg font-semibold shadow-md hover:shadow-lg transition-shadow bg-gradient-to-r from-yellow-400 via-blue-400 to-orange-400 text-white">
                    <Icon icon="mdi:plus" />
                    Sell Item
                </button>

            </div>
        </div>

        <!-- SEARCH ROW -->
        <div class="w-full bg-white border-t">
            <div
                class="max-w-9/11 mx-auto px-4 py-5 flex flex-col md:flex-row items-stretch md:items-center gap-4 md:gap-6">

                <!-- Location Dropdown -->
                <div class="relative w-full md:w-1/3">
                    <div
                        class="flex items-center border border-gray-300 rounded-lg px-3 py-3 bg-white hover:border-blue-400 transition-colors">
                        <Icon icon="mdi:map-marker-outline" class="text-2xl text-yellow-400" />
                        <select v-model="selectedCity" class="w-full px-4 py-2 bg-transparent focus:outline-none">
                            <option v-for="city in cities" :key="city" :value="city">{{ city }}</option>
                        </select>
                    </div>
                </div>

                <!-- Search Input -->
                <div class="flex flex-1 relative w-full">
                    <input v-model="searchTerm" @keyup.enter="performSearch" @input="checkResetFilters"
                        class="border border-r-0 px-4 py-4 text-xl rounded-l-lg w-full focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400"
                        placeholder="Search for item, brand, category..." />
                    <button @click="performSearch"
                        class="bg-yellow-500 -ml-4 px-6 text-white rounded-r-lg flex items-center hover:bg-yellow-600 transition-colors">
                        <Icon icon="mdi:magnify" class="text-2xl" />
                    </button>
                </div>

            </div>
        </div>

    </nav>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import { router, usePage, Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import citiesList from '@/data/cities.json'

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

// Filters
const selectedCity = ref(page.props.selectedCity || 'Pakistan')
const searchTerm = ref(page.props.filters?.filter?.global || '')
const selectedCategory = ref(page.props.filters?.filter?.category || '')
const showDropdown = ref(false)
const user = computed(() => page.props.auth.user)

// All categories for dropdown
const allCategories = ref(page.props.allCategories || [])

const mobileMenuOpen = ref(false)
const cities = ref<string[]>([])

onMounted(() => {
    cities.value = ['Pakistan', ...citiesList]

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(async (position) => {
            const lat = position.coords.latitude
            const lon = position.coords.longitude

            try {
                const res = await fetch(`https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${lat}&longitude=${lon}&localityLanguage=en`)
                const data = await res.json()
                const userCity = data.locality
                if (userCity && cities.value.includes(userCity)) {
                    selectedCity.value = userCity
                }
            } catch (error) {
                console.warn('Geolocation API failed', error)
            }
        })
    }
})

// Update selected city
watch(selectedCity, value => {
    router.post(route('set.city'), { city: value }, {
        preserveScroll: true,
        onSuccess: () => router.reload({ only: ['selectedCity'] })
    })
})

// Perform search: global + category + city
const performSearch = () => {
    router.visit(route('all.items'), {
        method: 'get',
        data: {
            filter: {
                global: searchTerm.value,
                category: selectedCategory.value,
                city: selectedCity.value
            }
        },
        preserveScroll: true,
        preserveState: true
    })
}

// Reset all filters if search bar is cleared
const checkResetFilters = () => {
    if (!searchTerm.value) {
        selectedCategory.value = ''
        router.visit(route('home'), {
            method: 'get',
            data: {
                filter: {
                    global: '',
                    category: '',
                    city: selectedCity.value
                }
            },
            preserveScroll: true,
            preserveState: true
        })
    }
}
</script>

<style scoped>
/* Smooth transitions for dropdown */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Scale animation for dropdown */
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