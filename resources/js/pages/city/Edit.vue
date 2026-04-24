<script setup lang="ts">
import CityForm from './_partials/CityForm.vue'

defineProps<{
    city: App.Data.CityData & {
        regions?: Array<{ id: number; name: string }>
    }
}>()
</script>

<template>
    <CityForm :city="city" />
</template>