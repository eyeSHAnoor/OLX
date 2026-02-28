<template>
    <div
        class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 flex flex-col items-center justify-center p-3 sm:p-4">

        <!-- Logo -->
        <div class="mb-6 md:mb-8">
            <img src="/images/logo.png" alt="OLX Clone Logo" class="h-10 md:h-14 w-auto mx-auto" />
            <h1 class="mt-2 text-xl md:text-2xl font-semibold text-center text-gray-800">
                Create Your Account
            </h1>
            <p class="text-gray-600 text-center mt-1 text-xs md:text-sm">
                Join millions of users buying and selling on OLX Clone
            </p>
        </div>

        <!-- Registration Card -->
        <div class="w-full max-w-md">
            <!-- Registration Form -->
            <div class="bg-white rounded-xl shadow-md p-5 md:p-6">
                <h2 class="text-base font-semibold text-gray-800 mb-1">Complete Your Registration</h2>
                <p class="text-gray-600 text-xs mb-4">Enter your details to create your free account</p>

                <!-- Social Registration Buttons -->
                <div class="space-y-2 mb-5">
                    <button @click="socialRegister('google')"
                        class="w-full flex items-center justify-center gap-2 bg-white border border-gray-300 text-gray-700 font-medium py-2.5 px-3 rounded-lg hover:bg-gray-50 transition-all duration-200 active:scale-[0.98] text-xs">
                        <svg class="w-4 h-4" viewBox="0 0 24 24">
                            <path fill="#4285F4"
                                d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                            <path fill="#34A853"
                                d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                            <path fill="#FBBC05"
                                d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                            <path fill="#EA4335"
                                d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                        </svg>
                        Continue with Google
                    </button>

                    <button @click="socialRegister('facebook')"
                        class="w-full flex items-center justify-center gap-2 bg-brand-blue text-white font-medium py-2.5 px-3 rounded-lg hover:bg-brand-blue/90 transition-all duration-200 active:scale-[0.98] text-xs">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                            <path
                                d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                        </svg>
                        Continue with Facebook
                    </button>
                </div>

                <!-- Divider -->
                <div class="flex items-center my-4">
                    <div class="flex-1 border-t border-gray-200"></div>
                    <span class="px-3 text-gray-500 text-[10px]">Or register with email</span>
                    <div class="flex-1 border-t border-gray-200"></div>
                </div>

                <!-- Registration Form -->
                <form @submit.prevent="submit" class="space-y-3">
                    <!-- Name -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">
                            Full Name
                        </label>
                        <div class="relative">
                            <Input id="name" type="text" required autofocus :tabindex="1" autocomplete="name"
                                v-model="form.name" placeholder="Enter your full name"
                                class="pl-8 w-full border-gray-300 rounded-lg focus:border-brand-teal focus:ring-1 focus:ring-brand-teal text-xs py-2" />
                        </div>
                        <InputError :message="form.errors.name" class="text-[10px]" />
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">
                            Email Address
                        </label>
                        <div class="relative">
                            <Input id="email" type="email" required :tabindex="2" autocomplete="email"
                                v-model="form.email" placeholder="you@example.com"
                                class="pl-8 w-full border-gray-300 rounded-lg focus:border-brand-teal focus:ring-1 focus:ring-brand-teal text-xs py-2" />
                        </div>
                        <InputError :message="form.errors.email" class="text-[10px]" />
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">
                            Phone Number
                        </label>
                        <div class="relative">
                            <Input id="phone" type="tel" required :tabindex="3" autocomplete="tel" v-model="form.phone"
                                placeholder="+92 300 1234567"
                                class="pl-8 w-full border-gray-300 rounded-lg focus:border-brand-teal focus:ring-1 focus:ring-brand-teal text-xs py-2" />
                        </div>
                        <InputError :message="form.errors.phone" class="text-[10px]" />
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">
                            Password
                        </label>
                        <div class="relative">
                            <Input id="password" :type="showPassword ? 'text' : 'password'" required :tabindex="4"
                                autocomplete="new-password" v-model="form.password"
                                placeholder="Create a strong password"
                                class="pl-8 pr-8 w-full border-gray-300 rounded-lg focus:border-brand-teal focus:ring-1 focus:ring-brand-teal text-xs py-2" />
                        </div>
                        <div class="mt-1.5">
                            <div class="flex items-center space-x-1 text-[8px] text-gray-500">
                                <div :class="[
                                    'h-0.5 flex-1 rounded-full',
                                    form.password.length >= 8 ? 'bg-green-500' : 'bg-gray-200'
                                ]"></div>
                                <div :class="[
                                    'h-0.5 flex-1 rounded-full',
                                    /[A-Z]/.test(form.password) && /[a-z]/.test(form.password) ? 'bg-green-500' : 'bg-gray-200'
                                ]"></div>
                                <div :class="[
                                    'h-0.5 flex-1 rounded-full',
                                    /\d/.test(form.password) ? 'bg-green-500' : 'bg-gray-200'
                                ]"></div>
                                <div :class="[
                                    'h-0.5 flex-1 rounded-full',
                                    /[!@#$%^&*]/.test(form.password) ? 'bg-green-500' : 'bg-gray-200'
                                ]"></div>
                            </div>
                            <p class="mt-0.5 text-sm text-gray-500">
                                Use 8+ characters with letters, numbers & symbols
                            </p>
                        </div>
                        <InputError :message="form.errors.password" class="text-[10px]" />
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">
                            Confirm Password
                        </label>
                        <div class="relative">
                            <Input id="password_confirmation" :type="showConfirmPassword ? 'text' : 'password'" required
                                :tabindex="5" autocomplete="new-password" v-model="form.password_confirmation"
                                placeholder="Confirm your password"
                                class="pl-8 pr-8 w-full border-gray-300 rounded-lg focus:border-brand-teal focus:ring-1 focus:ring-brand-teal text-xs py-2" />
                        </div>
                        <InputError :message="form.errors.password_confirmation" class="text-[10px]" />
                    </div>

                    <!-- Terms & Conditions -->
                    <div class="flex items-start mt-2">
                        <div class="flex items-center h-4">
                            <input id="terms" type="checkbox" v-model="form.terms" required
                                class="h-3.5 w-3.5 text-brand-teal focus:ring-brand-teal border-gray-300 rounded" />
                        </div>
                        <div class="ml-2 text-[10px]">
                            <label for="terms" class="text-gray-600">
                                I agree to the
                                <a href="#" class="font-medium text-brand-teal hover:text-brand-teal/80">Terms</a>
                                and
                                <a href="#" class="font-medium text-brand-teal hover:text-brand-teal/80">Privacy</a>
                            </label>
                        </div>
                    </div>

                    <!-- Register Button -->
                    <Button type="submit" :disabled="form.processing" :tabindex="6"
                        class="w-full bg-brand-blue text-white font-medium py-2.5 px-3 rounded-lg hover:bg-brand-blue/90 focus:outline-none focus:ring-1 focus:ring-brand-blue focus:ring-offset-1 transition-all duration-200 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed text-xs">
                        <div class="flex items-center justify-center">
                            <LoaderCircle v-if="form.processing" class="animate-spin h-3.5 w-3.5 mr-1.5" />
                            {{ form.processing ? 'Processing...' : 'Register' }}
                        </div>
                    </Button>
                </form>

                <!-- Login Link -->
                <div class="mt-4 pt-3 border-t border-gray-100">
                    <p class="text-center text-gray-600 text-xs">
                        Already have an account?
                        <TextLink :href="route('amo.login')"
                            class="font-medium text-brand-teal hover:text-brand-teal/80 transition-colors ml-1 text-xs">
                            Sign in
                        </TextLink>
                    </p>
                </div>
            </div>
        </div>

        <!-- App Download Links -->
        <div class="mt-6 text-center">
            <p class="text-xs text-gray-600 mb-2">Get the app</p>
            <div class="flex justify-center space-x-2">
                <a href="#" class="inline-block">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg"
                        alt="Google Play" class="h-8">
                </a>
                <a href="#" class="inline-block">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/3/3c/Download_on_the_App_Store_Badge.svg"
                        alt="App Store" class="h-8">
                </a>
            </div>
        </div>

        <!-- Footer Links -->
        <div class="mt-6 md:mt-8 text-center">
            <div class="flex flex-wrap justify-center gap-3 text-[10px] text-gray-500">
                <a href="#" class="hover:text-gray-700 transition-colors">Help</a>
                <a href="#" class="hover:text-gray-700 transition-colors">Privacy</a>
                <a href="#" class="hover:text-gray-700 transition-colors">Terms</a>
                <a href="#" class="hover:text-gray-700 transition-colors">Blog</a>
                <a href="#" class="hover:text-gray-700 transition-colors">Careers</a>
                <a href="#" class="hover:text-gray-700 transition-colors">About</a>
            </div>
            <p class="mt-2 text-[10px] text-gray-500">
                © {{ new Date().getFullYear() }} OLX Clone. All rights reserved.
            </p>
        </div>
    </div>
</template>
<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { ref } from 'vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    phone: '',
    password_confirmation: '',
    terms: false,
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const submit = () => {
    if (!form.terms) {
        alert('You must agree to the Terms of Service and Privacy Policy.');
        return;
    }

    form.post(route('register'), {
        preserveScroll: true,
        onError: (errors) => {
            console.error('Registration failed:', errors);
        },
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        }
    });
};

const socialRegister = (provider: string) => {
    console.log(`Registering with ${provider}`);
    window.location.href = `/auth/${provider}/register`;
};
</script>

<style scoped>
/* Smooth transitions */
* {
    transition: all 0.2s ease-in-out;
}

/* Custom focus styles */
input:focus {
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
}

/* Button hover effects */
button:not(:disabled):hover {
    transform: translateY(-1px);
}

/* Smooth scale animation */
@keyframes gentleScale {
    0% {
        transform: scale(0.95);
        opacity: 0;
    }

    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.bg-white {
    animation: gentleScale 0.3s ease-out;
}

/* Password strength indicator animation */
.h-1 {
    transition: background-color 0.3s ease;
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb {
    background: #fbbf24;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #f59e0b;
}
</style>