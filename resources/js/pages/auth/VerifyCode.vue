<!-- resources/js/Pages/auth/VerifyCode.vue -->
<template>
    <div
        class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 flex flex-col items-center justify-center p-4">
        <!-- Simple Logo -->
        <div class="mb-8">
            <img src="/images/logo.png" alt="AMO Mercatus" class="h-10 w-auto mx-auto" />
        </div>

        <!-- Verification Card -->
        <div class="w-full max-w-md">
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Verify your email</h1>
                    <p class="text-sm text-slate-600">
                        We sent a 6-digit code to<br />
                        <span class="font-medium text-slate-900">{{ email }}</span>
                    </p>
                </div>

                <!-- Timer -->
                <div class="flex justify-center mb-6">
                    <div class="flex items-center gap-2 px-4 py-2 bg-slate-100 rounded-full">
                        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm font-medium text-slate-700" :class="{ 'text-red-600': timeLeft < 30 }">
                            {{ formattedTime }}
                        </span>
                    </div>
                </div>

                <!-- Expired Message -->
                <div v-if="isExpired" class="mb-6 p-4 bg-red-50 rounded-xl">
                    <p class="text-sm text-red-600 text-center">
                        Code expired. Request a new one below.
                    </p>
                </div>

                <!-- Success Message -->
                <div v-if="$page.props.flash?.success" class="mb-6 p-4 bg-green-50 rounded-xl">
                    <p class="text-sm text-green-600 text-center">
                        {{ $page.props.flash.success }}
                    </p>
                </div>

                <!-- Error Message -->
                <div v-if="form.errors.code" class="mb-6 p-4 bg-red-50 rounded-xl">
                    <p class="text-sm text-red-600 text-center">
                        {{ form.errors.code }}
                    </p>
                </div>

                <!-- Code Input Boxes -->
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-3 text-center">
                            Enter verification code
                        </label>

                        <!-- 6-digit boxes -->
                        <div class="flex justify-center gap-2">
                            <input v-for="(digit, index) in 6" :key="index"
                                :ref="el => { if (el) inputRefs[index] = el as HTMLInputElement }" type="text"
                                :value="codeDigits[index] || ''" maxlength="1" :disabled="isExpired || form.processing"
                                class="w-12 h-14 text-black text-center text-2xl font-semibold border-2 rounded-xl focus:outline-none focus:ring-2 transition-all disabled:bg-slate-50 disabled:text-slate-400"
                                :class="[
                                    form.errors.code
                                        ? 'border-red-300 focus:border-red-500 focus:ring-red-200'
                                        : 'border-slate-200 focus:border-blue-500 focus:ring-blue-200',
                                ]" @input="(e) => handleDigitInput(index, (e.target as HTMLInputElement).value)"
                                @keydown="(e) => handleKeyDown(index, e)" @paste="handlePaste" inputmode="numeric"
                                pattern="[0-9]*" />
                        </div>
                    </div>

                    <!-- Verify Button -->
                    <Button type="submit" :disabled="form.processing || !isCodeComplete || isExpired"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-blue-200">
                        <div class="flex items-center justify-center gap-2">
                            <LoaderCircle v-if="form.processing" class="animate-spin h-4 w-4" />
                            <span>{{
                                form.processing ? "Verifying..." : "Verify & Create Account"
                                }}</span>
                        </div>
                    </Button>
                </form>

                <!-- Resend Section -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-slate-600 mb-2">Didn't receive the code?</p>
                    <button @click="resendCode" :disabled="resendCooldown > 0 || resendLoading || form.processing"
                        class="text-sm font-medium text-blue-600 hover:text-blue-700 disabled:text-slate-400 transition-colors">
                        <span v-if="resendCooldown > 0"> Resend in {{ resendCooldown }}s </span>
                        <span v-else-if="resendLoading"> Sending... </span>
                        <span v-else> Request new code </span>
                    </button>
                </div>

                <!-- Back Link -->
                <div class="mt-6 pt-4 border-t border-slate-100">
                    <TextLink :href="route('register')"
                        class="text-sm text-slate-500 hover:text-slate-700 flex items-center justify-center gap-1">
                        <span>←</span>
                        <span>Back to registration</span>
                    </TextLink>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <p class="mt-8 text-xs text-slate-500">
            © {{ new Date().getFullYear() }} AMO Mercatus. All rights reserved.
        </p>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import { Button } from "@/components/ui/button";
import TextLink from "@/components/TextLink.vue";
import { LoaderCircle } from "lucide-vue-next";

const props = defineProps<{
    email: string;
    isExpired?: boolean;
    expiresAt?: string;
}>();

const form = useForm({
    code: "",
});

// Refs for input boxes
const inputRefs = ref<(HTMLInputElement | null)[]>([]);

// Code digits array
const codeDigits = ref<string[]>(Array(6).fill(""));

// Computed property to check if code is complete
const isCodeComplete = computed(() => {
    return codeDigits.value.every((digit) => digit && /^\d$/.test(digit));
});

// Update form.code when digits change
const updateFormCode = () => {
    form.code = codeDigits.value.join("");
};

// Handle digit input
const handleDigitInput = (index: number, value: string) => {
    // Allow only digits
    const digit = value.replace(/[^0-9]/g, "");

    if (digit) {
        codeDigits.value[index] = digit;
        updateFormCode();

        // Auto-focus next input
        if (index < 5) {
            nextTick(() => {
                inputRefs.value[index + 1]?.focus();
            });
        }
    } else {
        codeDigits.value[index] = "";
        updateFormCode();
    }
};

// Handle key down for backspace
const handleKeyDown = (index: number, event: KeyboardEvent) => {
    if (event.key === "Backspace") {
        if (!codeDigits.value[index] && index > 0) {
            // If current box is empty and backspace pressed, focus previous
            codeDigits.value[index - 1] = "";
            updateFormCode();
            nextTick(() => {
                inputRefs.value[index - 1]?.focus();
            });
        } else if (codeDigits.value[index]) {
            // Clear current box
            codeDigits.value[index] = "";
            updateFormCode();
        }
    }
};

// Handle paste event
const handlePaste = (event: ClipboardEvent) => {
    event.preventDefault();
    const pastedText = event.clipboardData?.getData("text") || "";
    const digits = pastedText
        .replace(/[^0-9]/g, "")
        .slice(0, 6)
        .split("");

    digits.forEach((digit, index) => {
        if (index < 6) {
            codeDigits.value[index] = digit;
        }
    });

    updateFormCode();

    // Focus the next empty box or last box
    const nextEmptyIndex = codeDigits.value.findIndex((d) => !d);
    if (nextEmptyIndex !== -1) {
        inputRefs.value[nextEmptyIndex]?.focus();
    } else {
        inputRefs.value[5]?.focus();
    }
};

// Reset all inputs
const resetInputs = () => {
    codeDigits.value = Array(6).fill("");
    form.code = "";
    nextTick(() => {
        inputRefs.value[0]?.focus();
    });
};

// Timer states
const resendLoading = ref(false);
const resendCooldown = ref(0);
const timeLeft = ref(120); // 2 minutes in seconds
let timerInterval: ReturnType<typeof setInterval> | null = null;

// Formatted time for display
const formattedTime = computed(() => {
    const minutes = Math.floor(timeLeft.value / 60);
    const seconds = timeLeft.value % 60;
    return `${minutes}:${seconds.toString().padStart(2, "0")}`;
});

// Submit verification
const submit = () => {
    if (!isCodeComplete.value) return;

    form.post(route("verification.verify"), {
        preserveScroll: true,
        onError: () => {
            resetInputs();
        },
        onSuccess: () => {
            // Clear timer on success
            if (timerInterval) {
                clearInterval(timerInterval);
            }
        },
    });
};

// Resend code
const resendCode = () => {
    if (resendCooldown.value > 0 || resendLoading.value || form.processing) return;

    resendLoading.value = true;

    router.post(
        route("verification.resend"),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                resendLoading.value = false;
                resendCooldown.value = 60;
                timeLeft.value = 120; // Reset timer to 2 minutes

                // Reset inputs
                resetInputs();

                // Start cooldown timer
                const cooldownInterval = setInterval(() => {
                    resendCooldown.value -= 1;
                    if (resendCooldown.value <= 0) {
                        clearInterval(cooldownInterval);
                    }
                }, 1000);
            },
            onError: () => {
                resendLoading.value = false;
            },
        }
    );
};

// Timer logic
const startTimer = () => {
    if (props.expiresAt) {
        const expiryTime = new Date(props.expiresAt).getTime();

        timerInterval = setInterval(() => {
            const now = new Date().getTime();
            const distance = expiryTime - now;

            if (distance <= 0) {
                timeLeft.value = 0;
                if (timerInterval) {
                    clearInterval(timerInterval);
                }
                // Reload page to show expired state
                router.reload();
            } else {
                timeLeft.value = Math.floor(distance / 1000);
            }
        }, 1000);
    }
};

// Focus first input on mount
onMounted(() => {
    if (!props.isExpired && props.expiresAt) {
        startTimer();
        nextTick(() => {
            inputRefs.value[0]?.focus();
        });
    }
});

onUnmounted(() => {
    if (timerInterval) {
        clearInterval(timerInterval);
    }
});
</script>

<style scoped>
/* Smooth transitions */
input {
    transition: all 0.2s ease;
}

/* Hide spinner buttons for number inputs */
input[type="text"]::-webkit-inner-spin-button,
input[type="text"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Focus animation */
input:focus {
    transform: scale(1.02);
}

/* Custom shadow for button */
.shadow-lg {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.shadow-blue-200 {
    --tw-shadow-color: #bfdbfe;
    --tw-shadow: var(--tw-shadow-colored);
}
</style>
