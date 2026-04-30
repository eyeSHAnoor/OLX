<template>
    <div class="w-full bg-white border-t">
        <div class="max-w-full md:max-w-9/11 mx-auto px-4 md:px-3 py-4">
            <!-- Desktop layout -->
            <div class="hidden md:flex md:flex-row md:items-stretch md:gap-3">
                <!-- Location Trigger (opens dropdown) -->
                <div ref="dropdownContainer" class="relative w-full md:w-72">
                    <div @click="toggleDropdown"
                        class="flex items-center justify-between border border-gray-300 rounded-md px-2 py-3 bg-white hover:border-blue-400 transition-colors cursor-pointer">
                        <div class="flex items-center">
                            <Icon icon="mdi:map-marker-outline" class="text-sm text-blue-600 mr-2" />
                            <span class="text-sm">{{ locationDisplay }}</span>
                        </div>
                        <Icon icon="mdi:chevron-down" class="text-gray-500 text-sm" />
                    </div>

                    <!-- Dropdown -->
                    <div v-if="dropdownOpen"
                        class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-md shadow-lg z-20">
                        <!-- City selection view -->
                        <template v-if="!showRegionsInDropdown">
                            <div class="p-2 border-b">
                                <input v-model="citySearchQuery" type="text" placeholder="Search city..."
                                    class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:border-blue-400"
                                    autofocus />
                            </div>
                            <div class="max-h-60 overflow-y-auto">
                                <!-- Use current location button -->
                                <div @click="useCurrentLocation"
                                    class="px-3 py-2 hover:bg-gray-50 cursor-pointer text-sm text-blue-600 border-b border-gray-100 flex items-center">
                                    <Icon icon="mdi:crosshairs-gps" class="mr-2" />
                                    Use current location
                                </div>
                                <div @click="selectCity('Pakistan')"
                                    class="px-3 py-2 hover:bg-gray-50 cursor-pointer text-sm" :class="{
                                        'bg-blue-50': selectedCity === 'Pakistan' && !selectedRegion,
                                    }">
                                    Pakistan
                                </div>
                                <div v-for="city in filteredCities" :key="city" @click="selectCity(city)"
                                    class="px-3 py-2 hover:bg-gray-50 cursor-pointer text-sm"
                                    :class="{ 'bg-blue-50': selectedCity === city && !selectedRegion }">
                                    {{ city }}
                                </div>
                                <div v-if="filteredCities.length === 0"
                                    class="px-3 py-2 text-gray-500 text-sm text-center">
                                    No cities found
                                </div>
                            </div>
                        </template>
                        <!-- Region selection view -->
                        <template v-else>
                            <div class="p-2 border-b flex items-center">
                                <button @click="backToCitiesInDropdown" class="mr-2 text-gray-600 hover:text-gray-800">
                                    <Icon icon="mdi:arrow-left" class="text-lg" />
                                </button>
                                <span class="text-sm font-medium">{{ selectedCity }}</span>
                            </div>
                            <div class="max-h-60 overflow-y-auto">
                                <div @click="selectRegion(null)"
                                    class="px-3 py-2 hover:bg-gray-50 cursor-pointer text-sm"
                                    :class="{ 'bg-blue-50': !selectedRegion }">
                                    All areas
                                </div>
                                <div v-for="region in regions" :key="region.id" @click="selectRegion(region.name)"
                                    class="px-3 py-2 hover:bg-gray-50 cursor-pointer text-sm"
                                    :class="{ 'bg-blue-50': selectedRegion === region.name }">
                                    {{ region.name }}
                                </div>
                                <div v-if="regions.length === 0 && !loadingRegions"
                                    class="px-3 py-2 text-gray-500 text-sm text-center">
                                    No regions found
                                </div>
                                <div v-if="loadingRegions" class="px-3 py-2 text-gray-500 text-sm text-center">
                                    Loading regions...
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- DESKTOP Search Input with suggestions -->
                <div class="flex flex-1 relative">
                    <input v-model="searchTerm" @keyup.enter="performSearch" @input="checkResetFilters"
                        @focus="onSearchFocus" @blur="onSearchBlur"
                        class="border border-r-0 px-3 py-3 text-sm rounded-l-md w-full focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400"
                        placeholder="Search for item, brand, category..." />

                    <!-- SUGGESTIONS DROPDOWN -->
                    <ul v-if="showSuggestions && suggestions.length > 0"
                        class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-md shadow-lg z-30 mt-1 max-h-60 overflow-y-auto">
                        <li v-for="(suggestion, idx) in suggestions" :key="idx"
                            @mousedown.prevent="selectSuggestion(suggestion)"
                            class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm flex items-center">
                            <Icon icon="mdi:magnify" class="text-gray-400 mr-2 text-sm" />
                            {{ suggestion }}
                        </li>
                    </ul>

                    <button @click="performSearch"
                        class="bg-brand-blue -ml-4 px-4 text-white rounded-r-md flex items-center hover:bg-blue-700 transition-colors">
                        <Icon icon="mdi:magnify" class="text-base" />
                    </button>
                </div>
            </div>

            <!-- MOBILE layout -->
            <div class="block md:hidden">
                <!-- MOBILE Search Input with suggestions -->
                <div class="flex w-full relative">
                    <input v-model="searchTerm" @keyup.enter="performSearch" @input="checkResetFilters"
                        @focus="onSearchFocus" @blur="onSearchBlur"
                        class="border border-r-0 px-3 py-3 text-sm rounded-l-md w-full focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400"
                        placeholder="Search for item, brand, category..." />

                    <!-- SUGGESTIONS DROPDOWN (mobile) -->
                    <ul v-if="showSuggestions && suggestions.length > 0"
                        class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-md shadow-lg z-30 mt-1 max-h-60 overflow-y-auto">
                        <li v-for="(suggestion, idx) in suggestions" :key="idx"
                            @mousedown.prevent="selectSuggestion(suggestion)"
                            class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm flex items-center">
                            <Icon icon="mdi:magnify" class="text-gray-400 mr-2 text-sm" />
                            {{ suggestion }}
                        </li>
                    </ul>

                    <button @click="performSearch"
                        class="bg-brand-blue -ml-4 px-4 text-white rounded-r-md flex items-center hover:bg-blue-700 transition-colors">
                        <Icon icon="mdi:magnify" class="text-base" />
                    </button>
                </div>

                <!-- Pakistan Button + City/Region Dropdown -->
                <div class="mt-2 flex flex-row gap-4 items-center justify-start">
                    <button @click="openModal"
                        class="text-sm text-brand-teal hover:text-brand-teal/90 transition-colors flex items-center gap-1">
                        <Icon icon="mdi:map-marker-outline" class="text-md" />
                        <span class="text-sm">{{ locationDisplay || "Select location" }}</span>
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
                        <div class="flex items-center">
                            <button v-if="modalView === 'regions'" @click="modalView = 'cities'"
                                class="mr-3 text-gray-600">
                                <Icon icon="mdi:arrow-left" class="text-xl" />
                            </button>
                            <h3 class="text-lg font-medium">
                                {{
                                    modalView === "cities"
                                        ? "Select City"
                                        : `Select Area in ${selectedCity}`
                                }}
                            </h3>
                        </div>
                        <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
                            <Icon icon="mdi:close" class="text-xl" />
                        </button>
                    </div>

                    <!-- Search Input -->
                    <div class="p-4 border-b">
                        <input v-model="modalSearchQuery" type="text"
                            :placeholder="modalView === 'cities' ? 'Search city...' : 'Search area...'"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:border-blue-400"
                            autofocus />
                    </div>

                    <!-- Content -->
                    <div class="flex-1 overflow-y-auto p-2">
                        <!-- City List View -->
                        <template v-if="modalView === 'cities'">
                            <!-- Use current location button -->
                            <div @click="useCurrentLocationInModal"
                                class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm text-blue-600 rounded-md flex items-center border-b border-gray-100">
                                <Icon icon="mdi:crosshairs-gps" class="mr-2" />
                                Use current location
                            </div>
                            <div @click="selectCityFromModal('Pakistan')"
                                class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm rounded-md"
                                :class="{ 'bg-blue-50': selectedCity === 'Pakistan' && !selectedRegion }">
                                All Pakistan
                            </div>
                            <div v-for="city in filteredModalCities" :key="city" @click="selectCityFromModal(city)"
                                class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm rounded-md"
                                :class="{ 'bg-blue-50': selectedCity === city && !selectedRegion }">
                                {{ city }}
                            </div>
                            <div v-if="filteredModalCities.length === 0"
                                class="px-3 py-2 text-gray-500 text-sm text-center">
                                No cities found
                            </div>
                        </template>

                        <!-- Region List View -->
                        <template v-else>
                            <div @click="selectRegionFromModal(null)"
                                class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm rounded-md"
                                :class="{ 'bg-blue-50': !selectedRegion }">
                                All areas
                            </div>
                            <div v-for="region in filteredModalRegions" :key="region.id"
                                @click="selectRegionFromModal(region.name)"
                                class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm rounded-md"
                                :class="{ 'bg-blue-50': selectedRegion === region.name }">
                                {{ region.name }}
                            </div>
                            <div v-if="filteredModalRegions.length === 0 && !loadingRegions"
                                class="px-3 py-2 text-gray-500 text-sm text-center">
                                No areas found
                            </div>
                            <div v-if="loadingRegions" class="px-3 py-2 text-gray-500 text-sm text-center">
                                Loading areas...
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, onBeforeUnmount, computed } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { Icon } from "@iconify/vue";
import citiesList from "@/data/cities.json";

const page = usePage();
const cities = ref<string[]>([
    ...(Array.isArray(citiesList)
        ? citiesList.map((c: any) => (typeof c === "string" ? c : c.name))
        : []),
]);

// Initialise from session via shared Inertia props (no localStorage needed, but kept for backward compatibility)
const selectedCity = ref(
    localStorage.getItem("selectedCity") || page.props.selectedCity || "Pakistan"
);
const selectedRegion = ref(
    localStorage.getItem("selectedRegion") || page.props.selectedRegion || null
);
const userSelectedCity = ref(!!localStorage.getItem("selectedCity"));
const searchTerm = ref(page.props.filters?.filter?.global || "");
const selectedCategory = ref(page.props.filters?.filter?.category || "");

// Desktop dropdown state
const dropdownOpen = ref(false);
const citySearchQuery = ref("");
const dropdownContainer = ref<HTMLElement | null>(null);
const showRegionsInDropdown = ref(false);

// Regions data
const regions = ref<any[]>([]);
const loadingRegions = ref(false);

// Mobile modal state
const modalOpen = ref(false);
const modalView = ref<"cities" | "regions">("cities");
const modalSearchQuery = ref("");

// Suggestion state
const showSuggestions = ref(false);
const suggestions = ref<string[]>([]);
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

// Computed location display
const locationDisplay = computed(() => {
    if (selectedCity.value === "Pakistan") return "Pakistan";
    if (selectedRegion.value) return `${selectedRegion.value}, ${selectedCity.value}`;
    return selectedCity.value;
});

// Filtered cities for desktop dropdown
const filteredCities = computed(() => {
    if (!citySearchQuery.value.trim()) return cities.value;
    const query = citySearchQuery.value.toLowerCase();
    return cities.value.filter((city) => city.toLowerCase().includes(query));
});

// Filtered cities for mobile modal
const filteredModalCities = computed(() => {
    if (!modalSearchQuery.value.trim()) return cities.value;
    const query = modalSearchQuery.value.toLowerCase();
    return cities.value.filter((city) => city.toLowerCase().includes(query));
});

// Filtered regions for mobile modal
const filteredModalRegions = computed(() => {
    if (!modalSearchQuery.value.trim()) return regions.value;
    const query = modalSearchQuery.value.toLowerCase();
    return regions.value.filter((region: any) => region.name.toLowerCase().includes(query));
});

// Helper: fetch regions for a city
const fetchRegions = async (cityName: string) => {
    if (cityName === "Pakistan") {
        regions.value = [];
        return;
    }
    loadingRegions.value = true;
    try {
        const response = await fetch(`/regions/${encodeURIComponent(cityName)}`);
        const data = await response.json();
        regions.value = data.regions || [];
    } catch (error) {
        console.error("Failed to fetch regions:", error);
        regions.value = [];
    } finally {
        loadingRegions.value = false;
    }
};

// Geolocation logic to detect user's city
const detectUserCity = (): Promise<string | null> => {
    return new Promise((resolve) => {
        if (!navigator.geolocation) {
            resolve(null);
            return;
        }
        navigator.geolocation.getCurrentPosition(
            async (position) => {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                try {
                    const res = await fetch(
                        `https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${lat}&longitude=${lon}&localityLanguage=en`
                    );
                    const data = await res.json();
                    const userCity = data.locality;
                    resolve(userCity && cities.value.includes(userCity) ? userCity : null);
                } catch (error) {
                    console.warn("Geolocation API failed", error);
                    resolve(null);
                }
            },
            () => resolve(null)
        );
    });
};

// Use current location (desktop dropdown)
const useCurrentLocation = async () => {
    dropdownOpen.value = false;
    const city = await detectUserCity();
    if (city) {
        selectedCity.value = city;
        selectedRegion.value = null;
        localStorage.removeItem("selectedRegion");
        await fetchRegions(city);
        if (regions.value.length > 0) {
            showRegionsInDropdown.value = true;
            dropdownOpen.value = true;
        } else {
            dropdownOpen.value = false;
        }
    } else {
        alert("Could not detect your location or city not in our list.");
        dropdownOpen.value = true;
    }
};

// Use current location in mobile modal
const useCurrentLocationInModal = async () => {
    const city = await detectUserCity();
    if (city) {
        selectedCity.value = city;
        selectedRegion.value = null;
        localStorage.removeItem("selectedRegion");
        await fetchRegions(city);
        if (regions.value.length > 0) {
            modalView.value = "regions";
            modalSearchQuery.value = "";
        } else {
            closeModal();
        }
    } else {
        alert("Could not detect your location or city not in our list.");
    }
};

// Desktop dropdown methods
const toggleDropdown = () => {
    dropdownOpen.value = !dropdownOpen.value;
    if (dropdownOpen.value) {
        citySearchQuery.value = "";
        showRegionsInDropdown.value = false;
    }
};

const selectCity = async (city: string) => {
    selectedCity.value = city;
    selectedRegion.value = null;
    localStorage.removeItem("selectedRegion");

    if (city !== "Pakistan") {
        await fetchRegions(city);
        showRegionsInDropdown.value = true;
    } else {
        regions.value = [];
        showRegionsInDropdown.value = false;
        dropdownOpen.value = false;
    }
};

const backToCitiesInDropdown = () => {
    showRegionsInDropdown.value = false;
    citySearchQuery.value = "";
};

const selectRegion = (regionName: string | null) => {
    selectedRegion.value = regionName;
    dropdownOpen.value = false;
};

// Mobile modal methods
const openModal = () => {
    modalOpen.value = true;
    modalView.value = "cities";
    modalSearchQuery.value = "";
};
const closeModal = () => {
    modalOpen.value = false;
};
const setPakistanCity = () => {
    if (modalOpen.value) closeModal();
    selectedCity.value = "Pakistan";
    selectedRegion.value = null;
    localStorage.removeItem("selectedRegion");
    regions.value = [];
};

const selectCityFromModal = async (city: string) => {
    selectedCity.value = city;
    selectedRegion.value = null;
    localStorage.removeItem("selectedRegion");

    if (city !== "Pakistan") {
        await fetchRegions(city);
        modalView.value = "regions";
        modalSearchQuery.value = "";
    } else {
        regions.value = [];
        closeModal();
    }
};

const selectRegionFromModal = (regionName: string | null) => {
    selectedRegion.value = regionName;
    closeModal();
};

// Close desktop dropdown when clicking outside
const handleClickOutside = (e: MouseEvent) => {
    if (dropdownContainer.value && !dropdownContainer.value.contains(e.target as Node)) {
        dropdownOpen.value = false;
        showRegionsInDropdown.value = false;
    }
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);

    // Geolocation logic on initial load if user hasn't selected a city manually
    if (!userSelectedCity.value && navigator.geolocation) {
        detectUserCity().then((city) => {
            if (city) {
                selectedCity.value = city;
            }
        });
    }

    // If a city is already selected and not Pakistan, fetch its regions
    if (selectedCity.value && selectedCity.value !== "Pakistan") {
        fetchRegions(selectedCity.value);
    }
});

onBeforeUnmount(() => {
    document.removeEventListener("click", handleClickOutside);
});

// ─────────────────────────────────────────────────────────────────
// UPDATED WATCHER – single page reload after session update
// ─────────────────────────────────────────────────────────────────
watch([selectedCity, selectedRegion], ([city, region], [oldCity]) => {
    if (oldCity !== city) userSelectedCity.value = true;

    localStorage.setItem("selectedCity", city);
    if (region) {
        localStorage.setItem("selectedRegion", region);
    } else {
        localStorage.removeItem("selectedRegion");
    }

    router.post(
        route("set.city"),
        { city, region },
        { preserveScroll: true } // no onSuccess needed
    );
});

// Search function – no city/region in request, backend reads from session
const performSearch = () => {
    showSuggestions.value = false;
    router.visit(route("all.items"), {
        method: "get",
        data: {
            filter: {
                global: searchTerm.value,
                category: selectedCategory.value,
                // city and region are intentionally omitted – session handles location
            },
        },
        preserveScroll: true,
        preserveState: true,
    });
};

// Reset filters if search cleared – also fetch suggestions
const checkResetFilters = () => {
    if (!searchTerm.value) {
        selectedCategory.value = "";
        router.visit(route("home"), {
            method: "get",
            data: {
                filter: {
                    global: "",
                    category: "",
                },
            },
            preserveScroll: true,
            preserveState: true,
        });
    }
    // Fetch suggestions with debounce
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        fetchSuggestions(searchTerm.value);
    }, 300);
};

// Fetch suggestions from API
const fetchSuggestions = async (query: string) => {
    if (query.length < 2) {
        suggestions.value = [];
        return;
    }
    try {
        const response = await fetch(
            `/search-suggestions?query=${encodeURIComponent(query)}`
        );
        const data = await response.json();
        suggestions.value = Array.isArray(data) ? data : [];
    } catch (error) {
        console.error("Failed to fetch suggestions:", error);
        suggestions.value = [];
    }
};

// Focus/Blur handlers
const onSearchFocus = () => {
    showSuggestions.value = true;
    if (searchTerm.value.length >= 2) {
        fetchSuggestions(searchTerm.value);
    }
};

const onSearchBlur = () => {
    // Small delay so click on suggestion registers
    setTimeout(() => {
        showSuggestions.value = false;
    }, 200);
};

// Select a suggestion
const selectSuggestion = (suggestion: string) => {
    searchTerm.value = suggestion;
    showSuggestions.value = false;
    performSearch();
};
</script>
