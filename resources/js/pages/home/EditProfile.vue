<template>
    <OlxLayout>
        <div class="max-w-9/11 mx-auto px-3 sm:px-4 py-4 md:py-6">
            <!-- Header with back button -->
            <div class="flex items-center gap-3 mb-4">
                <Link :href="route('user.profile', { id: user.id })"
                    class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <h1 class="text-xl md:text-2xl font-semibold text-gray-900">Edit Profile</h1>
            </div>

            <!-- Edit Profile Form -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <form @submit.prevent="submitForm" class="p-4 md:p-6">
                    <!-- Cover Photo Section -->
                    <div class="mb-6">
                        <label class="block text-xs font-medium text-gray-700 mb-2">Cover Photo</label>
                        <div class="relative h-32 bg-gray-100 rounded-lg overflow-hidden group">
                            <img v-if="form.cover_image_preview || userProfile.cover_image"
                                :src="form.cover_image_preview || userProfile.cover_image"
                                class="w-full h-full object-cover" />
                            <div v-else class="w-full h-full bg-gradient-to-r from-brand-blue/20 to-brand-teal/20">
                            </div>

                            <!-- Overlay with change button -->
                            <div
                                class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <label
                                    class="cursor-pointer bg-white hover:bg-gray-50 text-gray-700 px-3 py-1.5 rounded-lg shadow-sm text-xs font-medium">
                                    <span>Change Cover</span>
                                    <input type="file" @change="handleCoverUpload" accept="image/*" class="hidden" />
                                </label>
                            </div>

                            <!-- Remove cover button (only if cover exists) -->
                            <button v-if="form.cover_image_preview || userProfile.cover_image" @click="removeCover"
                                type="button"
                                class="absolute top-2 right-2 bg-red-600 hover:bg-red-700 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.errors.cover_image" class="mt-1 text-xs text-red-600">{{ form.errors.cover_image
                        }}</p>
                    </div>

                    <!-- Profile Image Section -->
                    <div class="mb-6 -mt-10 flex justify-center">
                        <div class="relative group">
                            <div
                                class="w-24 h-24 rounded-full border-4 border-white shadow-md overflow-hidden bg-gray-100">
                                <img v-if="form.profile_image_preview || userProfile.profile_image"
                                    :src="form.profile_image_preview || userProfile.profile_image"
                                    class="w-full h-full object-cover" />
                                <div v-else class="w-full h-full bg-brand-blue flex items-center justify-center">
                                    <span class="text-4xl font-semibold text-white uppercase">{{ userInitial }}</span>
                                </div>
                            </div>

                            <!-- Profile image upload button -->
                            <label
                                class="absolute bottom-0 right-0 cursor-pointer bg-brand-teal hover:bg-brand-teal/90 text-white p-1.5 rounded-full shadow-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <input type="file" @change="handleProfileUpload" accept="image/*" class="hidden" />
                            </label>

                            <!-- Remove profile image button -->
                            <button v-if="form.profile_image_preview || userProfile.profile_image"
                                @click="removeProfileImage" type="button"
                                class="absolute -top-1 -right-1 bg-red-600 hover:bg-red-700 text-white p-1 rounded-full shadow-lg">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Name (Read-only) -->
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" :value="user.name" disabled
                            class="w-full px-3 py-2 bg-gray-100 border border-gray-200 rounded-lg text-sm text-gray-600 cursor-not-allowed" />
                        <p class="mt-1 text-xs text-gray-500">Name cannot be changed</p>
                    </div>

                    <!-- Email (Read-only) -->
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" :value="user.email" disabled
                            class="w-full px-3 py-2 bg-gray-100 border border-gray-200 rounded-lg text-sm text-gray-600 cursor-not-allowed" />
                        <p class="mt-1 text-xs text-gray-500">Email cannot be changed</p>
                    </div>

                    <!-- Phone Number -->
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Phone Number</label>
                        <input v-model="form.phone" type="tel"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-brand-teal focus:border-brand-teal outline-none transition text-sm"
                            :class="{ 'border-red-500': form.errors.phone }" placeholder="+92 300 1234567" />
                        <p v-if="form.errors.phone" class="mt-1 text-xs text-red-600">{{ form.errors.phone }}</p>
                        <p class="mt-1 text-xs text-gray-500">Enter your contact number (optional)</p>
                    </div>

                    <!-- Username with availability check -->
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Username</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">@</span>
                            <input v-model="form.username" type="text" @input="checkUsernameAvailability"
                                @blur="validateUsername"
                                class="w-full pl-7 pr-10 py-2 border rounded-lg focus:ring-1 focus:ring-brand-teal focus:border-brand-teal outline-none transition text-sm"
                                :class="{
                                    'border-green-500 ring-1 ring-green-500': usernameAvailable === true,
                                    'border-red-500 ring-1 ring-red-500': usernameAvailable === false,
                                    'border-gray-300': usernameAvailable === null
                                }" placeholder="username" />

                            <!-- Availability indicators -->
                            <div class="absolute right-3 top-1/2 -translate-y-1/2">
                                <svg v-if="usernameChecking" class="animate-spin w-4 h-4 text-gray-400" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <svg v-else-if="usernameAvailable === true" class="w-4 h-4 text-green-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                <svg v-else-if="usernameAvailable === false" class="w-4 h-4 text-red-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Status messages -->
                        <p v-if="usernameAvailable === false" class="mt-1 text-xs text-red-600">
                            Username is already taken
                        </p>
                        <p v-else-if="usernameAvailable === true && form.username && form.username !== originalUsername"
                            class="mt-1 text-xs text-green-600">
                            Username is available!
                        </p>
                        <p v-else-if="form.errors.username" class="mt-1 text-xs text-red-600">{{ form.errors.username }}
                        </p>

                        <!-- Username rules -->
                        <p class="mt-1 text-xs text-gray-500">
                            Username can only contain letters, numbers, and underscores
                        </p>
                    </div>

                    <!-- Bio -->
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Bio</label>
                        <textarea v-model="form.bio" rows="4"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-brand-teal focus:border-brand-teal outline-none transition text-sm resize-none"
                            :class="{ 'border-red-500': form.errors.bio }"
                            placeholder="Tell us about yourself..."></textarea>
                        <p v-if="form.errors.bio" class="mt-1 text-xs text-red-600">{{ form.errors.bio }}</p>
                        <p class="mt-1 text-xs text-gray-500">{{ form.bio?.length || 0 }}/500 characters</p>
                    </div>

                    <!-- Location -->
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Location</label>
                        <input v-model="form.location" type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-brand-teal focus:border-brand-teal outline-none transition text-sm"
                            :class="{ 'border-red-500': form.errors.location }" placeholder="City, Country" />
                        <p v-if="form.errors.location" class="mt-1 text-xs text-red-600">{{ form.errors.location }}</p>
                    </div>

                    <!-- Website -->
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Website</label>
                        <input v-model="form.website" type="url"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-brand-teal focus:border-brand-teal outline-none transition text-sm"
                            :class="{ 'border-red-500': form.errors.website }" placeholder="https://example.com" />
                        <p v-if="form.errors.website" class="mt-1 text-xs text-red-600">{{ form.errors.website }}</p>
                    </div>

                    <!-- Privacy Settings -->
                    <div class="mb-6">
                        <label class="block text-xs font-medium text-gray-700 mb-2">Privacy</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2">
                                <input v-model="form.is_public" type="radio" :value="true"
                                    class="text-brand-teal focus:ring-brand-teal" />
                                <span class="text-sm text-gray-700">Public Profile</span>
                                <span class="text-xs text-gray-500">- Anyone can see your profile</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input v-model="form.is_public" type="radio" :value="false"
                                    class="text-brand-teal focus:ring-brand-teal" />
                                <span class="text-sm text-gray-700">Private Profile</span>
                                <span class="text-xs text-gray-500">- Only you can see your profile</span>
                            </label>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end gap-2 pt-4 border-t">
                        <Link :href="route('user.profile', { id: user.id })"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                            Cancel
                        </Link>
                        <button type="submit" :disabled="form.processing"
                            class="px-4 py-2 bg-brand-blue hover:bg-brand-blue/90 text-white rounded-lg text-sm font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                            <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Danger Zone -->
            <div class="mt-6 bg-white rounded-lg shadow-sm overflow-hidden border border-red-200">
                <div class="p-4 md:p-6">
                    <h2 class="text-base font-semibold text-red-600 mb-2">Danger Zone</h2>
                    <p class="text-xs text-gray-600 mb-4">Once you delete your account, there is no going back. Please
                        be certain.</p>
                    <button @click="destroy"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition-colors">
                        Delete Account
                    </button>
                </div>
            </div>
        </div>
    </OlxLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { router, Link, useForm, usePage } from '@inertiajs/vue3'
import OlxLayout from '@/layouts/OlxLayout.vue'
import { useAlertDialog } from '@/composables/useAlertDialog';
import axios from 'axios'

const page = usePage()

interface Props {
    user: {
        id: number
        name: string
        email: string
        phone: string | null
    }
    userProfile: {
        username: string | null
        profile_image: string | null
        cover_image: string | null
        bio: string | null
        location: string | null
        website: string | null
        is_public: boolean
    }
}

const props = defineProps<Props>()
useForceTheme('light');

// Store original username for comparison
const originalUsername = ref(props.userProfile.username || '')

// Username availability state
const usernameChecking = ref(false)
const usernameAvailable = ref<boolean | null>(null)

// Get user initial for avatar fallback
const userInitial = computed(() => {
    return props.user.name?.charAt(0) || 'U'
})

// Form state with phone number
const form = useForm({
    username: props.userProfile.username || '',
    phone: props.user.phone || '',
    bio: props.userProfile.bio || '',
    location: props.userProfile.location || '',
    website: props.userProfile.website || '',
    is_public: props.userProfile.is_public ?? true,
    profile_image: null as File | null,
    cover_image: null as File | null,
    profile_image_preview: null as string | null,
    cover_image_preview: null as string | null,
    remove_profile_image: false,
    remove_cover_image: false,
})

// Check if form is valid to submit
const isFormValid = computed(() => {
    // If username is changed, it must be available
    if (form.username !== originalUsername.value) {
        return usernameAvailable.value === true
    }
    return true
})

// Validate username format
const validateUsername = () => {
    if (form.username && !/^[a-zA-Z0-9_]+$/.test(form.username)) {
        form.errors.username = 'Username can only contain letters, numbers, and underscores'
    } else {
        form.errors.username = ''
    }
}

// Check username availability
const checkUsernameAvailability = async () => {
    // Clear previous errors
    form.errors.username = ''

    // Don't check if username is empty or same as original
    if (!form.username || form.username === originalUsername.value) {
        usernameAvailable.value = null
        return
    }

    // Validate format first
    if (!/^[a-zA-Z0-9_]+$/.test(form.username)) {
        usernameAvailable.value = null
        return
    }

    usernameChecking.value = true

    try {
        const response = await axios.get(route('user.check-username'), {
            params: { username: form.username }
        })
        usernameAvailable.value = response.data.available
    } catch (error) {
        console.error('Error checking username:', error)
        usernameAvailable.value = null
    } finally {
        usernameChecking.value = false
    }
}

// Debounce username check
let usernameCheckTimeout: NodeJS.Timeout
watch(() => form.username, (newVal) => {
    clearTimeout(usernameCheckTimeout)
    if (newVal && newVal !== originalUsername.value) {
        usernameCheckTimeout = setTimeout(() => {
            checkUsernameAvailability()
        }, 500)
    } else {
        usernameAvailable.value = null
    }
})

// Handle profile image upload
const handleProfileUpload = (event: Event) => {
    const input = event.target as HTMLInputElement
    if (input.files && input.files[0]) {
        const file = input.files[0]
        form.profile_image = file
        form.profile_image_preview = URL.createObjectURL(file)
        form.remove_profile_image = false
    }
}

// Handle cover image upload
const handleCoverUpload = (event: Event) => {
    const input = event.target as HTMLInputElement
    if (input.files && input.files[0]) {
        const file = input.files[0]
        form.cover_image = file
        form.cover_image_preview = URL.createObjectURL(file)
        form.remove_cover_image = false
    }
}

// Remove profile image
const removeProfileImage = () => {
    form.profile_image = null
    form.profile_image_preview = null
    form.remove_profile_image = true
}

// Remove cover image
const removeCover = () => {
    form.cover_image = null
    form.cover_image_preview = null
    form.remove_cover_image = true
}

// Submit form
const submitForm = () => {
    // Validate username before submit
    if (form.username && !/^[a-zA-Z0-9_]+$/.test(form.username)) {
        form.errors.username = 'Username can only contain letters, numbers, and underscores'
        return
    }

    // Check if username is taken (if changed)
    if (form.username !== originalUsername.value && usernameAvailable.value === false) {
        return
    }

    form.post(route('user.profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Clean up preview URLs
            if (form.profile_image_preview) {
                URL.revokeObjectURL(form.profile_image_preview)
            }
            if (form.cover_image_preview) {
                URL.revokeObjectURL(form.cover_image_preview)
            }
        }
    })
}

const alert = useAlertDialog();
const destroy = async () => {
    //console.log('button pressed', props.user);

    form.delete(route('users.destroy', props.user.id), {
        preserveScroll: true,
        onSuccess: () => {
            router.visit(route('ads.index'));
        },
    });
};

// Clean up preview URLs on component unmount
onMounted(() => {
    return () => {
        if (form.profile_image_preview) {
            URL.revokeObjectURL(form.profile_image_preview)
        }
        if (form.cover_image_preview) {
            URL.revokeObjectURL(form.cover_image_preview)
        }
    }
})
</script>