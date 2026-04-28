<script setup lang="ts">
import { CardContent } from '@/components/ui/card';
import { useForm } from '@inertiajs/vue3';
import { watch, ref, computed, Text } from 'vue';
import { Plus, Trash2, ChevronUp, ChevronDown } from 'lucide-vue-next';

const { plan } = defineProps<{
    plan?: App.Models.Plan;
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
}

const getDefaultForm = (item: App.Models.Plan | undefined): PlanFormData => ({
    id: item?.id ?? '',
    name: item?.name ?? '',
    price: item?.price ?? '',
    discount: item?.discount ?? '',
    duration_days: item?.duration_days ?? '',
    description: item?.description ?? '',
    features: item?.features?.length ? [...item.features] : [''],
    is_popular: item?.is_popular ?? false,
    sort_order: item?.sort_order ?? 0,
});

const form = useForm<PlanFormData>({ ...getDefaultForm(plan) });

// Refs for features management
const newFeature = ref('');
const model = defineModel<boolean>();

watch(model, (isOpen) => {
    if (isOpen) {
        const newValues = getDefaultForm(plan);
        form.defaults(newValues);
        form.reset();
        newFeature.value = '';
    }
});

// Add a new feature
const addFeature = () => {
    if (newFeature.value.trim()) {
        if (!form.features) {
            form.features = [];
        }
        form.features.push(newFeature.value.trim());
        newFeature.value = '';
    }
};

// Remove a feature
const removeFeature = (index: number) => {
    if (form.features && form.features.length > 1) {
        form.features.splice(index, 1);
    } else if (form.features) {
        form.features[index] = '';
    }
};

// Move feature up/down
const moveFeature = (index: number, direction: 'up' | 'down') => {
    if (!form.features) return;

    if (direction === 'up' && index > 0) {
        [form.features[index], form.features[index - 1]] = [form.features[index - 1], form.features[index]];
    } else if (direction === 'down' && index < form.features.length - 1) {
        [form.features[index], form.features[index + 1]] = [form.features[index + 1], form.features[index]];
    }
};

// Watch for Enter key in feature input
const handleFeatureKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Enter') {
        event.preventDefault();
        addFeature();
    }
};

const submit = () => {
    // Clean up features - remove empty strings and ensure it's null if empty
    const featuresArray = form.features
        ? form.features.filter(feature => feature.trim() !== '')
        : [];

    const formData = {
        name: form.name,
        price: parseFloat(form.price as string) || 0,
        duration_days: parseInt(form.duration_days as string) || 0,
        description: form.description?.trim() || null,
        features: featuresArray.length > 0 ? featuresArray : null,
        is_popular: Boolean(form.is_popular),
        sort_order: Number(form.sort_order || 0),
    };

    if (form.id) {
        form.put(route('plans.update', form.id), {
            preserveScroll: true,
            onSuccess: () => (model.value = false),
        });
    } else {
        form.post(route('plans.store'), {
            preserveScroll: true,
            onSuccess: () => (model.value = false),
        });
    }
};

const alert = useAlertDialog();

const destroy = async () => {
    if (!form.id) return;

    const confirmed = await alert.show({
        title: 'Delete Plan',
        description: `Are you sure you want to delete "${form.name}"? This action cannot be undone.`,
        confirmText: 'Yes, Delete',
        cancelText: 'Cancel',
    });

    if (confirmed) {
        form.delete(route('plans.destroy', form.id), {
            onSuccess: () => (model.value = false),
        });
    }
};
</script>

<template>
    <Dialog v-model:open="model">
        <DialogContent class="!w-7/12 max-w-4xl !overflow-y-auto px-7 max-h-[90vh]">
            <DialogHeader class="!px-0 !pb-0">
                <DialogTitle class="text-xl font-semibold">
                    {{ plan ? `Edit Plan: ${plan.name}` : 'Create New Plan' }}
                </DialogTitle>
            </DialogHeader>

            <div class="mt-3 grid gap-y-4 overflow-y-auto">
                <ValidationErrors />

                <Card>
                    <CardContent class="space-y-6 pt-6">
                        <!-- Basic Information Section -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b pb-2">
                                Basic Information
                            </h3>

                            <div class="grid grid-cols-2 gap-6">
                                <!-- Name -->
                                <div>
                                    <TextInput label="Plan Name *" v-model="form.name" :error="form.errors.name"
                                        required placeholder="e.g., Premium Plan" class="w-full" />
                                </div>

                                <!-- Sort Order -->
                                <div>
                                    <TextInput label="Sort Order" type="number" v-model="form.sort_order"
                                        :error="form.errors.sort_order" placeholder="0" class="w-full" />
                                </div>
                            </div>

                            <!-- Price + Duration -->
                            <div class="grid grid-cols-2 gap-6">
                                <TextInput label="Price (PKR) *" type="number" v-model="form.price"
                                    :error="form.errors.price" min="0" step="0.01" placeholder="0.00" class="w-full" />

                                <TextInput label="Discount price" type="number" v-model="form.discount"
                                    :error="form.errors.discount" min="0" max="100" step="0.01" placeholder="0"
                                    class="w-full" />

                                <TextInput label="Duration (Days) *" type="number" v-model="form.duration_days"
                                    :error="form.errors.duration_days" min="1" placeholder="30" class="w-full" />
                            </div>

                            <!-- Description -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Description
                                </label>
                                <textarea v-model="form.description" :error="form.errors.description" rows="4"
                                    placeholder="Describe the plan details, benefits, and target audience..."
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white"></textarea>
                            </div>
                        </div>

                        <!-- Features Section -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between border-b pb-2">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    Features
                                </h3>
                                <span class="text-sm text-gray-500">
                                    {{(form.features?.filter(f => f.trim()).length || 0)}} features added
                                </span>
                            </div>

                            <!-- Add New Feature -->
                            <div class="flex gap-2">
                                <TextInput v-model="newFeature" placeholder="Enter a feature (e.g., Unlimited Ads)"
                                    @keydown="handleFeatureKeydown" class="flex-1" />
                                <AppButton type="button" variant="outline" size="sm" @click="addFeature"
                                    :disabled="!newFeature.trim()">
                                    <Plus class="w-4 h-4 mr-1" />
                                    Add
                                </AppButton>
                            </div>

                            <!-- Features List -->
                            <div v-if="form.features?.some(f => f.trim())"
                                class="space-y-2 max-h-60 overflow-y-auto p-2 border rounded-md">
                                <div v-for="(feature, index) in form.features" :key="index"
                                    class="flex items-center gap-2 p-3 bg-gray-50 dark:bg-gray-800 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    <!-- Reorder Buttons -->
                                    <div class="flex flex-col">
                                        <button type="button" @click="moveFeature(index, 'up')" :disabled="index === 0"
                                            class="p-1 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 disabled:opacity-30">
                                            <ChevronUp class="w-4 h-4" />
                                        </button>
                                        <button type="button" @click="moveFeature(index, 'down')"
                                            :disabled="index === form.features!.length - 1"
                                            class="p-1 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 disabled:opacity-30">
                                            <ChevronDown class="w-4 h-4" />
                                        </button>
                                    </div>

                                    <!-- Feature Input -->
                                    <input type="text" v-model="form.features![index]" placeholder="Feature description"
                                        class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />

                                    <!-- Remove Button -->
                                    <button type="button" @click="removeFeature(index)"
                                        class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors"
                                        :disabled="form.features!.length === 1 && !form.features![0].trim()">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>

                            <div v-else
                                class="text-center py-8 text-gray-500 dark:text-gray-400 border-2 border-dashed rounded-md">
                                <p>No features added yet. Add features using the input above.</p>
                            </div>

                            <!-- Features Helper Text -->
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Press Enter or click Add to add features. Features can be reordered using the up/down
                                arrows.
                            </p>
                        </div>

                        <!-- Popular Plan Toggle -->
                        <div
                            class="flex items-center justify-between p-4 border rounded-lg bg-gray-50 dark:bg-gray-800">
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-gray-100">Popular Plan</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Mark this plan as popular. It will be highlighted on the pricing page.
                                </p>
                            </div>
                            <div class="flex items-center">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" v-model="form.is_popular" class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
                                    </div>
                                    <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">
                                        {{ form.is_popular ? 'Popular' : 'Standard' }}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Action Buttons -->
                <DialogFooter class="pt-4 border-t">
                    <div class="flex items-center justify-between w-full">
                        <div>
                            <AppButton v-if="form.id" label="Delete Plan" icon="lucide:trash-2" variant="danger"
                                size="sm" @click="destroy" :disabled="form.processing" />
                        </div>
                        <div class="flex gap-3">
                            <AppButton size="sm" variant="outline" label="Cancel" @click="model = false"
                                :disabled="form.processing" />
                            <AppButton size="sm" :processing="form.processing"
                                :label="form.id ? 'Update Plan' : 'Create Plan'" @click="submit"
                                :disabled="!form.name || !form.price || !form.duration_days" />
                        </div>
                    </div>
                </DialogFooter>
            </div>
        </DialogContent>
    </Dialog>
</template>

<style scoped>
/* Custom scrollbar for features list */
.max-h-60::-webkit-scrollbar {
    width: 6px;
}

.max-h-60::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.max-h-60::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.max-h-60::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.dark .max-h-60::-webkit-scrollbar-track {
    background: #374151;
}

.dark .max-h-60::-webkit-scrollbar-thumb {
    background: #6b7280;
}

.dark .max-h-60::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}
</style>