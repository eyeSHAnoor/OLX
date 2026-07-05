<script setup lang="ts">
import { InertiaPageProps } from "@/types";
import { usePage, router, useForm, Head } from "@inertiajs/vue3";
import { ref, computed, onMounted } from "vue";
import Layout from "@/layouts/AppLayout.vue";
import { useAlertDialog } from "@/composables/useAlertDialog";
import { Gift, Upload, X } from "lucide-vue-next";

// // UI Components - Make sure these paths match your project structure
// import AppContainer from "@/components/AppContainer.vue";
// import Card from "@/components/ui/card/Card.vue";
// import CardHeader from "@/components/ui/card/CardHeader.vue";
// import CardTitle from "@/components/ui/card/CardTitle.vue";
// import CardDescription from "@/components/ui/card/CardDescription.vue";
// import CardContent from "@/components/ui/card/CardContent.vue";
// import TextInput from "@/components/ui/TextInput.vue";
// import ValidationErrors from "@/components/ValidationErrors.vue";
// import Separator from "@/components/ui/separator/Separator.vue";
// import AppButton from "@/components/ui/AppButton.vue";

// Composables
import { useBreadcrumb } from "@/composables/useBreadcrumb";

defineOptions({ layout: Layout });

interface PageProps extends InertiaPageProps {
    gift?: App.Data.GiftData;
}

const page = usePage<PageProps>();
const gift = computed(() => page.props.gift);
const isEditing = computed(() => !!gift.value);

interface GiftFormData {
    id?: string | number;
    name: string;
    description: string;
    image: File | null;
    quantity: number | string;
    is_active: boolean;
    remove_image: boolean;
}

const getDefaultForm = (item: App.Data.GiftData | undefined): GiftFormData => ({
    id: item?.id ?? "",
    name: item?.name ?? "",
    description: item?.description ?? "",
    image: null,
    quantity: item?.quantity ?? 0,
    is_active: item?.is_active ?? true,
    remove_image: false,
});

const form = useForm<GiftFormData>({ ...getDefaultForm(gift.value) });

// Image preview
const imagePreview = ref<string | null>(null);
const existingImageUrl = computed(() => {
    if (gift.value?.image) {
        return `/storage/${gift.value.image}`;
    }
    return null;
});

const displayImage = computed(() => {
    if (form.remove_image) return null;
    if (imagePreview.value) return imagePreview.value;
    return existingImageUrl.value;
});

// Handle image selection
const onImageSelected = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (file) {
        form.image = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
        form.remove_image = false;
    }
};

const removeImage = () => {
    form.image = null;
    imagePreview.value = null;
    if (isEditing.value && existingImageUrl.value) {
        form.remove_image = true;
    }
};

// Submit form
const submit = () => {
    if (isEditing.value) {
        form
            .transform((data: any) => ({
                ...data,
                _method: "PUT",
            }))
            .post(route("gifts.update", form.id), {
                preserveScroll: true,
                onSuccess: () => {
                    router.visit(route("gifts.index"));
                },
            });
    } else {
        form.post(route("gifts.store"), {
            preserveScroll: true,
            onSuccess: () => {
                router.visit(route("gifts.index"));
            },
        });
    }
};

// Delete gift
const alert = useAlertDialog();
const destroy = async () => {
    if (!form.id) return;
    const confirmed = await alert.show({
        title: "Delete Gift",
        description: `Are you sure you want to delete "${form.name}"? This action cannot be undone.`,
        confirmText: "Yes, Delete",
        cancelText: "Cancel",
    });
    if (confirmed) {
        form.delete(route("gifts.destroy", form.id), {
            preserveScroll: true,
            onSuccess: () => {
                router.visit(route("gifts.index"));
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
        { label: "Gifts", href: route("gifts.index") },
        { label: isEditing.value ? "Edit Gift" : "Create Gift", href: route("gifts.create") },
    ]);
});

// Format date safely
const formatDate = (date: string | undefined) => {
    if (!date) return "N/A";
    return new Date(date).toLocaleDateString();
};
</script>

<template>
    <AppContainer>

        <Head :title="isEditing ? `Edit: ${gift?.name}` : 'Create New Gift'" />

        <div class="my-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        {{ isEditing ? `Edit: ${gift?.name}` : "Create New Gift" }}
                    </h1>
                    <p class="text-muted-foreground mt-2">
                        {{
                            isEditing
                                ? "Update gift details and inventory"
                                : "Add a new gift to your inventory"
                        }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <AppButton label="Cancel" variant="outline" @click="router.visit(route('gifts.index'))"
                        :disabled="form.processing" />
                    <AppButton :label="isEditing ? 'Update Gift' : 'Create Gift'" icon="lucide:check"
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
                        <CardTitle>Gift Information</CardTitle>
                        <CardDescription>Basic details about the gift item</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <ValidationErrors />

                        <div class="grid grid-cols-1 gap-4">
                            <TextInput label="Gift Name *" v-model="form.name" :error="form.errors.name"
                                placeholder="e.g., Premium Headphones" required />

                            <div>
                                <label class="text-sm font-medium block mb-2">Description</label>
                                <textarea v-model="form.description" rows="4"
                                    class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                    :class="{ 'border-destructive': form.errors.description }"
                                    placeholder="Describe the gift item..."></textarea>
                                <p v-if="form.errors.description" class="text-sm text-destructive mt-1">
                                    {{ form.errors.description }}
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <TextInput label="Quantity *" v-model="form.quantity" type="number"
                                    :error="form.errors.quantity" placeholder="0" required min="0" />

                                <div class="flex items-end">
                                    <label class="flex items-center gap-2 pb-2">
                                        <input type="checkbox" v-model="form.is_active"
                                            class="rounded border-gray-300 text-primary focus:ring-primary" />
                                        <span class="text-sm font-medium">Gift is active</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Gift Image</CardTitle>
                        <CardDescription>Upload an image for this gift (optional)</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <!-- Image Preview -->
                            <div v-if="displayImage" class="relative inline-block">
                                <img :src="displayImage" alt="Gift preview"
                                    class="w-48 h-48 object-cover rounded-lg border" />
                                <button @click="removeImage"
                                    class="absolute -top-2 -right-2 bg-red-500 text-white p-1 rounded-full hover:bg-red-600 transition-colors">
                                    <X class="size-4" />
                                </button>
                            </div>

                            <!-- Upload Area -->
                            <div v-if="!displayImage">
                                <label
                                    class="border-2 border-dashed border-muted-foreground/25 rounded-lg p-8 text-center cursor-pointer hover:bg-muted/50 transition-colors block">
                                    <div class="flex flex-col items-center justify-center">
                                        <div
                                            class="size-12 rounded-full bg-primary/10 flex items-center justify-center mb-4">
                                            <Upload class="size-6 text-primary" />
                                        </div>
                                        <p class="text-sm font-medium">Click to upload gift image</p>
                                        <p class="text-xs text-muted-foreground mt-1">
                                            PNG, JPG, GIF up to 2MB
                                        </p>
                                    </div>
                                    <input type="file" accept="image/*" @change="onImageSelected" class="hidden" />
                                </label>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Gift Preview</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div class="aspect-square rounded-lg bg-muted overflow-hidden">
                                <img v-if="displayImage" :src="displayImage" alt="Gift preview"
                                    class="w-full h-full object-cover" />
                                <div v-else class="w-full h-full flex items-center justify-center">
                                    <div class="text-center">
                                        <Gift class="size-16 text-muted-foreground mx-auto mb-2" />
                                        <p class="text-sm text-muted-foreground">No image</p>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <h3 class="font-semibold line-clamp-1">
                                    {{ form.name || "Gift Name" }}
                                </h3>
                                <p v-if="form.description" class="text-sm text-muted-foreground line-clamp-2">
                                    {{ form.description }}
                                </p>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-muted-foreground">Quantity:</span>
                                    <span class="font-semibold">{{ form.quantity || 0 }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-muted-foreground">Status:</span>
                                    <span :class="form.is_active ? 'text-green-600' : 'text-red-600'"
                                        class="font-medium text-sm">
                                        {{ form.is_active ? "Active" : "Inactive" }}
                                    </span>
                                </div>
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
                            <AppButton :label="isEditing ? 'Update Gift' : 'Create Gift'" icon="lucide:check"
                                :processing="form.processing" @click="submit"
                                class="bg-brand-orange hover:bg-brand-orange/80 w-full justify-center" />

                            <AppButton label="Cancel" variant="outline" class="w-full justify-center"
                                @click="router.visit(route('gifts.index'))" :disabled="form.processing" />

                            <AppButton v-if="isEditing" label="Delete Gift" variant="danger" icon="lucide:trash-2"
                                class="w-full justify-center" @click="destroy" :disabled="form.processing" />
                        </div>
                    </CardContent>
                </Card>

                <!-- Inventory Info -->
                <Card v-if="gift">
                    <CardHeader>
                        <CardTitle>Inventory Info</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Created</span>
                                <span>{{ formatDate(gift.created_at) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Last Updated</span>
                                <span>{{ formatDate(gift.updated_at) }}</span>
                            </div>
                            <Separator />
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Current Stock</span>
                                <span :class="gift.quantity === 0 ? 'text-red-600 font-bold' : 'font-semibold'
                                    ">
                                    {{ gift.quantity }}
                                </span>
                            </div>
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
