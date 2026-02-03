<script setup lang="ts">
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Icon } from '@iconify/vue';

interface RadioOption {
    value: string;
    label: string;
    icon?: string;
}

interface Props {
    modelValue: string | number | boolean | null | undefined;
    options: RadioOption[];
    layout?: 'grid' | 'horizontal' | 'vertical';
    gridCols?: number;
    gap?: string;
    iconSize?: string;
    showTick?: boolean;
    groupName?: string;
    labelPosition?: 'top' | 'bottom' | 'left' | 'right';
}

const props = withDefaults(defineProps<Props>(), {
    layout: 'grid',
    gridCols: 2,
    gap: '4',
    iconSize: '7',
    showTick: false,
    labelPosition: 'bottom',
});

const emit = defineEmits(['update:modelValue']);

const selectedValue = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const containerClasses = computed(() => {
    switch (props.layout) {
        case 'horizontal':
            return `flex flex-row flex-wrap gap-${props.gap}`;
        case 'vertical':
            return `flex flex-col gap-${props.gap}`;
        default:
            return `grid grid-cols-${props.gridCols} gap-${props.gap}`;
    }
});

const labelClasses = computed(() => {
    const base =
        'rounded-md border-2 border-muted bg-popover p-4 peer-data-[state=checked]:border-primary peer-data-[state=checked]:bg-accent hover:bg-accent hover:text-accent-foreground [&:has([data-state=checked])]:border-primary relative';

    return `${base}`;
});

const contentClasses = computed(() => {
    const directions = {
        top: 'flex-col-reverse items-center',
        bottom: 'flex-col items-center',
        left: 'flex-row items-center',
        right: 'flex-row-reverse items-center',
    };

    return `flex ${directions[props.labelPosition]} gap-2 w-full`;
});
</script>

<template>
    <RadioGroup v-model="selectedValue" class="mt-2" :class="containerClasses" :name="groupName" :id="groupName">
        <div v-for="item in options" :key="item.value">
            <RadioGroupItem :id="item.value" :value="item.value" class="peer sr-only" />
            <Label :for="item.value" :class="labelClasses">
                <div :class="contentClasses">
                    <Icon v-if="item.icon" :icon="item.icon" :class="`size-${iconSize}`" />
                    <span>{{ item.label }}</span>

                    <span v-if="showTick && selectedValue === item.value && layout === 'vertical'" class="ml-auto">
                        <Icon icon="mdi:tick-circle" class="size-4 text-green-600" />
                    </span>
                </div>

                <span v-if="showTick && selectedValue === item.value && (layout === 'horizontal' || layout === 'grid')"
                      class="absolute top-1 right-1">
                    <Icon icon="mdi:tick-circle" class="size-4 text-green-600" />
                </span>
            </Label>
        </div>
    </RadioGroup>
</template>
