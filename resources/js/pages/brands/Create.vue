<script setup lang="ts">
import Form from './_partials/Form.vue'

defineProps<{
    categories: App.Data.CategoryData[]
}>()
</script>

<template>
    <Form :categories="categories" />
</template>