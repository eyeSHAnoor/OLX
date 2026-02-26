<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 flex flex-col items-center justify-center p-4">

        <!-- Logo -->
        <div class="mb-8 md:mb-12">
            <img src="/images/logo.png" alt="OLX Clone Logo" class="h-12 md:h-16 w-auto mx-auto" />
            <h1 class="mt-3 text-2xl md:text-3xl font-bold text-center text-gray-800">
                Create Your Account
            </h1>
            <p class="text-gray-600 text-center mt-2">
                Join millions of users buying and selling on OLX Clone
            </p>
        </div>

        <!-- Registration Card -->
        <div class="w-full max-w-lg">
            <!-- Registration Form -->
            <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8">
                <h2 class="text-xl font-bold text-gray-800 mb-2">Complete Your Registration</h2>
                <p class="text-gray-600 mb-6">Enter your details to create your free account</p>

                <!-- Social Registration Buttons -->
                <div class="space-y-3 mb-8">
                    <button @click="socialRegister('google')"
                        class="w-full flex items-center justify-center gap-3 bg-white border border-gray-300 text-gray-700 font-medium py-3.5 px-4 rounded-xl hover:bg-gray-50 transition-all duration-200 active:scale-[0.98]">
                        <svg class="w-5 h-5" viewBox="0 0 24 24">
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
                        class="w-full flex items-center justify-center gap-3 bg-blue-600 text-white font-medium py-3.5 px-4 rounded-xl hover:bg-blue-700 transition-all duration-200 active:scale-[0.98]">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                            <path
                                d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                        </svg>
                        Continue with Facebook
                    </button>
                </div>

                <!-- Divider -->
                <div class="flex items-center my-8">
                    <div class="flex-1 border-t border-gray-200"></div>
                    <span class="px-4 text-gray-500 text-sm">Or register with email</span>
                    <div class="flex-1 border-t border-gray-200"></div>
                </div>

                <!-- Registration Form -->
                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Full Name
                        </label>
                        <div class="relative">
                            <Input id="name" type="text" required autofocus :tabindex="1" autocomplete="name"
                                v-model="form.name" placeholder="Enter your full name"
                                class="pl-10 w-full border-gray-300 rounded-xl focus:border-brand-blue focus:ring-2 focus:ring-brand-blue" />
                        </div>
                        <InputError :message="form.errors.name" />
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Email Address
                        </label>
                        <div class="relative">
                            <Input id="email" type="email" required :tabindex="2" autocomplete="email"
                                v-model="form.email" placeholder="you@example.com"
                                class="pl-10 w-full border-gray-300 rounded-xl focus:border-brand-blue focus:ring-2 focus:ring-brand-blue" />
                        </div>
                        <InputError :message="form.errors.email" />
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Phone Number
                        </label>
                        <div class="relative">
                            <Input id="phone" type="tel" required :tabindex="3" autocomplete="tel" v-model="form.phone"
                                placeholder="+92 300 1234567"
                                class="pl-10 w-full border-gray-300 rounded-xl focus:border-brand-blue focus:ring-2 focus:ring-brand-blue" />
                        </div>
                        <InputError :message="form.errors.phone" />
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Password
                        </label>
                        <div class="relative">
                            <Input id="password" :type="showPassword ? 'text' : 'password'" required :tabindex="4"
                                autocomplete="new-password" v-model="form.password"
                                placeholder="Create a strong password"
                                class="pl-10 pr-10 w-full border-gray-300 rounded-xl focus:border-brand-blue focus:ring-2 focus:ring-brand-blue" />
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path v-if="showPassword" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        <div class="mt-2">
                            <div class="flex items-center space-x-2 text-xs text-gray-500">
                                <div :class="[
                                    'h-1 flex-1 rounded-full',
                                    form.password.length >= 8 ? 'bg-green-500' : 'bg-gray-200'
                                ]"></div>
                                <div :class="[
                                    'h-1 flex-1 rounded-full',
                                    /[A-Z]/.test(form.password) && /[a-z]/.test(form.password) ? 'bg-green-500' : 'bg-gray-200'
                                ]"></div>
                                <div :class="[
                                    'h-1 flex-1 rounded-full',
                                    /\d/.test(form.password) ? 'bg-green-500' : 'bg-gray-200'
                                ]"></div>
                                <div :class="[
                                    'h-1 flex-1 rounded-full',
                                    /[!@#$%^&*]/.test(form.password) ? 'bg-green-500' : 'bg-gray-200'
                                ]"></div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                Use 8+ characters with a mix of letters, numbers & symbols
                            </p>
                        </div>
                        <InputError :message="form.errors.password" />
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Confirm Password
                        </label>
                        <div class="relative">
                            <Input id="password_confirmation" :type="showConfirmPassword ? 'text' : 'password'" required
                                :tabindex="5" autocomplete="new-password" v-model="form.password_confirmation"
                                placeholder="Confirm your password"
                                class="pl-10 pr-10 w-full border-gray-300 rounded-xl focus:border-brand-blue focus:ring-2 focus:ring-brand-blue" />
                            <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path v-if="showConfirmPassword" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        <InputError :message="form.errors.password_confirmation" />
                    </div>

                    <!-- Terms & Conditions -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="terms" type="checkbox" v-model="form.terms" required
                                class="h-4 w-4 text-brand-blue focus:ring-brand-blue border-gray-300 rounded" />
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="terms" class="text-gray-600">
                                I agree to the
                                <a href="#" class="font-medium text-brand-blue hover:text-brand-teal">Terms of
                                    Service</a>
                                and
                                <a href="#" class="font-medium text-brand-blue hover:text-brand-teal">Privacy
                                    Policy</a>
                            </label>
                        </div>
                    </div>

                    <!-- Register Button -->
                    <Button type="submit" :disabled="form.processing" :tabindex="6"
                        class="w-full bg-brand-blue text-white font-semibold py-3.5 px-4 rounded-xl hover:bg-brand-teal focus:outline-none focus:ring-2 focus:ring-brand-blue focus:ring-offset-2 transition-all duration-200 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed">
                        <div class="flex items-center justify-center">
                            <LoaderCircle v-if="form.processing" class="animate-spin h-5 w-5 mr-2" />
                            {{ form.processing ? 'Processing...' : 'Register' }}
                        </div>
                    </Button>
                </form>

                <!-- Login Link -->
                <div class="mt-8 pt-6 border-t border-gray-100">
                    <p class="text-center text-gray-600">
                        Already have an account?
                        <TextLink :href="route('amo.login')"
                            class="font-semibold text-brand-blue hover:text-brand-teal transition-colors ml-1"
                            :tabindex="7">
                            Sign in
                        </TextLink>
                    </p>
                </div>
            </div>
        </div>

        <!-- App Download Links -->
        <div class="mt-8 text-center">
            <p class="text-sm text-gray-600 mb-4">Get the app</p>
            <div class="flex justify-center space-x-3">
                <a href="#" class="inline-block">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg"
                        alt="Google Play" class="h-10">
                </a>
                <a href="#" class="inline-block">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/3/3c/Download_on_the_App_Store_Badge.svg"
                        alt="App Store" class="h-10">
                </a>
            </div>
        </div>

        <!-- Footer Links -->
        <div class="mt-8 md:mt-12 text-center">
            <div class="flex flex-wrap justify-center gap-4 md:gap-6 text-sm text-gray-500">
                <a href="#" class="hover:text-gray-700 transition-colors">Help</a>
                <a href="#" class="hover:text-gray-700 transition-colors">Privacy</a>
                <a href="#" class="hover:text-gray-700 transition-colors">Terms</a>
                <a href="#" class="hover:text-gray-700 transition-colors">Blog</a>
                <a href="#" class="hover:text-gray-700 transition-colors">Careers</a>
                <a href="#" class="hover:text-gray-700 transition-colors">About</a>
            </div>
            <p class="mt-4 text-sm text-gray-500">
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