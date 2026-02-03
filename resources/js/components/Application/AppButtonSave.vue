<script setup>

const props = defineProps({
    href: {
        type: String,
    },
});

</script>

<template>
    <AppButton label="Save" icon="fa-regular:times-circle" variant="outline" :href v-bind="$attrs" />
</template>
