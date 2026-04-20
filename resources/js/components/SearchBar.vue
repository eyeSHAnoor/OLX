<template>
    <div class="w-full bg-white border-t">
        <div class="max-w-full md:max-w-9/11 mx-auto px-4 md:px-3 py-4">
            <!-- Desktop layout: row with city dropdown + search bar -->
            <div class="hidden md:flex md:flex-row md:items-stretch md:gap-3">
                <!-- Location Trigger (opens dropdown) -->
                <div ref="dropdownContainer" class="relative w-full md:w-72">
                    <div @click="toggleDropdown"
                        class="flex items-center justify-between border border-gray-300 rounded-md px-2 py-3 bg-white hover:border-blue-400 transition-colors cursor-pointer">
                        <div class="flex items-center">
                            <Icon icon="mdi:map-marker-outline" class="text-sm text-blue-600 mr-2" />
                            <span class="text-sm">{{ selectedCity }}</span>
                        </div>
                        <Icon icon="mdi:chevron-down" class="text-gray-500 text-sm" />
                    </div>

                    <!-- Dropdown (positioned below) -->
                    <div v-if="dropdownOpen"
                        class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-md shadow-lg z-20">
                        <div class="p-2 border-b">
                            <input v-model="citySearchQuery" type="text" placeholder="Search city..."
                                class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:border-blue-400"
                                autofocus />
                        </div>
                        <div class="max-h-60 overflow-y-auto">
                            <div @click="selectCity('Pakistan')"
                                class="px-3 py-2 hover:bg-gray-50 cursor-pointer text-sm"
                                :class="{ 'bg-blue-50': selectedCity === 'Pakistan' }">
                                Pakistan
                            </div>
                            <div v-for="city in filteredCities" :key="city" @click="selectCity(city)"
                                class="px-3 py-2 hover:bg-gray-50 cursor-pointer text-sm"
                                :class="{ 'bg-blue-50': selectedCity === city }">
                                {{ city }}
                            </div>
                            <div v-if="filteredCities.length === 0" class="px-3 py-2 text-gray-500 text-sm text-center">
                                No cities found
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search Input (takes remaining width) -->
                <div class="flex flex-1 relative">
                    <input v-model="searchTerm" @keyup.enter="performSearch" @input="checkResetFilters"
                        class="border border-r-0 px-3 py-3 text-sm rounded-l-md w-full focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400"
                        placeholder="Search for item, brand, category..." />
                    <button @click="performSearch"
                        class="bg-brand-blue -ml-4 px-4 text-white rounded-r-md flex items-center hover:bg-blue-700 transition-colors">
                        <Icon icon="mdi:magnify" class="text-base" />
                    </button>
                </div>
            </div>

            <!-- Mobile layout: search bar + Pakistan button + city dropdown -->
            <div class="block md:hidden">
                <!-- Search Input -->
                <div class="flex w-full">
                    <input v-model="searchTerm" @keyup.enter="performSearch" @input="checkResetFilters"
                        class="border border-r-0 px-3 py-3 text-sm rounded-l-md w-full focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400"
                        placeholder="Search for item, brand, category..." />
                    <button @click="performSearch"
                        class="bg-brand-blue -ml-4 px-4 text-white rounded-r-md flex items-center hover:bg-blue-700 transition-colors">
                        <Icon icon="mdi:magnify" class="text-base" />
                    </button>
                </div>

                <!-- Pakistan Button + City Dropdown (side by side) -->
                <div class="mt-2 flex flex-row gap-4 items-center justify-start">
                    <!-- Pakistan Button (Separate) -->

                    <button @click="setPakistanCity"
                        class="text-sm text-brand-teal hover:text-brand-teal/90 transition-colors flex items-center gap-1">
                        <Icon icon="mdi:flag" class="text-sm text-green-600" />
                        <span class="text-sm">Pakistan</span>
                    </button>
                    <!-- City Dropdown Button (shows selected city, opens modal) -->
                    <button @click="openModal"
                        class="text-sm text-brand-teal hover:text-brand-teal/90 transition-colors flex items-center gap-1">
                        <Icon icon="mdi:map-marker-outline" class="text-md" />
                        <span class="text-sm"> {{ selectedCity || 'Select a city' }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Modal (Teleported to body) -->
        <Teleport to="body">
            <div v-if="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-2 bg-black/50"
                @click.self="closeModal">
                <div class="bg-white rounded-lg w-full max-w-md max-h-[80vh] flex flex-col shadow-xl">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between p-4 border-b">
                        <h3 class="text-lg font-medium">Select City</h3>
                        <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
                            <Icon icon="mdi:close" class="text-xl" />
                        </button>
                    </div>

                    <!-- Search Input -->
                    <div class="p-4 border-b">
                        <input v-model="modalCitySearch" type="text" placeholder="Search city..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:border-blue-400"
                            autofocus />
                    </div>

                    <!-- City List -->
                    <div class="flex-1 overflow-y-auto p-2">
                        <div @click="selectCityFromModal('Pakistan')"
                            class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm rounded-md"
                            :class="{ 'bg-blue-50': selectedCity === 'Pakistan' }">
                            All cities
                        </div>
                        <div v-for="city in modalFilteredCities" :key="city" @click="selectCityFromModal(city)"
                            class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm rounded-md"
                            :class="{ 'bg-blue-50': selectedCity === city }">
                            {{ city }}
                        </div>
                        <div v-if="modalFilteredCities.length === 0"
                            class="px-3 py-2 text-gray-500 text-sm text-center">
                            No cities found
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, onBeforeUnmount, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import citiesList from '@/data/cities.json'

const page = usePage()
const cities = ref<string[]>([...(Array.isArray(citiesList) ? citiesList.map((c: any) => typeof c === 'string' ? c : c.name) : [])])
const selectedCity = ref(localStorage.getItem('selectedCity') || page.props.selectedCity || 'Pakistan')
const userSelectedCity = ref(!!localStorage.getItem('selectedCity'))
const searchTerm = ref(page.props.filters?.filter?.global || '')
const selectedCategory = ref(page.props.filters?.filter?.category || '')

// Desktop dropdown state
const dropdownOpen = ref(false)
const citySearchQuery = ref('')
const dropdownContainer = ref<HTMLElement | null>(null)

// Mobile modal state
const modalOpen = ref(false)
const modalCitySearch = ref('')

// Filtered cities for desktop dropdown
const filteredCities = computed(() => {
    if (!citySearchQuery.value.trim()) return cities.value
    const query = citySearchQuery.value.toLowerCase()
    return cities.value.filter(city => city.toLowerCase().includes(query))
})

// Filtered cities for mobile modal
const modalFilteredCities = computed(() => {
    if (!modalCitySearch.value.trim()) return cities.value
    const query = modalCitySearch.value.toLowerCase()
    return cities.value.filter(city => city.toLowerCase().includes(query))
})

// Desktop dropdown methods
const toggleDropdown = () => {
    dropdownOpen.value = !dropdownOpen.value
    if (dropdownOpen.value) {
        citySearchQuery.value = ''
    }
}

const selectCity = (city: string) => {
    selectedCity.value = city
    dropdownOpen.value = false
}

// Mobile modal methods
const openModal = () => {
    modalOpen.value = true
    modalCitySearch.value = ''
}
const closeModal = () => {
    modalOpen.value = false
}
const selectCityFromModal = (city: string) => {
    selectedCity.value = city
    closeModal()
}

// New method to set Pakistan as the selected city (for mobile separate button)
const setPakistanCity = () => {
    if (modalOpen.value) closeModal()
    selectedCity.value = 'Pakistan'
}

// Close desktop dropdown when clicking outside
const handleClickOutside = (e: MouseEvent) => {
    if (dropdownContainer.value && !dropdownContainer.value.contains(e.target as Node)) {
        dropdownOpen.value = false
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
    // Geolocation logic (unchanged)
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

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside)
})

// Persist selected city
watch(selectedCity, (value, oldValue) => {
    if (oldValue !== value) userSelectedCity.value = true
    localStorage.setItem('selectedCity', value)
    router.post(route('set.city'), { city: value }, {
        preserveScroll: true,
        onSuccess: () => router.reload({ only: ['selectedCity'] })
    })
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

// Reset filters if search cleared
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