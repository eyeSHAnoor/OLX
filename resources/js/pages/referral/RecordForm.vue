<script setup lang="ts">
import { InertiaPageProps } from "@/types";
import { usePage, router, useForm, Head } from "@inertiajs/vue3";
import { ref, computed, onMounted, watch } from "vue";
import Layout from "@/layouts/AppLayout.vue";
import { useAlertDialog } from "@/composables/useAlertDialog";
import { useBreadcrumb } from "@/composables/useBreadcrumb";

defineOptions({ layout: Layout });

interface UserData {
  id: number;
  name: string;
  email: string;
  referral_code: string | null;
  points_balance: number;
  referred_by?: number | null;
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
}

const form = useForm<FormData>({
  user_id: selectedUser.value?.id ?? "",
  referral_code: user.value?.referral_code ?? "",
  points_to_award: 0, // Initialize to 0, not the user's balance
});

const selectedUserInfo = computed(() => {
  const userId = isEditing.value ? user.value?.id : form.user_id;
  return users.value.find((u) => u.id === userId);
});

// Current points balance based on mode
const currentBalance = computed(() => {
  if (isEditing.value) {
    return user.value?.points_balance ?? 0;
  }
  return selectedUserInfo.value?.points_balance ?? 0;
});

// New balance after adjustment
const newBalance = computed(() => {
  return currentBalance.value + (form.points_to_award ?? 0);
});

// Is this adding or deducting points?
const isAdding = computed(() => form.points_to_award >= 0);

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

// Points presets - for edit mode, include negative presets too
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
        <AppButton label="Cancel" variant="outline" @click="router.visit(route('referrals.index'))"
          :disabled="form.processing" />
        <AppButton :label="isEditing ? 'Update Settings' : 'Assign Code & Points'" icon="lucide:check"
          :processing="form.processing" @click="submit" class="bg-brand-orange hover:bg-brand-orange/80" />
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
                : `Assign a referral code and initial points to a user. They can then share their referral link to earn
            points
            whenever someone joins using it.`
            }}
          </CardDescription>
        </CardHeader>
        <CardContent class="space-y-6">
          <ValidationErrors />

          <!-- User Selection (Only in create mode) -->
          <div v-if="!isEditing">
            <SelectInput label="Select User *" v-model="form.user_id" :error="form.errors.user_id"
              placeholder="Choose a user to assign referral code...">
              <SelectContent>
                <SelectItem v-for="u in users" :key="u.id" :value="u.id">
                  {{ u.name }} - {{ u.email }}
                  <span v-if="u.referral_code" class="text-xs text-muted-foreground ml-2">
                    (Has code: {{ u.referral_code }})
                  </span>
                </SelectItem>
              </SelectContent>
            </SelectInput>

            <!-- Selected User Info -->
            <div v-if="selectedUserInfo" class="mt-4 p-4 rounded-lg border bg-blue-50">
              <div class="flex items-center gap-3">
                <Icon name="lucide:user-plus" class="size-8 text-blue-600" />
                <div>
                  <p class="font-semibold text-blue-900">
                    {{ selectedUserInfo.name }}
                  </p>
                  <p class="text-sm text-blue-600">
                    {{ selectedUserInfo.email }}
                  </p>
                  <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs text-blue-600">
                      Current Balance:
                      {{ selectedUserInfo.points_balance.toLocaleString() }} pts
                    </span>
                    <span v-if="selectedUserInfo.referral_code"
                      class="text-xs px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full">
                      Existing Code: {{ selectedUserInfo.referral_code }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Divider -->
          <div v-if="!isEditing && selectedUserInfo" class="border-t"></div>

          <!-- Referral Code -->
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <label class="text-sm font-medium">Referral Code *</label>
              <button @click="generateReferralCode" type="button"
                class="text-xs text-primary hover:text-primary/80 flex items-center gap-1">
                <Icon name="lucide:refresh-cw" class="size-3" />
                Generate New Code
              </button>
            </div>
            <div class="flex gap-2">
              <TextInput v-model="form.referral_code" placeholder="e.g., REF123ABC" :error="form.errors.referral_code"
                class="flex-1" />
              <AppButton v-if="form.referral_code" variant="outline" size="sm"
                @click="copyToClipboard(form.referral_code, 'code')" :label="copied === 'code' ? 'Copied!' : 'Copy'"
                :icon="copied === 'code' ? 'lucide:check' : 'lucide:copy'" />
            </div>
          </div>

          <!-- Referral Link Preview -->
          <div v-if="referralLink" class="p-4 rounded-lg border bg-green-50">
            <div class="flex items-center gap-2 mb-2">
              <Icon name="lucide:link" class="size-4 text-green-600" />
              <span class="text-sm font-medium text-green-800">Referral Link Preview</span>
            </div>
            <div class="flex items-center gap-2">
              <code class="text-xs bg-green-100 px-3 py-1.5 rounded flex-1 break-all">
            {{ referralLink }}
          </code>
              <AppButton variant="ghost" size="sm" @click="copyToClipboard(referralLink, 'link')"
                :icon="copied === 'link' ? 'lucide:check' : 'lucide:copy'" />
            </div>
            <p class="text-xs text-green-600 mt-2">
              Share this link with others. When they register using this link, both users
              will earn points.
            </p>
          </div>

          <!-- Points to Assign/Adjust -->
          <div>
            <label class="text-sm font-medium block mb-2">
              {{
                isEditing
                  ? "Points Adjustment (positive to add, negative to deduct)"
                  : "Initial Points to Assign *"
              }}
            </label>
            <TextInput v-model="form.points_to_award" type="number" :error="form.errors.points_to_award" :placeholder="isEditing
                ? 'e.g., 100 to add or -50 to deduct'
                : 'Enter points amount (e.g., 100)'
              " :min="isEditing ? -10000 : 0" />
            <div class="flex flex-wrap gap-2 mt-3">
              <button v-for="points in pointsPresets" :key="points" @click="form.points_to_award = points" :class="[
                form.points_to_award === points
                  ? 'bg-primary text-primary-foreground'
                  : 'bg-muted hover:bg-muted/80',
                points < 0 ? 'text-red-600' : '',
              ]" class="px-3 py-1 text-xs rounded-full transition-colors" type="button">
                {{ points >= 0 ? "+" : "" }}{{ points }}
              </button>
            </div>
            <p class="text-xs text-muted-foreground mt-2">
              {{
                isEditing
                  ? `Adjust points for this user. Current balance: ${currentBalance.toLocaleString()} pts`
                  : `Initial points will be added to the user's balance.`
              }}
            </p>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppContainer>
</template>
