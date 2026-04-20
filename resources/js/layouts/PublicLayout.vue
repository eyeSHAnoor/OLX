<script setup>
import { Link } from '@inertiajs/vue3';
import MobileBottomNav from '@/components/MobileBottomNav.vue';
import { ref } from 'vue';

const isMenuOpen = ref(false);

const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value;
};

const closeMenu = () => {
    isMenuOpen.value = false;
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <!-- Header -->
        <header class="bg-[#bbdedd] z-50 shadow-sm sticky top-0">
            <div class="max-w-full md:max-w-10/12 mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex justify-between items-center">
                    <!-- Logo -->
                    <Link href="/" class="text-xl font-bold text-primary hover:text-primary/80 transition">
                        amomercatus
                    </Link>

                    <!-- Desktop Navigation (hidden on mobile) -->
                    <nav class="hidden md:flex space-x-6">
                        <Link href="/page/contact" class="text-gray-600 hover:text-primary transition">
                            Contact
                        </Link>
                        <Link href="/page/about" class="text-gray-600 hover:text-primary transition">
                            About
                        </Link>
                        <Link href="/page/team" class="text-gray-600 hover:text-primary transition">
                            Team
                        </Link>
                    </nav>

                    <!-- Mobile menu button -->
                    <button @click="toggleMenu"
                        class="md:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary"
                        aria-label="Toggle menu">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path v-if="!isMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Mobile Navigation (slide-down) -->
                <transition enter-active-class="transition duration-200 ease-out"
                    enter-from-class="transform -translate-y-4 opacity-0"
                    enter-to-class="transform translate-y-0 opacity-100"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="transform translate-y-0 opacity-100"
                    leave-to-class="transform -translate-y-4 opacity-0">
                    <nav v-if="isMenuOpen" class="md:hidden mt-4 pt-2 border-t bg-white border-gray-200">
                        <div class="flex flex-col space-y-3">
                            <Link href="/page/contact" @click="closeMenu"
                                class="text-gray-600 hover:text-primary py-2 px-3 rounded-lg hover:bg-gray-100 transition">
                                Contact
                            </Link>
                            <Link href="/page/about" @click="closeMenu"
                                class="text-gray-600 hover:text-primary py-2 px-3 rounded-lg hover:bg-gray-100 transition">
                                About
                            </Link>
                            <Link href="/page/team" @click="closeMenu"
                                class="text-gray-600 hover:text-primary py-2 px-3 rounded-lg hover:bg-gray-100 transition">
                                Team
                            </Link>
                            <Link href="/policy/terms" @click="closeMenu"
                                class="text-gray-600 hover:text-primary py-2 px-3 rounded-lg hover:bg-gray-100 transition">
                                Terms of Service
                            </Link>
                        </div>
                    </nav>
                </transition>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 pb-16 md:pb-8">
            <slot />
        </main>

        <MobileBottomNav />

        <!-- Footer -->
        <footer class="bg-gray-100 border-t py-6 text-center text-sm text-gray-500">
            &copy; {{ new Date().getFullYear() }} amomercatus. All rights reserved.
        </footer>
    </div>
</template>