<!-- CategoryAccordion.vue -->
<script setup>
import CategoryDetailsModal from './CategoryDetailsModal.vue';
import { router } from '@inertiajs/vue3';

defineProps({
    categories: {
        type: Array,
        required: true
    },
    classes: {
        type: String,
        default: ''
    },
    allCategories: {
        type: Array,
        required: true
    },
    level: {
        type: Number,
        default: 0
    }
});

const emit = defineEmits(['categoryEdited', 'categoryDeleted']);

const { handleShowModal, showModal, selectedItem } = useModal();

const editCategory = (category) => {
    handleShowModal(category);
};

const handleCategoryUpdated = () => {
    emit('categoryEdited');
};
</script>

<template>
    <Accordion type="single" collapsible :class="['w-full', classes]" :style="{ '--level': level }">
        <AccordionItem v-for="category in categories" :key="category.id" :value="`category-${category.id}`" :class="[
            'border-l-2 transition-colors',
            level === 0 ? 'border-l-primary' :
                level === 1 ? 'ml-10 border-l-blue-400' :
                    'ml-16 border-l-purple-400'
        ]">
            <AccordionTrigger class="hover:bg-muted/50 transition-colors px-4 py-3 group" :class="{
                'bg-muted/30': level === 0,
                'pl-4': level === 0,
                'pl-6': level === 1,
                'pl-8': level > 1
            }">
                <template #icon>
                    <div class="flex items-center gap-1">
                        <!-- Position Indicator -->
                        <span v-if="category.position"
                            class="inline-flex items-center justify-center size-6 text-xs font-medium bg-primary/10 text-primary rounded-full">
                            {{ category.position }}
                        </span>
                    </div>
                </template>

                <div class="flex items-center justify-between flex-1 min-w-0">
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <!-- Level Indicator -->
                        <div class="flex items-center">
                            <Icon icon="teenyicons:down-solid" class="size-3 text-muted-foreground" />
                        </div>

                        <!-- Category Name -->
                        <div class="flex items-center gap-4 flex-1 min-w-0">
                            <div class="size-10 flex-shrink-0 rounded overflow-hidden bg-muted">
                                <img v-if="category.files?.length" :src="`${category.files[0].file_url}`"
                                    :alt="category.name" class="w-full h-full object-cover rounded-full" />
                                <div v-else class="w-full h-full flex items-center justify-center">
                                    <Icon icon="lucide:image" class="size-4 text-muted-foreground" />
                                </div>
                            </div>
                            <span class="font-medium truncate">{{ category.name }}</span>

                            <!-- Badges -->
                            <div class="flex items-center gap-1 shrink-0">
                                <!-- Subcategory Count Badge -->
                                <span v-if="category.children_recursive?.length"
                                    class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-primary/10 text-primary rounded-full">
                                    {{ category.children_recursive.length }} sub
                                </span>

                                <!-- Top-level Badge -->
                                <span v-if="!category.parent_id"
                                    class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                    Main
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-1 shrink-0 ml-2">
                        <!-- Edit Button -->
                        <AppButton size="xs" icon="lucide:edit" variant="ghost" no-label
                            class="opacity-0 group-hover:opacity-100 transition-opacity"
                            @click="router.visit(route('categories.edit', category.id))"
                            :title="`Edit ${category.name}`" />
                    </div>
                </div>
            </AccordionTrigger>

            <AccordionContent class="pt-2">
                <!-- Recursive Children -->
                <CategoryAccordion v-if="category.children_recursive?.length" :categories="category.children_recursive"
                    :allCategories="allCategories" :level="level + 1" />

                <!-- Empty Children State -->
                <div v-else class="text-sm text-muted-foreground px-4 py-3 italic" :class="{
                    'pl-4': level === 0,
                    'pl-6': level === 1,
                    'pl-8': level > 1
                }">
                    No subcategories
                </div>
            </AccordionContent>
        </AccordionItem>
    </Accordion>

    <!-- Modal for Editing -->
    <CategoryDetailsModal :category="selectedItem" v-model="showModal" :allCategories="allCategories"
        @success="handleCategoryUpdated" />
</template>

<style scoped>
[data-state="open"] .accordion-trigger {
    background-color: hsl(var(--muted) / 0.5);
}
</style>