<template>
    <nav class="border-b">
        <!-- TOP BAR -->
        <div class="w-full bg-slate-100">
            <div class="max-w-9/11 mx-auto px-3 py-4 flex items-center justify-between">

                <!-- Logo + Hamburger -->
                <div class="flex items-center gap-3 w-full md:w-auto justify-between">
                    <Link :href="route('home')" class="flex items-center">
                        <img src="/images/logo.png" alt="Logo" class="h-6 md:h-7 w-auto" />
                    </Link>

                    <!-- Mobile Hamburger -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-gray-700 p-1">
                        <Icon :icon="mobileMenuOpen ? 'mdi:close' : 'mdi:menu'" class="text-lg" />
                    </button>

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
                        <Icon icon="mdi:bell-outline" class="text-base cursor-pointer hover:text-blue-600" />
                        <Icon icon="mdi:cart-outline" class="text-base cursor-pointer hover:text-blue-600" />

                        <!-- Avatar Dropdown -->
                        <div class="relative" v-click-outside="() => showDropdown = false">
                            <div class="w-6 h-6 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 cursor-pointer border border-white shadow-sm hover:shadow transition-shadow"
                                @click="showDropdown = !showDropdown"></div>

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
                                        <Link href="/orders"
                                            class="flex items-center gap-2 px-3 py-1.5 text-xs text-gray-700 hover:text-blue-600 transition-colors">
                                            <Icon icon="mdi:package-variant" class="text-sm text-gray-500" />
                                            <span>My Orders</span>
                                        </Link>
                                        <Link href="/favourites"
                                            class="flex items-center gap-2 px-3 py-1.5 text-xs text-gray-700 hover:text-blue-600 transition-colors">
                                            <Icon icon="mdi:heart-outline" class="text-sm text-gray-500" />
                                            <span>My Favourites</span>
                                        </Link>
                                        <Link href="/my/ads"
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
                                        <Link href="/settings"
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

                        <div
                            class="rounded-full p-[3px] bg-gradient-to-r from-brand-orange via-brand-teal to-brand-blue">
                            <Link :href="route('user.ads.create')"
                                class="flex items-center gap-0.5 rounded-full px-3 py-1 text-xs font-medium bg-white hover:shadow transition-shadow">
                                <Icon icon="mdi:plus" class="text-xs" />
                                SELL
                            </Link>
                        </div>
                    </div>

                    <div v-else
                        class="rounded-full p-[3px] bg-gradient-to-l from-yellow-400 via-blue-400 to-orange-400">
                        <Link
                            class="flex items-center gap-0.5 rounded-full px-3 py-1 text-xs font-medium bg-white hover:shadow transition-shadow"
                            :href="route('amo.login')">
                            <Icon icon="mdi:plus" class="text-xs" />
                            Login
                        </Link>
                    </div>


                </div>
            </div>

            <!-- Mobile Menu -->
            <div v-if="mobileMenuOpen" class="md:hidden px-3 pb-4 space-y-3 bg-gray-50 rounded-b-lg shadow-inner">
                <!-- Mobile Links -->
                <div class="py-2 space-y-1">
                    <Link href="/motors" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg">
                        Motors
                    </Link>
                    <Link href="/property" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg">
                        Property
                    </Link>
                    <Link :href="route('user.ads.create')" class="py-2 space-y-1">
                        <Icon icon="mdi:plus" class="text-xs" />
                        SELL
                    </Link>
                </div>

                <!-- Mobile User Section -->
                <div v-if="user" class="border-t border-gray-200 pt-2">
                    <Link :href="route('user.profile', user.id)"
                        class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg">
                        <Icon icon="mdi:account-outline" class="text-base" />
                        Profile
                    </Link>
                    <Link :href="route('my.ads')"
                        class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg">
                        <Icon icon="mdi:bullhorn-outline" class="text-sm text-gray-500" />
                        My ads
                    </Link>
                    <Link href="/orders"
                        class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg">
                        <Icon icon="mdi:package-variant" class="text-base" />
                        Orders
                    </Link>
                    <Link href="/favourites"
                        class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg">
                        <Icon icon="mdi:heart-outline" class="text-base" />
                        Favourites
                    </Link>
                    <Link :href="route('subscriptions.index')"
                        class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg">
                        <Icon icon="mdi:crown-outline" class="text-base text-yellow-500" />
                        Subscription
                    </Link>
                    <div class="border-t border-gray-200 my-2"></div>
                    <Link href="/logout" method="post" as="button"
                        class="w-full flex items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg">
                        <Icon icon="mdi:logout" class="text-base" />
                        Logout
                    </Link>
                </div>

                <!-- Mobile Login/Sell -->
                <div v-else class="border-t border-gray-200 pt-3 flex gap-2">
                    <Link :href="route('amo.login')"
                        class="flex-1 flex items-center justify-center gap-1 px-3 py-2 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        <Icon icon="mdi:login" class="text-sm" />
                        Login
                    </Link>
                    <button
                        class="flex-1 flex items-center justify-center gap-1 px-3 py-2 bg-gradient-to-r from-brand-orange to-brand-teal text-white text-xs font-medium rounded-lg hover:opacity-90 transition-opacity">
                        <Icon icon="mdi:plus" class="text-sm" />
                        SELL
                    </button>
                </div>
            </div>
        </div>

        <!-- SEARCH ROW -->
        <div class="w-full bg-white border-t">
            <div
                class="max-w-9/11 mx-auto px-3 py-4 flex flex-col md:flex-row items-stretch md:items-center gap-2 md:gap-3">
                <!-- Location Dropdown -->
                <div class="relative w-full md:w-64">
                    <div
                        class="flex items-center border border-gray-300 rounded-md px-2 py-2 bg-white hover:border-blue-400 transition-colors">
                        <Icon icon="mdi:map-marker-outline" class="text-sm text-blue-600" />
                        <select v-model="selectedCity"
                            class="w-full px-2 py-1.5 bg-transparent focus:outline-none text-xs">
                            <option v-for="city in cities" :key="city" :value="city">{{ city }}</option>
                        </select>
                    </div>
                </div>

                <!-- Search Input -->
                <div class="flex flex-1 relative w-full">
                    <input v-model="searchTerm" @keyup.enter="performSearch" @input="checkResetFilters"
                        class="border border-r-0 px-3 py-3 text-sm rounded-l-md w-full focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400"
                        placeholder="Search for item, brand, category..." />
                    <button @click="performSearch"
                        class="bg-brand-blue -ml-4 px-4 text-white rounded-r-md flex items-center hover:bg-blue-700 transition-colors">
                        <Icon icon="mdi:magnify" class="text-base" />
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
const cities = ref<string[]>(['Pakistan', ...citiesList])
const selectedCity = ref(localStorage.getItem('selectedCity') || page.props.selectedCity || 'Pakistan')
const userSelectedCity = ref(!!localStorage.getItem('selectedCity'))
const searchTerm = ref(page.props.filters?.filter?.global || '')
const selectedCategory = ref(page.props.filters?.filter?.category || '')
const showDropdown = ref(false)
const user = computed(() => page.props.auth?.user)
const mobileMenuOpen = ref(false)

// Persist user-selected city
watch(selectedCity, (value, oldValue) => {
    if (oldValue !== value) userSelectedCity.value = true
    localStorage.setItem('selectedCity', value)

    router.post(route('set.city'), { city: value }, {
        preserveScroll: true,
        onSuccess: () => router.reload({ only: ['selectedCity'] })
    })
})

// Only use geolocation if user hasn't manually selected a city
onMounted(() => {
    if (!userSelectedCity.value && navigator.geolocation) {
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

// Search function
const performSearch = () => {
    router.visit(route('all.items'), {
        method: 'get',
        data: { filter: { global: searchTerm.value, category: selectedCategory.value, city: selectedCity.value } },
        preserveScroll: true,
        preserveState: true
    })
}

// Reset filters if search is cleared
const checkResetFilters = () => {
    if (!searchTerm.value) {
        selectedCategory.value = ''
        router.visit(route('home'), {
            method: 'get',
            data: { filter: { global: '', category: '', city: selectedCity.value } },
            preserveScroll: true,
            preserveState: true
        })
    }
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