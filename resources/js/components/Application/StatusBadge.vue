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

    picking: 'warning',
    packed: 'warning',
    shipped: 'info',
    completed: 'success',

    pending: 'secondary',
    paid: 'success',
    partial_paid: 'success',
    cancelled: 'danger',

    draft: 'secondary',
    partial_received: 'warning',
    received: 'info',
    completed_order: 'success',
    closed: 'success',
    difference_closed: 'warning',

    sent: 'info',
    confirmed: 'success',
    partial: 'warning',
    completed_internal: 'success',
    not_available: 'danger',

    delivered: 'success',
    partial_delivered: 'warning',
    shipped: 'info',

    waiting_for_proforma: 'secondary',
    waiting_for_payment: 'warning',
    payment_confirmation_verification: 'info',
    payment_confirmation_sent: 'success',
    waiting_for_final_invoice: 'warning',
    invoice_uploaded: 'success',

    waiting: 'secondary',
    in_transit: 'info',
    delivered: 'success',
    delay: 'danger',

    IN: 'success',
    Out: 'success',

    ordered: 'secondary',
    in_arrival: 'secondary',
    ready_to_pick: 'warning',
    ready_to_ship: 'info',
    delivered_status: 'success',

    // default: "default",
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
