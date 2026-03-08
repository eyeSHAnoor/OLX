<!-- resources/js/Pages/Auth/ChangePassword.vue -->

<script setup>
import { ref } from 'vue'
import { useForm, Head } from '@inertiajs/vue3'
import OlxLayout from '@/layouts/OlxLayout.vue'

const props = defineProps({
    errors: Object,
    flash: Object
})

const form = useForm({
    current_password: '',
    new_password: '',
    new_password_confirmation: ''
})
useForceTheme('light');
const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

const submit = () => {
    form.post(route('password.change'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('current_password', 'new_password', 'new_password_confirmation')
        }
    })
}

const passwordStrength = computed(() => {
    const password = form.new_password
    if (!password) return { score: 0, label: 'Enter password', color: '#666' }

    let score = 0
    if (password.length >= 8) score++
    if (password.match(/[a-z]/)) score++
    if (password.match(/[A-Z]/)) score++
    if (password.match(/[0-9]/)) score++
    if (password.match(/[^a-zA-Z0-9]/)) score++

    const strengths = ['Weak', 'Fair', 'Good', 'Strong', 'Very Strong']
    const colors = ['#dc3545', '#ffc107', '#4A90E2', '#2C6B5E', '#2C6B5E']

    return {
        score: score,
        label: strengths[score - 1] || 'Weak',
        color: colors[score - 1] || '#dc3545'
    }
})
</script>

<template>
    <OlxLayout>

        <Head title="Change Password" />

        <div class="py-12">
            <div class="max-w-8/10 mx-auto  space-y-12 ">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <!-- Header -->
                    <div class="bg-brand-teal/10 px-6 py-4">
                        <h2 class="text-xl font-semibold ">Change Password</h2>
                    </div>

                    <!-- Body -->
                    <div class="p-6">
                        <!-- Flash Messages -->
                        <div v-if="$page.props.flash.success"
                            class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                            {{ $page.props.flash.success }}
                        </div>

                        <div v-if="$page.props.flash.error"
                            class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            {{ $page.props.flash.error }}
                        </div>

                        <form @submit.prevent="submit">
                            <!-- Current Password -->
                            <div class="mb-4">
                                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">
                                    Current Password
                                </label>
                                <div class="relative">
                                    <input :type="showCurrentPassword ? 'text' : 'password'" id="current_password"
                                        v-model="form.current_password"
                                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-[#2C6B5E] focus:ring-1 focus:ring-[#2C6B5E]"
                                        :class="{ 'border-red-500': form.errors.current_password }" />
                                    <button type="button" @click="showCurrentPassword = !showCurrentPassword"
                                        class="absolute right-3 top-2.5 text-gray-500 hover:text-[#2C6B5E]">
                                        <svg v-if="!showCurrentPassword" xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                        </svg>
                                    </button>
                                </div>
                                <p v-if="form.errors.current_password" class="mt-1 text-sm text-red-500">
                                    {{ form.errors.current_password }}
                                </p>
                            </div>

                            <!-- New Password -->
                            <div class="mb-4">
                                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">
                                    New Password
                                </label>
                                <div class="relative">
                                    <input :type="showNewPassword ? 'text' : 'password'" id="new_password"
                                        v-model="form.new_password"
                                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-[#2C6B5E] focus:ring-1 focus:ring-[#2C6B5E]"
                                        :class="{ 'border-red-500': form.errors.new_password }" />
                                    <button type="button" @click="showNewPassword = !showNewPassword"
                                        class="absolute right-3 top-2.5 text-gray-500 hover:text-[#2C6B5E]">
                                        <svg v-if="!showNewPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Password Strength Meter -->
                                <div class="mt-2">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                            <div class="h-full transition-all duration-300" :style="{
                                                width: (passwordStrength.score * 20) + '%',
                                                backgroundColor: passwordStrength.color
                                            }"></div>
                                        </div>
                                        <span class="text-xs" :style="{ color: passwordStrength.color }">
                                            {{ passwordStrength.label }}
                                        </span>
                                    </div>
                                </div>

                                <p class="mt-1 text-xs text-gray-500">Minimum 8 characters</p>
                                <p v-if="form.errors.new_password" class="mt-1 text-sm text-red-500">
                                    {{ form.errors.new_password }}
                                </p>
                            </div>

                            <!-- Confirm New Password -->
                            <div class="mb-6">
                                <label for="new_password_confirmation"
                                    class="block text-sm font-medium text-gray-700 mb-1">
                                    Confirm New Password
                                </label>
                                <div class="relative">
                                    <input :type="showConfirmPassword ? 'text' : 'password'"
                                        id="new_password_confirmation" v-model="form.new_password_confirmation"
                                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-[#2C6B5E] focus:ring-1 focus:ring-[#2C6B5E]"
                                        :class="{
                                            'border-red-500': form.errors.new_password_confirmation,
                                            'border-green-500': form.new_password_confirmation && form.new_password === form.new_password_confirmation && form.new_password.length >= 8
                                        }" />
                                    <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                                        class="absolute right-3 top-2.5 text-gray-500 hover:text-[#2C6B5E]">
                                        <svg v-if="!showConfirmPassword" xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Match Indicator -->
                                <div v-if="form.new_password_confirmation" class="mt-1 flex items-center gap-1">
                                    <svg v-if="form.new_password === form.new_password_confirmation && form.new_password.length >= 8"
                                        xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#2C6B5E]"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-500"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-xs" :class="{
                                        'text-[#2C6B5E]': form.new_password === form.new_password_confirmation && form.new_password.length >= 8,
                                        'text-red-500': form.new_password !== form.new_password_confirmation
                                    }">
                                        {{ form.new_password === form.new_password_confirmation &&
                                            form.new_password.length >= 8 ? 'Passwords match' : 'Passwords do not match' }}
                                    </span>
                                </div>

                                <p v-if="form.errors.new_password_confirmation" class="mt-1 text-sm text-red-500">
                                    {{ form.errors.new_password_confirmation }}
                                </p>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" :disabled="form.processing"
                                class="w-full bg-brand-teal text-white py-2 px-4 rounded-lg hover:bg-brand-blue/80 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-[#4A90E2] focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span v-if="form.processing">Changing Password...</span>
                                <span v-else>Change Password</span>
                            </button>
                        </form>

                        <!-- Back Link -->
                        <div class="mt-4 text-center">
                            <a :href="route('home')"
                                class="text-sm text-[#4A90E2] hover:text-[#2C6B5E] transition-colors duration-200">
                                ← Back to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </OlxLayout>
</template>

<style scoped>
/* Optional custom styles if needed */
</style>