<template>
    <div class="w-full bg-white border-t">
        <div
            class="max-w-9/11 mx-auto px-3 py-4 flex flex-col md:flex-row items-stretch md:items-center gap-2 md:gap-3">
            <!-- Location Dropdown -->
            <div class="relative w-full md:w-64">
                <div
                    class="flex items-center border border-gray-300 rounded-md px-2 py-2 bg-white hover:border-blue-400 transition-colors">
                    <Icon icon="mdi:map-marker-outline" class="text-sm text-blue-600" />
                    <select v-model="selectedCity" class="w-full px-2 py-1.5 bg-transparent focus:outline-none text-xs">
                        <option value="Pakistan">Pakistan</option>
                        <option v-for="city in cities" :key="city" :value="city.name">{{ city.name }}</option>
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
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import citiesList from '@/data/cities.json'

const page = usePage()
const cities = ref<string[]>(['Pakistan', ...citiesList])
const selectedCity = ref(localStorage.getItem('selectedCity') || page.props.selectedCity || 'Pakistan')
const userSelectedCity = ref(!!localStorage.getItem('selectedCity'))
const searchTerm = ref(page.props.filters?.filter?.global || '')
const selectedCategory = ref(page.props.filters?.filter?.category || '')

// Persist user-selected city
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

// Geolocation for city selection
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
</script>