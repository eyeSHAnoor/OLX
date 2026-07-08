<script setup lang="ts">
import { InertiaPageProps } from "@/types";
import { usePage, router, useForm } from "@inertiajs/vue3";
import { computed, ref, onMounted } from "vue";
import Layout from "@/layouts/AppLayout.vue";
import { useAlertDialog } from "@/composables/useAlertDialog";
import {
  Gift,
  Users,
  CheckCircle,
  Clock,
  Search,
  Shuffle,
  Sparkles,
  X,
} from "lucide-vue-next";

defineOptions({ layout: Layout });

interface EligibleUser {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  profile: {
    avatar: string | null;
    city: string | null;
  } | null;
  current_plan: string;
  subscription_started: string;
  subscription_ends: string;
  total_subscription_months: number;
}

interface CampaignGift {
  id: number;
  gift_id: number;
  name: string;
  image: string | null;
  description: string | null;
  allocated: number;
  remaining: number;
  notes: string | null;
}

interface AutoAssignPlan {
  gift_id: number;
  gift_name: string;
  user_count: number;
  users: EligibleUser[];
}

interface PageProps extends InertiaPageProps {
  period: App.Data.GiftPeriodData;
  eligibleUsers: EligibleUser[];
  campaignGifts: CampaignGift[];
  assignedUserIds: number[];
  assignedCount: number;
  totalEligible: number;
}

const page = usePage<PageProps>();
const period = computed(() => page.props.period);
const eligibleUsers = computed(() => page.props.eligibleUsers);
const campaignGifts = computed(() => page.props.campaignGifts);
const assignedUserIds = computed(() => page.props.assignedUserIds);

// Search and filter
const searchQuery = ref("");
const selectedGiftId = ref<number | null>(null);
const selectedUserIds = ref<number[]>([]);
const selectAll = ref(false);

// Smart Auto-Assign state
const showSmartAssignModal = ref(false);
const smartAssignPlans = ref<AutoAssignPlan[]>([]);
const isShuffling = ref(false);
const showShuffleAnimation = ref(false);

// Filtered users
const filteredUsers = computed(() => {
  let users = eligibleUsers.value;

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    users = users.filter(
      (user) =>
        user.name.toLowerCase().includes(query) ||
        user.email.toLowerCase().includes(query) ||
        user.phone?.toLowerCase().includes(query)
    );
  }

  return users;
});

// Available users (not yet assigned)
const availableUsers = computed(() => {
  return filteredUsers.value.filter((user) => !assignedUserIds.value.includes(user.id));
});

const assignedUsersList = computed(() => {
  return eligibleUsers.value.filter((user) => assignedUserIds.value.includes(user.id));
});

// Fisher-Yates shuffle algorithm
const shuffleArray = <T>(array: T[]): T[] => {
  const shuffled = [...array];
  for (let i = shuffled.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
  }
  return shuffled;
};

// Generate smart auto-assign plan
const generateSmartAssignPlan = () => {
  isShuffling.value = true;
  showShuffleAnimation.value = true;

  // Simulate shuffling animation delay
  setTimeout(() => {
    // Get available users (not yet assigned)
    const unassignedUsers = eligibleUsers.value.filter(
      (user) => !assignedUserIds.value.includes(user.id)
    );

    // Get gifts with remaining quantity
    const availableGifts = campaignGifts.value.filter((gift) => gift.remaining > 0);

    if (unassignedUsers.length === 0) {
      alert("No unassigned users available!");
      isShuffling.value = false;
      showShuffleAnimation.value = false;
      return;
    }

    if (availableGifts.length === 0) {
      alert("No gifts available with remaining quantity!");
      isShuffling.value = false;
      showShuffleAnimation.value = false;
      return;
    }

    // Shuffle the users randomly
    const shuffledUsers = shuffleArray(unassignedUsers);

    // Calculate how many users each gift can get
    const plans: AutoAssignPlan[] = [];
    let userIndex = 0;

    // Distribute users across gifts based on remaining quantities
    for (const gift of availableGifts) {
      const userCount = Math.min(gift.remaining, shuffledUsers.length - userIndex);

      if (userCount > 0) {
        const assignedUsers = shuffledUsers.slice(userIndex, userIndex + userCount);
        plans.push({
          gift_id: gift.gift_id,
          gift_name: gift.name,
          user_count: userCount,
          users: assignedUsers,
        });
        userIndex += userCount;
      }
    }

    smartAssignPlans.value = plans;
    isShuffling.value = false;

    // Hide shuffle animation after a brief moment
    setTimeout(() => {
      showShuffleAnimation.value = false;
    }, 500);

    showSmartAssignModal.value = true;
  }, 1500); // 1.5 second shuffle animation
};

// Execute smart auto-assign
const executeSmartAssign = async () => {
  const alert = useAlertDialog();
  const totalUsers = smartAssignPlans.value.reduce(
    (sum, plan) => sum + plan.user_count,
    0
  );

  const confirmed = await alert.show({
    title: "Confirm Smart Auto-Assign",
    description: `This will assign gifts to ${totalUsers} users across ${smartAssignPlans.value.length} gift types. Continue?`,
    confirmText: "Yes, Assign All",
    cancelText: "Cancel",
  });

  if (!confirmed) return;

  // Process each plan sequentially
  for (const plan of smartAssignPlans.value) {
    if (plan.user_count === 0) continue;

    try {
      await router.post(
        route("gift-campaigns.assign-gifts", period.value.id),
        {
          user_ids: plan.users.map((u) => u.id),
          gift_id: plan.gift_id,
          notes: "Auto-assigned via Smart Distribution",
        },
        {
          preserveScroll: true,
          preserveState: true,
          onSuccess: () => {
            // console.log(`Assigned ${plan.gift_name} to ${plan.user_count} users`);
          },
        }
      );
    } catch (error) {
      console.error(`Failed to assign ${plan.gift_name}:`, error);
    }
  }

  showSmartAssignModal.value = false;
  smartAssignPlans.value = [];
};

// Toggle select all
const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedUserIds.value = availableUsers.value.map((u) => u.id);
  } else {
    selectedUserIds.value = [];
  }
};

// Toggle individual user selection
const toggleUser = (userId: number) => {
  const index = selectedUserIds.value.indexOf(userId);
  if (index > -1) {
    selectedUserIds.value.splice(index, 1);
  } else {
    selectedUserIds.value.push(userId);
  }

  // Update selectAll state
  selectAll.value = selectedUserIds.value.length === availableUsers.value.length;
};

// Get selected gift
const selectedGift = computed(() => {
  return campaignGifts.value.find((g) => g.gift_id === selectedGiftId.value);
});

// Assign gifts form
const assignForm = useForm({
  user_ids: [] as number[],
  gift_id: null as number | null,
  notes: "",
});

const assignGifts = () => {
  if (selectedUserIds.value.length === 0) {
    alert("Please select at least one user");
    return;
  }
  if (!selectedGiftId.value) {
    alert("Please select a gift");
    return;
  }

  assignForm.user_ids = selectedUserIds.value;
  assignForm.gift_id = selectedGiftId.value;

  assignForm.post(route("gift-campaigns.assign-gifts", period.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      selectedUserIds.value = [];
      selectedGiftId.value = null;
      selectAll.value = false;
      assignForm.reset();
    },
  });
};

// Bulk assign
const bulkAssignForm = useForm({
  gift_id: null as number | null,
  notes: "",
});

const showBulkAssignModal = ref(false);

const bulkAssign = () => {
  if (!bulkAssignForm.gift_id) {
    alert("Please select a gift");
    return;
  }

  bulkAssignForm.post(route("gift-campaigns.bulk-assign", period.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      showBulkAssignModal.value = false;
      bulkAssignForm.reset();
    },
  });
};

// Breadcrumbs
const { set, resetList } = useBreadcrumb();
onMounted(() => {
  resetList();
  set([
    { label: "Home", href: "/dashboard" },
    { label: "Gift Campaigns", href: route("gift-campaigns.index") },
    {
      label: period.value?.name || "Campaign",
      href: route("gift-campaigns.eligible-users", period.value?.id),
    },
    { label: "Eligible Users", href: "#" },
  ]);
});

// Format date
const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
};
</script>

<template>
  <AppContainer>

    <Head :title="`Eligible Users - ${period?.name}`" />

    <PageHeading>
      <template #title>{{ period?.name }} - Eligible Users</template>
      <template #subtitle> Users with 4+ months of continuous subscription </template>
      <template #links>
        <div class="flex items-center gap-2">
          <Button @click="generateSmartAssignPlan" variant="outline" size="sm"
            :disabled="isShuffling || availableUsers.length === 0" class="relative">
            <Sparkles class="size-4 mr-2" />
            Smart Auto-Assign
          </Button>
          <Button as-child size="sm" variant="outline" @click="router.visit(route('gift-campaigns.index'))">
            <Icon icon="lucide:arrow-left" class="size-4" /> Back to Campaigns
          </Button>
        </div>
      </template>
    </PageHeading>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
      <Card>
        <CardContent class="p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-muted-foreground">Total Eligible</p>
              <p class="text-2xl font-bold">{{ totalEligible }}</p>
            </div>
            <div class="size-12 rounded-full bg-blue-100 flex items-center justify-center">
              <Users class="size-6 text-blue-600" />
            </div>
          </div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-muted-foreground">Already Assigned</p>
              <p class="text-2xl font-bold text-green-600">{{ assignedCount }}</p>
            </div>
            <div class="size-12 rounded-full bg-green-100 flex items-center justify-center">
              <CheckCircle class="size-6 text-green-600" />
            </div>
          </div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-muted-foreground">Remaining</p>
              <p class="text-2xl font-bold text-orange-600">
                {{ totalEligible - assignedCount }}
              </p>
            </div>
            <div class="size-12 rounded-full bg-orange-100 flex items-center justify-center">
              <Clock class="size-6 text-orange-600" />
            </div>
          </div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-muted-foreground">Available Gifts</p>
              <p class="text-2xl font-bold text-purple-600">
                {{ campaignGifts?.length || 0 }}
              </p>
            </div>
            <div class="size-12 rounded-full bg-purple-100 flex items-center justify-center">
              <Gift class="size-6 text-purple-600" />
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Shuffle Loading Overlay -->
    <div v-if="showShuffleAnimation" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div class="text-center">
        <div class="relative inline-block mb-4">
          <Shuffle class="size-16 text-white animate-bounce" />
        </div>
        <p class="text-white text-lg font-semibold">Shuffling & Distributing Gifts...</p>
        <p class="text-white/80 text-sm mt-2">
          Randomly assigning gifts to eligible users
        </p>
        <div class="mt-4 flex justify-center gap-1">
          <span class="size-3 bg-white rounded-full animate-bounce" style="animation-delay: 0s"></span>
          <span class="size-3 bg-white rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
          <span class="size-3 bg-white rounded-full animate-bounce" style="animation-delay: 0.4s"></span>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mt-6">
      <!-- Users List -->
      <div class="lg:col-span-3">
        <Card>
          <CardHeader>
            <div class="flex items-center justify-between">
              <div>
                <CardTitle>Eligible Users</CardTitle>
                <CardDescription>
                  {{ availableUsers.length }} users available for assignment
                </CardDescription>
              </div>
              <div class="flex items-center gap-2">
                <Button @click="generateSmartAssignPlan" variant="outline" size="sm"
                  :disabled="isShuffling || availableUsers.length === 0">
                  <Sparkles class="size-4 mr-2" />
                  Smart Auto-Assign
                </Button>
                <Button @click="showBulkAssignModal = true" variant="outline" size="sm"
                  :disabled="availableUsers.length === 0">
                  <Gift class="size-4 mr-2" />
                  Bulk Assign All
                </Button>
              </div>
            </div>
          </CardHeader>
          <CardContent>
            <!-- Search -->
            <div class="mb-4">
              <div class="relative">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
                <input v-model="searchQuery" type="text" placeholder="Search users by name, email or phone..."
                  class="w-full pl-10 pr-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary" />
              </div>
            </div>

            <!-- Select All -->
            <div class="flex items-center gap-2 mb-4 p-2 bg-muted/50 rounded">
              <input type="checkbox" v-model="selectAll" @change="toggleSelectAll"
                class="rounded border-gray-300 text-primary focus:ring-primary"
                :disabled="availableUsers.length === 0" />
              <span class="text-sm font-medium">
                Select All ({{ selectedUserIds.length }} selected)
              </span>
            </div>

            <!-- Users Table -->
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead>
                  <tr class="border-b text-left text-sm">
                    <th class="p-2 w-10"></th>
                    <th class="p-2">User</th>
                    <th class="p-2">Contact</th>
                    <th class="p-2">Plan</th>
                    <th class="p-2">Subscription Period</th>
                    <th class="p-2">Months</th>
                    <th class="p-2">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="user in filteredUsers" :key="user.id" :class="{
                    'bg-green-50': assignedUserIds.includes(user.id),
                    'hover:bg-muted/50': !assignedUserIds.includes(user.id),
                  }" class="border-b text-sm">
                    <td class="p-2">
                      <input type="checkbox" :checked="selectedUserIds.includes(user.id)" @change="toggleUser(user.id)"
                        :disabled="assignedUserIds.includes(user.id)"
                        class="rounded border-gray-300 text-primary focus:ring-primary disabled:opacity-50" />
                    </td>
                    <td class="p-2">
                      <div class="flex items-center gap-2">
                        <div class="size-8 rounded-full bg-primary/10 flex items-center justify-center overflow-hidden">
                          <img v-if="user.profile?.avatar" :src="`/storage/${user.profile.avatar}`" :alt="user.name"
                            class="w-full h-full object-cover" />
                          <span v-else class="text-xs font-medium text-primary">
                            {{ user.name.charAt(0).toUpperCase() }}
                          </span>
                        </div>
                        <div>
                          <p class="font-medium">{{ user.name }}</p>
                        </div>
                      </div>
                    </td>
                    <td class="p-2">
                      <div class="text-xs space-y-1">
                        <p>{{ user.email }}</p>
                        <p v-if="user.phone" class="text-muted-foreground">
                          {{ user.phone }}
                        </p>
                      </div>
                    </td>
                    <td class="p-2">
                      <span
                        class="inline-flex items-center px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                        {{ user.current_plan }}
                      </span>
                    </td>
                    <td class="p-2">
                      <div class="text-xs space-y-1">
                        <p>From: {{ formatDate(user.subscription_started) }}</p>
                        <p>To: {{ formatDate(user.subscription_ends) }}</p>
                      </div>
                    </td>
                    <td class="p-2">
                      <span class="font-semibold text-green-600">
                        {{ user.total_subscription_months }} months
                      </span>
                    </td>
                    <td class="p-2">
                      <span v-if="assignedUserIds.includes(user.id)"
                        class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                        <CheckCircle class="size-3 mr-1" />
                        Assigned
                      </span>
                      <span v-else
                        class="inline-flex items-center px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">
                        <Clock class="size-3 mr-1" />
                        Pending
                      </span>
                    </td>
                  </tr>
                  <tr v-if="filteredUsers.length === 0">
                    <td colspan="7" class="p-8 text-center text-muted-foreground">
                      No eligible users found
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Sidebar - Assignment Panel -->
      <div class="space-y-6">
        <!-- Gift Selection -->
        <Card>
          <CardHeader>
            <CardTitle>Assign Gift</CardTitle>
            <CardDescription> Select a gift and assign to chosen users </CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <!-- Gift Selection -->
            <div>
              <label class="text-sm font-medium block mb-2">Select Gift *</label>
              <select v-model="selectedGiftId"
                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                <option :value="null">Choose a gift</option>
                <option v-for="gift in campaignGifts" :key="gift.gift_id" :value="gift.gift_id">
                  {{ gift.name }} ({{ gift.remaining }} remaining)
                </option>
              </select>
            </div>

            <!-- Selected Gift Details -->
            <div v-if="selectedGift" class="p-3 bg-muted/50 rounded-lg">
              <div class="flex items-center gap-3">
                <div class="size-12 rounded-lg overflow-hidden bg-muted">
                  <img v-if="selectedGift.image" :src="`/storage/${selectedGift.image}`" :alt="selectedGift.name"
                    class="w-full h-full object-cover" />
                  <div v-else class="w-full h-full flex items-center justify-center">
                    <Gift class="size-6 text-muted-foreground" />
                  </div>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="font-medium text-sm truncate">{{ selectedGift.name }}</p>
                  <p class="text-xs text-muted-foreground">
                    Allocated: {{ selectedGift.allocated }} | Remaining:
                    {{ selectedGift.remaining }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Notes -->
            <div>
              <label class="text-sm font-medium block mb-2">Notes (optional)</label>
              <textarea v-model="assignForm.notes" rows="2"
                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                placeholder="Any notes for this assignment..."></textarea>
            </div>

            <!-- Summary -->
            <div class="p-3 bg-blue-50 rounded-lg space-y-2">
              <div class="flex justify-between text-sm">
                <span>Selected Users:</span>
                <span class="font-semibold">{{ selectedUserIds.length }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span>Selected Gift:</span>
                <span class="font-semibold">{{ selectedGift?.name || "None" }}</span>
              </div>
            </div>

            <!-- Assign Button -->
            <AppButton label="Assign Gifts" icon="lucide:gift" :processing="assignForm.processing" @click="assignGifts"
              :disabled="selectedUserIds.length === 0 || !selectedGiftId"
              class="bg-brand-orange hover:bg-brand-orange/80 w-full justify-center" />
          </CardContent>
        </Card>

        <!-- Campaign Gifts Overview -->
        <Card>
          <CardHeader>
            <CardTitle>Campaign Gifts</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-3">
              <div v-for="gift in campaignGifts" :key="gift.gift_id" class="p-3 border rounded-lg">
                <div class="flex items-center gap-3">
                  <div class="size-10 rounded-lg overflow-hidden bg-muted flex-shrink-0">
                    <img v-if="gift.image" :src="`/storage/${gift.image}`" :alt="gift.name"
                      class="w-full h-full object-cover" />
                    <div v-else class="w-full h-full flex items-center justify-center">
                      <Gift class="size-5 text-muted-foreground" />
                    </div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="font-medium text-sm truncate">{{ gift.name }}</p>
                    <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                      <div class="bg-primary h-1.5 rounded-full" :style="{
                        width:
                          gift.allocated > 0
                            ? ((gift.allocated - gift.remaining) / gift.allocated) *
                            100 +
                            '%'
                            : '0%',
                      }"></div>
                    </div>
                    <p class="text-xs text-muted-foreground mt-1">
                      {{ gift.remaining }} / {{ gift.allocated }} remaining
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Assigned Users List -->
        <Card v-if="assignedUsersList.length > 0">
          <CardHeader>
            <CardTitle>Assigned Users</CardTitle>
            <CardDescription>
              {{ assignedUsersList.length }} users assigned
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div class="space-y-2 max-h-60 overflow-y-auto">
              <div v-for="user in assignedUsersList" :key="user.id"
                class="flex items-center gap-2 p-2 bg-green-50 rounded">
                <CheckCircle class="size-3 text-green-600 flex-shrink-0" />
                <span class="text-sm truncate">{{ user.name }}</span>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Smart Auto-Assign Modal -->
    <div v-if="showSmartAssignModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="absolute inset-0 bg-black/50" @click="showSmartAssignModal = false"></div>
      <div class="relative bg-background rounded-lg shadow-lg p-6 w-full max-w-2xl mx-4 max-h-[80vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-lg font-semibold flex items-center gap-2">
              <Sparkles class="size-5 text-yellow-500" />
              Smart Auto-Assign Plan
            </h2>
            <p class="text-sm text-muted-foreground mt-1">
              Randomly shuffled and distributed based on gift quantities
            </p>
          </div>
          <button @click="showSmartAssignModal = false" class="text-muted-foreground hover:text-foreground">
            <X class="size-5" />
          </button>
        </div>

        <!-- Total Summary -->
        <div class="p-4 bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg mb-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-muted-foreground">Total Users to Assign</p>
              <p class="text-2xl font-bold text-purple-600">
                {{smartAssignPlans.reduce((sum, p) => sum + p.user_count, 0)}}
              </p>
            </div>
            <div>
              <p class="text-sm text-muted-foreground">Gift Types Used</p>
              <p class="text-2xl font-bold text-blue-600">
                {{ smartAssignPlans.length }}
              </p>
            </div>
          </div>
        </div>

        <!-- Per-Gift Breakdown -->
        <div class="space-y-4">
          <div v-for="(plan, index) in smartAssignPlans" :key="plan.gift_id" class="border rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
              <h3 class="font-semibold flex items-center gap-2">
                <Gift class="size-4 text-primary" />
                {{ plan.gift_name }}
              </h3>
              <span
                class="inline-flex items-center px-2 py-1 text-xs font-medium bg-primary/10 text-primary rounded-full">
                {{ plan.user_count }} users
              </span>
            </div>

            <!-- Users list for this gift -->
            <div class="space-y-1 max-h-40 overflow-y-auto">
              <div v-for="user in plan.users" :key="user.id"
                class="flex items-center gap-2 p-2 bg-muted/30 rounded text-sm">
                <Shuffle class="size-3 text-purple-500 flex-shrink-0" />
                <span class="truncate">{{ user.name }}</span>
                <span class="text-xs text-muted-foreground ml-auto">
                  {{ user.total_subscription_months }}mo
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3 mt-6">
          <AppButton label="Cancel" variant="outline" @click="showSmartAssignModal = false" />
          <AppButton label="Execute Smart Assign" icon="lucide:sparkles" @click="executeSmartAssign"
            class="bg-purple-600 hover:bg-purple-700" />
        </div>
      </div>
    </div>

    <!-- Bulk Assign Modal -->
    <div v-if="showBulkAssignModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="absolute inset-0 bg-black/50" @click="showBulkAssignModal = false"></div>
      <div class="relative bg-background rounded-lg shadow-lg p-6 w-full max-w-md mx-4">
        <h2 class="text-lg font-semibold mb-4">Bulk Assign Gifts</h2>
        <p class="text-sm text-muted-foreground mb-4">
          This will assign gifts to all {{ availableUsers.length }} eligible users who
          haven't received gifts yet.
        </p>

        <div class="space-y-4">
          <div>
            <label class="text-sm font-medium block mb-2">Select Gift *</label>
            <select v-model="bulkAssignForm.gift_id"
              class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
              <option :value="null">Choose a gift</option>
              <option v-for="gift in campaignGifts" :key="gift.gift_id" :value="gift.gift_id">
                {{ gift.name }} ({{ gift.remaining }} remaining)
              </option>
            </select>
          </div>

          <div>
            <label class="text-sm font-medium block mb-2">Notes (optional)</label>
            <textarea v-model="bulkAssignForm.notes" rows="2"
              class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
              placeholder="Any notes for this assignment..."></textarea>
          </div>
        </div>

        <div class="flex justify-end gap-3 mt-6">
          <AppButton label="Cancel" variant="outline" @click="showBulkAssignModal = false"
            :disabled="bulkAssignForm.processing" />
          <AppButton label="Assign All" icon="lucide:gift" :processing="bulkAssignForm.processing" @click="bulkAssign"
            :disabled="!bulkAssignForm.gift_id || availableUsers.length === 0"
            class="bg-brand-orange hover:bg-brand-orange/80" />
        </div>
      </div>
    </div>
  </AppContainer>
</template>
