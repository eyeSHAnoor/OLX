<template>
    <nav class="border-b">
        <!-- TOP BAR (FULL WIDTH + COLOR) -->
        <div class="w-full bg-slate-100">
            <div class="max-w-9/11 mx-auto px-4 py-4 flex items-center justify-between">

                <!-- Logo + Hamburger for Mobile -->
                <div class="flex items-center gap-4 w-full md:w-auto justify-between">
                    <Link :href="route('home')" class="flex items-center">
                        <img src="/images/logo.png" alt="Logo" class="h-10 w-auto" />
                    </Link>

                    <!-- Mobile Hamburger -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-gray-700">
                        <Icon :icon="mobileMenuOpen ? 'mdi:close' : 'mdi:menu'" class="text-3xl" />
                    </button>
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

                <!-- Desktop Menu -->

                <!-- Right Icons & SELL button -->
                <div class="hidden md:flex items-center gap-8">
                    <div class="flex items-center gap-5">
                        <Icon icon="mdi:chat-outline" class="text-3xl cursor-pointer" />
                        <Icon icon="mdi:bell-outline" class="text-3xl cursor-pointer" />
                        <Icon icon="mdi:cart-outline" class="text-3xl cursor-pointer" />
                        <div class="w-8 h-8 rounded-full bg-gray-300" />
                    </div>

                    <div class="rounded-full p-[5px] bg-gradient-to-r from-yellow-400 via-blue-400 to-orange-400">
                        <button class="flex items-center gap-1 rounded-full px-6 py-2 text-xl font-bold bg-white">
                            <Icon icon="mdi:plus" />
                            SELL
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div v-if="mobileMenuOpen" class="md:hidden px-4 pb-4 space-y-2">
                <button class="flex items-center gap-1 w-full hover:text-blue-600">
                    <Icon icon="mdi:car-outline" class="text-2xl" />
                    Motors
                </button>
                <button class="flex items-center gap-1 w-full hover:text-blue-600">
                    <Icon icon="mdi:home-outline" class="text-2xl" />
                    Property
                </button>
            </div>
        </div>

        <!-- SEARCH ROW (FULL WIDTH) -->
        <div class="w-full bg-white border-t">
            <div
                class="max-w-9/11 mx-auto px-4 py-3 flex flex-col md:flex-row items-stretch md:items-center gap-4 md:gap-6">

                <!-- Location Dropdown -->
                <div class="relative w-full md:w-1/3">
                    <div class="flex items-center border border-gray-300 rounded-lg px-3 py-2 bg-white shadow-sm">
                        <Icon icon="mdi:map-marker-outline" class="text-xl text-teal-600" />
                        <select v-model="selectedCity"
                            class="ml-2 w-full bg-transparent focus:outline-none text-sm cursor-pointer">
                            <option v-for="city in cities" :key="city" :value="city">{{ city }}</option>
                        </select>
                    </div>
                </div>

                <!-- Search -->
                <div class="flex flex-1 relative w-full">
                    <input class="border border-r-0 px-4 py-3 rounded-l-lg w-full focus:outline-none"
                        placeholder="Find Cars, Mobile Phones and more..." />
                    <button class="bg-yellow-500 -ml-4 px-6 text-white rounded-r-lg flex items-center">
                        <Icon icon="mdi:magnify" class="text-xl" />
                    </button>
                </div>
            </div>
        </div>
    </nav>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import citiesList from '@/data/cities.json'

const mobileMenuOpen = ref(false)
const selectedCity = ref('Pakistan')
const cities = ref<string[]>([])

onMounted(() => {
    // Load cities
    cities.value = ['Pakistan', ...citiesList]

    // Geolocation
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
</script>
