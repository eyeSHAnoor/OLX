<script setup lang="ts">
import { CardContent } from "@/components/ui/card";
import { useForm } from "@inertiajs/vue3";
import { watch, ref } from "vue";
import { Plus, Trash2, ChevronUp, ChevronDown } from "lucide-vue-next";

const { plan, permissions } = defineProps<{
  plan?: App.Models.Plan;
  permissions: App.Models.SubscriptionPermission[];
}>();

interface PlanFormData {
  id?: string | number;
  name: string;
  price: number | string;
  discount: number | string;
  duration_days: number | string;
  description?: string;
  features?: string[];
  is_popular?: boolean;
  sort_order?: number | string;

  permission_ids?: number[];
}

const getDefaultForm = (item: App.Models.Plan | undefined): PlanFormData => ({
  id: item?.id ?? "",
  name: item?.name ?? "",
  price: item?.price ?? "",
  discount: item?.discount ?? "",
  duration_days: item?.duration_days ?? "",
  description: item?.description ?? "",
  features: item?.features?.length ? [...item.features] : [""],
  is_popular: item?.is_popular ?? false,
  sort_order: item?.sort_order ?? 0,

  permission_ids: item?.permissions?.map((p) => p.id) ?? [],
});

const form = useForm<PlanFormData>({ ...getDefaultForm(plan) });

const newFeature = ref("");
const model = defineModel<boolean>();

watch(model, (isOpen) => {
  if (isOpen) {
    const newValues = getDefaultForm(plan);
    form.defaults(newValues);
    form.reset();
    newFeature.value = "";
  }
});

// ---------------- FEATURES ----------------
const addFeature = () => {
  if (newFeature.value.trim()) {
    form.features ??= [];
    form.features.push(newFeature.value.trim());
    newFeature.value = "";
  }
};

const removeFeature = (index: number) => {
  if (!form.features) return;

  if (form.features.length > 1) {
    form.features.splice(index, 1);
  } else {
    form.features[index] = "";
  }
};

const moveFeature = (index: number, direction: "up" | "down") => {
  if (!form.features) return;

  if (direction === "up" && index > 0) {
    [form.features[index], form.features[index - 1]] = [
      form.features[index - 1],
      form.features[index],
    ];
  }

  if (direction === "down" && index < form.features.length - 1) {
    [form.features[index], form.features[index + 1]] = [
      form.features[index + 1],
      form.features[index],
    ];
  }
};

const handleFeatureKeydown = (event: KeyboardEvent) => {
  if (event.key === "Enter") {
    event.preventDefault();
    addFeature();
  }
};

// ---------------- SUBMIT ----------------
const submit = () => {
  const featuresArray = form.features ? form.features.filter((f) => f.trim() !== "") : [];

  const formData = {
    name: form.name,
    price: parseFloat(form.price as string) || 0,
    discount: parseFloat(form.discount as string) || 0,
    duration_days: parseInt(form.duration_days as string) || 0,
    description: form.description?.trim() || null,
    features: featuresArray.length ? featuresArray : null,
    is_popular: Boolean(form.is_popular),
    sort_order: Number(form.sort_order || 0),

    permission_ids: form.permission_ids || [],
  };

  if (form.id) {
    form.put(route("plans.update", form.id), {
      preserveScroll: true,
      onSuccess: () => (model.value = false),
    });
  } else {
    form.post(route("plans.store"), {
      preserveScroll: true,
      onSuccess: () => (model.value = false),
    });
  }
};

// ---------------- DELETE ----------------
const alert = useAlertDialog();

const destroy = async () => {
  if (!form.id) return;

  const confirmed = await alert.show({
    title: "Delete Plan",
    description: `Are you sure you want to delete "${form.name}"?`,
    confirmText: "Yes, Delete",
    cancelText: "Cancel",
  });

  if (confirmed) {
    form.delete(route("plans.destroy", form.id), {
      onSuccess: () => (model.value = false),
    });
  }
};
</script>

<template>
  <Dialog v-model:open="model">
    <DialogContent class="!w-7/12 max-w-4xl !overflow-y-auto px-7 max-h-[90vh]">
      <DialogHeader>
        <DialogTitle>
          {{ plan ? `Edit Plan: ${plan.name}` : "Create New Plan" }}
        </DialogTitle>
      </DialogHeader>

      <div class="space-y-6">
        <ValidationErrors />

        <Card>
          <CardContent class="space-y-6 pt-6">
            <!-- BASIC INFO -->
            <div class="space-y-4">
              <h3 class="font-semibold border-b pb-2">Basic Information</h3>

              <div class="grid grid-cols-2 gap-4">
                <TextInput label="Plan Name" v-model="form.name" />
                <TextInput label="Sort Order" type="number" v-model="form.sort_order" />
              </div>

              <div class="grid grid-cols-3 gap-4">
                <TextInput label="Price" type="number" v-model="form.price" />
                <TextInput label="Discount" type="number" v-model="form.discount" />
                <TextInput
                  label="Duration Days"
                  type="number"
                  v-model="form.duration_days"
                />
              </div>

              <textarea
                v-model="form.description"
                rows="3"
                class="w-full border rounded p-2"
                placeholder="Description"
              ></textarea>
            </div>

            <!-- FEATURES -->
            <div class="space-y-3">
              <h3 class="font-semibold border-b pb-2">Features</h3>

              <div class="flex gap-2">
                <TextInput
                  v-model="newFeature"
                  placeholder="Add feature"
                  @keydown="handleFeatureKeydown"
                />
                <AppButton @click="addFeature">
                  <Plus class="w-4 h-4" />
                </AppButton>
              </div>

              <div
                v-for="(feature, i) in form.features"
                :key="i"
                class="flex gap-2 items-center border p-2 rounded"
              >
                <div class="flex flex-col">
                  <button @click="moveFeature(i, 'up')"><ChevronUp /></button>
                  <button @click="moveFeature(i, 'down')"><ChevronDown /></button>
                </div>

                <input v-model="form.features[i]" class="flex-1 border p-1 rounded" />

                <button @click="removeFeature(i)">
                  <Trash2 />
                </button>
              </div>
            </div>

            <!-- PERMISSIONS -->
            <div class="space-y-3">
              <h3 class="font-semibold border-b pb-2">Permissions</h3>

              <div class="grid grid-cols-2 gap-2">
                <label
                  v-for="p in permissions"
                  :key="p.id"
                  class="flex items-center gap-2 border p-2 rounded"
                >
                  <input type="checkbox" :value="p.id" v-model="form.permission_ids" />

                  <div>
                    <div class="font-medium">{{ p.name }}</div>
                    <div class="text-xs text-gray-500">{{ p.label }}</div>
                  </div>
                </label>
              </div>
            </div>

            <!-- POPULAR -->
            <div class="flex items-center justify-between border p-3 rounded">
              <div>
                <div class="font-medium">Popular Plan</div>
                <div class="text-sm text-gray-500">Highlight this plan</div>
              </div>

              <input type="checkbox" v-model="form.is_popular" />
            </div>
          </CardContent>
        </Card>

        <!-- ACTIONS -->
        <div class="flex justify-between">
          <AppButton v-if="form.id" variant="danger" @click="destroy"> Delete </AppButton>

          <div class="flex gap-2">
            <AppButton variant="outline" @click="model = false"> Cancel </AppButton>

            <AppButton @click="submit">
              {{ form.id ? "Update" : "Create" }}
            </AppButton>
          </div>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>
