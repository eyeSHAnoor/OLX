<script setup>
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge'; // adjust to your import

const props = defineProps({
    status: {
        type: String,
        required: true,
    },
    variant: {
        type: String,
        default: 'filled',
    },
});

const colorMap = {
    out_of_stock: 'danger',
    insufficient_inventory: 'warning',
    on_sale: 'info',
    active: 'success',
    inactive: 'danger',
    disabled: 'danger',

    pending: 'warning',
    rejected: 'danger',
    packed: 'warning',
    shipped: 'info',
    completed: 'success',
    default: 'gray',

    expired: 'danger',


};


const color = computed(() => {
    const status = props.status?.toLowerCase(); // just in case
    return colorMap[status] || colorMap.default;
});


function titleCase(str) {
    return str.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}
</script>

<template>
    <Badge :variant="variant" :color="color">
        {{ titleCase(status) }}
    </Badge>
</template>
