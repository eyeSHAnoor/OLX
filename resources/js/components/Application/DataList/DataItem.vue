<script setup>
const props = defineProps({
    label: {
        type: String,
        required: true,
    },
    cols: {
        type: String,
        default: 'sm:grid-cols-2', // Default to 2 columns on sm screens
    },
    detailsColsSpan: {
        type: String,
        default: 'sm:col-span-1', // Default to 2 columns on sm screens
    },
    gap: {
        type: String,
        default: 'sm:gap-4', // Default gap
    },
    padding: {
        type: String,
        default: 'sm:px-0', // Default padding
    },
});

// Dynamically build grid classes
const gridClass = computed(() => [
    props.cols,
    props.gap,
    props.padding,
    'sm:grid', // Ensure grid is only applied on sm screens
]);
</script>

<template>
    <div class="grid" :class="gridClass">
        <dt class="text-sm/6 font-medium text-foreground">
            <slot name="label">
                {{ label }}
            </slot>
        </dt>
        <dd :class="['mt-1 flex items-center gap-x-1 text-sm/6 text-foreground sm:mt-0', detailsColsSpan]">
            <slot />
        </dd>
    </div>
</template>
