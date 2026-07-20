<script setup lang="ts">
import { InertiaPageProps, PaginatedData } from "@/types";
import { usePage, router, Head } from "@inertiajs/vue3";
import { computed, onMounted, ref } from "vue";
import Layout from "@/layouts/AppLayout.vue";
import { useAlertDialog } from "@/composables/useAlertDialog";
import { useBreadcrumb } from "@/composables/useBreadcrumb";
import WithdrawalManageModal from "./_partials/WithdrawalManageModal.vue";

defineOptions({ layout: Layout });

interface ReferralData {
  id: number;
  name: string;
  email: string;
  referral_code: string | null;
  points_balance: number;
  referred_by: number | null;
  referrer_name: string | null;
  total_referrals_count: number;
  total_points_earned: number;
  points_per_referral: number;
  can_assign_code: boolean;
  created_at: string;
  referral_score: {
    total_earned: number;
    total_withdrawn: number;
    available: number;
    pending: number;
    status: string;
    has_pending_withdrawal: boolean;
  } | null; // Make it nullable
}

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

const page = usePage<{
  referrers: PaginatedData<ReferralData>;
  withdrawalRequests: PaginatedData<WithdrawalRequest>;
  stats: {
    total_users_with_codes: number;
    total_referrals: number;
    total_points_earned: number;
    total_points_balance: number;
    total_withdrawn: number;
    total_pending_points: number;
    pending_withdrawals: number;
    approved_withdrawals: number;
    completed_withdrawals: number;
    rejected_withdrawals: number;
  };
}>();

const referrers = computed(() => page.props.referrers);
const withdrawalRequests = computed(() => page.props.withdrawalRequests);
const stats = computed(() => page.props.stats);
console.log(page.props);
// Active tab
const activeTab = ref<"referrals" | "withdrawals">("referrals");

// Modal state
const showWithdrawalModal = ref(false);
const selectedWithdrawal = ref<WithdrawalRequest | null>(null);

// Initialize search filter
const { form, reset, isFiltered } = useSearchFilter(route("referrals.index"));

// Copy state tracking
const copiedId = ref<number | null>(null);

// Columns for referrals table
const columns = [
  { accessorKey: "name", header: "User", sortable: true, mobileTitle: "User" },
  {
    accessorKey: "referral_code",
    header: "Referral Code",
    sortable: true,
    mobileTitle: "Code",
  },
  {
    accessorKey: "total_referrals_count",
    header: "Referrals",
    sortable: true,
    mobileTitle: "Referrals",
  },
  {
    accessorKey: "points_balance",
    header: "Points per user",
    sortable: true,
    mobileTitle: "points",
  },
  {
    accessorKey: "total_points_earned",
    header: "Earned from Referrals",
    sortable: true,
    mobileTitle: "Earned",
  },
  {
    accessorKey: "referral_score.available",
    header: "Available Points",
    sortable: false,
    mobileTitle: "Available",
  },
  {
    accessorKey: "referral_score.total_withdrawn",
    header: "Withdrawn",
    sortable: false,
    mobileTitle: "Withdrawn",
  },
  { accessorKey: "can_assign_code", header: "Can Assign Codes", sortable: true },
  {
    accessorKey: "referred_by",
    header: "Referred By",
    sortable: false,
    mobileTitle: "Referred",
  },
  { accessorKey: "actions", header: "", sortable: false, mobileTitle: "Actions" },
];

// Columns for withdrawal requests table
const withdrawalColumns = [
  { accessorKey: "user.name", header: "User", sortable: true },
  { accessorKey: "requested_amount", header: "Points Requested", sortable: true },
  { accessorKey: "payment_method", header: "Payment Method", sortable: false },
  { accessorKey: "status", header: "Status", sortable: true },
  { accessorKey: "created_at", header: "Requested At", sortable: true },
  { accessorKey: "actions", header: "Actions", sortable: false },
];

// Breadcrumbs
const { set, resetList } = useBreadcrumb();

onMounted(() => {
  resetList();
  set([
    { label: "Home", href: "/dashboard" },
    { label: "Referrals", href: route("referrals.index") },
  ]);
});

// Copy referral link to clipboard
const copyReferralLink = async (code: string, id: number) => {
  try {
    const link = `${window.location.origin}/register?ref=${code}`;
    await navigator.clipboard.writeText(link);
    copiedId.value = id;
    setTimeout(() => (copiedId.value = null), 2000);
  } catch (err) {
    console.error("Failed to copy link:", err);
  }
};

// DELETE handler for removing referral code
async function handleRemoveReferral(user: ReferralData) {
  const alert = useAlertDialog();
  const confirmed = await alert.show({
    title: "Remove Referral Code",
    description: `Are you sure you want to remove the referral code from "${user.name}"? This will cancel all their referral records.`,
    confirmText: "Remove",
    cancelText: "Cancel",
  });

  if (confirmed) {
    router.delete(route("users.referral.destroy", user.id), {
      preserveScroll: true,
    });
  }
}

// Navigate to edit referral
function handleEditReferral(user: ReferralData) {
  router.visit(route("users.referral.edit", user.id));
}

function handleViewReferral(user: ReferralData) {
  router.visit(route("users.referrals.show", user.id));
}

// Navigate to create referral
function handleCreateReferral() {
  router.visit(route("users.referral.create"));
}

function handleAssignReferral() {
  router.post(route("user.assign.referral"), {});
}

// ========== WITHDRAWAL METHODS ==========

function openWithdrawalModal(withdrawal: WithdrawalRequest) {
  selectedWithdrawal.value = withdrawal;
  showWithdrawalModal.value = true;
}

function handleWithdrawalUpdate() {
  router.reload({ only: ["withdrawalRequests", "stats"] });
}

// Get status badge color
function getStatusColor(status: string) {
  const colors = {
    pending: "bg-yellow-100 text-yellow-800",
    approved: "bg-blue-100 text-blue-800",
    completed: "bg-green-100 text-green-800",
    rejected: "bg-red-100 text-red-800",
  };
  return colors[status as keyof typeof colors] || "bg-gray-100 text-gray-800";
}

// Get status label
function getStatusLabel(status: string) {
  const labels = {
    pending: "Pending",
    approved: "Approved",
    completed: "Completed",
    rejected: "Rejected",
  };
  return labels[status as keyof typeof labels] || status;
}

// Get payment method label
function getPaymentMethodLabel(method: string) {
  const labels = {
    bank_transfer: "Bank Transfer",
    easypaisa: "Easypaisa",
    jazzcash: "JazzCash",
  };
  return labels[method as keyof typeof labels] || method;
}

// Helper function to safely get referral score value
function getReferralScoreValue(
  user: ReferralData,
  key: keyof ReferralData["referral_score"]
) {
  if (!user.referral_score) return 0;
  return user.referral_score[key] || 0;
}
</script>

<template>
  <AppContainer>
    <Head title="Referrals Management" />

    <PageHeading>
      <template #title>Referrals Management</template>
      <template #description>
        Assign referral codes, track user referral performance, and manage withdrawals
      </template>
      <template #links>
        <AppButton
          label="Assign Referral Code"
          icon="radix-icons:plus-circled"
          @click="handleCreateReferral"
        />
        <AppButton
          label="Assign Referral Code to all users"
          icon="radix-icons:plus-circled"
          @click="handleAssignReferral"
        />
      </template>
    </PageHeading>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <Card class="bg-gradient-to-br from-blue-50 to-blue-100 border-blue-200">
        <CardContent class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-blue-600">Users with Codes</p>
              <p class="text-3xl font-bold text-blue-900">
                {{ stats.total_users_with_codes || 0 }}
              </p>
            </div>
            <div class="p-3 bg-blue-200 rounded-full">
              <Icon name="lucide:users" class="size-6 text-blue-700" />
            </div>
          </div>
        </CardContent>
      </Card>

      <Card class="bg-gradient-to-br from-green-50 to-green-100 border-green-200">
        <CardContent class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-green-600">Total Referrals</p>
              <p class="text-3xl font-bold text-green-900">
                {{ stats.total_referrals || 0 }}
              </p>
            </div>
            <div class="p-3 bg-green-200 rounded-full">
              <Icon name="lucide:user-plus" class="size-6 text-green-700" />
            </div>
          </div>
        </CardContent>
      </Card>

      <Card class="bg-gradient-to-br from-purple-50 to-purple-100 border-purple-200">
        <CardContent class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-purple-600">Points Earned</p>
              <p class="text-3xl font-bold text-purple-900">
                {{ (stats.total_points_earned || 0).toLocaleString() }}
              </p>
            </div>
            <div class="p-3 bg-purple-200 rounded-full">
              <Icon name="lucide:gift" class="size-6 text-purple-700" />
            </div>
          </div>
        </CardContent>
      </Card>

      <Card class="bg-gradient-to-br from-orange-50 to-orange-100 border-orange-200">
        <CardContent class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-orange-600">Total Withdrawn</p>
              <p class="text-3xl font-bold text-orange-900">
                {{ (stats.total_withdrawn || 0).toLocaleString() }}
              </p>
              <p
                v-if="stats.total_pending_points > 0"
                class="text-xs text-yellow-600 mt-1"
              >
                {{ stats.total_pending_points.toLocaleString() }} points pending
              </p>
            </div>
            <div class="p-3 bg-orange-200 rounded-full">
              <Icon name="lucide:wallet" class="size-6 text-orange-700" />
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200 mb-6">
      <nav class="-mb-px flex space-x-8" aria-label="Tabs">
        <button
          @click="activeTab = 'referrals'"
          :class="[
            'py-2 px-1 text-sm font-medium border-b-2 transition-colors',
            activeTab === 'referrals'
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
          ]"
        >
          <span class="flex items-center gap-2">
            <Icon name="lucide:users" class="size-4" />
            Referrals
            <span class="ml-1 py-0.5 px-2 rounded-full text-xs bg-gray-100 text-gray-600">
              {{ referrers?.total || 0 }}
            </span>
          </span>
        </button>
        <button
          @click="activeTab = 'withdrawals'"
          :class="[
            'py-2 px-1 text-sm font-medium border-b-2 transition-colors',
            activeTab === 'withdrawals'
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
          ]"
        >
          <span class="flex items-center gap-2">
            <Icon name="lucide:wallet" class="size-4" />
            Withdrawals
            <span
              v-if="stats.pending_withdrawals > 0"
              class="ml-1 py-0.5 px-2 rounded-full text-xs bg-yellow-100 text-yellow-800"
            >
              {{ stats.pending_withdrawals }}
            </span>
          </span>
        </button>
      </nav>
    </div>

    <!-- Referrals Tab -->
    <div v-if="activeTab === 'referrals'">
      <Card class="mt-4">
        <CardContent>
          <AppDataTableNew
            :columns="columns"
            :data="referrers?.data"
            search-placeholder="Search users by name, email, or referral code..."
            v-model:search="form.filter.global"
            :pagination-data="referrers"
            v-model:perPage="form.perPage"
            @resetFilter="reset()"
            :isFiltered="isFiltered"
          >
            <template #name-cell="{ row }">
              <div class="flex flex-col">
                <span class="font-semibold">{{ row.original.name }}</span>
                <span class="text-sm text-muted-foreground">{{
                  row.original.email
                }}</span>
              </div>
            </template>

            <template #referral_code-cell="{ row }">
              <div class="flex items-center gap-2">
                <code
                  v-if="row.original.referral_code"
                  class="text-xs bg-muted px-2 py-1 rounded font-mono"
                >
                  {{ row.original.referral_code }}
                </code>
                <span v-else class="text-xs text-muted-foreground italic">No code</span>
              </div>
            </template>

            <template #total_referrals_count-cell="{ row }">
              <span
                class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-semibold"
                :class="{
                  'bg-green-100 text-green-700': row.original.total_referrals_count > 0,
                  'bg-gray-100 text-gray-500': row.original.total_referrals_count === 0,
                }"
              >
                {{ row.original.total_referrals_count }}
              </span>
            </template>

            <template #referral_score.available-cell="{ row }">
              <span class="font-semibold text-blue-600">
                {{ getReferralScoreValue(row.original, "available") || 0 }} Pkr
              </span>
            </template>

            <template #referral_score.total_withdrawn-cell="{ row }">
              <span class="font-semibold text-red-600">
                {{ getReferralScoreValue(row.original, "total_withdrawn") || 0 }} Pkr
              </span>
            </template>

            <template #can_assign_code-cell="{ row }">
              <span
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                  row.original.can_assign_code
                    ? 'bg-green-100 text-green-800'
                    : 'bg-gray-100 text-gray-500',
                ]"
              >
                <svg
                  v-if="row.original.can_assign_code"
                  class="w-3 h-3 mr-1"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M5 13l4 4L19 7"
                  />
                </svg>
                <svg
                  v-else
                  class="w-3 h-3 mr-1"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
                {{ row.original.can_assign_code ? "Yes" : "No" }}
              </span>
            </template>

            <template #points_balance-cell="{ row }">
              <span
                class="font-semibold"
                :class="{
                  'text-blue-600': row.original.points_balance > 0,
                  'text-muted-foreground': row.original.points_balance === 0,
                }"
              >
                {{ row.original.points_balance.toLocaleString() }} Pkr
              </span>
            </template>

            <template #total_points_earned-cell="{ row }">
              <span
                class="font-bold"
                :class="{
                  'text-purple-600': row.original.total_points_earned > 0,
                  'text-muted-foreground': row.original.total_points_earned === 0,
                }"
              >
                {{ row.original.total_points_earned.toLocaleString() }} Pkr
              </span>
            </template>

            <template #referred_by-cell="{ row }">
              <span v-if="row.original.referrer_name" class="text-sm font-medium">
                {{ row.original.referrer_name }}
              </span>
              <span v-else class="text-xs text-muted-foreground"> Direct / Admin </span>
            </template>

            <template #actions-cell="{ row }">
              <div class="flex items-center justify-end gap-2">
                <AppDataTableActionButton
                  v-if="row.original.referral_code"
                  :icon="copiedId === row.original.id ? 'lucide:check' : 'lucide:copy'"
                  :tooltip="
                    copiedId === row.original.id ? 'Link Copied!' : 'Copy Referral Link'
                  "
                  :class="copiedId === row.original.id ? 'text-green-600' : ''"
                  @click="copyReferralLink(row.original.referral_code, row.original.id)"
                />
                <AppDataTableActionButton
                  icon="lucide:users"
                  tooltip="View Referrals"
                  @click="handleViewReferral(row.original)"
                />
                <AppDataTableActionButton
                  icon="lucide:edit"
                  tooltip="Edit Referral Code & Points"
                  @click="handleEditReferral(row.original)"
                />
                <AppDataTableActionButton
                  v-if="row.original.referral_code"
                  icon="lucide:trash-2"
                  tooltip="Remove Referral Code"
                  variant="danger"
                  @click="handleRemoveReferral(row.original)"
                />
              </div>
            </template>
          </AppDataTableNew>
        </CardContent>
      </Card>
    </div>

    <!-- Withdrawals Tab -->
    <div v-if="activeTab === 'withdrawals'">
      <Card class="mt-4">
        <CardContent>
          <!-- Withdrawal Stats Summary -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
            <div class="bg-yellow-50 rounded-lg p-3 border border-yellow-200">
              <p class="text-xs text-yellow-600">Pending</p>
              <p class="text-xl font-bold text-yellow-700">
                {{ stats.pending_withdrawals || 0 }}
              </p>
            </div>
            <div class="bg-blue-50 rounded-lg p-3 border border-blue-200">
              <p class="text-xs text-blue-600">Approved</p>
              <p class="text-xl font-bold text-blue-700">
                {{ stats.approved_withdrawals || 0 }}
              </p>
            </div>
            <div class="bg-green-50 rounded-lg p-3 border border-green-200">
              <p class="text-xs text-green-600">Completed</p>
              <p class="text-xl font-bold text-green-700">
                {{ stats.completed_withdrawals || 0 }}
              </p>
            </div>
            <div class="bg-red-50 rounded-lg p-3 border border-red-200">
              <p class="text-xs text-red-600">Rejected</p>
              <p class="text-xl font-bold text-red-700">
                {{ stats.rejected_withdrawals || 0 }}
              </p>
            </div>
          </div>

          <AppDataTableNew
            :columns="withdrawalColumns"
            :data="withdrawalRequests?.data"
            search-placeholder="Search by user name or email..."
            :pagination-data="withdrawalRequests"
            v-model:perPage="form.perPage"
          >
            <template #user.name-cell="{ row }">
              <div class="flex flex-col">
                <span class="font-semibold">{{ row.original.user?.name }}</span>
                <span class="text-sm text-muted-foreground">{{
                  row.original.user?.email
                }}</span>
              </div>
            </template>

            <template #requested_amount-cell="{ row }">
              <span class="font-semibold text-purple-600">
                {{ row.original.requested_amount?.toLocaleString() || 0 }} Pkr
              </span>
            </template>

            <template #payment_method-cell="{ row }">
              <span class="text-sm">{{
                getPaymentMethodLabel(row.original.payment_method)
              }}</span>
            </template>

            <template #status-cell="{ row }">
              <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                :class="getStatusColor(row.original.status)"
              >
                {{ getStatusLabel(row.original.status) }}
              </span>
            </template>

            <template #created_at-cell="{ row }">
              <span class="text-sm text-muted-foreground">
                {{ new Date(row.original.created_at).toLocaleDateString() }}
              </span>
            </template>

            <template #actions-cell="{ row }">
              <div class="flex items-center justify-end gap-2">
                <AppDataTableActionButton
                  icon="lucide:eye"
                  tooltip="View & Manage"
                  @click="openWithdrawalModal(row.original)"
                />
              </div>
            </template>
          </AppDataTableNew>
        </CardContent>
      </Card>
    </div>

    <!-- Withdrawal Manage Modal -->
    <WithdrawalManageModal
      :show="showWithdrawalModal"
      :withdrawal="selectedWithdrawal"
      @close="showWithdrawalModal = false"
      @updated="handleWithdrawalUpdate"
    />
  </AppContainer>
</template>
