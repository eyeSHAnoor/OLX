<template>
    <div class="w-full bg-white border-t border-b py-3">
        <div class="max-w-7xl mx-auto px-4">
            <div
                class="flex space-x-4 sm:space-x-6 md:space-x-8 lg:space-x-10 overflow-x-auto no-scrollbar justify-start md:justify-center">
                <div v-for="cat in topCategories" :key="cat.id"
                    class="flex flex-col items-center flex-shrink-0 cursor-pointer hover:bg-blue-50 rounded-lg px-3 sm:px-4 py-2 transition">
                    <!-- Category Name -->
                    <span class="text-xs sm:text-sm md:text-base font-medium text-gray-700 text-center">{{ cat.name
                        }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const topCategories = page.props.topCategories || []
</script>

<style scoped>
/* Hide horizontal scrollbar */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}

.no-scrollbar {
    -ms-overflow-style: none;
    /* IE and Edge */
    scrollbar-width: none;
    /* Firefox */
}
</style>
