<template>
    <OlxLayout>
        <TopCategoriesBar />

        <!-- Top Carousel for Generic Banners -->
        <section v-if="genericBanners.length"
            class="relative bg-gray-100 h-[200px] md:h-[300px] lg:h-[400px] overflow-hidden">
            <div v-for="(banner, index) in genericBanners" :key="banner.id"
                class="absolute inset-0 transition-opacity duration-700"
                :class="{ 'opacity-100 z-10': currentSlide === index, 'opacity-0': currentSlide !== index }">
                <a :href="banner.link || '#'" :target="banner.link ? '_blank' : '_self'" class="block w-full h-full">
                    <img :src="banner.image_url" :alt="banner.title" class="w-full h-full object-cover" />
                </a>
            </div>

            <!-- Navigation Buttons -->
            <button v-if="genericBanners.length > 1" @click="prevSlide"
                class="absolute left-4 top-1/2 -translate-y-1/2 z-20 bg-white/90 rounded-full p-2 md:p-3 shadow-md hover:bg-white transition">
                <Icon icon="mdi:chevron-left" class="text-xl md:text-2xl" />
            </button>
            <button v-if="genericBanners.length > 1" @click="nextSlide"
                class="absolute right-4 top-1/2 -translate-y-1/2 z-20 bg-white/90 rounded-full p-2 md:p-3 shadow-md hover:bg-white transition">
                <Icon icon="mdi:chevron-right" class="text-xl md:text-2xl" />
            </button>

            <!-- Dots -->
            <div v-if="genericBanners.length > 1" class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex gap-2">
                <button v-for="(_, idx) in genericBanners" :key="idx" @click="currentSlide = idx"
                    class="h-1.5 md:h-2 rounded-full transition-all"
                    :class="currentSlide === idx ? 'w-6 md:w-8 bg-yellow-500' : 'w-1.5 md:w-2 bg-white/70'">
                </button>
            </div>
        </section>

        <section class="max-w-full md:max-w-8/10 mx-auto px-4 md:px-3 sm:px-4 py-4 md:py-6">
            <div class=" py-3">
                <button @click="goBack"
                    class="inline-flex items-center gap-1 px-3 py-2 rounded-md border border-gray-200 bg-white text-sm text-gray-700 hover:bg-gray-50 transition">
                    <Icon icon="mdi:arrow-left" class="text-base" />
                    Back
                </button>
            </div>

            <!-- Mobile Filter Toggle - Compact -->
            <div class="lg:hidden mb-3">
                <button @click="showMobileFilters = !showMobileFilters"
                    class="w-full flex items-center justify-between bg-white p-3 rounded-lg shadow-sm border border-gray-200 hover:border-brand-teal transition-colors">
                    <span class="text-sm font-medium text-gray-700 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            :style="{ color: 'var(--brand-teal)' }">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Filters
                        <span v-if="activeFilterCount > 0"
                            class="ml-1.5 bg-brand-blue text-white text-[10px] px-1.5 py-0.5 rounded-full">
                            {{ activeFilterCount }}
                        </span>
                    </span>
                    <svg class="w-4 h-4 text-gray-500 transition-transform" :class="{ 'rotate-180': showMobileFilters }"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-5">

                <!-- Sidebar - Compact -->
                <aside class="lg:col-span-1 space-y-4"
                    :class="showMobileFilters ? 'block mobile-filter-sidebar' : 'hidden lg:block'">

                    <!-- Close button for mobile -->
                    <div class="lg:hidden flex items-center justify-between mb-3">
                        <h2 class="font-semibold text-base">Filters</h2>
                        <button @click="showMobileFilters = false" class="p-1.5 hover:bg-gray-100 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Category Filter - Compact -->
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-medium text-sm text-gray-800 flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    :style="{ color: 'var(--brand-teal)' }">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                                Categories
                            </h3>
                            <button @click="showAllCategories = !showAllCategories" v-if="categories?.length > 5"
                                class="text-xs text-brand-teal hover:text-brand-teal/80">
                                {{ showAllCategories ? 'Show Less' : 'Show All' }}
                            </button>
                        </div>
                        <div class="space-y-0.5 max-h-60 overflow-y-auto">
                            <div v-for="(category, index) in categories" :key="category.id"
                                @click="selectCategory(category)" :class="[
                                    'text-xs cursor-pointer py-1.5 px-2 rounded transition-all duration-200',
                                    'hover:bg-brand-teal/5 hover:text-brand-teal hover:pl-3',
                                    'border-l-2 transition-colors',
                                    selectedCategoryId === category.id
                                        ? 'bg-brand-blue/10 text-brand-blue border-brand-blue'
                                        : 'border-transparent hover:border-brand-teal',
                                    index >= 5 && !showAllCategories ? 'hidden' : 'block'
                                ]">
                                {{ category.name }}
                                <span class="text-[10px] text-gray-400 ml-1">({{ getCategoryAdCount(category) }})</span>
                            </div>
                        </div>
                    </div>

                    <!-- Brand Filter - Compact -->
                    <div class="bg-white rounded-lg shadow-sm p-4" v-if="brands?.length">
                        <h3 class="font-medium text-sm text-gray-800 mb-3 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                :style="{ color: 'var(--brand-teal)' }">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M16 4h2a2 2 0 012 2v2M16 4h-2a2 2 0 00-2 2v2m4-4v2a2 2 0 01-2 2h-2m4 8h2a2 2 0 002-2v-2m-2 0h-2a2 2 0 00-2 2v2m-4-4v2a2 2 0 01-2 2H8a2 2 0 01-2-2V8a2 2 0 012-2h6a2 2 0 012 2z" />
                            </svg>
                            Brands
                        </h3>
                        <div class="space-y-1 max-h-60 overflow-y-auto">
                            <div v-for="brand in filteredBrands" :key="brand.id" @click="toggleBrand(brand.id)" :class="[
                                'flex items-center justify-between cursor-pointer py-1.5 px-2 rounded transition-colors text-xs',
                                'hover:bg-brand-teal/5',
                                selectedBrands.includes(brand.id) ? 'bg-brand-blue/10 text-brand-blue' : ''
                            ]">
                                <span>{{ brand.name }}</span>
                                <span class="text-[10px] text-gray-400">{{ getBrandAdCount(brand.id) }}</span>
                            </div>
                        </div>

                        <!-- Show all brands toggle -->
                        <button v-if="brands.length > 10" @click="showAllBrands = !showAllBrands"
                            class="mt-2 text-xs text-brand-teal hover:text-brand-teal/80 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    :d="showAllBrands ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7'" />
                            </svg>
                            {{ showAllBrands ? 'Show Less' : `Show All (${brands.length})` }}
                        </button>
                    </div>

                    <!-- Model Filter - Compact -->
                    <div class="bg-white rounded-lg shadow-sm p-4" v-if="brands?.some(brand => brand.models?.length)">
                        <h3 class="font-medium text-sm text-gray-800 mb-3 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                :style="{ color: 'var(--brand-teal)' }">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M9 3v4m6-4v4M9 7h6M3 9h18M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" />
                            </svg>
                            Models
                        </h3>
                        <div class="space-y-2 max-h-60 overflow-y-auto">
                            <div v-for="brand in brands.filter(b => b.models?.length)" :key="brand.id">
                                <div class="text-xs font-medium text-gray-600 mb-1">{{ brand.name }}</div>
                                <div class="ml-2 space-y-0.5">
                                    <div v-for="model in brand.models" :key="model.id" @click="toggleModel(model.id)"
                                        :class="[
                                            'flex items-center justify-between cursor-pointer py-1 px-2 rounded transition-colors text-xs',
                                            'hover:bg-brand-teal/5',
                                            selectedModels.includes(model.id) ? 'bg-brand-blue/10 text-brand-blue' : ''
                                        ]">
                                        <span>{{ model.name }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Price Filter - Compact -->
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <h3 class="font-medium text-sm text-gray-800 mb-3 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                :style="{ color: 'var(--brand-teal)' }">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Price Range
                        </h3>
                        <div class="space-y-3">
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[10px] text-gray-500 mb-0.5">Min (Pkr)</label>
                                    <input type="number" placeholder="Min" v-model.number="minPrice"
                                        @input="debouncedApplyFilters"
                                        class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-1 focus:ring-brand-teal focus:border-brand-teal outline-none transition text-xs" />
                                </div>
                                <div>
                                    <label class="block text-[10px] text-gray-500 mb-0.5">Max (Pkr)</label>
                                    <input type="number" placeholder="Max" v-model.number="maxPrice"
                                        @input="debouncedApplyFilters"
                                        class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-1 focus:ring-brand-teal focus:border-brand-teal outline-none transition text-xs" />
                                </div>
                            </div>

                            <!-- Quick price suggestions - Compact -->
                            <div class="flex flex-wrap gap-1.5">
                                <button v-for="range in priceRanges" :key="range.label"
                                    @click="setQuickPriceRange(range.min, range.max)"
                                    class="text-[10px] px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded-full transition-colors">
                                    {{ range.label }}
                                </button>
                            </div>

                            <button @click="applyFilters"
                                class="w-full bg-brand-blue hover:bg-brand-blue/90 text-white font-medium py-2 rounded transition-all duration-200 shadow-sm hover:shadow text-xs">
                                Apply Filter
                            </button>
                        </div>
                    </div>

                    <!-- Attribute Filters - Compact -->
                    <div v-if="attributes?.filter(attr => attr.is_filterable).length"
                        class="bg-white rounded-lg shadow-sm p-4">
                        <h3 class="font-medium text-sm text-gray-800 mb-3 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                :style="{ color: 'var(--brand-teal)' }">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M3 6a3 3 0 013-3h12a3 3 0 013 3v12a3 3 0 01-3 3H6a3 3 0 01-3-3V6zM8 8h8M8 12h6M8 16h4" />
                            </svg>
                            Specifications
                        </h3>
                        <div class="space-y-3">
                            <div v-for="attribute in attributes.filter(attr => attr.is_filterable)" :key="attribute.id">
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">{{ attribute.name
                                }}</label>

                                <!-- Select Attributes -->
                                <div v-if="attribute.type === 'select' && attribute.options?.length" class="space-y-1">
                                    <div v-for="option in attribute.options" :key="option.id"
                                        @click="toggleAttribute(attribute.id, option.id)" :class="[
                                            'flex items-center justify-between cursor-pointer py-1 px-2 rounded transition-colors text-xs',
                                            'hover:bg-brand-teal/5',
                                            isAttributeSelected(attribute.id, option.id) ? 'bg-brand-blue/10 text-brand-blue' : ''
                                        ]">
                                        <span>{{ option.value }}</span>
                                    </div>
                                </div>

                                <!-- Text Attributes -->
                                <div v-else-if="attribute.type === 'text'">
                                    <input type="text" :placeholder="`Enter ${attribute.name.toLowerCase()}`"
                                        :value="attributeFilters[`attribute_${attribute.id}`]"
                                        @input="(e) => updateAttributeFilter(attribute.id, e.target.value)"
                                        class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-1 focus:ring-brand-teal focus:border-brand-teal outline-none transition text-xs" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Active Filters Summary - Compact -->
                    <div v-if="activeFilterCount > 0" class="bg-brand-blue/5 rounded-lg p-3">
                        <h4 class="text-xs font-medium text-gray-700 mb-2">Active Filters:</h4>
                        <div class="flex flex-wrap gap-1.5">
                            <span v-if="selectedCategoryId"
                                class="inline-flex items-center gap-1 bg-white text-[10px] px-2 py-1 rounded-full shadow-sm">
                                {{ selectedCategoryName }}
                                <button @click="clearCategoryFilter" class="ml-0.5 hover:text-brand-teal">×</button>
                            </span>
                            <span v-if="minPrice || maxPrice"
                                class="inline-flex items-center gap-1 bg-white text-[10px] px-2 py-1 rounded-full shadow-sm">
                                {{ minPrice || 0 }} - {{ maxPrice || '∞' }}
                                <button @click="clearPriceFilter" class="ml-0.5 hover:text-brand-teal">×</button>
                            </span>
                            <span v-if="selectedBrands.length"
                                class="inline-flex items-center gap-1 bg-white text-[10px] px-2 py-1 rounded-full shadow-sm">
                                {{ selectedBrands.length }} {{ selectedBrands.length === 1 ? 'brand' : 'brands' }}
                                <button @click="clearBrandFilter" class="ml-0.5 hover:text-brand-teal">×</button>
                            </span>
                            <span v-if="selectedModels.length"
                                class="inline-flex items-center gap-1 bg-white text-[10px] px-2 py-1 rounded-full shadow-sm">
                                {{ selectedModels.length }} {{ selectedModels.length === 1 ? 'model' : 'models' }}
                                <button @click="clearModelFilter" class="ml-0.5 hover:text-brand-teal">×</button>
                            </span>
                            <span v-for="(value, key) in attributeFilters" :key="key"
                                v-if="value && (Array.isArray(value) ? value.length > 0 : value)">
                                <span
                                    class="inline-flex items-center gap-1 bg-white text-[10px] px-2 py-1 rounded-full shadow-sm">
                                    {{ getAttributeName(key) }}
                                    <button @click="clearAttributeFilter(key)"
                                        class="ml-0.5 hover:text-brand-teal">×</button>
                                </span>
                            </span>
                        </div>

                        <button @click="resetFilters"
                            class="mt-2 text-xs text-brand-teal hover:text-brand-teal/80 font-medium">
                            Clear all filters
                        </button>
                    </div>

                    <!-- Mobile Filter Actions - Compact -->
                    <div class="lg:hidden bg-white rounded-lg shadow-sm p-3 border-t border-gray-100 sticky bottom-0">
                        <div class="grid grid-cols-2 gap-2">
                            <button @click="resetFilters"
                                class="py-2 border border-gray-300 text-gray-700 font-medium rounded hover:bg-gray-50 transition-colors text-xs">
                                Reset All
                            </button>
                            <button @click="showMobileFilters = false"
                                class="py-2 bg-brand-blue text-white font-medium rounded hover:bg-brand-blue/90 transition-colors text-xs shadow-sm">
                                Show Results
                            </button>
                        </div>
                    </div>

                </aside>

                <!-- Main Content - Compact -->
                <main class="lg:col-span-2">
                    <!-- Header - Compact -->
                    <div class="mb-4 md:mb-5">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                            <div>
                                <h1 class="text-xl md:text-2xl font-semibold text-gray-900 mb-1">
                                    All Items
                                </h1>
                                <p class="text-gray-600 text-xs md:text-sm">
                                    {{ totalAds }} ads found
                                    <span v-if="selectedCategoryId" class="text-brand-teal">
                                        in {{ selectedCategoryName }}
                                    </span>
                                    <span v-if="allLoadedAds.length > 0 && allLoadedAds.length < totalAds"
                                        class="text-brand-teal ml-1">
                                        • Showing {{ allLoadedAds.length }} of {{ totalAds }}
                                    </span>
                                </p>
                            </div>

                            <!-- Sort for Mobile -->
                            <div class="md:hidden">
                                <SelectInput v-model="sortBy" @update:modelValue="applyFilters" placeholder="Sort By">
                                    <SelectContent>
                                        <SelectItem value="newest">
                                            Newest First
                                        </SelectItem>

                                        <SelectItem value="price_low">
                                            Price: Low to High
                                        </SelectItem>

                                        <SelectItem value="price_high">
                                            Price: High to Low
                                        </SelectItem>
                                    </SelectContent>
                                </SelectInput>
                            </div>
                        </div>
                    </div>

                    <!-- Toolbar - Compact -->
                    <div class="bg-white rounded-lg shadow-sm p-3 mb-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <!-- View Toggle -->
                            <div class="flex items-center justify-between sm:justify-start">
                                <div class="flex items-center space-x-1">
                                    <button @click="viewMode = 'grid'"
                                        :class="viewMode === 'grid' ? 'bg-brand-blue/10 text-brand-blue' : 'text-gray-400 hover:text-gray-600'"
                                        class="p-1.5 rounded transition-all duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                        </svg>
                                    </button>
                                    <button @click="viewMode = 'list'"
                                        :class="viewMode === 'list' ? 'bg-brand-blue/10 text-brand-blue' : 'text-gray-400 hover:text-gray-600'"
                                        class="p-1.5 rounded transition-all duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M4 6h16M4 12h16M4 18h16" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Active Filters Badge - Mobile -->
                                <div class="sm:hidden text-xs text-gray-500">
                                    {{ activeFilterCount > 0 ? `${activeFilterCount} active` : '' }}
                                </div>
                            </div>

                            <!-- Sort for Desktop -->
                            <div class="hidden md:flex items-center space-x-3">
                                <span class="text-xs text-gray-600">Sort:</span>
                                <SelectInput v-model="sortBy" @update:modelValue="applyFilters" placeholder="Sort By"
                                    class="min-w-[140px]">
                                    <SelectContent>
                                        <SelectItem value="newest">
                                            Newest First
                                        </SelectItem>

                                        <SelectItem value="price_low">
                                            Price: Low to High
                                        </SelectItem>

                                        <SelectItem value="price_high">
                                            Price: High to Low
                                        </SelectItem>
                                    </SelectContent>
                                </SelectInput>
                            </div>

                            <!-- Reset Filters Button - Desktop -->
                            <button @click="resetFilters" v-if="activeFilterCount > 0"
                                class="hidden md:inline-flex items-center text-xs text-gray-600 hover:text-gray-800">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Reset Filters
                            </button>
                        </div>
                    </div>

                    <!-- Global Loading Spinner -->
                    <div v-if="isLoading && allLoadedAds.length === 0" class="text-center py-12">
                        <svg class="animate-spin w-10 h-10 text-brand-teal mx-auto mb-3" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <p class="text-sm text-gray-500">Loading ads...</p>
                    </div>

                    <!-- Results -->
                    <div v-else-if="allLoadedAds.length > 0">
                        <!-- Grid View -->
                        <div v-if="viewMode === 'grid'"
                            class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-4">
                            <template v-for="(ad, idx) in allLoadedAds" :key="ad.id">
                                <AdCard :ad="ad" />
                                <!-- Insert category banner after every 5 ads -->
                                <div v-if="shouldShowInlineBanner(idx, allLoadedAds.length)"
                                    :key="'inline-banner-' + idx"
                                    class="col-span-1 xs:col-span-2 sm:col-span-2 md:col-span-2 lg:col-span-3">
                                    <a :href="getInlineBanner(idx)?.link" target="_blank" rel="noopener noreferrer"
                                        class="block">
                                        <img :src="getInlineBanner(idx)?.image_url" :alt="getInlineBanner(idx)?.title"
                                            class="w-full rounded-lg shadow-sm hover:shadow-md transition-shadow" />
                                    </a>
                                </div>
                            </template>
                        </div>

                        <!-- List View -->
                        <div v-if="viewMode === 'list'" class="space-y-2 md:space-y-3">
                            <template v-for="(ad, idx) in allLoadedAds" :key="ad.id">
                                <AdListItem :ad="ad" />
                                <div v-if="shouldShowInlineBanner(idx, allLoadedAds.length)"
                                    :key="'inline-banner-' + idx" class="my-2">
                                    <a :href="getInlineBanner(idx)?.link" target="_blank" rel="noopener noreferrer"
                                        class="block">
                                        <img :src="getInlineBanner(idx)?.image_url" :alt="getInlineBanner(idx)?.title"
                                            class="w-full rounded-lg shadow-sm hover:shadow-md transition-shadow" />
                                    </a>
                                </div>
                            </template>
                        </div>

                        <!-- Loading indicator for infinite scroll -->
                        <div v-if="loading" class="text-center py-4">
                            <svg class="animate-spin w-6 h-6 text-brand-teal mx-auto" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <p class="text-xs text-gray-500 mt-2">Loading more ads...</p>
                        </div>

                        <!-- Infinite scroll trigger - only show if there are more pages -->
                        <div ref="loadMoreTrigger" v-if="hasMorePages && !loading && allLoadedAds.length > 0"
                            class="h-10"></div>

                        <!-- No more items message -->
                        <div v-if="!hasMorePages && allLoadedAds.length > 0 && allLoadedAds.length === totalAds"
                            class="text-center py-4">
                            <p class="text-xs text-gray-400">You've seen all {{ totalAds }} ads</p>
                        </div>
                    </div>

                    <!-- No Results - Compact (only show when not loading) -->
                    <div v-else-if="!isLoading && allLoadedAds.length === 0"
                        class="text-center py-10 md:py-12 bg-white rounded-lg shadow-sm">
                        <div class="max-w-md mx-auto px-4">
                            <svg class="w-12 h-12 md:w-16 md:h-16 text-gray-300 mx-auto mb-3 md:mb-4" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <h3 class="text-lg md:text-xl font-semibold text-gray-900 mb-2">
                                No matching ads found
                            </h3>
                            <p class="text-gray-600 text-xs md:text-sm mb-4 md:mb-5">
                                Try adjusting your filters to find what you're looking for.
                            </p>
                            <button @click="resetFilters"
                                class="px-5 py-2 bg-brand-blue text-white font-medium rounded-lg hover:bg-brand-blue/90 transition-colors duration-200 text-xs shadow-sm">
                                Reset Filters
                            </button>
                        </div>
                    </div>

                </main>
            </div>

        </section>
    </OlxLayout>
</template>

<style scoped>
.mobile-filter-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: white;
    z-index: 50;
    overflow-y: auto;
    padding: 1rem;
    margin: 0;
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        transform: translateX(-100%);
    }

    to {
        transform: translateX(0);
    }
}

.rotate-180 {
    transform: rotate(180deg);
}

/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
    width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: var(--brand-teal);
    border-radius: 4px;
}

/* Custom breakpoint for very small screens */
@media (min-width: 475px) {
    .xs\:grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

/* Improve scrolling on mobile */
@media (max-width: 1023px) {
    aside {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: white;
        z-index: 50;
        overflow-y: auto;
        padding: 1rem;
        margin: 0;
    }

    aside:not(.hidden) {
        animation: slideIn 0.3s ease-out;
    }
}

@keyframes slideIn {
    from {
        transform: translateX(-100%);
    }

    to {
        transform: translateX(0);
    }
}
</style>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AdCard from '@/components/AdCard.vue'
import AdListItem from '@/components/AdListItem.vue'
import OlxLayout from '@/layouts/OlxLayout.vue'
import debounce from 'lodash/debounce'

const page = usePage()

// Props from controller
const props = defineProps<{
    ads: {
        data: any[]
        links: any[]
        current_page: number
        last_page: number
        total: number
    }
    categories: any[]
    brands: any[]
    attributes?: any[]
    filters: {
        filter: {
            global?: string
            category?: string
            brand?: string
            model?: string
        }
        min_price?: number
        max_price?: number
        sort_by?: string
        attributeFilters?: Record<string, any>
    }
    totalAds: number
    priceRange?: {
        min: number
        max: number
    }
}>()

// ----------------------
// BANNERS (new logic)
// ----------------------
const allBanners = computed(() => (page.props as any).banners || [])

// Banners that are generic (target_category_id = null) – shown as top carousel
const genericBanners = computed(() =>
    allBanners.value.filter((b: any) => b.target_category_id === null)
)

// Banners that target the selected category – shown between ads
const categoryBanners = computed(() =>
    allBanners.value.filter((b: any) => b.target_category_id?.toString() === selectedCategoryId.value)
)

// Carousel state
const currentSlide = ref(0)
let slideInterval: ReturnType<typeof setInterval> | null = null

const nextSlide = () => {
    if (genericBanners.value.length > 1) {
        currentSlide.value = (currentSlide.value + 1) % genericBanners.value.length
    }
}
const prevSlide = () => {
    if (genericBanners.value.length > 1) {
        currentSlide.value = (currentSlide.value - 1 + genericBanners.value.length) % genericBanners.value.length
    }
}

// Auto‑rotate every 5 seconds
const startAutoRotate = () => {
    if (slideInterval) clearInterval(slideInterval)
    if (genericBanners.value.length > 1) {
        slideInterval = setInterval(nextSlide, 5000)
    }
}
const stopAutoRotate = () => {
    if (slideInterval) {
        clearInterval(slideInterval)
        slideInterval = null
    }
}

// Inline banner placement: after every 5 ads, or after the last ad if total ≤ 5
const BANNER_POSITION_INTERVAL = 5

const shouldShowInlineBanner = (index: number, totalAds: number) => {
    if (!categoryBanners.value.length) return false
    if (totalAds <= BANNER_POSITION_INTERVAL) {
        // Show only after the last ad
        return index === totalAds - 1
    }
    // Show after every N ads, but not after the last one (to avoid double banner after last)
    return (index + 1) % BANNER_POSITION_INTERVAL === 0 && index < totalAds - 1
}

const getInlineBanner = (position: number) => {
    const banners = categoryBanners.value
    if (!banners.length) return null
    // Cycle through available banners
    const bannerIndex = position % banners.length
    return banners[bannerIndex]
}

// Reactive state
const viewMode = ref<'grid' | 'list'>('grid')
const sortBy = ref(props.filters.sort_by || 'newest')
const minPrice = ref<number | null>(props.filters.min_price || null)
const maxPrice = ref<number | null>(props.filters.max_price || null)
const selectedCategoryId = ref<string | null>(props.filters.filter.category || null)
const selectedBrands = ref<string[]>(props.filters.filter.brand ? props.filters.filter.brand.split(',') : [])
const selectedModels = ref<string[]>(props.filters.filter.model ? props.filters.filter.model.split(',') : [])
const attributeFilters = ref<Record<string, any>>(props.filters.attributeFilters || {})
const showMobileFilters = ref(false)
const showAllCategories = ref(false)
const showAllBrands = ref(false)

// Infinite scroll state
const allLoadedAds = ref<any[]>([])
const currentPage = ref(1)
const totalPages = ref(1)
const loading = ref(false)
const loadMoreTrigger = ref<HTMLElement | null>(null)
let observer: IntersectionObserver | null = null

// Global loading state for initial load and filter changes
const isLoading = ref(false)

// Computed properties
const totalAds = computed(() => props.totalAds)

const filteredBrands = computed(() => {
    let filtered = props.brands

    // Filter brands by selected category if any
    if (selectedCategoryId.value) {
        filtered = filtered.filter(brand =>
            brand.categories?.some((cat: any) => cat.id == selectedCategoryId.value)
        )
    }

    // Limit brands if not showing all
    if (!showAllBrands.value && filtered.length > 10) {
        filtered = filtered.slice(0, 10)
    }

    return filtered
})

const activeFilterCount = computed(() => {
    let count = 0
    if (minPrice.value !== null && minPrice.value > 0) count++
    if (maxPrice.value !== null && maxPrice.value > 0) count++
    if (selectedCategoryId.value) count++
    if (selectedBrands.value.length) count++
    if (selectedModels.value.length) count++

    // Count attribute filters
    Object.values(attributeFilters.value).forEach(value => {
        if (value && (Array.isArray(value) ? value.length > 0 : true)) {
            count++
        }
    })

    return count
})

const selectedCategoryName = computed(() => {
    if (!selectedCategoryId.value) return ''
    const category = props.categories.find(c => c.id == selectedCategoryId.value)
    return category?.name || ''
})

const hasMorePages = computed(() => currentPage.value < totalPages.value)

// Watch for initial ads data
watch(() => props.ads, (newAds) => {
    if (newAds) {
        // For first load or filter change, replace all ads
        if (newAds.current_page === 1) {
            allLoadedAds.value = [...newAds.data]
        } else {
            // For subsequent pages, append new ads
            allLoadedAds.value = [...allLoadedAds.value, ...newAds.data]
        }
        currentPage.value = newAds.current_page
        totalPages.value = newAds.last_page
        // Turn off global loading when data arrives
        isLoading.value = false
    }
}, { immediate: true, deep: true })

// Watch category selection to update category banners
watch(selectedCategoryId, () => {
    // Category banners will automatically update via computed property
})

// Setup Intersection Observer for infinite scroll
const setupObserver = () => {
    if (observer) {
        observer.disconnect()
    }

    observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && !loading.value && hasMorePages.value) {
            loadMore()
        }
    }, { threshold: 0.1, rootMargin: '100px' })

    if (loadMoreTrigger.value) {
        observer.observe(loadMoreTrigger.value)
    }
}

// Load more ads
const loadMore = () => {
    if (loading.value || !hasMorePages.value) return

    const nextPage = currentPage.value + 1
    if (nextPage > totalPages.value) {
        loading.value = false
        return
    }

    loading.value = true

    const params: any = {
        page: nextPage
    }

    // Add all filter parameters
    if (minPrice.value !== null) params.min_price = minPrice.value
    if (maxPrice.value !== null) params.max_price = maxPrice.value
    if (selectedCategoryId.value) params['filter[category]'] = selectedCategoryId.value
    if (selectedBrands.value.length) params['filter[brand]'] = selectedBrands.value.join(',')
    if (selectedModels.value.length) params['filter[model]'] = selectedModels.value.join(',')
    if (sortBy.value) params.sort_by = sortBy.value

    // Add attribute filters
    Object.entries(attributeFilters.value).forEach(([key, value]) => {
        if (value && (Array.isArray(value) ? value.length > 0 : true)) {
            params[`filter[${key}]`] = Array.isArray(value) ? value.join(',') : value
        }
    })

    // Preserve search term if exists
    if (props.filters.filter.global) {
        params['filter[global]'] = props.filters.filter.global
    }

    router.visit(route('all.items'), {
        method: 'get',
        data: params,
        preserveState: true,
        preserveScroll: true,
        only: ['ads'], // Only update the ads prop
        onSuccess: () => {
            loading.value = false
            // Re-setup observer after new content loads
            setTimeout(() => {
                setupObserver()
            }, 100)
        },
        onError: () => {
            loading.value = false
        }
    })
}

// Helper methods for counts
const getCategoryAdCount = (category: any) => {
    return category.ads_count || 0
}

const getBrandAdCount = (brandId: string) => {
    return allLoadedAds.value.filter(ad => ad.brand_id == brandId).length || 0
}

// Filter methods
const selectCategory = (category: any) => {
    if (selectedCategoryId.value === category.id) {
        selectedCategoryId.value = null
    } else {
        selectedCategoryId.value = category.id
        // Reset showAllBrands when category changes
        showAllBrands.value = false
    }
    applyFilters()
}

const toggleBrand = (brandId: string) => {
    const index = selectedBrands.value.indexOf(brandId)
    if (index > -1) {
        selectedBrands.value.splice(index, 1)
    } else {
        selectedBrands.value.push(brandId)
    }
    applyFilters()
}

const toggleModel = (modelId: string) => {
    const index = selectedModels.value.indexOf(modelId)
    if (index > -1) {
        selectedModels.value.splice(index, 1)
    } else {
        selectedModels.value.push(modelId)
    }
    applyFilters()
}

const toggleAttribute = (attributeId: number, optionId: number) => {
    const key = `attribute_${attributeId}`

    if (!attributeFilters.value[key]) {
        attributeFilters.value[key] = []
    }

    const arr = attributeFilters.value[key]
    const index = arr.indexOf(optionId)

    if (index > -1) {
        arr.splice(index, 1)
        if (arr.length === 0) {
            delete attributeFilters.value[key]
        }
    } else {
        arr.push(optionId)
    }

    applyFilters()
}

const isAttributeSelected = (attributeId: number, optionId: number): boolean => {
    const key = `attribute_${attributeId}`
    return attributeFilters.value[key]?.includes(optionId) || false
}

const updateAttributeFilter = (attributeId: number, value: string) => {
    const key = `attribute_${attributeId}`
    if (value) {
        attributeFilters.value[key] = value
    } else {
        delete attributeFilters.value[key]
    }
    applyFilters()
}

const getAttributeName = (key: string): string => {
    const attributeId = key.replace('attribute_', '')
    const attribute = props.attributes?.find(attr => attr.id == attributeId)
    return attribute?.name || key
}

const clearAttributeFilter = (key: string) => {
    delete attributeFilters.value[key]
    applyFilters()
}

const setQuickPriceRange = (min: number | null, max: number | null) => {
    minPrice.value = min
    maxPrice.value = max
    applyFilters()
}

const applyFilters = () => {
    // Reset all loaded ads and pagination when filters change
    allLoadedAds.value = []
    currentPage.value = 1
    isLoading.value = true // Show global spinner

    const params: any = {}

    if (minPrice.value !== null) params.min_price = minPrice.value
    if (maxPrice.value !== null) params.max_price = maxPrice.value
    if (selectedCategoryId.value) params['filter[category]'] = selectedCategoryId.value
    if (selectedBrands.value.length) params['filter[brand]'] = selectedBrands.value.join(',')
    if (selectedModels.value.length) params['filter[model]'] = selectedModels.value.join(',')
    if (sortBy.value) params.sort_by = sortBy.value

    // Add attribute filters
    Object.entries(attributeFilters.value).forEach(([key, value]) => {
        if (value && (Array.isArray(value) ? value.length > 0 : true)) {
            params[`filter[${key}]`] = Array.isArray(value) ? value.join(',') : value
        }
    })

    // Preserve search term if exists
    if (props.filters.filter.global) {
        params['filter[global]'] = props.filters.filter.global
    }

    // Auto-close mobile filters after applying
    if (window.innerWidth < 1024) {
        showMobileFilters.value = false
    }

    router.visit(route('all.items'), {
        method: 'get',
        data: params,
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            isLoading.value = false
            loading.value = false
        },
        onError: () => {
            isLoading.value = false
            loading.value = false
        }
    })
}

const debouncedApplyFilters = debounce(applyFilters, 500)

const resetFilters = () => {
    minPrice.value = null
    maxPrice.value = null
    selectedCategoryId.value = null
    selectedBrands.value = []
    selectedModels.value = []
    attributeFilters.value = {}
    sortBy.value = 'newest'
    showAllCategories.value = false
    showAllBrands.value = false
    showMobileFilters.value = false

    // Reset loaded ads
    allLoadedAds.value = []
    currentPage.value = 1
    isLoading.value = true

    const params: any = {}

    // Preserve search term if exists
    if (props.filters.filter.global) {
        params['filter[global]'] = props.filters.filter.global
    }

    router.visit(route('all.items'), {
        method: 'get',
        data: params,
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            isLoading.value = false
        },
        onError: () => {
            isLoading.value = false
        }
    })
}

const clearPriceFilter = () => {
    minPrice.value = null
    maxPrice.value = null
    applyFilters()
}

const clearCategoryFilter = () => {
    selectedCategoryId.value = null
    applyFilters()
}

const clearBrandFilter = () => {
    selectedBrands.value = []
    applyFilters()
}

const clearModelFilter = () => {
    selectedModels.value = []
    applyFilters()
}

const priceRanges = [
    { label: 'Under 100', min: 0, max: 100 },
    { label: '100 - 500', min: 100, max: 500 },
    { label: '500 - 1000', min: 500, max: 1000 },
    { label: '1000+', min: 1000, max: null }
]

// Watch for ads changes to re-setup observer
watch(allLoadedAds, () => {
    if (hasMorePages.value && allLoadedAds.value.length > 0) {
        setTimeout(() => {
            setupObserver()
        }, 100)
    }
}, { deep: true })

// Lifecycle hooks
onMounted(() => {
    // Setup infinite scroll observer after DOM is ready
    setTimeout(() => {
        setupObserver()
    }, 100)

    // Start auto-rotate for banners
    startAutoRotate()

    // Handle resize to close mobile filters
    const handleResize = () => {
        if (window.innerWidth >= 1024) {
            showMobileFilters.value = false
        }
    }

    window.addEventListener('resize', handleResize)

    // Cleanup
    onUnmounted(() => {
        window.removeEventListener('resize', handleResize)
        if (observer) observer.disconnect()
        debouncedApplyFilters.cancel()
        stopAutoRotate()
    })
})

onUnmounted(() => {
    if (observer) observer.disconnect()
    debouncedApplyFilters.cancel()
    stopAutoRotate()
})

const goBack = () => {
    window.history.back()
}
</script>