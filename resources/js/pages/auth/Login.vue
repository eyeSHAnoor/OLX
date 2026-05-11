<template>
    <div
        class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 flex flex-col items-center justify-center p-3 sm:p-4">
        <!-- Logo -->
        <div class="mb-6 md:mb-8">
            <img src="/images/logo.png" alt="AMO Mercatus " class="h-10 md:h-14 w-auto mx-auto" />
            <h1 class="mt-2 text-xl md:text-2xl font-semibold text-center text-gray-800">
                Welcome Back
            </h1>
            <p class="text-gray-600 text-center mt-1 text-xs md:text-sm">
                Sign in to continue to your account
            </p>
        </div>

        <!-- Login Card -->
        <div class="w-full max-w-md">
            <div class="bg-white rounded-xl shadow-md p-5 md:p-6">
                <!-- Social Login Buttons -->
                <div class="space-y-2 mb-5">
                    <button @click="socialLogin('google')"
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

                    <button @click="socialLogin('facebook')"
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
                    <span class="px-3 text-gray-500 text-[10px]">Or continue with email</span>
                    <div class="flex-1 border-t border-gray-200"></div>
                </div>

                <!-- Login Form -->
                <form @submit.prevent="handleLogin" class="space-y-4">
                    <div>
                        <label for="email" class="block text-xs font-medium text-gray-700 mb-1">
                            Email Address
                        </label>
                        <div class="relative">
                            <input id="email" v-model="form.email" type="email" required placeholder="you@example.com"
                                class="pl-8 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-brand-teal focus:border-brand-teal outline-none transition-all duration-200 text-gray-800 placeholder-gray-400 text-xs"
                                :class="{ 'border-red-500': errors.email }" />
                        </div>
                        <p v-if="errors.email" class="mt-1 text-[10px] text-red-600">
                            {{ errors.email }}
                        </p>
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-medium text-gray-700 mb-1">
                            Password
                        </label>
                        <div class="relative">
                            <input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'"
                                required placeholder="••••••••"
                                class="pl-8 pr-8 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-brand-teal focus:border-brand-teal outline-none transition-all duration-200 text-gray-800 placeholder-gray-400 text-xs"
                                :class="{ 'border-red-500': errors.password }" />
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-2 flex items-center">
                                <svg class="h-3.5 w-3.5 text-gray-400 hover:text-gray-600" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path v-if="showPassword" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="errors.password" class="mt-1 text-[10px] text-red-600">
                            {{ errors.password }}
                        </p>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" v-model="form.remember"
                                class="h-3.5 w-3.5 text-brand-teal focus:ring-brand-teal border-gray-300 rounded" />
                            <span class="ml-1.5 text-xs text-gray-600">Remember me</span>
                        </label>

                        <!-- <Link :href="route('password.request')"
                            class="text-xs font-medium text-brand-teal hover:text-brand-teal/80 transition-colors">
                            Forgot password?
                        </Link> -->
                    </div>

                    <!-- Login Button -->
                    <button type="submit" :disabled="processing"
                        class="w-full bg-brand-blue text-white font-medium py-2.5 px-3 rounded-lg hover:bg-brand-blue/90 focus:outline-none focus:ring-1 focus:ring-brand-blue focus:ring-offset-1 transition-all duration-200 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed text-xs">
                        <div class="flex items-center justify-center">
                            {{ processing ? "Signing in..." : "Sign in to your account" }}
                        </div>
                    </button>
                </form>

                <!-- Sign Up Link -->
                <div class="mt-4 pt-3 border-t border-gray-100">
                    <p class="text-center text-gray-600 text-xs">
                        Don't have an account?
                        <Link :href="route('register')"
                            class="font-medium text-brand-teal hover:text-brand-teal/80 transition-colors ml-1 text-xs">
                            Create account
                        </Link>
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer Links -->
        <div class="mt-6 md:mt-8 text-center">
            <div class="flex flex-wrap justify-center gap-3 text-[10px] text-gray-500">
                <a href="/page/contact" class="hover:text-gray-700 transition-colors">Help</a>
                <a :href="route('policy.show', 'privacy')" class="hover:text-gray-700 transition-colors">Privacy</a>
                <a :href="route('policy.show', 'terms')" class="hover:text-gray-700 transition-colors">Terms</a>
                <a href="/page/about" class="hover:text-gray-700 transition-colors">About</a>
            </div>
            <p class="mt-2 text-[10px] text-gray-500">
                © {{ new Date().getFullYear() }} AMO Mercatus. All rights reserved.
            </p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive } from "vue";
import { router, Link } from "@inertiajs/vue3";

useForceTheme("light");

interface LoginForm {
    email: string;
    password: string;
    remember: boolean;
}

const form = reactive<LoginForm>({
    email: "",
    password: "",
    remember: false,
});

const errors = reactive({
    email: "",
    password: "",
});

const processing = ref(false);
const showPassword = ref(false);

const handleLogin = () => {
    processing.value = true;
    errors.email = "";
    errors.password = "";

    // Basic validation
    if (!form.email.includes("@")) {
        errors.email = "Please enter a valid email address";
        processing.value = false;
        return;
    }

    if (form.password.length < 6) {
        errors.password = "Password must be at least 6 characters";
        processing.value = false;
        return;
    }

    // Simulate API call
    setTimeout(() => {
        router.post("/login", form, {
            onSuccess: () => {
                //console.log('Login successful')
            },
            onError: (err) => {
                if (err.email) errors.email = err.email;
                if (err.password) errors.password = err.password;
            },
            onFinish: () => {
                processing.value = false;
            },
        });
    }, 1500);
};

const socialLogin = (provider: string) => {
    //console.log(`Logging in with ${provider}`)
    window.location.href = `/auth/${provider}`;
};
</script>

<style scoped>
/* Smooth transitions */
* {
    transition: all 0.2s ease-in-out;
}

/* Custom focus styles */
input:focus {
    box-shadow: 0 0 0 2px rgba(var(--brand-teal-rgb), 0.1);
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

/* Custom scrollbar for form */
::-webkit-scrollbar {
    width: 4px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}

::-webkit-scrollbar-thumb {
    background: var(--brand-teal);
    border-radius: 2px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--brand-blue);
}
</style>
