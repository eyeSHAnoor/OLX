<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="sticky top-0 z-50 bg-white ">
            <Header />
        </header>
        <TopCategoriesBar />
        <!-- Main Content -->
        <main class="pb-8">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white">
            <Footer />
        </footer>
    </div>
</template>

<script setup>
import Header from '../components/Header.vue'
import Footer from '../components/Footer.vue'
import TopCategoriesBar from '../components/TopCategoriesBar.vue';
</script>