<script setup lang="ts">
import { InertiaPageProps } from "@/types";
import { usePage, router, useForm, Head } from "@inertiajs/vue3";
import { ref, computed, onMounted, watch } from "vue";
import axios from "axios";
import Layout from "@/layouts/OlxLayout.vue";
import { useBreadcrumb } from "@/composables/useBreadcrumb";
import { debounce } from "lodash";
import { useTheme } from '@/Composables/useTheme'

const { theme } = useTheme()

defineOptions({ layout: Layout });

interface EligibleUser {
  id: number;
  name: string;
  email: string;
  points_balance: number;
  referral_code?: string;
}

interface PageProps extends InertiaPageProps {
  selectedUser: EligibleUser | null;
  currentUserPoints: number;
  editing: boolean;
}

const page = usePage<PageProps>();
const currentUserPoints = computed(() => page.props.currentUserPoints);
const isEditing = computed(() => page.props.editing);
const selectedUser = computed(() => page.props.selectedUser);

// Form
interface FormData {
  user_id: number | string;
  referral_code: string;
  points: number;
}

const form = useForm<FormData>({
  user_id: selectedUser.value?.id ?? "",
  referral_code: selectedUser.value?.referral_code ?? "",
  points: 0,
});

// =============================================
//  SEARCH BY EMAIL (Axios backend call)
// =============================================
const search = ref("");
const isLoading = ref(false);
const searchResult = ref<EligibleUser | null>(null);
const searchError = ref("");
const selectedUserId = ref<number | null>(selectedUser.value?.id ?? null);

const performSearch = debounce(async () => {
  const query = search.value.trim();
  if (!query) {
    searchResult.value = null;
    searchError.value = "";
    return;
  }

  isLoading.value = true;
  searchError.value = "";
  searchResult.value = null;

  try {
    const response = await axios.get(route("downline-referrals.search-by-email"), {
      params: { email: query },
    });
    if (response.data.found) {
      searchResult.value = response.data.user;
    } else {
      searchError.value = response.data.message || "No matching downline member found.";
    }
  } catch (err: any) {
    searchError.value =
      err.response?.data?.message || "Something went wrong. Please try again.";
  } finally {
    isLoading.value = false;
  }
}, 400);
watch(searchResult, (newResult) => {
  if (newResult && newResult.referral_code) {
    // Auto-fill the code in the form
    form.referral_code = newResult.referral_code;
  } else if (newResult && !newResult.referral_code) {
    // Generate a new code if they don't have one
    generateReferralCode();
  }
});
watch(search, () => {
  performSearch();
});

// Clear result when manually typing
watch(search, (newVal) => {
  if (!newVal) {
    searchResult.value = null;
    searchError.value = "";
  }
});

function selectUser(user: UserData) {
  selectedUserId.value = user.id;
  form.user_id = user.id;

  // Auto-fill the referral code if it exists
  if (user.referral_code) {
    form.referral_code = user.referral_code;
  } else {
    // Generate a new code if they don't have one
    generateReferralCode();
  }

  search.value = "";
  searchResult.value = null;
}

// Clear selection
function clearSelection() {
  selectedUserId.value = null;
  form.user_id = "";
  searchResult.value = null;
  searchError.value = "";
}

// Generate referral code
const generateReferralCode = () => {
  const timestamp = Date.now().toString(36).toUpperCase();
  const random = Math.random().toString(36).substring(2, 5).toUpperCase();
  form.referral_code = `REF${timestamp}${random}`;
};

// Referral link preview
const referralLink = computed(() => {
  if (form.referral_code) {
    return `${window.location.origin}/register?ref=${form.referral_code}`;
  }
  return null;
});

// Copy to clipboard
const copied = ref("");
const copyToClipboard = async (text: string, type: string) => {
  await navigator.clipboard.writeText(text);
  copied.value = type;
  setTimeout(() => (copied.value = ""), 2000);
};

// Submit
const submit = () => {
  if (isEditing.value) {
    form.put(route("downline-referrals.update", form.user_id), {
      preserveScroll: true,
      onSuccess: () => router.visit(route("downline-referrals.index")),
    });
  } else {
    form.post(route("downline-referrals.store"), {
      preserveScroll: true,
      onSuccess: () => router.visit(route("downline-referrals.index")),
    });
  }
};

// Points presets
const pointsPresets = [10, 25, 50, 100, 200, 500];

// Breadcrumbs
const { set, resetList } = useBreadcrumb();
onMounted(() => {
  resetList();
  set([
    { label: "Home", href: "/dashboard" },
    { label: "My Referral Team", href: route("downline-referrals.index") },
    {
      label: isEditing.value ? "Edit Referral" : "Assign Code & Points",
      href: isEditing.value
        ? route("downline-referrals.edit", selectedUser.value?.id)
        : route("downline-referrals.create"),
    },
  ]);
});
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <Head :title="isEditing ? 'Edit Referral' : 'Assign Referral Code & Points'" />

    <!-- Header Section -->
    <div class="mb-8">
      <h1 class="text-2xl font-bold" :class="theme.text">
        {{
          isEditing
            ? "Edit Referral Code & Points"
            : "Assign Referral Code to Team Member"
        }}
      </h1>
      <p class="text-sm mt-1" :class="theme.textMuted">
        {{
          isEditing
            ? `Manage referral code and points for ${selectedUser?.name}`
            : "Give one of your direct referrals a referral code and points from your own balance"
        }}
      </p>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-col sm:flex-row gap-3 mb-6">
      <button @click="router.visit(route('downline-referrals.index'))" :disabled="form.processing"
        class="inline-flex items-center justify-center px-4 py-2 border rounded-md shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        :class="[theme.card, theme.border, theme.text, theme.hover]">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
        Cancel
      </button>
      <button @click="submit" :disabled="form.processing"
        class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        :class="theme.button">
        <svg v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor"
          viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        {{ isEditing ? "Update Settings" : "Assign Code & Points" }}
      </button>
    </div>

    <!-- Main Form Card -->
    <div class="rounded-lg shadow-sm border overflow-hidden" :class="[theme.card, theme.border]">
      <div class="px-6 py-4 border-b" :class="[theme.border, theme.bgLight]">
        <h3 class="text-lg font-medium" :class="theme.text">
          {{ isEditing ? "Referral Code Settings" : "New Referral Code Assignment" }}
        </h3>
        <p class="text-sm mt-1" :class="theme.textMuted">
          {{
            isEditing
              ? "Update the referral code and transfer more points to this team member."
              : "Enter the email of one of your downline members to assign them a referral code and points."
          }}
        </p>
      </div>

      <div class="px-6 py-6 space-y-6">
        <ValidationErrors />

        <!-- User Selection by Email (only in create mode) -->
        <div v-if="!isEditing">
          <label class="block text-sm font-medium mb-1" :class="theme.text">
            Search downline member by email *
          </label>
          <div class="relative">
            <div class="flex gap-2">
              <div class="relative flex-1">
                <input v-model="search" type="email" placeholder="Enter email address..."
                  class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors disabled:bg-gray-100 disabled:cursor-not-allowed"
                  :class="[theme.input, theme.border]" :disabled="!!selectedUserId" />
                <div v-if="isLoading" class="absolute right-3 top-2.5">
                  <svg class="w-5 h-5 animate-spin" :class="theme.textMuted" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                  </svg>
                </div>
              </div>
              <button v-if="selectedUserId" type="button" @click="clearSelection"
                class="px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-md border border-red-200 transition-colors">
                Clear
              </button>
            </div>

            <!-- Success: User found -->
            <div v-if="searchResult && !selectedUserId" class="mt-3 p-4 rounded-md border border-green-200 bg-green-50">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <div>
                    <p class="font-medium text-green-900">{{ searchResult.name }}</p>
                    <p class="text-sm text-green-700">{{ searchResult.email }}</p>
                    <span class="text-xs text-green-600">
                      Current Balance:
                      {{ searchResult.points_balance.toLocaleString() }} pts
                    </span>
                  </div>
                </div>
                <button type="button" @click="selectUser(searchResult)"
                  class="px-4 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 transition-colors">
                  Select
                </button>
              </div>
            </div>

            <!-- Error: User not found -->
            <div v-if="searchError && !isLoading && !searchResult"
              class="mt-3 p-3 rounded-md border border-red-200 bg-red-50">
              <p class="text-sm text-red-600 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ searchError }}
              </p>
            </div>
          </div>

          <!-- Selected user card -->
          <div v-if="selectedUserId" class="mt-4 p-4 rounded-md border border-blue-200 bg-blue-50">
            <div class="flex items-center gap-3">
              <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <div>
                <p class="font-medium text-blue-900">{{ searchResult?.name }}</p>
                <p class="text-sm text-blue-700">{{ searchResult?.email }}</p>
                <span class="text-xs text-blue-600">
                  Current Balance:
                  {{ searchResult?.points_balance?.toLocaleString() }} pts
                </span>
              </div>
            </div>
          </div>
          <p v-if="form.errors.user_id" class="text-sm text-red-600 mt-1">
            {{ form.errors.user_id }}
          </p>
        </div>

        <!-- Divider -->
        <div v-if="!isEditing && selectedUserId" class="border-t" :class="theme.border"></div>

        <!-- Referral Code -->
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <label class="block text-sm font-medium" :class="theme.text">Referral Code *</label>
            <button @click="generateReferralCode" type="button" :disabled="!form.user_id"
              class="text-sm flex items-center gap-1 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              :class="theme.textAccent">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              Generate New Code
            </button>
          </div>
          <div class="flex gap-2">
            <input v-model="form.referral_code" type="text" placeholder="e.g., REF123ABC"
              class="flex-1 px-4 py-2 border rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
              :class="[
                theme.input,
                theme.border,
                {
                  'border-red-300 focus:ring-red-500 focus:border-red-500':
                    form.errors.referral_code,
                },
              ]" />
            <button v-if="form.referral_code" type="button" @click="copyToClipboard(form.referral_code, 'code')"
              class="inline-flex items-center px-4 py-2 border rounded-md shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
              :class="[theme.card, theme.border, theme.text, theme.hover]">
              {{ copied === "code" ? "Copied!" : "Copy" }}
            </button>
          </div>
          <p v-if="form.errors.referral_code" class="text-sm text-red-600">
            {{ form.errors.referral_code }}
          </p>
        </div>

        <!-- Referral Link Preview -->
        <div v-if="referralLink" class="p-4 rounded-md border border-green-200 bg-green-50">
          <div class="flex items-center gap-2 mb-2">
            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
            </svg>
            <span class="text-sm font-medium text-green-800">Referral Link Preview</span>
          </div>
          <div class="flex items-center gap-2">
            <code class="text-xs bg-green-100 px-3 py-1.5 rounded flex-1 break-all text-green-800">
              {{ referralLink }}
            </code>
            <button type="button" @click="copyToClipboard(referralLink, 'link')"
              class="inline-flex items-center px-3 py-1.5 border border-green-300 rounded-md text-sm font-medium text-green-700 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
              {{ copied === "link" ? "Copied!" : "Copy" }}
            </button>
          </div>
          <p class="text-xs text-green-700 mt-2">
            Share this link. When someone registers with it, you'll both earn points.
          </p>
        </div>

        <!-- Points to Transfer -->
        <div>
          <label class="block text-sm font-medium mb-1" :class="theme.text">
            Points to Transfer (from your balance:
            {{ currentUserPoints.toLocaleString() }} pts)
          </label>
          <input v-model.number="form.points" type="number" min="1" :max="currentUserPoints"
            class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
            :class="[
              theme.input,
              theme.border,
              {
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  form.errors.points,
              },
            ]" placeholder="Enter amount" />
          <div class="flex flex-wrap gap-2 mt-3">
            <button v-for="pts in pointsPresets" :key="pts" @click="form.points = pts" :class="[
              form.points === pts
                ? theme.button
                : `${theme.bgLight} ${theme.text} ${theme.hover}`,
              'px-3 py-1 text-xs rounded-full transition-colors',
            ]" type="button" :disabled="pts > currentUserPoints">
              +{{ pts }}
            </button>
          </div>
          <p class="text-xs mt-2" :class="theme.textMuted">
            These points will be deducted from your balance and added to the selected
            user.
          </p>
          <p v-if="form.errors.points" class="text-sm text-red-600 mt-1">
            {{ form.errors.points }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>