<script setup lang="ts" generic="TData, TValue">
import { watch, computed, ref } from 'vue';
import type { Column } from '@tanstack/vue-table';
import { cn } from '@/lib/utils';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList, CommandSeparator } from '@/components/ui/command';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Separator } from '@/components/ui/separator';

interface DataTableFacetedFilter {
    modelValue?: string[];
    title?: string;
    showSelectedItem?: boolean;
    options: {
        label: string;
        value: string;
        icon?: string;
    }[];
}

const props = withDefaults(defineProps<DataTableFacetedFilter>(), {
    showSelectedItem: true
});

// const facets = computed(() => props.column?.getFacetedUniqueValues());

const selectedValues = ref<Set<string>>(new Set(props.modelValue ?? []));

// Sync from parent to local state
watch(
    () => props.modelValue,
    (newVal) => {
        selectedValues.value = new Set(newVal ?? []);
    },
);

// const emit = defineEmits(['onSelectedValues']); // Add this for v-model support

const emit = defineEmits<{
    (e: 'update:modelValue', value: string[]): void;
}>();
</script>

<template>
    <Popover>
        <PopoverTrigger as-child>
            <Button variant="secondary" size="sm" class="h-7">
                <Icon icon="lucide:list-filter-plus" class="mr-2 size-4" />
                {{ title }}
                <template v-if="showSelectedItem && selectedValues.size > 0">
                    <Separator orientation="vertical" class="mx-2 h-4" />
                    <Badge variant="secondary" class="rounded-sm px-1 font-normal lg:hidden">
                        {{ selectedValues.size }}
                    </Badge>
                    <div class="hidden space-x-1 lg:flex">
                        <Badge v-if="selectedValues.size > 2" variant="secondary" class="rounded-sm px-1 font-normal">
                            {{ selectedValues.size }} selected
                        </Badge>

                        <template v-else>
                            <Badge
                                v-for="option in options.filter((option) => selectedValues.has(option.value))"
                                :key="option.value"
                                variant="secondary"
                                class="rounded-sm px-1 font-normal"
                            >
                                {{ option.label }}
                            </Badge>
                        </template>
                    </div>
                </template>
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-[200px] p-0 z-[19999]" align="start">
            <Command>
                <CommandInput :placeholder="title" />
                <CommandList>
                    <CommandEmpty>No results found.</CommandEmpty>
                    <CommandGroup>
                        <CommandItem
                            v-for="option in options"
                            :key="option.value"
                            :value="option"
                            @select="
                                () => {
                                    const isSelected = selectedValues.has(option.value);
                                    if (isSelected) selectedValues.delete(option.value);
                                    else selectedValues.add(option.value);

                                    emit('update:modelValue', Array.from(selectedValues));
                                }
                            "
                        >
                            <div
                                :class="
                                    cn(
                                        'mr-2 flex size-4 items-center justify-center rounded-sm border border-primary',
                                        selectedValues.has(option.value) ? 'bg-primary text-neutral-500' : 'opacity-50 [&_svg]:invisible',
                                    )
                                "
                            >
                                <Icon icon="lucide:check" :class="cn('size-4 !text-white')" />
                            </div>
                            <Icon :icon="option.icon" v-if="option.icon" class="mr-2 size-4 text-muted-foreground" />
                            <span>{{ option.label }}</span>
                            <!-- <span
                                v-if="facets?.get(option.value)"
                                class="ml-auto flex size-4 items-center justify-center font-mono text-xs">
                                {{ facets.get(option.value) }}
                            </span> -->
                        </CommandItem>
                    </CommandGroup>

                    <template v-if="selectedValues.size > 0">
                        <CommandSeparator />
                        <CommandGroup>
                            <CommandItem
                                :value="{ label: 'Clear filters' }"
                                class="justify-center text-center"
                                @select="
                                    () => {
                                        selectedValues.clear();
                                        emit('update:modelValue', []);
                                    }
                                "
                            >
                                Clear filters
                            </CommandItem>
                        </CommandGroup>
                    </template>
                </CommandList>
            </Command>
        </PopoverContent>
    </Popover>
</template>
