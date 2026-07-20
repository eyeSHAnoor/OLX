<script setup lang="ts">
import { InertiaPageProps } from "@/types";
import { usePage, router, useForm, Head } from "@inertiajs/vue3";
import { ref, computed, onMounted, watch } from "vue";
import Layout from "@/layouts/AppLayout.vue";
import { useAlertDialog } from "@/composables/useAlertDialog";
import { useBreadcrumb } from "@/composables/useBreadcrumb";
import axios from "axios";
import { debounce } from "lodash";

defineOptions({ layout: Layout });

interface UserData {
  id: number;
  name: string;
  email: string;
  referral_code: string | null;
  points_balance: number;
  referred_by?: number | null;
  can_assign_code?: boolean;
}

interface PageProps extends InertiaPageProps {
  users: UserData[];
  selectedUser?: UserData | null;
  user?: UserData | null;
}

const page = usePage<PageProps>();
const users = computed(() => page.props.users);
const selectedUser = computed(() => page.props.selectedUser);
const user = computed(() => page.props.user);
const isEditing = computed(() => !!user.value);

interface FormData {
  user_id: number | string;
  referral_code: string;
  points_to_award: number;
  can_assign_code: boolean;
}

const form = useForm<FormData>({
  user_id: selectedUser.value?.id ?? "",
  referral_code: user.value?.referral_code ?? "",
  points_to_award: 0,
  can_assign_code: user.value?.can_assign_code ?? false,
});

// Watch for user changes in edit mode
watch(
  () => user.value,
  (newUser) => {
    if (newUser && isEditing.value) {
      form.can_assign_code = newUser.can_assign_code ?? false;
      form.referral_code = newUser.referral_code ?? "";
      form.points_to_award = newUser.points_balance ?? 0;
    }
  },
  { immediate: true }
);

// =============================================
//  SEARCH BY EMAIL (Axios backend call)
// =============================================
const search = ref("");
const isLoading = ref(false);
const searchResult = ref<UserData | null>(null);
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
      searchError.value = response.data.message || "No matching user found.";
    }
  } catch (err: any) {
    searchError.value =
      err.response?.data?.message || "Something went wrong. Please try again.";
  } finally {
    isLoading.value = false;
  }
}, 400);

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

// Select the found user
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

  // Auto-fill can_assign_code from user data
  form.can_assign_code = user.can_assign_code ?? false;

  search.value = "";
  searchResult.value = null;
}

// Watch for search results and auto-fill code
watch(searchResult, (newResult) => {
  if (newResult) {
    if (newResult.referral_code) {
      form.referral_code = newResult.referral_code;
    } else {
      generateReferralCode();
    }
    form.can_assign_code = newResult.can_assign_code ?? false;
  }
});

// Clear selection
function clearSelection() {
  selectedUserId.value = null;
  form.user_id = "";
  searchResult.value = null;
  searchError.value = "";
  form.can_assign_code = false;
}
// =======================================================

const selectedUserInfo = computed(() => {
  // For edit mode, use the user prop
  if (isEditing.value) {
    return user.value;
  }

  // For create mode with search, use searchResult first
  if (searchResult.value) {
    return searchResult.value;
  }

  // Fallback to dropdown selection
  const userId = form.user_id;
  return users.value.find((u) => u.id === userId);
});

const currentBalance = computed(() => {
  if (isEditing.value) {
    return user.value?.points_balance ?? 0;
  }
  return selectedUserInfo.value?.points_balance ?? 0;
});

const referralLink = computed(() => {
  if (form.referral_code) {
    return `${window.location.origin}/register?ref=${form.referral_code}`;
  }
  return null;
});

const copied = ref<string>("");
const copyToClipboard = async (text: string, type: string) => {
  await navigator.clipboard.writeText(text);
  copied.value = type;
  setTimeout(() => (copied.value = ""), 2000);
};

const generateReferralCode = () => {
  const timestamp = Date.now().toString(36).toUpperCase();
  const random = Math.random().toString(36).substring(2, 5).toUpperCase();
  form.referral_code = `REF${timestamp}${random}`;
};

const submit = () => {
  if (isEditing.value) {
    form.put(route("users.referral.update", user.value?.id), {
      preserveScroll: true,
      onSuccess: () => router.visit(route("referrals.index")),
    });
  } else {
    form.post(route("users.referral.store"), {
      preserveScroll: true,
      onSuccess: () => router.visit(route("referrals.index")),
    });
  }
};

const pointsPresets = computed(() => {
  if (isEditing.value) {
    return [-100, -50, 0, 50, 100, 200, 500, 1000];
  }
  return [25, 50, 100, 200, 500, 1000];
});

const { set, resetList } = useBreadcrumb();
onMounted(() => {
  resetList();
  set([
    { label: "Home", href: "/dashboard" },
    { label: "Referrals", href: route("referrals.index") },
    {
      label: isEditing.value ? "Edit Referral Code" : "Assign Referral Code",
      href: isEditing.value
        ? route("users.referral.edit", user.value?.id)
        : route("users.referral.create"),
    },
  ]);
});
</script>

<template>
  <AppContainer>
    <Head :title="isEditing ? 'Edit Referral Code' : 'Assign Referral Code'" />

    <PageHeading>
      <template #title>
        {{ isEditing ? "Edit Referral Code & Points" : "Assign Referral Code to User" }}
      </template>
      <template #description>
        {{
          isEditing
            ? `Manage referral code and points for ${user?.name}`
            : "Give a user a unique referral code and initial points balance"
        }}
      </template>
      <template #links>
        <AppButton
          label="Cancel"
          variant="outline"
          @click="router.visit(route('referrals.index'))"
          :disabled="form.processing"
        />
        <AppButton
          :label="isEditing ? 'Update Settings' : 'Assign Code & Points'"
          icon="lucide:check"
          :processing="form.processing"
          @click="submit"
          class="bg-brand-orange hover:bg-brand-orange/80"
        />
      </template>
    </PageHeading>

    <div class="">
      <Card>
        <CardHeader>
          <CardTitle>
            {{ isEditing ? "Referral Code Settings" : "New Referral Code Assignment" }}
          </CardTitle>
          <CardDescription>
            {{
              isEditing
                ? "Update the referral code and points balance for this user."
                : "Enter the email of a user to assign them a referral code and points."
            }}
          </CardDescription>
        </CardHeader>
        <CardContent class="space-y-6">
          <ValidationErrors />

          <!-- User Selection (Only in create mode) -->
          <div v-if="!isEditing">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Search user by email *
            </label>
            <div class="relative">
              <div class="flex gap-2">
                <div class="relative flex-1">
                  <input
                    v-model="search"
                    type="email"
                    placeholder="Enter email address..."
                    class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors disabled:bg-gray-100 disabled:cursor-not-allowed"
                    :class="{
                      'border-red-300': searchError,
                      'border-green-300': searchResult && !searchError,
                    }"
                    :disabled="!!selectedUserId"
                  />
                  <div v-if="isLoading" class="absolute right-3 top-2.5">
                    <svg
                      class="w-5 h-5 text-gray-400 animate-spin"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                      />
                    </svg>
                  </div>
                </div>
                <button
                  v-if="selectedUserId"
                  type="button"
                  @click="clearSelection"
                  class="px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-md border border-red-200 transition-colors"
                >
                  Clear
                </button>
              </div>

              <!-- Success: User found -->
              <div
                v-if="searchResult && !selectedUserId"
                class="mt-3 p-4 rounded-md border border-green-200 bg-green-50"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <svg
                      class="w-8 h-8 text-green-600"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                      />
                    </svg>
                    <div>
                      <p class="font-medium text-green-900">{{ searchResult.name }}</p>
                      <p class="text-sm text-green-700">{{ searchResult.email }}</p>
                      <span class="text-xs text-green-600">
                        Current Balance:
                        {{ searchResult.points_balance.toLocaleString() }} pts
                      </span>
                      <span
                        v-if="searchResult.referral_code"
                        class="text-xs ml-2 px-2 py-0.5 bg-green-200 text-green-700 rounded-full"
                      >
                        Has Code: {{ searchResult.referral_code }}
                      </span>
                    </div>
                  </div>
                  <button
                    type="button"
                    @click="selectUser(searchResult)"
                    class="px-4 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 transition-colors"
                  >
                    Select
                  </button>
                </div>
              </div>

              <!-- Error: User not found -->
              <div
                v-if="searchError && !isLoading && !searchResult"
                class="mt-3 p-3 rounded-md border border-red-200 bg-red-50"
              >
                <p class="text-sm text-red-600 flex items-center gap-2">
                  <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                  {{ searchError }}
                </p>
              </div>
            </div>

            <!-- Selected user card -->
            <div
              v-if="selectedUserId"
              class="mt-4 p-4 rounded-md border border-blue-200 bg-blue-50"
            >
              <div class="flex items-center gap-3">
                <svg
                  class="w-8 h-8 text-blue-600"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                  />
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
          <div v-if="!isEditing && selectedUserId" class="border-t border-gray-200"></div>

          <!-- Referral Code -->
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <label class="block text-sm font-medium text-gray-700"
                >Referral Code *</label
              >
              <button
                @click="generateReferralCode"
                type="button"
                :disabled="!form.user_id && !isEditing"
                class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <svg
                  class="w-3 h-3"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                  />
                </svg>
                Generate New Code
              </button>
            </div>
            <div class="flex gap-2">
              <input
                v-model="form.referral_code"
                type="text"
                placeholder="e.g., REF123ABC"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                :class="{
                  'border-red-300 focus:ring-red-500 focus:border-red-500':
                    form.errors.referral_code,
                }"
              />
              <button
                v-if="form.referral_code"
                type="button"
                @click="copyToClipboard(form.referral_code, 'code')"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
              >
                {{ copied === "code" ? "Copied!" : "Copy" }}
              </button>
            </div>
            <p v-if="form.errors.referral_code" class="text-sm text-red-600">
              {{ form.errors.referral_code }}
            </p>
          </div>

          <!-- Referral Link Preview -->
          <div
            v-if="referralLink"
            class="p-4 rounded-md border border-green-200 bg-green-50"
          >
            <div class="flex items-center gap-2 mb-2">
              <svg
                class="w-4 h-4 text-green-600"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"
                />
              </svg>
              <span class="text-sm font-medium text-green-800"
                >Referral Link Preview</span
              >
            </div>
            <div class="flex items-center gap-2">
              <code
                class="text-xs bg-green-100 px-3 py-1.5 rounded flex-1 break-all text-green-800"
              >
                {{ referralLink }}
              </code>
              <button
                type="button"
                @click="copyToClipboard(referralLink, 'link')"
                class="inline-flex items-center px-3 py-1.5 border border-green-300 rounded-md text-sm font-medium text-green-700 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors"
              >
                {{ copied === "link" ? "Copied!" : "Copy" }}
              </button>
            </div>
            <p class="text-xs text-green-700 mt-2">
              Share this link. When someone registers with it, both users will earn
              points.
            </p>
          </div>

          <!-- Points to Assign/Adjust -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{
                isEditing
                  ? "Points Adjustment (positive to add, negative to deduct)"
                  : "Initial Points to Assign *"
              }}
            </label>
            <input
              v-model.number="form.points_to_award"
              type="number"
              :min="isEditing ? -10000 : 0"
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  form.errors.points_to_award,
              }"
              :placeholder="
                isEditing
                  ? 'e.g., 100 to add or -50 to deduct'
                  : 'Enter points amount (e.g., 100)'
              "
            />
            <div class="flex flex-wrap gap-2 mt-3">
              <button
                v-for="points in pointsPresets"
                :key="points"
                @click="form.points_to_award = points"
                :class="[
                  form.points_to_award === points
                    ? 'bg-blue-600 text-white hover:bg-blue-700'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                  points < 0 ? 'text-red-600' : '',
                  'px-3 py-1 text-xs rounded-full transition-colors',
                ]"
                type="button"
              >
                {{ points >= 0 ? "+" : "" }}{{ points }}
              </button>
            </div>
            <p class="text-xs text-gray-500 mt-2">
              {{
                isEditing
                  ? `Adjust points for this user. Current balance: ${currentBalance.toLocaleString()} pts`
                  : `Initial points will be added to the user's balance.`
              }}
            </p>
            <p v-if="form.errors.points_to_award" class="text-sm text-red-600 mt-1">
              {{ form.errors.points_to_award }}
            </p>
          </div>

          <!-- ✅ Code Assignment Permission Toggle -->
          <div class="border-t border-gray-200 pt-6">
            <div class="flex items-center justify-between">
              <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Code Assignment Permission
                </label>
                <p class="text-sm text-gray-500">
                  Allow this user to assign referral codes and points to other users
                </p>
              </div>
              <button
                type="button"
                @click="form.can_assign_code = !form.can_assign_code"
                :class="[
                  'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2',
                  form.can_assign_code ? 'bg-blue-600' : 'bg-gray-200',
                ]"
                role="switch"
                :aria-checked="form.can_assign_code"
              >
                <span
                  :class="[
                    'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                    form.can_assign_code ? 'translate-x-5' : 'translate-x-0',
                  ]"
                />
              </button>
            </div>

            <!-- Permission Info Card -->
            <div
              :class="[
                'mt-3 p-4 rounded-md border',
                form.can_assign_code
                  ? 'border-blue-200 bg-blue-50'
                  : 'border-gray-200 bg-gray-50',
              ]"
            >
              <div class="flex items-start gap-3">
                <svg
                  v-if="form.can_assign_code"
                  class="w-5 h-5 text-blue-600 mt-0.5"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
                <svg
                  v-else
                  class="w-5 h-5 text-gray-400 mt-0.5"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
                <div>
                  <p
                    :class="[
                      'text-sm font-medium',
                      form.can_assign_code ? 'text-blue-800' : 'text-gray-700',
                    ]"
                  >
                    {{
                      form.can_assign_code
                        ? "Code Assignment Enabled"
                        : "Code Assignment Disabled"
                    }}
                  </p>
                  <p
                    :class="[
                      'text-xs mt-1',
                      form.can_assign_code ? 'text-blue-600' : 'text-gray-500',
                    ]"
                  >
                    {{
                      form.can_assign_code
                        ? "This user will be able to assign referral codes and manage their own downline team."
                        : "This user can only use their referral link but cannot assign codes to others."
                    }}
                  </p>
                </div>
              </div>
            </div>
            <p v-if="form.errors.can_assign_code" class="text-sm text-red-600 mt-1">
              {{ form.errors.can_assign_code }}
            </p>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppContainer>
</template>
