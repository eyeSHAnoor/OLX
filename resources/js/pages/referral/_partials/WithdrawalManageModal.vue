<script setup lang="ts">
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogFooter,
} from "@/components/ui/dialog";
import { Card, CardContent } from "@/components/ui/card";
import { router } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import AppButton from "@/components/Application/AppButton.vue";
import {
  Wallet,
  Banknote,
  Phone,
  AlertCircle,
  Eye,
  Upload,
  X,
  Check,
  Clock,
  ThumbsUp,
  ThumbsDown,
  Image,
  FileImage,
} from "lucide-vue-next";

interface WithdrawalRequest {
  id: number;
  user_id: number;
  user: {
    id: number;
    name: string;
    email: string;
  };
  total_earned: number;
  total_withdrawn: number;
  available: number;
  pending: number;
  requested_amount: number;
  status: "pending" | "approved" | "completed" | "rejected";
  payment_method: string;
  payment_details: any;
  proof_images: string[];
  transaction_id: string | null;
  admin_notes: string | null;
  created_at: string;
  processed_at: string | null;
  confirmed_at: string | null;
}

const props = defineProps<{
  show: boolean;
  withdrawal: WithdrawalRequest | null;
}>();

const emit = defineEmits<{
  (e: "close"): void;
  (e: "updated"): void;
}>();

const form = ref({
  transaction_id: "",
  admin_notes: "",
  proof_images: [] as File[],
  reason: "",
});

const isProcessing = ref(false);
const previewImages = ref<{ url: string; isExisting: boolean; path?: string }[]>([]);
const errors = ref<Record<string, string>>({});

// Watch for withdrawal changes
watch(
  () => props.withdrawal,
  (newVal) => {
    if (newVal) {
      form.value.transaction_id = newVal.transaction_id || "";
      form.value.admin_notes = newVal.admin_notes || "";
      form.value.reason = "";
      errors.value = {};

      // Load existing proof images
      previewImages.value = (newVal.proof_images || []).map((img) => ({
        url: `/storage/${img}`,
        isExisting: true,
        path: img,
      }));
    }
  },
  { immediate: true }
);

// Get status badge
const getStatusColor = (status: string) => {
  const colors = {
    pending: "bg-yellow-100 text-yellow-800",
    approved: "bg-blue-100 text-blue-800",
    completed: "bg-green-100 text-green-800",
    rejected: "bg-red-100 text-red-800",
  };
  return colors[status as keyof typeof colors] || "bg-gray-100 text-gray-800";
};

const getStatusIcon = (status: string) => {
  const icons = {
    pending: Clock,
    approved: ThumbsUp,
    completed: Check,
    rejected: ThumbsDown,
  };
  return icons[status as keyof typeof icons] || Clock;
};

const getStatusLabel = (status: string) => {
  const labels = {
    pending: "Pending",
    approved: "Approved",
    completed: "Completed",
    rejected: "Rejected",
  };
  return labels[status as keyof typeof labels] || status;
};

const getPaymentMethodLabel = (method: string) => {
  const labels = {
    bank_transfer: "Bank Transfer",
    easypaisa: "Easypaisa",
    jazzcash: "JazzCash",
  };
  return labels[method as keyof typeof labels] || method;
};

// Handle file upload
const handleFileUpload = (event: Event) => {
  const input = event.target as HTMLInputElement;
  if (input.files) {
    const files = Array.from(input.files);

    // Validate each file
    for (const file of files) {
      if (file.size > 5 * 1024 * 1024) {
        alert(`File ${file.name} exceeds 5MB limit`);
        input.value = "";
        return;
      }
      const allowedTypes = ["image/jpeg", "image/png", "image/gif", "image/webp"];
      if (!allowedTypes.includes(file.type)) {
        alert(`File ${file.name} is not a valid image format`);
        input.value = "";
        return;
      }
    }

    form.value.proof_images.push(...files);

    // Create preview URLs
    files.forEach((file) => {
      const reader = new FileReader();
      reader.onload = (e) => {
        previewImages.value.push({
          url: e.target?.result as string,
          isExisting: false,
        });
      };
      reader.readAsDataURL(file);
    });
  }
  input.value = "";
};

// Remove uploaded image
const removeImage = (index: number) => {
  const image = previewImages.value[index];
  if (!image.isExisting) {
    // Remove from form files
    const fileIndex = form.value.proof_images.findIndex((_, i) => {
      // Find matching file by checking if the preview URL matches
      return image.url.startsWith("blob:");
    });
    if (fileIndex !== -1) {
      form.value.proof_images.splice(fileIndex, 1);
    }
  }
  previewImages.value.splice(index, 1);
};

// Get existing proof count
const existingProofCount = computed(() => {
  return previewImages.value.filter((img) => img.isExisting).length;
});

const newProofCount = computed(() => {
  return previewImages.value.filter((img) => !img.isExisting).length;
});

// Approve withdrawal
const approveWithdrawal = () => {
  if (!props.withdrawal) return;

  errors.value = {};

  if (!form.value.transaction_id) {
    errors.value.transaction_id = "Transaction ID is required";
    return;
  }

  isProcessing.value = true;

  const formData = new FormData();
  formData.append("transaction_id", form.value.transaction_id);
  formData.append("admin_notes", form.value.admin_notes || "");

  // Upload new proof images only
  const newImages = previewImages.value.filter((img) => !img.isExisting);
  newImages.forEach((img, index) => {
    const file = form.value.proof_images[index];
    if (file) {
      formData.append(`proof_images[${index}]`, file);
    }
  });

  router.post(route("admin.withdrawals.approve", props.withdrawal.id), formData, {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      isProcessing.value = false;
      emit("updated");
      emit("close");
    },
    onError: (err) => {
      isProcessing.value = false;
      if (err.response?.data?.errors) {
        errors.value = err.response.data.errors;
      }
    },
  });
};

// Complete withdrawal
const completeWithdrawal = () => {
  if (!props.withdrawal) return;

  isProcessing.value = true;

  const formData = new FormData();
  formData.append("admin_notes", form.value.admin_notes || "");

  // Upload new proof images only
  const newImages = previewImages.value.filter((img) => !img.isExisting);
  newImages.forEach((img, index) => {
    const file = form.value.proof_images[index];
    if (file) {
      formData.append(`proof_images[${index}]`, file);
    }
  });

  router.post(route("admin.withdrawals.complete", props.withdrawal.id), formData, {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      isProcessing.value = false;
      emit("updated");
      emit("close");
    },
    onError: (err) => {
      isProcessing.value = false;
      if (err.response?.data?.errors) {
        errors.value = err.response.data.errors;
      }
    },
  });
};

// Reject withdrawal
const rejectWithdrawal = () => {
  if (!props.withdrawal) return;

  errors.value = {};

  if (!form.value.reason) {
    errors.value.reason = "Reason for rejection is required";
    return;
  }

  isProcessing.value = true;

  router.post(
    route("admin.withdrawals.reject", props.withdrawal.id),
    {
      reason: form.value.reason,
      admin_notes: form.value.admin_notes,
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        isProcessing.value = false;
        emit("updated");
        emit("close");
      },
      onError: (err) => {
        isProcessing.value = false;
        if (err.response?.data?.errors) {
          errors.value = err.response.data.errors;
        }
      },
    }
  );
};

// Close modal
const closeModal = () => {
  emit("close");
};
</script>

<template>
  <Dialog :open="show" @update:open="(val) => !val && closeModal()">
    <DialogContent class="!w-8/12 max-h-[90vh] flex flex-col px-7">
      <!-- Header -->
      <DialogHeader class="!px-0 !pb-0 flex-shrink-0">
        <DialogTitle class="flex items-center gap-2">
          <Wallet class="w-5 h-5 text-brand-teal" />
          Manage Withdrawal
          <span
            v-if="withdrawal"
            class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
            :class="getStatusColor(withdrawal.status)"
          >
            <component :is="getStatusIcon(withdrawal.status)" class="w-3 h-3 mr-1" />
            {{ getStatusLabel(withdrawal.status) }}
          </span>
        </DialogTitle>
      </DialogHeader>

      <!-- Content -->
      <div class="flex-1 overflow-y-auto mt-3 pr-2 custom-scrollbar" v-if="withdrawal">
        <div class="space-y-4">
          <!-- User Info -->
          <Card>
            <CardContent class="p-4">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <p class="text-sm text-muted-foreground">User</p>
                  <p class="font-semibold">{{ withdrawal.user.name }}</p>
                  <p class="text-sm text-muted-foreground">{{ withdrawal.user.email }}</p>
                </div>
                <div>
                  <p class="text-sm text-muted-foreground">Requested</p>
                  <p class="font-semibold text-purple-600">
                    {{ withdrawal.requested_amount?.toLocaleString() || 0 }} pts
                  </p>
                  <p class="text-sm text-muted-foreground">
                    {{ new Date(withdrawal.created_at).toLocaleString() }}
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Payment Details -->
          <Card>
            <CardContent class="p-4">
              <h4 class="text-sm font-medium mb-3">Payment Details</h4>
              <div class="space-y-2">
                <div class="flex items-center gap-2">
                  <component
                    :is="withdrawal.payment_method === 'bank_transfer' ? Banknote : Phone"
                    class="w-4 h-4 text-muted-foreground"
                  />
                  <span class="text-sm font-medium">{{
                    getPaymentMethodLabel(withdrawal.payment_method)
                  }}</span>
                </div>
                <div
                  v-if="withdrawal.payment_details"
                  class="bg-muted/30 rounded-lg p-3 text-sm space-y-1"
                >
                  <div v-if="withdrawal.payment_details.account_holder">
                    <span class="text-muted-foreground">Account Holder:</span>
                    {{ withdrawal.payment_details.account_holder }}
                  </div>
                  <div v-if="withdrawal.payment_details.account_number">
                    <span class="text-muted-foreground">Account Number:</span>
                    {{ withdrawal.payment_details.account_number }}
                  </div>
                  <div v-if="withdrawal.payment_details.bank_name">
                    <span class="text-muted-foreground">Bank Name:</span>
                    {{ withdrawal.payment_details.bank_name }}
                  </div>
                  <div v-if="withdrawal.payment_details.phone_number">
                    <span class="text-muted-foreground">Phone Number:</span>
                    {{ withdrawal.payment_details.phone_number }}
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Transaction ID (for approve) -->
          <div v-if="withdrawal.status === 'pending'" class="space-y-2">
            <label class="text-sm font-medium">Transaction ID *</label>
            <input
              v-model="form.transaction_id"
              type="text"
              class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-teal/20"
              :class="{ 'border-red-500': errors.transaction_id }"
              placeholder="Enter transaction ID/reference"
            />
            <p v-if="errors.transaction_id" class="text-xs text-red-600">
              {{ errors.transaction_id }}
            </p>
          </div>

          <!-- Proof Images -->
          <div class="space-y-2">
            <div class="flex items-center justify-between">
              <label class="text-sm font-medium">Proof Images</label>
              <div class="flex items-center gap-2 text-xs text-muted-foreground">
                <span v-if="existingProofCount > 0">
                  Existing: {{ existingProofCount }}
                </span>
                <span v-if="newProofCount > 0" class="text-green-600">
                  New: {{ newProofCount }}
                </span>
              </div>
            </div>

            <!-- Images Grid -->
            <div v-if="previewImages.length > 0" class="grid grid-cols-4 gap-2">
              <div
                v-for="(img, index) in previewImages"
                :key="index"
                class="relative group"
              >
                <img :src="img.url" class="w-full h-24 object-cover rounded-lg border" />
                <div class="absolute top-1 left-1">
                  <span
                    v-if="img.isExisting"
                    class="text-[10px] bg-blue-500 text-white px-1.5 py-0.5 rounded"
                  >
                    Existing
                  </span>
                  <span
                    v-else
                    class="text-[10px] bg-green-500 text-white px-1.5 py-0.5 rounded"
                  >
                    New
                  </span>
                </div>
                <button
                  @click="removeImage(index)"
                  class="absolute top-1 right-1 p-1 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
                >
                  <X class="w-3 h-3" />
                </button>
              </div>
            </div>

            <!-- Empty State -->
            <div
              v-else
              class="text-center py-6 bg-muted/20 rounded-lg border-2 border-dashed"
            >
              <Image class="w-8 h-8 mx-auto text-muted-foreground/50" />
              <p class="text-sm text-muted-foreground mt-1">No images uploaded yet</p>
            </div>

            <!-- Upload Button -->
            <div class="flex items-center gap-2">
              <label class="cursor-pointer">
                <div
                  class="flex items-center gap-2 px-4 py-2 border-2 border-dashed rounded-lg hover:border-brand-teal transition-colors"
                >
                  <Upload class="w-4 h-4" />
                  <span class="text-sm">{{
                    previewImages.length > 0 ? "Upload More Images" : "Upload Images"
                  }}</span>
                </div>
                <input
                  type="file"
                  accept="image/*"
                  multiple
                  class="hidden"
                  @change="handleFileUpload"
                />
              </label>
              <span class="text-xs text-muted-foreground"
                >PNG, JPG, WEBP up to 5MB each</span
              >
            </div>
          </div>

          <!-- Admin Notes -->
          <div class="space-y-2">
            <label class="text-sm font-medium">Admin Notes</label>
            <textarea
              v-model="form.admin_notes"
              rows="3"
              class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-teal/20"
              placeholder="Add admin notes..."
            />
          </div>

          <!-- Rejection Reason -->
          <div v-if="withdrawal.status === 'pending'" class="space-y-2">
            <label class="text-sm font-medium">Rejection Reason *</label>
            <textarea
              v-model="form.reason"
              rows="2"
              class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-teal/20"
              :class="{ 'border-red-500': errors.reason }"
              placeholder="Why is this being rejected?"
            />
            <p v-if="errors.reason" class="text-xs text-red-600">{{ errors.reason }}</p>
          </div>

          <!-- Status Info -->
          <div
            v-if="withdrawal.processed_at"
            class="flex items-center gap-2 text-sm text-muted-foreground"
          >
            <Clock class="w-4 h-4" />
            Processed: {{ new Date(withdrawal.processed_at).toLocaleString() }}
          </div>
          <div
            v-if="withdrawal.confirmed_at"
            class="flex items-center gap-2 text-sm text-green-600"
          >
            <Check class="w-4 h-4" />
            Confirmed by user: {{ new Date(withdrawal.confirmed_at).toLocaleString() }}
          </div>
        </div>
      </div>

      <!-- Footer -->
      <DialogFooter
        class="flex-shrink-0 pt-4 border-t border-gray-200 dark:border-gray-700 mt-4"
      >
        <div class="flex items-center justify-between w-full">
          <div class="flex items-center gap-2">
            <!-- Pending: Show Approve, Reject -->
            <template v-if="withdrawal?.status === 'pending'">
              <AppButton
                size="sm"
                variant="success"
                :processing="isProcessing"
                label="Approve"
                @click="approveWithdrawal"
              />
              <AppButton
                size="sm"
                variant="destructive"
                :processing="isProcessing"
                label="Reject"
                @click="rejectWithdrawal"
              />
            </template>

            <!-- Approved: Show Complete -->
            <template v-if="withdrawal?.status === 'approved'">
              <AppButton
                size="sm"
                variant="success"
                :processing="isProcessing"
                label="Mark as Completed"
                @click="completeWithdrawal"
              />
            </template>

            <!-- Completed/Rejected: Show info -->
            <span
              v-if="withdrawal?.status === 'completed'"
              class="text-sm text-green-600"
            >
              ✅ Withdrawal Completed
            </span>
            <span v-if="withdrawal?.status === 'rejected'" class="text-sm text-red-600">
              ❌ Withdrawal Rejected
            </span>
          </div>

          <AppButton
            size="sm"
            variant="outline"
            label="Close"
            @click="closeModal"
            :disabled="isProcessing"
          />
        </div>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: var(--brand-teal, #00c2bb);
  border-radius: 3px;
  opacity: 0.5;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  opacity: 0.8;
}
</style>
