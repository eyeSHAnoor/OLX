<script setup lang="ts">
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogFooter,
} from "@/components/ui/dialog";
import { ref, watch, computed, onMounted, onUnmounted } from "vue";
import {
  X,
  ChevronLeft,
  ChevronRight,
  Download,
  Image,
  ZoomIn,
  ZoomOut,
} from "lucide-vue-next";

interface WithdrawalHistory {
  id: number;
  requested_amount: number;
  status: string;
  proof_images: string[];
  created_at: string;
  transaction_id: string | null;
  payment_method?: string | null;
}

const props = defineProps<{
  show: boolean;
  withdrawal: WithdrawalHistory | null;
}>();

const emit = defineEmits<{
  (e: "close"): void;
}>();

const currentIndex = ref(0);
const isZoomed = ref(false);

// Normalize and clean image paths
const imageUrls = computed(() => {
  if (!props.withdrawal?.proof_images) return [];

  // Ensure proof_images is an array
  const images = Array.isArray(props.withdrawal.proof_images)
    ? props.withdrawal.proof_images
    : [];

  // Clean and prefix paths
  return images.map((img) => {
    // Remove any duplicate or extra storage prefix
    let cleanPath = img.replace(/^\/?storage\//, "");
    // Ensure the path starts with storage
    return `/storage/${cleanPath}`;
  });
});

const hasImages = computed(() => imageUrls.value.length > 0);
const currentImage = computed(() => {
  if (!hasImages.value) return null;
  return imageUrls.value[currentIndex.value] || imageUrls.value[0];
});

// Reset state when modal opens
watch(
  () => props.show,
  (newVal) => {
    if (newVal) {
      currentIndex.value = 0;
      isZoomed.value = false;
    }
  }
);

// Navigation functions
const nextImage = () => {
  if (currentIndex.value < imageUrls.value.length - 1) {
    currentIndex.value++;
    isZoomed.value = false;
  }
};

const prevImage = () => {
  if (currentIndex.value > 0) {
    currentIndex.value--;
    isZoomed.value = false;
  }
};

const closeModal = () => {
  emit("close");
};

const toggleZoom = () => {
  isZoomed.value = !isZoomed.value;
};

// Keyboard shortcuts
const onKeydown = (e: KeyboardEvent) => {
  if (!props.show) return;
  if (e.key === "Escape") closeModal();
  if (e.key === "ArrowRight") nextImage();
  if (e.key === "ArrowLeft") prevImage();
  if (e.key === "z" || e.key === "Z") toggleZoom();
};

// Download current image
const downloadCurrentImage = async () => {
  if (!currentImage.value) return;
  try {
    const response = await fetch(currentImage.value);
    const blob = await response.blob();
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = `withdrawal-${props.withdrawal?.id}-image-${
      currentIndex.value + 1
    }.jpg`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(link.href);
  } catch (error) {
    console.error("Failed to download image:", error);
  }
};

// Download all images
const downloadAllImages = async () => {
  if (!hasImages.value) return;
  for (let i = 0; i < imageUrls.value.length; i++) {
    try {
      const response = await fetch(imageUrls.value[i]);
      const blob = await response.blob();
      const link = document.createElement("a");
      link.href = URL.createObjectURL(blob);
      link.download = `withdrawal-${props.withdrawal?.id}-image-${i + 1}.jpg`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(link.href);
      await new Promise((resolve) => setTimeout(resolve, 200));
    } catch (error) {
      console.error(`Failed to download image ${i + 1}:`, error);
    }
  }
};

// Helper functions
const getStatusLabel = (status: string) => {
  const labels: Record<string, string> = {
    pending: "Pending",
    approved: "Approved",
    completed: "Completed",
    rejected: "Rejected",
  };
  return labels[status as keyof typeof labels] || status;
};

const getStatusColor = (status: string) => {
  const colors: Record<string, string> = {
    pending: "bg-yellow-100 text-yellow-800",
    approved: "bg-blue-100 text-blue-800",
    completed: "bg-green-100 text-green-800",
    rejected: "bg-red-100 text-red-800",
  };
  return colors[status as keyof typeof colors] || "bg-gray-100 text-gray-800";
};

// Format date
const formatDate = (date: string) => {
  return new Date(date).toLocaleString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};

// Lifecycle hooks
onMounted(() => document.addEventListener("keydown", onKeydown));
onUnmounted(() => document.removeEventListener("keydown", onKeydown));
</script>

<template>
  <Dialog :open="show" @update:open="(val) => !val && closeModal()">
    <DialogContent
      class="!w-11/12 max-w-5xl max-h-[92vh] flex flex-col px-0 !rounded-2xl overflow-hidden bg-white dark:bg-gray-900"
    >
      <!-- Header -->
      <DialogHeader
        class="!px-6 !pb-3 flex-shrink-0 border-b border-gray-200 dark:border-gray-700"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <DialogTitle class="flex items-center gap-2 text-base">
              <Image class="w-4 h-4 text-brand-teal" />
              Proof Images
              <span v-if="hasImages" class="text-sm font-normal text-muted-foreground">
                ({{ imageUrls.length }})
              </span>
            </DialogTitle>
            <span
              v-if="withdrawal"
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
              :class="getStatusColor(withdrawal.status)"
            >
              {{ getStatusLabel(withdrawal.status) }}
            </span>
          </div>
          <button
            @click="closeModal"
            class="p-1.5 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
          >
            <X class="w-4 h-4" />
          </button>
        </div>
        <div
          v-if="withdrawal"
          class="text-xs text-muted-foreground mt-1 flex items-center gap-3 flex-wrap"
        >
          <span>
            <span class="font-medium">Points:</span>
            {{ withdrawal.requested_amount?.toLocaleString() || 0 }}
          </span>
          <span>•</span>
          <span>
            <span class="font-medium">Date:</span>
            {{ formatDate(withdrawal.created_at) }}
          </span>
          <span v-if="withdrawal.transaction_id">•</span>
          <span v-if="withdrawal.transaction_id" class="font-mono">
            <span class="font-medium">TXN:</span> {{ withdrawal.transaction_id }}
          </span>
          <span v-if="withdrawal.payment_method">•</span>
          <span v-if="withdrawal.payment_method">
            <span class="font-medium">Method:</span> {{ withdrawal.payment_method }}
          </span>
        </div>
      </DialogHeader>

      <!-- Image Display Area -->
      <div class="flex-1 overflow-hidden relative bg-gray-100 dark:bg-gray-800">
        <!-- No Images State -->
        <div v-if="!hasImages" class="flex items-center justify-center h-full">
          <div class="text-center">
            <Image class="w-20 h-20 mx-auto text-gray-300 dark:text-gray-600" />
            <p class="mt-3 text-gray-500 dark:text-gray-400">No proof images available</p>
            <p class="text-sm text-gray-400 dark:text-gray-500">
              This withdrawal has no uploaded images
            </p>
          </div>
        </div>

        <!-- Image Display -->
        <template v-else>
          <div
            class="flex items-center justify-center h-full p-4 cursor-pointer select-none"
            @click="toggleZoom"
          >
            <img
              :src="currentImage"
              :alt="`Proof image ${currentIndex + 1}`"
              class="max-h-full max-w-full object-contain transition-transform duration-300"
              :class="{ 'scale-150': isZoomed }"
              draggable="false"
            />
          </div>

          <!-- Image Counter -->
          <div
            v-if="imageUrls.length > 1"
            class="absolute bottom-4 left-1/2 -translate-x-1/2"
          >
            <span class="bg-black/60 text-white text-xs px-3 py-1.5 rounded-full">
              {{ currentIndex + 1 }} / {{ imageUrls.length }}
            </span>
          </div>

          <!-- Navigation Buttons -->
          <button
            v-if="imageUrls.length > 1 && currentIndex > 0"
            @click="prevImage"
            class="absolute left-4 top-1/2 -translate-y-1/2 p-2 bg-black/50 hover:bg-black/70 text-white rounded-full transition-all hover:scale-110"
          >
            <ChevronLeft class="w-5 h-5" />
          </button>
          <button
            v-if="imageUrls.length > 1 && currentIndex < imageUrls.length - 1"
            @click="nextImage"
            class="absolute right-4 top-1/2 -translate-y-1/2 p-2 bg-black/50 hover:bg-black/70 text-white rounded-full transition-all hover:scale-110"
          >
            <ChevronRight class="w-5 h-5" />
          </button>

          <!-- Top Right Controls -->
          <div class="absolute top-4 right-4 flex items-center gap-2">
            <button
              @click="toggleZoom"
              class="p-2 bg-black/50 hover:bg-black/70 text-white rounded-lg transition-all"
              :title="isZoomed ? 'Zoom Out' : 'Zoom In'"
            >
              <ZoomIn v-if="!isZoomed" class="w-4 h-4" />
              <ZoomOut v-else class="w-4 h-4" />
            </button>
            <button
              @click="downloadCurrentImage"
              class="p-2 bg-black/50 hover:bg-black/70 text-white rounded-lg transition-all"
              title="Download Image"
            >
              <Download class="w-4 h-4" />
            </button>
          </div>

          <!-- Thumbnails -->
          <div
            v-if="imageUrls.length > 1"
            class="absolute bottom-16 left-1/2 -translate-x-1/2 flex gap-2 px-3 py-2 bg-black/40 rounded-lg backdrop-blur-sm max-w-[80%] overflow-x-auto"
          >
            <button
              v-for="(img, index) in imageUrls.slice(0, 10)"
              :key="index"
              @click="
                currentIndex = index;
                isZoomed = false;
              "
              class="w-10 h-10 rounded border-2 transition-all hover:scale-110 flex-shrink-0"
              :class="[
                currentIndex === index
                  ? 'border-brand-teal scale-110'
                  : 'border-transparent opacity-60 hover:opacity-100',
              ]"
            >
              <img
                :src="img"
                :alt="`Thumbnail ${index + 1}`"
                class="w-full h-full object-cover rounded"
              />
            </button>
            <div
              v-if="imageUrls.length > 10"
              class="flex items-center text-white text-xs px-2 flex-shrink-0"
            >
              +{{ imageUrls.length - 10 }}
            </div>
          </div>
        </template>
      </div>

      <!-- Footer -->
      <DialogFooter
        class="flex-shrink-0 px-6 py-3 border-t border-gray-200 dark:border-gray-700"
      >
        <div class="flex items-center justify-between w-full">
          <div class="flex items-center gap-2">
            <span class="text-xs text-muted-foreground">
              {{
                hasImages
                  ? `Showing ${currentIndex + 1} of ${imageUrls.length}`
                  : "No images"
              }}
            </span>
            <span v-if="hasImages" class="text-xs text-muted-foreground">•</span>
            <span v-if="hasImages" class="text-xs text-muted-foreground">
              Click image to {{ isZoomed ? "zoom out" : "zoom in" }}
            </span>
            <span v-if="hasImages" class="text-xs text-muted-foreground">•</span>
            <span v-if="hasImages" class="text-xs text-muted-foreground">
              Use arrow keys to navigate
            </span>
          </div>
          <div class="flex items-center gap-2">
            <button
              v-if="hasImages && imageUrls.length > 1"
              @click="downloadAllImages"
              class="text-xs text-brand-teal hover:underline flex items-center gap-1"
            >
              <Download class="w-3 h-3" /> Download All
            </button>
            <button
              v-if="hasImages"
              @click="downloadCurrentImage"
              class="text-xs text-brand-teal hover:underline flex items-center gap-1 ml-2"
            >
              <Download class="w-3 h-3" /> Download Current
            </button>
            <button
              @click="closeModal"
              class="ml-4 px-3 py-1 text-xs bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg transition-colors"
            >
              Close
            </button>
          </div>
        </div>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<style scoped>
:root {
  --brand-teal: #00c2bb;
}

.text-brand-teal {
  color: var(--brand-teal);
}

.border-brand-teal {
  border-color: var(--brand-teal);
}

img {
  user-select: none;
}

.dark .bg-gray-100 {
  background-color: #1a1a2e;
}

.dark .border-gray-200 {
  border-color: #2d2d44;
}

.dark .bg-black\/50 {
  background-color: rgba(0, 0, 0, 0.7);
}

.dark .bg-black\/60 {
  background-color: rgba(0, 0, 0, 0.8);
}

.dark .bg-black\/40 {
  background-color: rgba(0, 0, 0, 0.6);
}

/* Scrollbar styling for thumbnails */
.max-w-\[80\%\]::-webkit-scrollbar {
  height: 4px;
}

.max-w-\[80\%\]::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 2px;
}

.max-w-\[80\%\]::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.3);
  border-radius: 2px;
}

.max-w-\[80\%\]::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.5);
}
</style>
