<script setup lang="ts">
import Form from './_partials/Form.vue'

defineProps<{
    brand: App.Data.BrandData & {
        models?: Array<{ id: number; name: string }>
        categories?: App.Data.CategoryData[]
    }
    categories: App.Data.CategoryData[]
}>()
</script>

<template>
    <!-- <div>{{ brand }}</div> -->
    <Form :brand="brand" :categories="categories" />
</template>