<script setup lang="ts">
import { InertiaPageProps } from "@/types";
import { usePage, router, useForm, Head } from "@inertiajs/vue3";
import { ref, computed, onMounted } from "vue";
import Layout from "@/layouts/AppLayout.vue";
import { useAlertDialog } from "@/composables/useAlertDialog";
import { Users, Gift, Copy, RefreshCw } from "lucide-vue-next";

// Composables
import { useBreadcrumb } from "@/composables/useBreadcrumb";

defineOptions({ layout: Layout });

interface PageProps extends InertiaPageProps {
  users: Array<{
    id: number;
    name: string;
    email: string;
    referral_code: string | null;
    points_balance: number;
  }>;
  referral?: App.Data.ReferralData;
  generatedLink?: string;
}

const page = usePage<PageProps>();
const users = computed(() => page.props.users);
const referral = computed(() => page.props.referral);
const generatedLink = computed(() => page.props.generatedLink);
const isEditing = computed(() => !!referral.value);

interface ReferralFormData {
  id?: string | number;
  referrer_id: number; // Changed to number
  referred_user_id: number; // Changed to number
  status: string;
  points_awarded: number;
  link_code: string;
  generate_new_code: boolean;
}

const getDefaultForm = (item: App.Data.ReferralData | undefined): ReferralFormData => ({
  id: item?.id ?? "",
  referrer_id: item?.referrer_id ?? "", // Now number
  referred_user_id: item?.referred_user_id ?? "", // Now number
  status: item?.status ?? "completed",
  points_awarded: item?.points_awarded ?? 100,
  link_code: item?.link_code ?? "",
  generate_new_code: false,
});

const form = useForm<ReferralFormData>({ ...getDefaultForm(referral.value) });

// Selected users for display
const selectedReferrer = computed(() =>
  users.value.find((u) => u.id === form.referrer_id)
);

const selectedReferredUser = computed(() =>
  users.value.find((u) => u.id === form.referred_user_id)
);

// Copy to clipboard
const copied = ref(false);
const copyToClipboard = async (text: string) => {
  await navigator.clipboard.writeText(text);
  copied.value = true;
  setTimeout(() => {
    copied.value = false;
  }, 2000);
};

// Generate unique referral code
const generateReferralCode = () => {
  const timestamp = Date.now().toString(36).toUpperCase();
  const random = Math.random().toString(36).substring(2, 5).toUpperCase();
  form.link_code = `REF${timestamp}${random}`;
  form.generate_new_code = true;
};

// Get available users for referrer (exclude selected referred user)
const availableReferrers = computed(() =>
  users.value.filter((u) => u.id !== form.referred_user_id)
);

// Get available users for referred (exclude selected referrer and users already referred)
const availableReferredUsers = computed(() =>
  users.value.filter((u) => u.id !== form.referrer_id)
);

// Generate referral link
const generateLink = () => {
  if (!selectedReferrer.value?.referral_code) {
    useAlertDialog().show({
      title: "No Referral Code",
      description:
        "Selected user doesn't have a referral code. Please generate one first.",
      confirmText: "OK",
    });
    return;
  }

  router.post(
    route("referrals.generate-link"),
    {
      user_id: form.referrer_id,
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        // Link will be available in page.props.generatedLink
      },
    }
  );
};

// Submit form
const submit = () => {
  if (isEditing.value) {
    form
      .transform((data: any) => ({
        ...data,
        _method: "PUT",
      }))
      .post(route("referrals.update", form.id), {
        preserveScroll: true,
        onSuccess: () => {
          router.visit(route("referrals.index"));
        },
      });
  } else {
    form.post(route("referrals.store"), {
      preserveScroll: true,
      onSuccess: () => {
        router.visit(route("referrals.index"));
      },
    });
  }
};

// Delete referral
const alert = useAlertDialog();
const destroy = async () => {
  if (!form.id) return;
  const confirmed = await alert.show({
    title: "Delete Referral",
    description: `Are you sure you want to delete this referral record? This action cannot be undone.`,
    confirmText: "Yes, Delete",
    cancelText: "Cancel",
  });
  if (confirmed) {
    form.delete(route("referrals.destroy", form.id), {
      preserveScroll: true,
      onSuccess: () => {
        router.visit(route("referrals.index"));
      },
    });
  }
};

// Generate code for user
const generateCodeForUser = async (userId: number) => {
  const confirmed = await alert.show({
    title: "Generate Referral Code",
    description: "Generate a new referral code for this user?",
    confirmText: "Generate",
    cancelText: "Cancel",
  });

  if (confirmed) {
    router.post(
      route("referrals.generate-user-code"),
      {
        user_id: userId,
      },
      {
        preserveScroll: true,
      }
    );
  }
};

// Breadcrumbs
const { set, resetList } = useBreadcrumb();
onMounted(() => {
  resetList();
  set([
    { label: "Home", href: "/dashboard" },
    { label: "Referrals", href: route("referrals.index") },
    {
      label: isEditing.value ? "Edit Referral" : "Create Referral",
      href: route("referrals.create"),
    },
  ]);
});

// Points presets
const pointsPresets = [25, 50, 100, 200, 500, 1000];
</script>

<template>
  <AppContainer>
    <Head :title="isEditing ? 'Edit Referral' : 'Create New Referral'" />

    <div class="my-8">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">
            {{ isEditing ? "Edit Referral" : "Create New Referral" }}
          </h1>
          <p class="text-muted-foreground mt-2">
            {{
              isEditing
                ? "Update referral details and points"
                : "Manually create a referral record and assign points"
            }}
          </p>
        </div>
        <div class="flex items-center gap-3">
          <AppButton
            label="Cancel"
            variant="outline"
            @click="router.visit(route('referrals.index'))"
            :disabled="form.processing"
          />
          <AppButton
            :label="isEditing ? 'Update Referral' : 'Create Referral'"
            icon="lucide:check"
            :processing="form.processing"
            @click="submit"
            class="bg-brand-orange hover:bg-brand-orange/80"
          />
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Main Form -->
      <div class="lg:col-span-2 space-y-6">
        <Card>
          <CardHeader>
            <CardTitle>Referral Details</CardTitle>
            <CardDescription
              >Select users and configure referral settings</CardDescription
            >
          </CardHeader>
          <CardContent class="space-y-6">
            <ValidationErrors />

            <SelectInput
              label="Referrer (Who referred) *"
              v-model="form.referrer_id"
              :error="form.errors.referrer_id"
              placeholder="Select referrer"
            >
              <SelectContent>
                <SelectItem
                  v-for="user in availableReferrers"
                  :key="user.email"
                  :value="user.id"
                >
                  {{ user.name }} - {{ user.email }}
                </SelectItem>
              </SelectContent>
            </SelectInput>

            <!-- Selected Referrer Info -->
            <div v-if="selectedReferrer" class="p-4 rounded-lg border bg-muted/50">
              <div class="flex items-center justify-between">
                <div>
                  <p class="font-semibold">{{ selectedReferrer.name }}</p>
                  <p class="text-sm text-muted-foreground">
                    {{ selectedReferrer.email }}
                  </p>
                  <div class="flex items-center gap-2 mt-2">
                    <span
                      v-if="selectedReferrer.referral_code"
                      class="text-xs px-2 py-0.5 bg-primary/10 text-primary rounded-full"
                    >
                      Code: {{ selectedReferrer.referral_code }}
                    </span>
                    <span class="text-xs text-muted-foreground">
                      Balance: {{ selectedReferrer.points_balance }} pts
                    </span>
                  </div>
                </div>
                <div class="flex gap-2">
                  <AppButton
                    v-if="!selectedReferrer.referral_code"
                    label="Generate Code"
                    size="sm"
                    variant="outline"
                    @click="generateCodeForUser(selectedReferrer.id)"
                  />
                </div>
              </div>
            </div>

            <!-- Referred User Selection -->
            <SelectInput
              label="Referred User (Who was referred) *"
              v-model="form.referred_user_id"
              :error="form.errors.referred_user_id"
              placeholder="Select referred user..."
              required
            >
              <SelectContent>
                <SelectItem
                  v-for="user in availableReferredUsers"
                  :key="user.id"
                  :value="user.id"
                >
                  {{ user.name }} - {{ user.email }}
                </SelectItem>
              </SelectContent>
            </SelectInput>

            <!-- Selected Referred User Info -->
            <div v-if="selectedReferredUser" class="p-4 rounded-lg border bg-green-50">
              <div>
                <p class="font-semibold text-green-800">
                  {{ selectedReferredUser.name }}
                </p>
                <p class="text-sm text-green-600">{{ selectedReferredUser.email }}</p>
                <p class="text-xs text-green-600 mt-1">
                  Points: {{ selectedReferredUser.points_balance }} pts
                </p>
              </div>
            </div>

            <!-- Referral Link Code -->
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <label class="text-sm font-medium">Referral Link Code</label>
                <button
                  @click="generateReferralCode"
                  type="button"
                  class="text-xs text-primary hover:text-primary/80 flex items-center gap-1"
                >
                  <RefreshCw class="size-3" />
                  Generate Code
                </button>
              </div>
              <div class="flex gap-2">
                <TextInput
                  v-model="form.link_code"
                  placeholder="Auto-generated or custom code"
                  :error="form.errors.link_code"
                  class="flex-1"
                />
                <AppButton
                  v-if="form.link_code"
                  variant="outline"
                  size="sm"
                  @click="copyToClipboard(form.link_code)"
                  :label="copied ? 'Copied!' : 'Copy'"
                  :icon="copied ? 'lucide:check' : 'lucide:copy'"
                />
              </div>
            </div>

            <!-- Generate Shareable Link -->
            <div v-if="selectedReferrer" class="p-4 rounded-lg border border-dashed">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium">Generate Shareable Referral Link</p>
                  <p class="text-xs text-muted-foreground">
                    Creates a unique tracking link for this referrer
                  </p>
                </div>
                <AppButton
                  label="Generate Link"
                  size="sm"
                  variant="outline"
                  icon="lucide:link"
                  @click="generateLink"
                  :disabled="!selectedReferrer?.referral_code"
                />
              </div>

              <!-- Generated Link Display -->
              <div v-if="generatedLink" class="mt-3 p-3 bg-muted rounded-lg">
                <div class="flex items-center justify-between">
                  <code class="text-sm break-all">{{ generatedLink }}</code>
                  <AppButton
                    variant="ghost"
                    size="sm"
                    @click="copyToClipboard(generatedLink)"
                    :icon="copied ? 'lucide:check' : 'lucide:copy'"
                  />
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Points Configuration -->
        <Card>
          <CardHeader>
            <CardTitle>Points Configuration</CardTitle>
            <CardDescription>Set points and status for this referral</CardDescription>
          </CardHeader>
          <CardContent class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Points Input -->
              <div>
                <label class="text-sm font-medium block mb-2">Points Awarded *</label>
                <TextInput
                  v-model="form.points_awarded"
                  type="number"
                  :error="form.errors.points_awarded"
                  placeholder="100"
                  min="0"
                />
                <p
                  v-if="form.errors.points_awarded"
                  class="text-sm text-destructive mt-1"
                >
                  {{ form.errors.points_awarded }}
                </p>

                <!-- Points Presets -->
                <div class="flex flex-wrap gap-2 mt-3">
                  <button
                    v-for="points in pointsPresets"
                    :key="points"
                    @click="form.points_awarded = points"
                    :class="
                      form.points_awarded === points
                        ? 'bg-primary text-primary-foreground'
                        : 'bg-muted hover:bg-muted/80'
                    "
                    class="px-3 py-1 text-xs rounded-full transition-colors"
                    type="button"
                  >
                    +{{ points }}
                  </button>
                </div>
              </div>

              <!-- Status -->
              <SelectInput
                label="Status *"
                v-model="form.status"
                :error="form.errors.status"
                placeholder="Select status"
              >
                <SelectContent>
                  <SelectItem value="pending"> Pending </SelectItem>
                  <SelectItem value="completed"> Completed </SelectItem>
                  <SelectItem value="cancelled"> Cancelled </SelectItem>
                </SelectContent>
              </SelectInput>
            </div>

            <!-- Points Summary -->
            <div class="p-4 rounded-lg bg-blue-50 border border-blue-200">
              <div class="flex items-center gap-2 mb-2">
                <Gift class="size-5 text-blue-600" />
                <span class="font-semibold text-blue-800">Points Summary</span>
              </div>
              <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                  <span class="text-blue-600">Referrer receives:</span>
                  <span class="font-semibold text-blue-800"
                    >+{{ form.points_awarded || 0 }} points</span
                  >
                </div>
                <div class="flex justify-between">
                  <span class="text-blue-600">Referred user receives:</span>
                  <span class="font-semibold text-blue-800">
                    +{{ Math.floor(form.points_awarded / 2) || 0 }} points (welcome bonus)
                  </span>
                </div>
                <div
                  v-if="selectedReferrer"
                  class="flex justify-between border-t border-blue-200 pt-2 mt-2"
                >
                  <span class="text-blue-600">Referrer new balance:</span>
                  <span class="font-semibold text-blue-800">
                    {{
                      (selectedReferrer.points_balance || 0) +
                      (Number(form.points_awarded) || 0)
                    }}
                    pts
                  </span>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Preview Card -->
        <Card>
          <CardHeader>
            <CardTitle>Referral Preview</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div class="flex items-center gap-4 p-3 bg-muted rounded-lg">
                <Users class="size-8 text-muted-foreground" />
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-semibold truncate">
                    {{ selectedReferrer?.name || "Select referrer" }}
                  </p>
                  <p class="text-xs text-muted-foreground truncate">Referrer</p>
                </div>
              </div>

              <div class="flex justify-center">
                <div class="w-0.5 h-8 bg-muted-foreground/30"></div>
              </div>

              <div class="flex items-center gap-4 p-3 bg-green-50 rounded-lg">
                <Users class="size-8 text-green-600" />
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-semibold text-green-800 truncate">
                    {{ selectedReferredUser?.name || "Select user" }}
                  </p>
                  <p class="text-xs text-green-600 truncate">Referred User</p>
                </div>
              </div>

              <div class="flex justify-center">
                <div class="flex items-center gap-2 text-sm">
                  <Gift class="size-4 text-yellow-600" />
                  <span class="font-semibold"
                    >+{{ form.points_awarded || 0 }} points</span
                  >
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Actions Card -->
        <Card>
          <CardHeader>
            <CardTitle>Actions</CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="flex flex-col gap-2">
              <AppButton
                :label="isEditing ? 'Update Referral' : 'Create Referral'"
                icon="lucide:check"
                :processing="form.processing"
                @click="submit"
                class="bg-brand-orange hover:bg-brand-orange/80 w-full justify-center"
              />

              <AppButton
                label="Cancel"
                variant="outline"
                class="w-full justify-center"
                @click="router.visit(route('referrals.index'))"
                :disabled="form.processing"
              />

              <AppButton
                v-if="isEditing"
                label="Delete Referral"
                variant="danger"
                icon="lucide:trash-2"
                class="w-full justify-center"
                @click="destroy"
                :disabled="form.processing"
              />
            </div>
          </CardContent>
        </Card>

        <!-- Quick Info -->
        <Card v-if="referral">
          <CardHeader>
            <CardTitle>Record Info</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-2 text-sm">
              <div class="flex justify-between">
                <span class="text-muted-foreground">Created</span>
                <span>{{ new Date(referral.created_at).toLocaleDateString() }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-muted-foreground">Last Updated</span>
                <span>{{ new Date(referral.updated_at).toLocaleDateString() }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-muted-foreground">Link Code</span>
                <code class="text-xs">{{ referral.link_code || "N/A" }}</code>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppContainer>
</template>
