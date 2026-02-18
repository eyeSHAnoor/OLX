<template>
    <OlxLayout>

        <!-- NORMAL HOMEPAGE -->
        <template v-if="!isSearching">

            <!-- Browse Categories -->
            <section class="py-10 bg-gray-50 max-w-7/8 mx-auto px-4">
                <h2 class="text-2xl font-semibold mb-8 text-center">
                    Browse Categories
                </h2>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-7 gap-2 space-y-10">
                    <div v-for="category in categories" :key="category.id"
                        class="flex flex-col items-center cursor-pointer group" @click="filterByCategory(category.id)">
                        <div
                            class="w-35 h-40 rounded-xl overflow-hidden shadow-sm bg-white group-hover:shadow-md transition">
                            <img v-if="category.files?.length" :src="category.files[0].file_url"
                                class="w-full h-full object-cover" />
                            <div v-else class="w-full h-full flex items-center justify-center text-xs text-gray-400">
                                No Image
                            </div>
                        </div>
                        <span class="mt-2 text-lg font-medium text-center group-hover:text-yellow-600">
                            {{ category.name }}
                            <span v-if="category.ads_count" class="text-sm text-gray-500">
                                ({{ category.ads_count }})
                            </span>
                        </span>
                    </div>
                </div>
            </section>

            <section class="max-w-9/11 mx-auto px-4 space-y-12 pb-20">
                <CategoryAds v-for="cat in topCategories" :key="cat.id" :category="cat"
                    :search-term="form.filter.global" />
            </section>

        </template>

        <!-- SEARCH MODE -->
        <template v-else>
            <div class="max-w-9/11 mx-auto px-4 py-4">
                <button @click="resetFilters"
                    class="flex items-center px-5 py-3 rounded-xl text-gray-700 font-semibold hover:shadow-md transition-all duration-200 group">
                    <Icon icon="mdi:arrow-left"
                        class="text-4xl font-extrabold text-gray-600 group-hover:text-yellow-500 mr-2" />

                </button>
            </div>
            <SearchResults :category="activeCategory" :categories="categories" :search-term="form.filter.global"
                :selected-category="form.filter.category" :selected-brand="form.filter.brand" :reset="resetFilters" />
        </template>

    </OlxLayout>
</template>

<script setup lang="ts">
import OlxLayout from '@/layouts/OlxLayout.vue'
import { usePage } from '@inertiajs/vue3';
import CategoryAds from '@/components/CategoryAds.vue'
import SearchResults from './_partials/SearchResults.vue';
import { ref, computed } from 'vue'
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'

interface PageProps extends InertiaPageProps {
    ads: PaginatedData<App.Data.AdData>;
    categories: App.Data.CategoryData[];
    brands: App.Data.BrandData[];
    filters: any;
}

const page = usePage<PageProps>();
const categories = computed(() => page.props.categories)
const brands = computed(() => page.props.brands)
const isSearching = computed(() => {
    return page.props.isSearching
})

console.log(page.props)

// Initialize search filter with additional filters
const { form, reset, isFiltered, dateRange } = useSearchFilter(route('home'), {
    filter: {
        global: '',
        category: '',
        brand: ''
    }
});

// Reset filters
const resetFilters = () => {
    form.value = {
        filter: { global: '', category: '', brand: '' },
        start_date: null,
        end_date: null,
        sort: 'created_at'
    };
    dateRange.value = '';
};

// Filter by category on click
const filterByCategory = (categoryId: number) => {
    form.value.filter.category = categoryId;
};

// Get flat list of all categories (including children) for dropdown
const allCategoriesFlat = computed(() => {
    const flatList: any[] = [];

    categories.value.forEach(category => {
        // Add child categories
        if (category.children_recursive && category.children_recursive.length) {
            category.children_recursive.forEach(child => {
                flatList.push(child);
            });
        }
    });

    return flatList;
});

// Filter brands based on selected category
const filteredBrands = computed(() => {
    if (!form.value.filter.category) {
        return brands.value;
    }

    // Find selected category and get its leaf categories
    const selectedCat = categories.value.find(cat =>
        cat.id == form.value.filter.category ||
        cat.children_recursive?.some(child => child.id == form.value.filter.category)
    );

    if (!selectedCat) return brands.value;

    // Get all leaf category IDs
    const leafCategories = selectedCat.getLeafCategoriesEfficient?.() || [];
    const leafCategoryIds = leafCategories.map(cat => cat.id);

    // Filter brands that belong to these categories
    return brands.value.filter(brand =>
        brand.categories?.some(cat => leafCategoryIds.includes(cat.id))
    );
});

const topCategories = computed(() => {
    return categories.value
        .filter(c => c.parent_id === null)
        .slice(0, 7)
});

const activeCategory = computed(() => {
    if (form.value.filter.category) {
        const allCats = [...categories.value, ...allCategoriesFlat.value];
        return allCats.find(cat => cat.id == form.value.filter.category) || null;
    }
    return categories.value[0] || null;
});

console.log(activeCategory)
</script>

<style scoped>
/* Add custom styles if needed */
</style>