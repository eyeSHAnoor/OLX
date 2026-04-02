<template>
    <Transition enter-active-class="transition duration-300 ease-out"
        enter-from-class="transform translate-y-4 opacity-0" enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in" leave-from-class="transform translate-y-0 opacity-100"
        leave-to-class="transform translate-y-4 opacity-0">
        <div v-if="message"
            class="fixed inset-0 z-[60] flex items-end sm:items-start sm:justify-end p-0 sm:p-6 bg-black/20 sm:bg-transparent"
            :class="{ 'bg-black/50': isMobile }">
            <div class="broadcast-notification bg-white rounded-t-2xl sm:rounded-xl shadow-2xl w-full sm:w-80 md:w-96 max-w-full sm:max-w-sm md:max-w-md border-0 sm:border border-gray-100 animate-slide-up sm:animate-none"
                :class="{ 'rounded-b-2xl': isMobile }">
                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                        <h3 class="font-semibold text-gray-800 text-sm sm:text-base">
                            Broadcast Message
                        </h3>
                    </div>
                    <button @click="closePopup"
                        class="text-gray-400 hover:text-gray-600 transition-colors p-1 sm:p-0 focus:outline-none focus:ring-2 focus:ring-brand-teal rounded-full"
                        aria-label="Close">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <div class="p-4 sm:p-5">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-3 sm:p-4 mb-4">
                        <p class="text-xs sm:text-sm text-gray-600 mb-1">
                            {{ message.title }}
                        </p>
                        <p class="text-base sm:text-lg font-semibold text-gray-800 break-all">
                            {{ message.body }}
                        </p>
                        <p class="text-xs text-gray-500 mt-2">
                            Received just now
                        </p>
                    </div>

                    <!-- Single close button -->
                    <div class="flex flex-col-reverse sm:flex-row gap-2 sm:gap-3">
                        <button
                            class="w-full sm:flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-3 sm:py-2.5 rounded-lg font-medium transition-all duration-200"
                            @click="closePopup">
                            <span class="flex items-center justify-center gap-2 text-sm sm:text-base">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Dismiss
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue'

const props = defineProps({
    message: Object
})

const emit = defineEmits(['close'])

const open = ref(false)
const isMobile = ref(false)

// Watch the message prop to control visibility
watch(
    () => props.message,
    (newVal) => {
        open.value = !!newVal
    }
)

const closePopup = () => {
    open.value = false
    emit('close')
}

// Detect mobile viewport (same as in the example)
const checkMobile = () => {
    isMobile.value = window.innerWidth < 640
}

onMounted(() => {
    checkMobile()
    window.addEventListener('resize', checkMobile)
})

onUnmounted(() => {
    window.removeEventListener('resize', checkMobile)
})
</script>

<style scoped>
/* Optional: if animate-slide-up is not globally defined */
@keyframes slide-up {
    from {
        transform: translateY(100%);
    }

    to {
        transform: translateY(0);
    }
}

.animate-slide-up {
    animation: slide-up 0.3s ease-out;
}
</style>