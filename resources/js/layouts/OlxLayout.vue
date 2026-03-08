<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <!-- Header -->
        <header class="sticky top-0 z-50 bg-white">
            <Header />
        </header>

        <!-- Main Content - Add padding bottom for mobile to account for sticky footer -->
        <main class="flex-1 pb-16 md:pb-8">
            <ValidationErrors />
            <slot />
        </main>

        <!-- Footer - Hidden on mobile, shown on desktop -->
        <footer class="hidden md:block bg-gray-900 text-white">
            <Footer />
        </footer>

        <!-- Mobile Bottom Navigation - Sticky Footer -->
        <MobileBottomNav />
    </div>
</template>

<script setup>
import Header from '../components/Header.vue'
import Footer from '../components/Footer.vue'
import TopCategoriesBar from '../components/TopCategoriesBar.vue'
import MobileBottomNav from '../components/MobileBottomNav.vue'
import ValidationErrors from '@/components/ValidationErrors.vue';
</script>