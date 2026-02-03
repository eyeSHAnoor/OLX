
<script setup>
import { computed } from 'vue'

const props = defineProps({
    processing: {
        type: Boolean,
        default: false
    },
    disabled: {
        type: Boolean,
        default: false
    },
    label: {
        type: String,
        default: 'Delete'
    },
    size: {
        type: String,
        default: 'sm'
    },
    icon: {
        type: String,
        default: 'lucide:trash'
    },
    iconSize: {
        type: String,
        default: 'size-4'
    },
    additionalClasses: {
        type: String,
        default: ''
    }
})

const iconClasses = computed(() => props.iconSize)

</script>

<template>
    <Button
        variant="destructive"
        :class="[additionalClasses]"
        as="button"
        :size="size"
        :disabled="processing || disabled"
    >
        <Icon :icon="processing ? 'codex:loader' : icon" :class="iconClasses" />
        {{ label }}
    </Button>
</template>
