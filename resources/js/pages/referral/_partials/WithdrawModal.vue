<script setup lang="ts">
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogFooter,
} from "@/components/ui/dialog";
import { Card, CardContent } from "@/components/ui/card";
import { useForm } from "@inertiajs/vue3";
import { watch, ref, computed } from "vue";
import AppButton from "@/components/Application/AppButton.vue";
import { Wallet, Banknote, Phone, AlertCircle } from "lucide-vue-next";
import { router } from "@inertiajs/vue3";

const props = defineProps<{
  show?: boolean;
  availablePoints: number;
  minWithdrawal?: number;
}>();

const emit = defineEmits<{
  (e: "close"): void;
  (e: "success"): void;
}>();

const model = defineModel<boolean>();

const getDefaultForm = () => ({
  points: 0,
  payment_method: "bank_transfer",
  payment_details: {
    account_number: "",
    account_holder: "",
    bank_name: "",
    phone_number: "",
  },
});

const form = useForm({ ...getDefaultForm() });

// Sync open state from the parent prop and internal model
watch(
  () => props.show,
  (isOpen) => {
    model.value = isOpen ?? false;
  },
  { immediate: true }
);

watch(model, (isOpen) => {
  if (isOpen) {
    form.defaults(getDefaultForm());
    form.reset();
    errors.value = {};
  } else {
    emit("close");
  }
});

// Local errors for validation
const errors = ref<Record<string, string>>({});

// Available points display
const availablePointsDisplay = computed(() => {
  return props.availablePoints.toLocaleString();
});

// Check if points input is valid
const isPointsValid = computed(() => {
  return (
    form.points >= (props.minWithdrawal || 100) &&
    form.points <= props.availablePoints &&
    form.points > 0
  );
});

// Payment method icons
const getPaymentMethodIcon = (method: string) => {
  switch (method) {
    case "bank_transfer":
      return Banknote;
    case "easypaisa":
    case "jazzcash":
      return Phone;
    default:
      return Wallet;
  }
};

// Get payment method label
const getPaymentMethodLabel = (method: string) => {
  switch (method) {
    case "bank_transfer":
      return "Bank Transfer";
    case "easypaisa":
      return "Easypaisa";
    case "jazzcash":
      return "JazzCash";
    default:
      return method;
  }
};

const validateForm = (): boolean => {
  errors.value = {};

  if (!form.points || form.points < (props.minWithdrawal || 100)) {
    errors.value.points = `Minimum withdrawal is ${props.minWithdrawal || 100} points`;
    return false;
  }

  if (form.points > props.availablePoints) {
    errors.value.points = "Insufficient points available";
    return false;
  }

  const paymentMethod = form.payment_method;
  const details = form.payment_details;

  if (paymentMethod === "bank_transfer") {
    if (!details.account_number) {
      errors.value["payment_details.account_number"] = "Account number is required";
    }
    if (!details.account_holder) {
      errors.value["payment_details.account_holder"] = "Account holder name is required";
    }
    if (!details.bank_name) {
      errors.value["payment_details.bank_name"] = "Bank name is required";
    }
  } else if (paymentMethod === "easypaisa" || paymentMethod === "jazzcash") {
    if (!details.phone_number) {
      errors.value["payment_details.phone_number"] = "Phone number is required";
    }
  }

  return Object.keys(errors.value).length === 0;
};

const closeModal = () => {
  model.value = false;
  emit("close");
};

// ✅ Fixed submit function with better error handling
const submit = () => {
  // Validate form first
  if (!validateForm()) {
    return;
  }

  // Prepare data for submission
  const formData = {
    points: form.points,
    payment_method: form.payment_method,
    payment_details: form.payment_details,
  };

  console.log("pressed");

  // Use router directly instead of form.post for more control
  router.post(route("withdrawals.store"), formData, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      // Close modal and emit success
      model.value = false;
      emit("success");
      form.reset();
      errors.value = {};

      // Show success message
      useToast().success("Withdrawal request submitted successfully!");
    },
    onError: (err) => {
      // Handle validation errors from server
      if (err.response?.data?.errors) {
        errors.value = err.response.data.errors;
      } else if (err.response?.data?.message) {
        // Show error toast
        useToast().error(err.response.data.message);
      } else {
        useToast().error("Failed to submit withdrawal request. Please try again.");
      }
      console.log("Withdrawal errors:", err);
    },
  });
};

// Quick select points amount
const quickSelectPoints = (points: number) => {
  if (points <= props.availablePoints) {
    form.points = points;
    // Clear any points error when user selects
    if (errors.value.points) {
      delete errors.value.points;
    }
  }
};

// Get quick select options
const quickOptions = computed(() => {
  const options = [];
  const min = props.minWithdrawal || 100;
  const max = props.availablePoints;

  if (max >= min) {
    const half = Math.floor(max / 2);
    const quarter = Math.floor(max / 4);
    const threeQuarter = Math.floor(max * 0.75);

    options.push(min);
    if (quarter > min) options.push(quarter);
    if (half > quarter) options.push(half);
    if (threeQuarter > half) options.push(threeQuarter);
    options.push(max);
  }

  // Remove duplicates and sort
  return [...new Set(options)].sort((a, b) => a - b);
});

// Simple toast function (replace with your actual toast)
const useToast = () => {
  return {
    success: (msg: string) => {
      // You can replace this with your toast implementation
      console.log("✅", msg);
      // If you have a toast composable, use it here
      // Example: return useToast().success(msg);
    },
    error: (msg: string) => {
      console.error("❌", msg);
      // If you have a toast composable, use it here
      // Example: return useToast().error(msg);
    },
  };
};
</script>

<template>
  <Dialog v-model:open="model">
    <DialogContent class="!w-6/12 max-h-[90vh] flex flex-col px-7">
      <!-- Fixed Header -->
      <DialogHeader class="!px-0 !pb-0 flex-shrink-0">
        <DialogTitle class="flex items-center gap-2">
          <Wallet class="w-5 h-5" style="color: var(--brand-teal)" />
          Withdraw Points
        </DialogTitle>
      </DialogHeader>

      <!-- Scrollable Content -->
      <div class="flex-1 overflow-y-auto mt-3 pr-2 custom-scrollbar">
        <div class="grid gap-y-4">
          <!-- Show validation errors at top -->
          <div v-if="Object.keys(errors).length > 0" class="space-y-1">
            <div
              v-for="(error, key) in errors"
              :key="key"
              class="text-sm text-red-600 bg-red-50 p-2 rounded"
            >
              {{ error }}
            </div>
          </div>

          <!-- Available Points Card -->
          <Card
            class="border-2"
            style="
              border-color: var(--brand-teal);
              background: linear-gradient(
                135deg,
                rgba(0, 194, 187, 0.08),
                rgba(0, 194, 187, 0.02)
              );
            "
          >
            <CardContent class="p-4">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-gray-600 dark:text-gray-400">Available Points</p>
                  <p class="text-2xl font-bold" style="color: var(--brand-teal)">
                    {{ availablePointsDisplay }}
                  </p>
                </div>
                <div class="p-3 rounded-full" style="background: rgba(0, 194, 187, 0.12)">
                  <Wallet class="w-6 h-6" style="color: var(--brand-teal)" />
                </div>
              </div>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                Minimum withdrawal: {{ minWithdrawal || 100 }} points
              </p>
            </CardContent>
          </Card>

          <!-- Withdrawal Form -->
          <Card>
            <CardContent class="p-4">
              <div class="space-y-4">
                <!-- Points Input -->
                <div class="space-y-2">
                  <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Points to Withdraw
                  </label>

                  <!-- Quick select buttons -->
                  <div v-if="quickOptions.length > 0" class="flex flex-wrap gap-2 mb-2">
                    <button
                      v-for="option in quickOptions"
                      :key="option"
                      type="button"
                      @click="quickSelectPoints(option)"
                      class="px-3 py-1 text-xs rounded-full border transition-colors"
                      :class="{
                        'bg-brand-teal/10 border-brand-teal text-brand-teal':
                          form.points === option,
                        'border-gray-200 dark:border-gray-700 hover:border-brand-teal hover:bg-brand-teal/5':
                          form.points !== option,
                      }"
                    >
                      {{ option.toLocaleString() }}
                    </button>
                  </div>

                  <input
                    v-model.number="form.points"
                    type="number"
                    :min="minWithdrawal || 100"
                    :max="availablePoints"
                    class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-teal/20 focus:border-brand-teal"
                    :class="{
                      'border-red-500': errors.points,
                      'border-gray-200 dark:border-gray-700': !errors.points,
                    }"
                    placeholder="Enter points"
                    @input="
                      () => {
                        if (errors.points) delete errors.points;
                      }
                    "
                  />
                  <div
                    v-if="form.points > 0 && form.points <= availablePoints"
                    class="text-xs text-gray-500"
                  >
                    You will receive:
                    <span class="font-semibold" style="color: var(--brand-teal)">{{
                      form.points.toLocaleString()
                    }}</span>
                    units
                  </div>
                  <p v-if="errors.points" class="text-xs text-red-600">
                    {{ errors.points }}
                  </p>
                </div>

                <!-- Payment Method -->
                <div class="space-y-2">
                  <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Payment Method
                  </label>
                  <div class="grid grid-cols-3 gap-2">
                    <button
                      v-for="method in ['bank_transfer', 'easypaisa', 'jazzcash']"
                      :key="method"
                      type="button"
                      @click="form.payment_method = method"
                      class="px-3 py-2 text-sm rounded-lg border transition-all"
                      :class="{
                        'border-brand-teal bg-brand-teal/10 text-brand-teal':
                          form.payment_method === method,
                        'border-gray-200 dark:border-gray-700 hover:border-brand-teal hover:bg-brand-teal/5':
                          form.payment_method !== method,
                      }"
                    >
                      <component
                        :is="getPaymentMethodIcon(method)"
                        class="w-4 h-4 mx-auto mb-1"
                      />
                      {{ getPaymentMethodLabel(method) }}
                    </button>
                  </div>
                </div>

                <!-- Bank Transfer Details -->
                <div
                  v-if="form.payment_method === 'bank_transfer'"
                  class="space-y-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4"
                >
                  <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                      Account Holder Name
                    </label>
                    <input
                      v-model="form.payment_details.account_holder"
                      type="text"
                      class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-teal/20 focus:border-brand-teal"
                      :class="{
                        'border-red-500': errors['payment_details.account_holder'],
                        'border-gray-200 dark:border-gray-700': !errors[
                          'payment_details.account_holder'
                        ],
                      }"
                      placeholder="Enter account holder name"
                    />
                    <p
                      v-if="errors['payment_details.account_holder']"
                      class="text-xs text-red-600 mt-1"
                    >
                      {{ errors["payment_details.account_holder"] }}
                    </p>
                  </div>
                  <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                      Account Number
                    </label>
                    <input
                      v-model="form.payment_details.account_number"
                      type="text"
                      class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-teal/20 focus:border-brand-teal"
                      :class="{
                        'border-red-500': errors['payment_details.account_number'],
                        'border-gray-200 dark:border-gray-700': !errors[
                          'payment_details.account_number'
                        ],
                      }"
                      placeholder="Enter account number"
                    />
                    <p
                      v-if="errors['payment_details.account_number']"
                      class="text-xs text-red-600 mt-1"
                    >
                      {{ errors["payment_details.account_number"] }}
                    </p>
                  </div>
                  <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                      Bank Name
                    </label>
                    <input
                      v-model="form.payment_details.bank_name"
                      type="text"
                      class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-teal/20 focus:border-brand-teal"
                      :class="{
                        'border-red-500': errors['payment_details.bank_name'],
                        'border-gray-200 dark:border-gray-700': !errors[
                          'payment_details.bank_name'
                        ],
                      }"
                      placeholder="Enter bank name"
                    />
                    <p
                      v-if="errors['payment_details.bank_name']"
                      class="text-xs text-red-600 mt-1"
                    >
                      {{ errors["payment_details.bank_name"] }}
                    </p>
                  </div>
                </div>

                <!-- Easypaisa / JazzCash Details -->
                <div
                  v-if="
                    form.payment_method === 'easypaisa' ||
                    form.payment_method === 'jazzcash'
                  "
                  class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4"
                >
                  <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                      Phone Number
                    </label>
                    <input
                      v-model="form.payment_details.phone_number"
                      type="tel"
                      class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-teal/20 focus:border-brand-teal"
                      :class="{
                        'border-red-500': errors['payment_details.phone_number'],
                        'border-gray-200 dark:border-gray-700': !errors[
                          'payment_details.phone_number'
                        ],
                      }"
                      placeholder="Enter phone number"
                    />
                    <p
                      v-if="errors['payment_details.phone_number']"
                      class="text-xs text-red-600 mt-1"
                    >
                      {{ errors["payment_details.phone_number"] }}
                    </p>
                  </div>
                </div>

                <!-- Info Alert -->
                <div
                  class="flex items-start gap-2 p-3 rounded-lg border"
                  style="
                    background: rgba(0, 194, 187, 0.06);
                    border-color: var(--brand-teal);
                  "
                >
                  <AlertCircle
                    class="w-4 h-4 mt-0.5 flex-shrink-0"
                    style="color: var(--brand-teal)"
                  />
                  <p class="text-xs" style="color: var(--brand-teal)">
                    Withdrawal requests are processed within 24-48 hours. You will receive
                    a confirmation once processed.
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>

      <!-- Fixed Footer -->
      <DialogFooter
        class="flex-shrink-0 pt-4 border-t border-gray-200 dark:border-gray-700 mt-4"
      >
        <div class="flex items-center justify-between w-full gap-2">
          <p class="text-xs text-gray-500 dark:text-gray-400">
            Points after withdrawal:
            <span class="font-semibold" style="color: var(--brand-teal)">{{
              (availablePoints - (form.points || 0)).toLocaleString()
            }}</span>
          </p>
          <div class="flex items-center gap-2">
            <AppButton
              size="sm"
              variant="outline"
              label="Cancel"
              @click="closeModal"
              :disabled="form.processing"
            />
            <AppButton
              size="sm"
              :processing="form.processing"
              label="Submit Withdrawal"
              :disabled="form.processing"
              @click="submit"
              style="background: var(--brand-teal); border-color: var(--brand-teal)"
              class="hover:opacity-90"
            />
          </div>
        </div>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<style scoped>
/* Brand Colors */
:root {
  --brand-orange: #f26822;
  --brand-blue: #3b5fb5;
  --brand-teal: #00c2bb;
  --brand-black: #000000;
}

/* Custom Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: var(--brand-teal);
  border-radius: 3px;
  opacity: 0.5;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  opacity: 0.8;
}

/* Dark mode support */
.dark .border-gray-200 {
  border-color: #374151;
}

.dark .bg-gray-50 {
  background-color: #1f2937;
}

.dark .border-gray-300 {
  border-color: #4b5563;
}

/* Quick select buttons animation */
button[type="button"] {
  transition: all 0.2s ease;
}

/* Number input arrows style */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  opacity: 1;
}

/* Brand color utility classes */
.bg-brand-teal\/10 {
  background: rgba(0, 194, 187, 0.1);
}

.border-brand-teal {
  border-color: var(--brand-teal);
}

.text-brand-teal {
  color: var(--brand-teal);
}

.hover\:border-brand-teal:hover {
  border-color: var(--brand-teal);
}

.hover\:bg-brand-teal\/5:hover {
  background: rgba(0, 194, 187, 0.05);
}

/* Dialog content layout */
:deep(.dialog-content) {
  display: flex;
  flex-direction: column;
  max-height: 90vh;
}

/* Ensure proper spacing */
:deep(.dialog-content > *) {
  flex-shrink: 0;
}

:deep(.dialog-content > .flex-1) {
  flex-shrink: 1;
}

/* Loading state for button */
.btn-loading {
  opacity: 0.7;
  cursor: not-allowed;
}
</style>
