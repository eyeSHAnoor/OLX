<script setup lang="ts">
import { CardContent } from '@/components/ui/card';
import { usePage } from '@inertiajs/vue3';
import { computed, watch, ref } from 'vue';
import CategoryTreeSelect from '@/components/CategoryTreeSelect.vue';

const { brand, categories } = defineProps<{
    brand?: App.Data.BrandData;
    categories: App.Data.CategoryData[];
}>();

interface BrandFormData {
    id?: string | number;
    name: string;
    category_ids: (string | number)[];
}

const getDefaultForm = (item: App.Data.BrandData | undefined): BrandFormData => ({
    id: item?.id ?? '',
    name: item?.name ?? '',
    category_ids: item?.categories?.map(cat => cat.id) ?? [],
});

const page = usePage();
const form = useForm<BrandFormData>({ ...getDefaultForm(brand) });
const model = defineModel();

watch(model, (isOpen) => {
    if (isOpen) {
        const newValues = getDefaultForm(brand);
        form.defaults(newValues);
        form.reset();
    }
});

const submit = () => {
    if (form.id) {
        form.put(route('brands.update', form.id), {
            preserveScroll: true,
            onSuccess: () => (model.value = false),
        });
    } else {
        form.post(route('brands.store'), {
            preserveScroll: true,
            onSuccess: () => (model.value = false),
        });
    }
};

const alert = useAlertDialog();
const destroy = async () => {
    if (!form.id) return;

    const confirmed = await alert.show({
        title: 'Delete Brand',
        description: `Are you sure you want to delete "${form.name}"? This action cannot be undone.`,
        confirmText: 'Yes, Delete',
        cancelText: 'Cancel',
    });

    if (confirmed) {
        form.delete(route('brands.destroy', form.id), {
            onSuccess: () => (model.value = false),
        });
    }
};
</script>

<template>
    <Dialog v-model:open="model">
        <DialogContent class="!w-6/12 !overflow-y-auto px-7">
            <DialogHeader class="!px-0 !pb-0">
                <DialogTitle>
                    {{ brand ? `Edit Brand: ${brand.name}` : 'Create New Brand' }}
                </DialogTitle>
            </DialogHeader>

            <div class="mt-3 grid gap-y-4">
                <ValidationErrors />

                <Card>
                    <CardContent class="space-y-4 pt-4">
                        <TextInput label="Brand Name" v-model="form.name" :error="form.errors.name"
                            placeholder="Enter brand name" required />

                        <div class="space-y-2">
                            <label class="text-sm font-medium">Assign to Categories</label>
                            <p class="text-xs text-muted-foreground mb-2">
                                Select categories where this brand will be available
                            </p>

                            <CategoryTreeSelect v-model="form.category_ids" :allCategories="categories"
                                :error="form.errors.category_ids" :initialSelected="form.category_ids" multiple />
                        </div>
                    </CardContent>
                </Card>

                <DialogFooter>
                    <div class="flex items-center justify-between gap-1 w-full">
                        <AppButton v-if="form.id" label="Delete" icon="lucide:trash-2" variant="danger" size="sm"
                            :processing="form.processing" @click="destroy" />
                        <div class="ml-auto flex items-center gap-2">
                            <AppButton size="sm" variant="outline" label="Cancel" @click="model = false"
                                :disabled="form.processing" />
                            <AppButton size="sm" :processing="form.processing" label="Save" @click="submit" />
                        </div>
                    </div>
                </DialogFooter>
            </div>
        </DialogContent>
    </Dialog>
</template>