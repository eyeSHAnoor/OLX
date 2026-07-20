<!-- components/GiftFloatingIcon.vue -->
<template>
  <div v-if="isCampaignActive" class="fixed bottom-20 md:bottom-8 right-4 z-50">
    <!-- Floating Icon -->
    <div @click="togglePopup" class="relative cursor-pointer group">
      <div class="absolute -top-1 -right-1">
        <span class="flex h-3 w-3">
          <span
            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-orange opacity-75"
          ></span>
          <span class="relative inline-flex rounded-full h-3 w-3 bg-brand-orange"></span>
        </span>
      </div>
      <div
        class="w-14 h-14 md:w-16 md:h-16 bg-gradient-to-br from-brand-orange/70 to-brand-orange/90 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center group-hover:scale-110"
      >
        <Icon icon="mdi:gift" class="text-white text-2xl md:text-3xl" />
      </div>
      <div
        class="absolute -bottom-1 left-1/2 -translate-x-1/2 bg-brand-orange text-white text-[10px] font-bold px-2 py-0.5 rounded-full"
      >
        {{ totalCandidates }}
      </div>
    </div>

    <!-- Popup Modal -->
    <Teleport to="body">
      <div
        v-if="showPopup"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        @click.self="closePopup"
      >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>

        <!-- Popup Content -->
        <div
          class="relative bg-white rounded-xl max-w-3xl w-full max-h-[85vh] overflow-hidden shadow-2xl animate-scaleIn"
        >
          <!-- Header -->
          <div class="bg-brand-blue px-6 py-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div
                  class="w-9 h-9 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm"
                >
                  <Icon icon="mdi:gift" class="text-white text-lg" />
                </div>
                <div>
                  <h3 class="text-white font-semibold text-base">Gift Campaign</h3>
                  <p class="text-blue-100 text-xs">{{ campaignName }}</p>
                </div>
              </div>
              <button
                @click="closePopup"
                class="text-white/70 hover:text-white transition p-1 rounded-full hover:bg-white/10"
              >
                <Icon icon="mdi:close" class="text-2xl" />
              </button>
            </div>
          </div>

          <!-- Stats - Minimal -->
          <div
            class="grid grid-cols-3 gap-2 px-6 py-3 bg-gray-50 border-b border-gray-100"
          >
            <div class="text-center">
              <p class="text-xl font-semibold text-brand-blue">{{ totalCandidates }}</p>
              <p class="text-[10px] text-gray-500 uppercase tracking-wide">Candidates</p>
            </div>
            <div class="text-center">
              <p class="text-xl font-semibold text-green-600">{{ totalDelivered }}</p>
              <p class="text-[10px] text-gray-500 uppercase tracking-wide">Delivered</p>
            </div>
            <div class="text-center">
              <p class="text-xl font-semibold text-gray-700">{{ campaignDays }}</p>
              <p class="text-[10px] text-gray-500 uppercase tracking-wide">Days Active</p>
            </div>
          </div>

          <!-- Status Badge -->
          <div
            v-if="isCurrentUserCandidate"
            class="px-6 py-2 bg-blue-50 border-b border-blue-100"
          >
            <div class="flex items-center justify-center gap-2">
              <span class="w-1.5 h-1.5 bg-brand-blue rounded-full animate-pulse"></span>
              <span class="text-xs font-medium text-brand-blue">You're a Candidate!</span>
            </div>
          </div>

          <!-- Tabs - Minimal -->
          <div class="border-b border-gray-100">
            <div class="flex px-6">
              <button
                @click="activeTab = 'candidates'"
                class="py-2.5 px-3 text-xs font-medium border-b-2 transition"
                :class="
                  activeTab === 'candidates'
                    ? 'border-brand-blue/90 text-brand-blue'
                    : 'border-transparent text-gray-400 hover:text-gray-600'
                "
              >
                Candidates ({{ candidates.length }})
              </button>
              <button
                @click="activeTab = 'delivered'"
                class="py-2.5 px-3 text-xs font-medium border-b-2 transition"
                :class="
                  activeTab === 'delivered'
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-400 hover:text-gray-600'
                "
              >
                Delivered ({{ deliveredUsers.length }})
              </button>
            </div>
          </div>

          <!-- Content -->
          <div class="overflow-y-auto max-h-[45vh] p-4">
            <!-- Candidates Tab -->
            <div v-if="activeTab === 'candidates'">
              <div v-if="candidates.length === 0" class="text-center py-8">
                <Icon
                  icon="mdi:account-group"
                  class="text-3xl text-gray-300 mx-auto mb-2"
                />
                <p class="text-xs text-gray-400">No candidates yet</p>
              </div>
              <div v-else class="space-y-1.5">
                <div
                  v-for="candidate in candidates"
                  :key="candidate.id"
                  class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 transition"
                >
                  <img
                    v-if="candidate.user_avatar"
                    :src="candidate.user_avatar"
                    class="w-8 h-8 rounded-full object-cover"
                  />
                  <div
                    v-else
                    class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0"
                  >
                    <span class="text-brand-blue font-semibold text-xs">
                      {{ getInitials(candidate.user_name) }}
                    </span>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">
                      {{ candidate.user_name }}
                    </p>
                    <p class="text-[10px] text-gray-400 truncate">
                      {{ candidate.gift_name }}
                    </p>
                  </div>
                  <span class="text-[10px] text-gray-400 flex-shrink-0">
                    {{ formatDate(candidate.assigned_at) }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Delivered Tab -->
            <div v-if="activeTab === 'delivered'">
              <div v-if="deliveredUsers.length === 0" class="text-center py-8">
                <Icon
                  icon="mdi:package-variant"
                  class="text-3xl text-gray-300 mx-auto mb-2"
                />
                <p class="text-xs text-gray-400">No deliveries yet</p>
              </div>
              <div v-else class="space-y-1.5">
                <div
                  v-for="user in deliveredUsers"
                  :key="user.id"
                  class="flex items-center gap-2 p-2 rounded-lg hover:bg-green-50 transition"
                >
                  <img
                    v-if="user.user_avatar"
                    :src="user.user_avatar"
                    class="w-8 h-8 rounded-full object-cover"
                  />
                  <div
                    v-else
                    class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0"
                  >
                    <span class="text-green-600 font-semibold text-xs">
                      {{ getInitials(user.user_name) }}
                    </span>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">
                      {{ user.user_name }}
                    </p>
                    <p class="text-[10px] text-gray-400 truncate">
                      {{ user.gift_name }}
                    </p>
                  </div>
                  <span
                    class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] font-medium bg-green-100 text-green-700 flex-shrink-0"
                  >
                    {{ user.status }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer - Minimal -->
          <div class="border-t border-gray-100 px-6 py-2.5 bg-gray-50">
            <div class="flex items-center justify-between text-[10px] text-gray-400">
              <span>Active until {{ formatDate(campaignEndDate) }}</span>
              <span
                v-if="isCurrentUserCandidate"
                class="text-brand-blue font-medium text-[10px]"
              >
                🎯 You're in the running!
              </span>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { Icon } from "@iconify/vue";
import axios from "axios";

const showPopup = ref(false);
const activeTab = ref("candidates");
const campaignData = ref(null);
const candidates = ref([]);
const deliveredUsers = ref([]);
const isCurrentUserCandidate = ref(false);
const loading = ref(false);
let refreshInterval = null;

// Computed
const isCampaignActive = computed(() => {
  return campaignData.value !== null;
});

const campaignName = computed(() => {
  return campaignData.value?.name || "Gift Campaign";
});

const campaignEndDate = computed(() => {
  return campaignData.value?.end_date || null;
});

const totalCandidates = computed(() => {
  return candidates.value.length;
});

const totalDelivered = computed(() => {
  return deliveredUsers.value.length;
});

const campaignDays = computed(() => {
  if (!campaignData.value) return 0;
  const start = new Date(campaignData.value.start_date);
  const now = new Date();
  const diffTime = Math.abs(now - start);
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  return diffDays;
});

// Methods
const getInitials = (name) => {
  if (!name) return "?";
  return name
    .split(" ")
    .map((n) => n[0])
    .join("")
    .toUpperCase()
    .slice(0, 2);
};

const formatDate = (date) => {
  if (!date) return "N/A";
  return new Date(date).toLocaleDateString("en-US", {
    month: "short",
    day: "numeric",
    year: "numeric",
  });
};

const togglePopup = () => {
  showPopup.value = !showPopup.value;
  if (showPopup.value) {
    fetchData();
  }
};

const closePopup = () => {
  showPopup.value = false;
};

const fetchCampaign = async () => {
  try {
    const response = await axios.get("/gift/active-campaign");
    if (response.data.success) {
      campaignData.value = response.data.data;
      return response.data.data;
    }
    return null;
  } catch (error) {
    console.error("Error fetching campaign:", error);
    return null;
  }
};

const fetchCandidates = async () => {
  try {
    const response = await axios.get("/gift/candidates");
    if (response.data.success) {
      candidates.value = response.data.data.candidates || [];
      isCurrentUserCandidate.value =
        response.data.data.is_current_user_candidate || false;
    }
  } catch (error) {
    console.error("Error fetching candidates:", error);
  }
};

const fetchDeliveredUsers = async () => {
  try {
    const response = await axios.get("/gift/delivered-users");
    if (response.data.success) {
      deliveredUsers.value = response.data.data.delivered_users || [];
    }
  } catch (error) {
    console.error("Error fetching delivered users:", error);
  }
};

const fetchData = async () => {
  if (loading.value) return;
  loading.value = true;
  try {
    await Promise.all([fetchCampaign(), fetchCandidates(), fetchDeliveredUsers()]);
  } finally {
    loading.value = false;
  }
};

const initialize = async () => {
  const campaign = await fetchCampaign();
  if (campaign) {
    await fetchCandidates();
    await fetchDeliveredUsers();
  }
};

// Lifecycle
onMounted(() => {
  initialize();
  refreshInterval = setInterval(() => {
    if (isCampaignActive.value) {
      fetchCandidates();
      fetchDeliveredUsers();
    }
  }, 60000);
});

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval);
  }
});
</script>

<style scoped>
@keyframes scaleIn {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(10px);
  }

  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.animate-scaleIn {
  animation: scaleIn 0.2s ease-out;
}

.overflow-y-auto::-webkit-scrollbar {
  width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}
</style>
