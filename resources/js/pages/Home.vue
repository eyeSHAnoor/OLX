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
                        class="flex flex-col items-center cursor-pointer group">
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
                        </span>
                    </div>
                </div>
            </section>

            <section class="max-w-9/11 mx-auto px-4 space-y-12 pb-20">
                <CategoryAds v-for="cat in categories" :key="cat.id" :category="cat" />
            </section>

        </template>

        <!-- SEARCH MODE -->
        <template v-else>

            <section class="max-w-7xl mx-auto px-4 py-8 grid grid-cols-12 gap-6">

                <!-- SIDEBAR -->
                <aside class="col-span-3 space-y-2">

                    <h3 class="font-semibold mb-3">
                        {{ activeCategory?.name }}
                    </h3>

                    <div v-for="child in activeCategory?.children_recursive" :key="child.id"
                        class="text-sm cursor-pointer hover:text-yellow-600">
                        {{ child.name }}
                    </div>

                </aside>

                <!-- ADS -->
                <main class="col-span-9">

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">

                        <div v-for="ad in activeCategory?.ads" :key="ad.id" class="border rounded p-3">
                            <img v-if="ad.images?.length" :src="`/storage/${ad.images[0].path}`"
                                class="h-40 w-full object-cover mb-2" />

                            <h4 class="font-medium">{{ ad.ad_title }}</h4>
                            <p class="text-sm text-muted">{{ ad.location }}</p>
                            <p class="font-bold mt-1">${{ ad.price }}</p>

                        </div>

                    </div>

                </main>

            </section>

        </template>

    </OlxLayout>
</template>

<script setup lang="ts">
import OlxLayout from '@/layouts/OlxLayout.vue'
import { usePage } from '@inertiajs/vue3';
import CategoryAds from '@/components/CategoryAds.vue'
import { ref } from 'vue'
interface PageProps extends InertiaPageProps {
    ads: PaginatedData<App.Data.AdData>;
    categories: App.Data.CategoryData[];
    brands: App.Data.BrandData[];
}
const page = usePage<PageProps>();
const categories = computed(() => page.props.categories)
const isSearching = computed(() => {
    return !!page.props.isSearching
})

const activeCategory = computed(() => {
    return categories.value[0] || null
})

console.log(page.props);

const topCategories = computed(() => {
    return categories.value
        .filter(c => c.parent_id === null)
        .slice(0, 7)
})

</script>