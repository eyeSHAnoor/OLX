<script setup lang="ts">
import { InertiaPageProps } from "@/types";
import { usePage, router, useForm } from "@inertiajs/vue3";
import { ref, computed, watch, onMounted } from "vue";
import Layout from "@/layouts/AppLayout.vue";
import { useAlertDialog } from "@/composables/useAlertDialog";
import { Plus, X, Gift, Calendar, Package } from "lucide-vue-next";

defineOptions({ layout: Layout });

interface PageProps extends InertiaPageProps {
    period?: any;
    availableGifts?: App.Data.GiftData[];
    gifts?: App.Data.GiftData[];
}

const page = usePage<PageProps>();
const period = computed(() => page.props.period);
const availableGifts = computed(() => page.props.availableGifts || page.props.gifts);
const isEditing = computed(() => !!period.value);

interface GiftAllocation {
    gift_id: number | string;
    quantity: number;
    notes: string;
}

interface CampaignFormData {
    id?: string | number;
    name: string;
    start_date: string;
    end_date: string;
    is_active: boolean;
    gifts: GiftAllocation[];
}

const getDefaultForm = (item: any | undefined): CampaignFormData => ({
    id: item?.id ?? "",
    name: item?.name ?? "",
    start_date: item?.start_date ?? "",
    end_date: item?.end_date ?? "",
    is_active: item?.is_active ?? true,
    gifts: item?.campaign_gifts?.map((cg: any) => ({
        gift_id: cg.gift_id,
        quantity: cg.allocated_quantity,
        notes: cg.notes || "",
    })) ?? [{ gift_id: "", quantity: 1, notes: "" }],
});

const form = useForm<CampaignFormData>({ ...getDefaultForm(period.value) });

// Computed properties
const selectedGiftIds = computed(() =>
    form.gifts.map((g) => g.gift_id).filter((id) => id !== "")
);

const availableGiftsFiltered = computed(() => {
    return availableGifts.value?.filter((gift) => {
        // In edit mode, allow already selected gifts
        if (isEditing.value) {
            const existingGift = period.value?.campaign_gifts?.find(
                (cg) => cg.gift_id === gift.id
            );
            if (existingGift) return true;
        }
        return !selectedGiftIds.value.includes(gift.id);
    });
});

const totalGiftsAllocated = computed(() => {
    return form.gifts.reduce((sum, gift) => sum + (gift.quantity || 0), 0);
});

// Add/Remove gift allocations
const addGiftAllocation = () => {
    form.gifts.push({
        gift_id: "",
        quantity: 1,
        notes: "",
    });
};

const removeGiftAllocation = (index: number) => {
    if (form.gifts.length > 1) {
        form.gifts.splice(index, 1);
    }
};

// Get gift details by ID
const getGiftDetails = (giftId: number | string) => {
    return availableGifts.value?.find((g) => g.id == giftId);
};

// Validate minimum date
const minEndDate = computed(() => {
    return form.start_date || new Date().toISOString().split("T")[0];
});

// Submit form
const submit = () => {
    const formData = {
        ...form,
    };

    if (isEditing.value) {
        formData
            .transform((data: any) => ({
                ...data,
                _method: "PUT",
            }))
            .post(route("gift-campaigns.update", form.id), {
                preserveScroll: true,
                onSuccess: () => {
                    router.visit(route("gift-campaigns.index"));
                },
            });
    } else {
        formData.post(route("gift-campaigns.store"), {
            preserveScroll: true,
            onSuccess: () => {
                router.visit(route("gift-campaigns.index"));
            },
        });
    }
};

// Delete campaign
const alert = useAlertDialog();
const destroy = async () => {
    if (!form.id) return;
    const confirmed = await alert.show({
        title: "Delete Campaign",
        description: `Are you sure you want to delete "${form.name}"? This will remove all assignments and cannot be undone.`,
        confirmText: "Yes, Delete",
        cancelText: "Cancel",
    });
    if (confirmed) {
        form.delete(route("gift-campaigns.destroy", form.id), {
            preserveScroll: true,
            onSuccess: () => {
                router.visit(route("gift-campaigns.index"));
            },
        });
    }
};

// Breadcrumbs
const { set, resetList } = useBreadcrumb();
onMounted(() => {
    resetList();
    set([
        { label: "Home", href: "/dashboard" },
        { label: "Gift Campaigns", href: route("gift-campaigns.index") },
        {
            label: isEditing.value ? "Edit Campaign" : "Create Campaign",
            href: route("gift-campaigns.create"),
        },
    ]);
});

// Format date for display
const formatDate = (date: string) => {
    if (!date) return "";
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};
</script>

<template>
    <AppContainer>

        <Head :title="isEditing ? `Edit: ${period?.name}` : 'Create Gift Campaign'" />

        <div class="my-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        {{ isEditing ? `Edit: ${period?.name}` : "Create New Gift Campaign" }}
                    </h1>
                    <p class="text-muted-foreground mt-2">
                        {{
                            isEditing
                                ? "Update your gift campaign details and allocations"
                                : "Set up a new gift distribution campaign for loyal subscribers"
                        }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <AppButton label="Cancel" variant="outline" @click="router.visit(route('gift-campaigns.index'))"
                        :disabled="form.processing" />
                    <AppButton :label="isEditing ? 'Update Campaign' : 'Create Campaign'" icon="lucide:check"
                        :processing="form.processing" @click="submit"
                        class="bg-brand-orange hover:bg-brand-orange/80" />
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Campaign Information</CardTitle>
                        <CardDescription> Basic details about the gift campaign </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <ValidationErrors />

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <TextInput label="Campaign Name *" v-model="form.name" :error="form.errors.name"
                                    placeholder="e.g., Q1 2024 Loyalty Rewards" required />
                            </div>

                            <div>
                                <label class="text-sm font-medium block mb-2">Start Date *</label>
                                <input type="date" v-model="form.start_date"
                                    :min="new Date().toISOString().split('T')[0]"
                                    class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                    :class="{ 'border-destructive': form.errors.start_date }" required />
                                <p v-if="form.errors.start_date" class="text-sm text-destructive mt-1">
                                    {{ form.errors.start_date }}
                                </p>
                            </div>

                            <div>
                                <label class="text-sm font-medium block mb-2">End Date *</label>
                                <input type="date" v-model="form.end_date" :min="minEndDate"
                                    class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                    :class="{ 'border-destructive': form.errors.end_date }" required />
                                <p v-if="form.errors.end_date" class="text-sm text-destructive mt-1">
                                    {{ form.errors.end_date }}
                                </p>
                            </div>

                            <div class="md:col-span-2">
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" v-model="form.is_active"
                                        class="rounded border-gray-300 text-primary focus:ring-primary" />
                                    <span class="text-sm font-medium">Campaign is active</span>
                                </label>
                                <p class="text-xs text-muted-foreground mt-1 ml-6">
                                    Inactive campaigns won't be available for gift assignments
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle>Gift Allocations</CardTitle>
                                <CardDescription>
                                    Select gifts and allocate quantities for this campaign
                                </CardDescription>
                            </div>
                            <Button @click="addGiftAllocation" variant="outline" size="sm"
                                :disabled="availableGiftsFiltered?.length === 0">
                                <Plus class="size-4 mr-2" />
                                Add Gift
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div v-if="form.errors.gifts" class="text-sm text-destructive mb-2">
                            {{ form.errors.gifts }}
                        </div>

                        <div v-for="(allocation, index) in form.gifts" :key="index"
                            class="p-4 border rounded-lg space-y-3 relative">
                            <!-- Remove button -->
                            <button v-if="form.gifts.length > 1" @click="removeGiftAllocation(index)"
                                class="absolute top-2 right-2 text-muted-foreground hover:text-destructive">
                                <X class="size-4" />
                            </button>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="text-sm font-medium block mb-2">Select Gift *</label>
                                    <select v-model="allocation.gift_id"
                                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                        :class="{
                                            'border-destructive': form.errors[`gifts.${index}.gift_id`],
                                        }">
                                        <option value="">Choose a gift</option>
                                        <option v-for="gift in availableGiftsFiltered" :key="gift.id" :value="gift.id">
                                            {{ gift.name }} ({{ gift.quantity }} available)
                                        </option>
                                        <!-- Show current selection in edit mode -->
                                        <option v-if="isEditing && allocation.gift_id" :value="allocation.gift_id"
                                            selected>
                                            {{ getGiftDetails(allocation.gift_id)?.name }} (Current)
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="text-sm font-medium block mb-2">Quantity *</label>
                                    <input type="number" v-model.number="allocation.quantity" min="1"
                                        :max="getGiftDetails(allocation.gift_id)?.quantity || 999"
                                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                        :class="{
                                            'border-destructive': form.errors[`gifts.${index}.quantity`],
                                        }" />
                                    <p class="text-xs text-muted-foreground mt-1">
                                        Available: {{ getGiftDetails(allocation.gift_id)?.quantity || 0 }}
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium block mb-2">Notes</label>
                                    <input type="text" v-model="allocation.notes" placeholder="Optional notes"
                                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary" />
                                </div>
                            </div>

                            <!-- Gift preview card -->
                            <div v-if="allocation.gift_id && getGiftDetails(allocation.gift_id)"
                                class="flex items-center gap-3 p-3 bg-muted/50 rounded-lg">
                                <div class="size-12 rounded-lg overflow-hidden bg-muted flex-shrink-0">
                                    <img v-if="getGiftDetails(allocation.gift_id)?.image"
                                        :src="`/storage/${getGiftDetails(allocation.gift_id).image}`"
                                        :alt="getGiftDetails(allocation.gift_id).name"
                                        class="w-full h-full object-cover" />
                                    <div v-else class="w-full h-full flex items-center justify-center">
                                        <Gift class="size-6 text-muted-foreground" />
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-sm truncate">
                                        {{ getGiftDetails(allocation.gift_id)?.name }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        Allocated: {{ allocation.quantity }} units
                                    </p>
                                </div>
                            </div>

                            <p v-if="form.errors[`gifts.${index}.gift_id`]" class="text-sm text-destructive">
                                {{ form.errors[`gifts.${index}.gift_id`] }}
                            </p>
                            <p v-if="form.errors[`gifts.${index}.quantity`]" class="text-sm text-destructive">
                                {{ form.errors[`gifts.${index}.quantity`] }}
                            </p>
                        </div>

                        <!-- Empty state -->
                        <div v-if="form.gifts.length === 0" class="text-center py-8 text-muted-foreground">
                            <Gift class="size-12 mx-auto mb-3 opacity-50" />
                            <p>No gifts allocated yet</p>
                            <AppButton @click="addGiftAllocation" variant="outline" size="sm" class="mt-2">
                                Add First Gift
                            </AppButton>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Campaign Summary</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Campaign Name</span>
                                <span class="font-medium">{{ form.name || "Untitled" }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Duration</span>
                                <span class="font-medium">
                                    {{
                                        form.start_date && form.end_date
                                            ? `${formatDate(form.start_date)} - ${formatDate(form.end_date)}`
                                    : "Not set"
                                    }}
                                </span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Status</span>
                                <span :class="form.is_active ? 'text-green-600' : 'text-red-600'" class="font-medium">
                                    {{ form.is_active ? "Active" : "Inactive" }}
                                </span>
                            </div>
                            <Separator />
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Total Gifts</span>
                                <span class="font-medium">{{ form.gifts.length }} types</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Total Allocated</span>
                                <span class="font-medium">{{ totalGiftsAllocated }} units</span>
                            </div>
                        </div>

                        <!-- Gift allocation summary -->
                        <div v-if="form.gifts.some((g) => g.gift_id)" class="space-y-2">
                            <Separator />
                            <h4 class="text-sm font-medium">Gift Breakdown</h4>
                            <div v-for="allocation in form.gifts.filter((g) => g.gift_id)" :key="allocation.gift_id"
                                class="flex items-center justify-between text-sm p-2 bg-muted/50 rounded">
                                <div class="flex items-center gap-2 min-w-0">
                                    <Gift class="size-3 flex-shrink-0 text-muted-foreground" />
                                    <span class="truncate">{{
                                        getGiftDetails(allocation.gift_id)?.name
                                        }}</span>
                                </div>
                                <span class="font-medium ml-2">{{ allocation.quantity }}x</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Actions</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex flex-col gap-2">
                            <AppButton :label="isEditing ? 'Update Campaign' : 'Create Campaign'" icon="lucide:check"
                                :processing="form.processing" @click="submit"
                                class="bg-brand-orange hover:bg-brand-orange/80 w-full justify-center" />

                            <AppButton label="Cancel" variant="outline" class="w-full justify-center"
                                @click="router.visit(route('gift-campaigns.index'))" :disabled="form.processing" />

                            <AppButton v-if="isEditing" label="Delete Campaign" variant="danger" icon="lucide:trash-2"
                                class="w-full justify-center" @click="destroy" :disabled="form.processing" />
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppContainer>
</template>

<style scoped>
input:focus,
textarea:focus,
select:focus {
    outline: none;
    ring: 2px;
}
</style>
