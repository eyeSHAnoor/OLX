<script setup>
import { Link, useForm } from "@inertiajs/vue3";
import MobileBottomNav from "@/components/MobileBottomNav.vue";
import { ref, computed } from "vue";
import { Icon } from "@iconify/vue";

const isMenuOpen = ref(false);
const isContactOpen = ref(false);

const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value;
};

const closeMenu = () => {
    isMenuOpen.value = false;
};

// ==========================================
// UPDATED CONTACT FORM WITH SECURITY
// ==========================================
const form = useForm({
    name: "", // NEW: Name field
    email: "", // NEW: Email field
    subject: "",
    message: "",
    website: "", // Honeypot field
});

// Submit handler
const submitContact = () => {
    form.post("/contact/send", {
        preserveScroll: true,
        onSuccess: () => {
            // Reset form on success
            form.reset();

            // Close modal after 3 seconds
            setTimeout(() => {
                isContactOpen.value = false;
            }, 3000);
        },
        onError: () => {
            // Keep form open on error
        },
    });
};

// Character counter for message
const messageLength = computed(() => form.message.length);
const isMessageValid = computed(
    () => messageLength.value >= 20 && messageLength.value <= 5000
);

// Form validation
const isFormValid = computed(() => {
    return (
        form.name.length >= 2 &&
        form.email.length > 0 &&
        form.subject.length >= 5 &&
        form.message.length >= 20 &&
        !form.processing
    );
});

// Email validation helper
const isValidEmail = (email) => {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col relative">
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

        <!-- ====================== -->
        <!-- FLOATING CHAT BUTTON  -->
        <!-- ====================== -->
        <button @click="isContactOpen = true"
            class="fixed bottom-20 md:bottom-6 right-4 md:right-6 z-40 w-14 h-14 bg-brand-teal text-white rounded-full shadow-lg flex items-center justify-center hover:bg-[#a5d4d0] active:scale-95 transition-transform focus:outline-none focus:ring-2 focus:ring-primary/50"
            aria-label="Contact us">
            <Icon icon="mdi:email-outline" class="text-2xl" />
        </button>

        <!-- ====================== -->
        <!-- CONTACT MODAL          -->
        <!-- ====================== -->
        <transition enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 translate-y-4 scale-95" enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-4 scale-95">
            <div v-if="isContactOpen"
                class="fixed inset-0 z-50 flex items-end md:items-center justify-center p-0 md:p-4"
                @click.self="isContactOpen = false">
                <!-- Overlay -->
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="isContactOpen = false"></div>

                <!-- Modal content (bottom sheet on mobile, centered on desktop) -->
                <div
                    class="relative w-full md:max-w-md bg-white rounded-t-2xl md:rounded-2xl shadow-xl overflow-hidden max-h-[90vh] overflow-y-auto">
                    <!-- Close button -->
                    <button @click="isContactOpen = false"
                        class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 z-10">
                        <Icon icon="mdi:close" class="text-xl" />
                    </button>

                    <!-- Header -->
                    <div class="bg-brand-teal px-6 py-4">
                        <h2 class="text-lg font-semibold text-white">Send us a message</h2>
                        <p class="text-sm text-white/80">We'll get back to you soon.</p>
                    </div>

                    <!-- Form -->
                    <div class="p-6">
                        <!-- Success message -->
                        <div v-if="form.recentlySuccessful"
                            class="mb-4 p-4 bg-green-50 text-green-700 rounded-xl text-sm">
                            <Icon icon="mdi:check-circle-outline" class="inline mr-1" />
                            Thank you! Your message has been sent.
                        </div>

                        <!-- Error message from server -->
                        <div v-if="$page.props.flash?.error" class="mb-4 p-4 bg-red-50 text-red-700 rounded-xl text-sm">
                            <Icon icon="mdi:alert-circle-outline" class="inline mr-1" />
                            {{ $page.props.flash.error }}
                        </div>

                        <form @submit.prevent="submitContact" class="space-y-4">
                            <!-- ========================================== -->
                            <!-- NAME FIELD                              -->
                            <!-- ========================================== -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Your Name <span class="text-red-500">*</span>
                                </label>
                                <input id="name" v-model="form.name" type="text" required minlength="2" maxlength="100"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#bbdedd] focus:ring focus:ring-[#bbdedd]/20 outline-none"
                                    placeholder="John Doe" :disabled="form.processing" />
                                <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <!-- ========================================== -->
                            <!-- EMAIL FIELD                             -->
                            <!-- ========================================== -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                    Your Email <span class="text-red-500">*</span>
                                </label>
                                <input id="email" v-model="form.email" type="email" required maxlength="255"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#bbdedd] focus:ring focus:ring-[#bbdedd]/20 outline-none"
                                    placeholder="you@example.com" :disabled="form.processing" />
                                <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">
                                    {{ form.errors.email }}
                                </p>
                            </div>

                            <!-- ========================================== -->
                            <!-- SUBJECT FIELD                            -->
                            <!-- ========================================== -->
                            <div>
                                <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">
                                    Subject <span class="text-red-500">*</span>
                                </label>
                                <input id="subject" v-model="form.subject" type="text" required minlength="5"
                                    maxlength="255"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#bbdedd] focus:ring focus:ring-[#bbdedd]/20 outline-none"
                                    placeholder="What's this about?" :disabled="form.processing" />
                                <p v-if="form.errors.subject" class="mt-1 text-xs text-red-600">
                                    {{ form.errors.subject }}
                                </p>
                            </div>

                            <!-- ========================================== -->
                            <!-- MESSAGE FIELD                            -->
                            <!-- ========================================== -->
                            <div>
                                <label for="message" class="block text-sm font-medium text-gray-700 mb-1">
                                    Message <span class="text-red-500">*</span>
                                </label>
                                <textarea id="message" v-model="form.message" rows="4" required minlength="20"
                                    maxlength="5000"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#bbdedd] focus:ring focus:ring-[#bbdedd]/20 outline-none resize-none"
                                    placeholder="How can we help you? (Minimum 20 characters)"
                                    :disabled="form.processing"></textarea>
                                <div class="flex justify-between mt-1">
                                    <p v-if="form.errors.message" class="text-xs text-red-600">
                                        {{ form.errors.message }}
                                    </p>
                                    <span class="text-xs text-gray-500 ml-auto">
                                        {{ messageLength }} / 5000 characters
                                        <span v-if="!isMessageValid && messageLength > 0" class="text-red-500">
                                            (minimum 20)
                                        </span>
                                    </span>
                                </div>
                            </div>

                            <!-- ========================================== -->
                            <!-- HONEYPOT - Hidden field for bots          -->
                            <!-- ========================================== -->
                            <input v-model="form.website" type="text" name="website" autocomplete="off" tabindex="-1"
                                style="display: none; position: absolute; left: -9999px" />

                            <!-- ========================================== -->
                            <!-- SUBMIT BUTTON                            -->
                            <!-- ========================================== -->
                            <button type="submit"
                                class="w-full py-3 bg-brand-teal text-white font-medium rounded-xl hover:bg-[#a5d4d0] transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                                :disabled="!isFormValid">
                                <span v-if="form.processing">
                                    <Icon icon="mdi:loading" class="animate-spin text-lg" />
                                </span>
                                <span v-else>
                                    <Icon icon="mdi:send" class="text-lg" />
                                </span>
                                {{ form.processing ? "Sending..." : "Send Message" }}
                            </button>

                            <!-- ========================================== -->
                            <!-- FORM STATUS INDICATOR                    -->
                            <!-- ========================================== -->
                            <div class="text-xs text-gray-500 text-center mt-2">
                                <span v-if="isFormValid" class="text-green-600">✅ Ready to send</span>
                                <span v-else>
                                    ⏳ Please fill all fields correctly
                                    <span v-if="form.message.length > 0 && form.message.length < 20"
                                        class="text-red-500 block">
                                        Message needs at least 20 characters ({{ form.message.length }}/20)
                                    </span>
                                    <span v-if="form.name.length > 0 && form.name.length < 2"
                                        class="text-red-500 block">
                                        Name needs at least 2 characters
                                    </span>
                                    <span v-if="form.email.length > 0 && !isValidEmail(form.email)"
                                        class="text-red-500 block">
                                        Please enter a valid email address
                                    </span>
                                </span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>
