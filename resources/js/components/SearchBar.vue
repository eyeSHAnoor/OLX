<template>
    <div class="w-full bg-white border-t">
        <div class="max-w-full md:max-w-9/11 mx-auto px-4 md:px-3 py-4">
            <!-- Desktop layout -->
            <div class="hidden md:flex md:flex-col md:gap-2">
                <!-- Row 1: Location + Search -->
                <div class="flex md:flex-row md:items-stretch md:gap-3">
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
                                    <button @click="backToCitiesInDropdown"
                                        class="mr-2 text-gray-600 hover:text-gray-800">
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

                <!-- Row 2: Filters (Wholesale checkbox – visible only when searching) -->
                <div v-if="searchTerm.length > 0" class="flex items-center gap-4 pl-80 m1-16">
                    <label class="flex items-center text-sm text-gray-700 cursor-pointer select-none">
                        <input type="checkbox" v-model="wholesale" @change="performSearch"
                            class="mr-1.5 rounded border-gray-300 text-brand-blue focus:ring-brand-blue" />
                        Wholesale
                    </label>
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

                <!-- Mobile Wholesale checkbox – visible only when searching -->
                <div v-if="searchTerm.length > 0" class="mt-2 flex items-center">
                    <label class="flex items-center text-sm text-gray-700 cursor-pointer select-none">
                        <input type="checkbox" v-model="wholesale" @change="performSearch"
                            class="mr-1.5 rounded border-gray-300 text-brand-blue focus:ring-brand-blue" />
                        Wholesale
                    </label>
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
                            <button v-if="modalView === 'regions'" @click="goBackToCitiesFromModal"
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
import { ref, onMounted, onBeforeUnmount, computed } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { Icon } from "@iconify/vue";
import citiesList from "@/data/cities.json";

const page = usePage();

// ────────────────────────────────────────────────────────
//  Data
// ────────────────────────────────────────────────────────
const cities = ref<string[]>([
    ...(Array.isArray(citiesList)
        ? citiesList.map((c: any) => (typeof c === "string" ? c : c.name))
        : []),
]);

// Initial values come from shared Inertia props (cookies)
const selectedCity = ref(page.props.selectedCity || "Pakistan");
const selectedRegion = ref(page.props.selectedRegion || null);

const searchTerm = ref(page.props.filters?.filter?.global || "");
const selectedCategory = ref(page.props.filters?.filter?.category || "");
// const wholesale = ref(page.props.filters?.filter?.wholesale == 1);
const wholesale = ref(page.props.filters?.filter?.wholesale === "wholesale");

// Desktop dropdown
const dropdownOpen = ref(false);
const citySearchQuery = ref("");
const dropdownContainer = ref<HTMLElement | null>(null);
const showRegionsInDropdown = ref(false);

// Regions
const regions = ref<any[]>([]);
const loadingRegions = ref(false);

// Mobile modal
const modalOpen = ref(false);
const modalView = ref<"cities" | "regions">("cities");
const modalSearchQuery = ref("");

// Suggestions
const showSuggestions = ref(false);
const suggestions = ref<string[]>([]);
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

// ────────────────────────────────────────────────────────
//  Computed
// ────────────────────────────────────────────────────────
const locationDisplay = computed(() => {
    if (selectedCity.value === "Pakistan") return "Pakistan";
    if (selectedRegion.value) return `${selectedRegion.value}, ${selectedCity.value}`;
    return selectedCity.value;
});

const filteredCities = computed(() => {
    if (!citySearchQuery.value.trim()) return cities.value;
    const q = citySearchQuery.value.toLowerCase();
    return cities.value.filter((city) => city.toLowerCase().includes(q));
});

const filteredModalCities = computed(() => {
    if (!modalSearchQuery.value.trim()) return cities.value;
    const q = modalSearchQuery.value.toLowerCase();
    return cities.value.filter((city) => city.toLowerCase().includes(q));
});

const filteredModalRegions = computed(() => {
    if (!modalSearchQuery.value.trim()) return regions.value;
    const q = modalSearchQuery.value.toLowerCase();
    return regions.value.filter((r: any) => r.name.toLowerCase().includes(q));
});

// ────────────────────────────────────────────────────────
//  Helpers
// ────────────────────────────────────────────────────────
const fetchRegions = async (cityName: string) => {
    if (cityName === "Pakistan") {
        regions.value = [];
        return;
    }
    loadingRegions.value = true;
    try {
        const res = await fetch(`/regions/${encodeURIComponent(cityName)}`);
        const data = await res.json();
        regions.value = data.regions || [];
    } catch (e) {
        console.error("Failed to fetch regions:", e);
        regions.value = [];
    } finally {
        loadingRegions.value = false;
    }
};

const detectUserCity = (): Promise<string | null> => {
    return new Promise((resolve) => {
        if (!navigator.geolocation) return resolve(null);
        navigator.geolocation.getCurrentPosition(
            async (pos) => {
                try {
                    const res = await fetch(
                        `https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${pos.coords.latitude}&longitude=${pos.coords.longitude}&localityLanguage=en`
                    );
                    const data = await res.json();
                    const userCity = data.locality;
                    resolve(userCity && cities.value.includes(userCity) ? userCity : null);
                } catch {
                    resolve(null);
                }
            },
            () => resolve(null)
        );
    });
};

// ─────────────────────────────────────────────────────────────────
//  THE ONE FUNCTION THAT ACTUALLY APPLIES THE LOCATION & RELOADS
// ─────────────────────────────────────────────────────────────────
const applyLocation = (city: string, region: string | null) => {
    // Update local state for instant UI feedback
    selectedCity.value = city;
    selectedRegion.value = region;

    // Sync localStorage (optional, as a backup)
    localStorage.setItem("selectedCity", city);
    region
        ? localStorage.setItem("selectedRegion", region)
        : localStorage.removeItem("selectedRegion");

    // POST to server → server sets cookies → Inertia::location() → ONE reload
    router.post(route("set.city"), { city, region });
};

// ────────────────────────────────────────────────────────
//  Geolocation
// ────────────────────────────────────────────────────────
const useCurrentLocation = async () => {
    dropdownOpen.value = false;
    const city = await detectUserCity();
    if (city) {
        await fetchRegions(city);
        if (regions.value.length > 0) {
            selectedCity.value = city;
            selectedRegion.value = null;
            localStorage.removeItem("selectedRegion");
            showRegionsInDropdown.value = true;
            dropdownOpen.value = true;
        } else {
            applyLocation(city, null);
        }
    } else {
        alert("Could not detect your location.");
    }
};

const useCurrentLocationInModal = async () => {
    const city = await detectUserCity();
    if (city) {
        await fetchRegions(city);
        if (regions.value.length > 0) {
            selectedCity.value = city;
            selectedRegion.value = null;
            localStorage.removeItem("selectedRegion");
            modalView.value = "regions";
            modalSearchQuery.value = "";
        } else {
            applyLocation(city, null);
        }
    } else {
        alert("Could not detect your location.");
    }
};

// ────────────────────────────────────────────────────────
//  Desktop dropdown behaviour
// ────────────────────────────────────────────────────────
const toggleDropdown = () => {
    dropdownOpen.value = !dropdownOpen.value;
    if (dropdownOpen.value) {
        citySearchQuery.value = "";
        showRegionsInDropdown.value = false;
    }
};

const selectCity = async (city: string) => {
    if (city === "Pakistan") {
        applyLocation("Pakistan", null);
        dropdownOpen.value = false;
        return;
    }
    await fetchRegions(city);
    selectedCity.value = city;
    selectedRegion.value = null;
    localStorage.removeItem("selectedRegion");
    if (regions.value.length > 0) {
        showRegionsInDropdown.value = true;
    } else {
        applyLocation(city, null);
        dropdownOpen.value = false;
    }
};

const backToCitiesInDropdown = () => {
    showRegionsInDropdown.value = false;
    citySearchQuery.value = "";
};

const selectRegion = (regionName: string | null) => {
    dropdownOpen.value = false;
    applyLocation(selectedCity.value, regionName);
};

// ────────────────────────────────────────────────────────
//  Mobile modal behaviour
// ────────────────────────────────────────────────────────
const openModal = () => {
    modalOpen.value = true;
    modalView.value = "cities";
    modalSearchQuery.value = "";
};
const closeModal = () => {
    modalOpen.value = false;
};

const goBackToCitiesFromModal = () => {
    modalView.value = "cities";
    modalSearchQuery.value = "";
};

const selectCityFromModal = async (city: string) => {
    if (city === "Pakistan") {
        closeModal();
        applyLocation("Pakistan", null);
        return;
    }
    await fetchRegions(city);
    selectedCity.value = city;
    selectedRegion.value = null;
    localStorage.removeItem("selectedRegion");
    if (regions.value.length > 0) {
        modalView.value = "regions";
        modalSearchQuery.value = "";
    } else {
        closeModal();
        applyLocation(city, null);
    }
};

const selectRegionFromModal = (regionName: string | null) => {
    closeModal();
    applyLocation(selectedCity.value, regionName);
};

// ────────────────────────────────────────────────────────
//  Outside click (desktop)
// ────────────────────────────────────────────────────────
const handleClickOutside = (e: MouseEvent) => {
    if (dropdownContainer.value && !dropdownContainer.value.contains(e.target as Node)) {
        dropdownOpen.value = false;
        showRegionsInDropdown.value = false;
    }
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
    if (selectedCity.value && selectedCity.value !== "Pakistan") {
        fetchRegions(selectedCity.value);
    }
});

onBeforeUnmount(() => {
    document.removeEventListener("click", handleClickOutside);
});

// ────────────────────────────────────────────────────────
//  Search
// ────────────────────────────────────────────────────────
const performSearch = () => {
    showSuggestions.value = false;

    // Build filter object conditionally
    const filterData = {
        global: searchTerm.value,
        category: selectedCategory.value,
    };

    // Only add wholesale if checked
    if (wholesale.value) {
        filterData.wholesale = "wholesale";
    }

    router.visit(route("all.items"), {
        method: "get",
        data: {
            filter: filterData,
            min_price: page.props.filters?.min_price ?? "",
            max_price: page.props.filters?.max_price ?? "",
            sort_by: page.props.filters?.sort_by ?? "newest",
        },
        preserveScroll: true,
        preserveState: true,
    });
};

watch(
    () => page.props.filters?.filter?.wholesale,
    (val) => {
        wholesale.value = val === "wholesale";
    }
);

const checkResetFilters = () => {
    if (!searchTerm.value) {
        selectedCategory.value = "";
        // Don't reset wholesale – keep the checkbox state for when user types again.
        // Optionally you may reset it, but we keep it.
        router.visit(route("home"), {
            method: "get",
            data: { filter: { global: "", category: "", wholesale: 0 } },
            preserveScroll: true,
            preserveState: true,
        });
    }
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => fetchSuggestions(searchTerm.value), 300);
};

const fetchSuggestions = async (query: string) => {
    if (query.length < 2) {
        suggestions.value = [];
        return;
    }
    try {
        const res = await fetch(`/search-suggestions?query=${encodeURIComponent(query)}`);
        const data = await res.json();
        suggestions.value = Array.isArray(data) ? data : [];
    } catch (e) {
        suggestions.value = [];
    }
};

const onSearchFocus = () => {
    showSuggestions.value = true;
    if (searchTerm.value.length >= 2) fetchSuggestions(searchTerm.value);
};
const onSearchBlur = () => {
    setTimeout(() => {
        showSuggestions.value = false;
    }, 200);
};
const selectSuggestion = (suggestion: string) => {
    searchTerm.value = suggestion;
    showSuggestions.value = false;
    performSearch();
};
</script>
