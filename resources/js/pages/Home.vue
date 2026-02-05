<template>
    <OlxLayout>
        <!-- Hero Banner -->
        <div class="bg-gradient-to-r from-blue-50 to-gray-50 py-8">
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Categories Sidebar -->
                    <div class="lg:col-span-1 bg-white rounded-lg shadow p-6 h-fit">
                        <h2 class="text-xl font-bold mb-6 text-gray-800">All Categories</h2>
                        <div class="space-y-4">
                            <div v-for="category in categories" :key="category.id" class="category-item">
                                <a href="#" class="flex items-center justify-between p-2 hover:bg-blue-50 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="p-2 bg-blue-100 rounded">
                                            <component :is="category.icon" class="w-5 h-5 text-blue-600" />
                                        </div>
                                        <span class="text-gray-700">{{ category.name }}</span>
                                    </div>
                                    <span class="text-gray-400">{{ category.count }}</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Featured Listings -->
                    <div class="lg:col-span-2">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-gray-800">Fresh recommendations</h2>
                            <a href="#" class="text-blue-600 hover:text-blue-800">View all</a>
                        </div>

                        <!-- Product Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-for="item in featuredItems" :key="item.id"
                                class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition-shadow duration-300">
                                <div class="relative">
                                    <img :src="item.image" :alt="item.title" class="w-full h-48 object-cover">
                                    <button class="absolute top-2 right-2 p-1 bg-white rounded-full shadow">
                                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="p-4">
                                    <h3 class="font-semibold text-gray-800 truncate">{{ item.title }}</h3>
                                    <p class="text-lg font-bold text-gray-900 mt-2">Rs {{ item.price.toLocaleString() }}
                                    </p>
                                    <div class="flex items-center justify-between mt-4">
                                        <div class="flex items-center text-gray-500 text-sm">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <span>{{ item.location }}</span>
                                        </div>
                                        <span class="text-gray-500 text-sm">{{ item.date }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Featured Categories -->
        <div class="py-12 bg-white">
            <div class="container mx-auto px-4">
                <h2 class="text-2xl font-bold mb-8 text-center text-gray-800">Featured Categories</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <a v-for="feature in featuredCategories" :key="feature.id" href="#"
                        class="flex flex-col items-center p-4 border rounded-lg hover:shadow-md transition-shadow">
                        <div class="p-3 bg-blue-100 rounded-full mb-3">
                            <component :is="feature.icon" class="w-8 h-8 text-blue-600" />
                        </div>
                        <span class="text-sm font-medium text-center">{{ feature.name }}</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Cities Section -->
        <div class="py-12 bg-gray-50">
            <div class="container mx-auto px-4">
                <h2 class="text-2xl font-bold mb-8 text-center text-gray-800">Popular Cities</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <a v-for="city in popularCities" :key="city" href="#"
                        class="p-4 bg-white rounded-lg shadow hover:shadow-md transition-shadow text-center">
                        <span class="font-medium text-gray-800">{{ city }}</span>
                    </a>
                </div>
            </div>
        </div>
    </OlxLayout>
</template>

<script setup lang="ts">
import OlxLayout from '@/layouts/OlxLayout.vue'
import { usePage } from '@inertiajs/vue3';
import { ref } from 'vue'
interface PageProps extends InertiaPageProps {
    ads: PaginatedData<App.Data.AdData>;
    categories: App.Data.CategoryData[];
    brands: App.Data.BrandData[];
}
const page = usePage<PageProps>();
console.log(page.props);

// Define icons inline
const icons = {
    Cars: {
        template: `<svg fill="currentColor" viewBox="0 0 20 20">
      <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
      <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1v-1h4v1a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H20a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7h4v3h-4V7z"/>
    </svg>`
    },
    Properties: {
        template: `<svg fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M5 2a2 2 0 00-2 2v14l3.5-2 3.5 2 3.5-2 3.5 2V4a2 2 0 00-2-2H5z" clip-rule="evenodd"/>
    </svg>`
    },
    Mobile: {
        template: `<svg fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M7 2a2 2 0 00-2 2v12a2 2 0 002 2h6a2 2 0 002-2V4a2 2 0 00-2-2H7zm3 14a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
    </svg>`
    },
    Electronics: {
        template: `<svg fill="currentColor" viewBox="0 0 20 20">
      <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
      <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
    </svg>`
    },
    Bikes: {
        template: `<svg fill="currentColor" viewBox="0 0 20 20">
      <circle cx="5.5" cy="15.5" r="1.5"/>
      <circle cx="14.5" cy="15.5" r="1.5"/>
      <path fill-rule="evenodd" d="M13.257 11H10V9h3.457a1 1 0 00.962-.726l1.2-4.0A1 1 0 0014.657 3H12v2h2.057l-.8 2.674L13.257 11zM5.829 9H8V11H4.886a1 1 0 00-.962 1.276l1.2 4A1 1 0 006.543 17h2.257v-2H6.943l.8-2.674L5.829 9z" clip-rule="evenodd"/>
    </svg>`
    }
}

const categories = ref([
    { id: 1, name: 'Cars', count: '12,345', icon: icons.Cars },
    { id: 2, name: 'Properties', count: '8,901', icon: icons.Properties },
    { id: 3, name: 'Mobile Phones', count: '23,456', icon: icons.Mobile },
    { id: 4, name: 'Electronics & Appliances', count: '18,765', icon: icons.Electronics },
    { id: 5, name: 'Bikes', count: '15,432', icon: icons.Bikes },
    { id: 6, name: 'Furniture', count: '9,876', icon: icons.Properties },
    { id: 7, name: 'Jobs', count: '5,678', icon: icons.Cars },
    { id: 8, name: 'Services', count: '3,210', icon: icons.Electronics },
])

const featuredItems = ref([
    { id: 1, title: 'iPhone 14 Pro Max 256GB', price: 235000, location: 'Lahore', date: 'Today', image: 'https://images.unsplash.com/photo-1663499482523-1c0c1eae63ed?w=400&h=300&fit=crop' },
    { id: 2, title: 'Honda Civic 2020 Model', price: 4500000, location: 'Karachi', date: '2 days ago', image: 'https://images.unsplash.com/photo-1553440569-bcc63803a83d?w=400&h=300&fit=crop' },
    { id: 3, title: '3 Bedroom Apartment DHA', price: 85000000, location: 'Islamabad', date: '1 week ago', image: 'https://images.unsplash.com/photo-1518780664697-55e3ad937233?w-400&h=300&fit=crop' },
    { id: 4, title: 'MacBook Pro M2 2023', price: 345000, location: 'Rawalpindi', date: 'Yesterday', image: 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=400&h=300&fit=crop' },
    { id: 5, title: 'Yamaha YBR 125 2021', price: 245000, location: 'Faisalabad', date: '3 days ago', image: 'https://images.unsplash.com/photo-1558981403-c5f9899a28bc?w=400&h=300&fit=crop' },
    { id: 6, title: 'Samsung LED TV 55"', price: 85000, location: 'Multan', date: 'Today', image: 'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?w=400&h=300&fit=crop' },
])

const featuredCategories = ref([
    { id: 1, name: 'Cars', icon: icons.Cars },
    { id: 2, name: 'Properties', icon: icons.Properties },
    { id: 3, name: 'Mobiles', icon: icons.Mobile },
    { id: 4, name: 'Electronics', icon: icons.Electronics },
    { id: 5, name: 'Bikes', icon: icons.Bikes },
    { id: 6, name: 'Furniture', icon: icons.Properties },
])

const popularCities = ref([
    'Lahore', 'Karachi', 'Islamabad', 'Rawalpindi', 'Faisalabad', 'Multan',
    'Gujranwala', 'Peshawar', 'Quetta', 'Sialkot', 'Sargodha', 'Bahawalpur'
])
</script>