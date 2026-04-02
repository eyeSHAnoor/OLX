<script setup lang="ts">
import Form from './_partials/Form.vue'

defineProps<{
    category: App.Data.CategoryData & {
        brands?: App.Data.BrandData[]
        attributes?: Array<App.Data.CategoryAttributeData & {
            options?: App.Data.AttributeOptionData[]
        }>
        files?: Array<{ file_url: string }>
    }
    allCategories: App.Data.CategoryData[]
    allBrands: App.Data.BrandData[]
    attributeGroups: App.Data.AttributeGroupData[]
}>()
</script>

<template>
    <Form :category="category" :allCategories="allCategories" :allBrands="allBrands"
        :attributeGroups="attributeGroups" />
</template>