<script setup lang="ts">
import { computed, ref } from 'vue';

interface CategoryTreeSelectProps {
    modelValue: (string | number)[];
    allCategories: App.Data.CategoryData[];
    placeholder?: string;
    multiple?: boolean;
    initialSelected?: (string | number)[];
    error?: string;
}

const props = withDefaults(defineProps<CategoryTreeSelectProps>(), {
    placeholder: 'Select categories...',
    multiple: true,
    initialSelected: () => [],
    error: '',
});

const emit = defineEmits(['update:modelValue']);

const open = ref(false);
const selectedIds = ref<(string | number)[]>(props.initialSelected);

const selectedCategories = computed(() => {
    return props.allCategories.filter(cat => selectedIds.value.includes(cat.id));
});

const toggleCategory = (categoryId: string | number) => {
    if (props.multiple) {
        const index = selectedIds.value.indexOf(categoryId);
        if (index > -1) {
            selectedIds.value.splice(index, 1);
        } else {
            selectedIds.value.push(categoryId);
        }
    } else {
        selectedIds.value = [categoryId];
    }

    emit('update:modelValue', [...selectedIds.value]);
};

const removeCategory = (categoryId: string | number) => {
    const index = selectedIds.value.indexOf(categoryId);
    if (index > -1) {
        selectedIds.value.splice(index, 1);
        emit('update:modelValue', [...selectedIds.value]);
    }
};

// Flatten categories for checkbox display
const flattenCategories = (categories: App.Data.CategoryData[], level = 0): any[] => {
    return categories.flatMap(category => {
        const item = {
            ...category,
            level,
            indent: level * 20
        };

        const children = category.children_recursive ?
            flattenCategories(category.children_recursive, level + 1) : [];

        return [item, ...children];
    });
};

const flatCategories = computed(() => flattenCategories(props.allCategories));
</script>

<template>
    <div class="space-y-2">
        <Popover v-model:open="open">
            <PopoverTrigger as-child>
                <Button variant="outline" role="combobox" :aria-expanded="open" class="w-full justify-between"
                    :class="{ 'border-destructive': error }">
                    <div class="flex flex-wrap gap-1 flex-1 text-left">
                        <template v-if="selectedCategories.length">
                            <span v-for="cat in selectedCategories" :key="cat.id"
                                class="inline-flex items-center px-2 py-1 text-xs bg-primary/10 text-primary rounded-full">
                                {{ cat.name }}
                                <X class="ml-1 h-3 w-3 cursor-pointer" @click.stop="removeCategory(cat.id)" />
                            </span>
                        </template>
                        <span v-else class="text-muted-foreground">
                            {{ placeholder }}
                        </span>
                    </div>
                    <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                </Button>
            </PopoverTrigger>
            <PopoverContent class="w-full p-0">
                <Command class="w-full">
                    <CommandInput :placeholder="placeholder" />
                    <CommandEmpty>No category found.</CommandEmpty>
                    <CommandGroup class="max-h-60 overflow-auto">
                        <CommandList>
                            <CommandItem v-for="category in flatCategories" :key="category.id"
                                :style="{ paddingLeft: `${category.indent + 10}px` }"
                                @select="toggleCategory(category.id)">
                                <Checkbox :checked="selectedIds.includes(category.id)" class="mr-2" />
                                <span>{{ category.name }}</span>
                                <span v-if="!category.parent_id" class="ml-2 text-xs text-blue-600">
                                    (Main)
                                </span>
                                <CommandShortcut v-if="category.children_recursive?.length"
                                    class="ml-auto text-xs text-muted-foreground">
                                    {{ category.children_recursive.length }} sub
                                </CommandShortcut>
                            </CommandItem>
                        </CommandList>
                    </CommandGroup>
                </Command>
            </PopoverContent>
        </Popover>

        <p v-if="error" class="text-sm text-destructive">{{ error }}</p>
    </div>
</template>