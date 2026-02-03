<script setup>

const props = defineProps({
    href: {
        type: String,
    },
});

</script>

<template>
    <AppButton label="Cancel" icon="fa-regular:times-circle" variant="outline"  :href v-bind="$attrs" />
</template>

